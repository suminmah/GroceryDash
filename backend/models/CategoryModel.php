<?php
// ============================================================
//  backend/models/CategoryModel.php
//  Table: Categories (category_id, name, parent_id)
//  Supports self-referencing parent → sub-category tree
// ============================================================

require_once __DIR__ . '/../config/database.php';

class CategoryModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  flat lists
    // ─────────────────────────────────────────────────────────

    /**
     * Return every category row (no hierarchy — raw flat list).
     * Used by: admin dropdowns, filters
     *
     * @return array[]
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT id, name, slug, parent_id, sort_order, is_active
             FROM   Categories
             ORDER  BY name ASC"
        )->fetchAll();
    }

    /**
     * Return only top-level (root) categories where parent_id IS NULL.
     * Used by: homepage category grid, main navigation
     *
     * @return array[]
     */
    public function getRootCategories(): array
    {
        return $this->db->query(
            "SELECT id, name, slug, parent_id
             FROM   Categories
             WHERE  parent_id IS NULL"
        )->fetchAll();
    }

    /**
     * Return direct children of a given parent category.
     * Used by: category landing page, breadcrumb expansion
     *
     * @return array[]
     */
    public function getSubCategories(int $parentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, parent_id
             FROM   Categories
             WHERE  parent_id = :pid
             ORDER  BY name ASC"
        );
        $stmt->execute([':pid' => $parentId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a category by its slug.
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, slug, parent_id
            FROM   categories
            WHERE  slug = :slug
            LIMIT  1"
        );
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  single row
    // ─────────────────────────────────────────────────────────

    /**
     * Find a single category by its primary key.
     * Used by: product listing, breadcrumbs
     *
     * @return array|null
     */
    public function findById(int $categoryId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, parent_id
             FROM   Categories
             WHERE  id = :id
             LIMIT  1"
        );
        $stmt->execute([':id' => $categoryId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find a single category by its name (case-insensitive).
     * Used by: import / seed scripts
     *
     * @return array|null
     */
    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, parent_id
             FROM   Categories
             WHERE  LOWER(name) = LOWER(:name)
             LIMIT  1"
        );
        $stmt->execute([':name' => $name]);
        return $stmt->fetch() ?: null;
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  tree / hierarchy
    // ─────────────────────────────────────────────────────────

    /**
     * Build a full nested tree of categories.
     * Returns root categories, each with a 'children' key
     * containing their sub-categories.
     *
     * Used by: mega-menu, admin category manager
     *
     * @return array[]  Tree structure
     */
    public function getTree(): array
    {
        $all = $this->getAll();

        // Index all rows by id
        $indexed = [];
        foreach ($all as $row) {
            $row['children']                  = [];
            $indexed[$row['id']]     = $row;
        }

        $tree = [];
        foreach ($indexed as $id => $row) {
            if ($row['parent_id'] === null) {
                $tree[] = &$indexed[$id];
            } else {
                $indexed[$row['parent_id']]['children'][] = &$indexed[$id];
            }
        }
        return $tree;
    }

    /**
     * Return the breadcrumb path for a category (leaf → root).
     * Used by: product detail breadcrumb, SEO breadcrumb schema
     *
     * Example: [Dairy & Eggs] → [Food] for a sub-category
     *
     * @return array[]  Ordered root-first list
     */
    public function getBreadcrumb(int $categoryId): array
    {
        $path = [];
        $id   = $categoryId;

        while ($id !== null) {
            $cat = $this->findById($id);
            if (!$cat) break;
            array_unshift($path, $cat);   // prepend so root comes first
            $id = $cat['parent_id'];
        }
        return $path;
    }

    /**
     * Collect all descendant category IDs (children + grandchildren…)
     * for use in product queries that should include sub-categories.
     *
     * Used by: shop filter when a parent category is selected
     *
     * @return int[]  Array of category IDs including $categoryId itself
     */
    public function getAllDescendantIds(int $categoryId): array
    {
        $ids      = [$categoryId];
        $children = $this->getSubCategories($categoryId);

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getAllDescendantIds((int)$child['id']));
        }
        return $ids;
    }

    // ─────────────────────────────────────────────────────────
    //  WRITE  —  create / update / delete
    // ─────────────────────────────────────────────────────────

    /**
     * Create a new category.
     *
     * @param  array{name:string, slug:string, parent_id:int|null, created_by:int|null} $data
     * @return bool
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO categories (name, slug, parent_id, created_by, created_at) 
                VALUES (:name, :slug, :parent_id, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);

        // Use explicit parameter binding to gracefully handle the structural NULL transition
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', $data['slug'], PDO::PARAM_STR);
        
        // Bind as NULL if parent_id is missing, otherwise bind as an integer
        if ($data['parent_id'] === null) {
            $stmt->bindValue(':parent_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':parent_id', $data['parent_id'], PDO::PARAM_INT);
        }

        // Bind your administrative creator logging field safely
        if (($data['created_by'] ?? null) === null) {
            $stmt->bindValue(':created_by', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':created_by', (int)$data['created_by'], PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    /**
     * Rename a category (parent_id can be re-assigned too).
     *
     * @return bool
     */
    public function update(int $categoryId, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Categories
             SET    name      = :name,
                    parent_id = :pid
             WHERE  id = :id"
        );
        return $stmt->execute([
            ':name' => trim($data['name']),
            ':pid'  => $data['parent_id'] ?? null,
            ':id'   => $categoryId,
        ]);
    }

    /**
     * Delete a category by ID.
     * Children will have their parent_id set to NULL (per FK constraint).
     *
     * @return bool
     */
    public function delete(int $categoryId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM Categories WHERE id = :id"
        );
        return $stmt->execute([':id' => $categoryId]);
    }

    public function getAllCategories(): array
    {
        $sql = "SELECT 
                    cat.*, 
                    c.name AS creator_name
                FROM categories cat
                LEFT JOIN users u ON cat.created_by = u.id
                LEFT JOIN customers c ON c.user_id = u.id
                ORDER BY cat.name ASC";
                
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
