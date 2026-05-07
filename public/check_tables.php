<?php
/**
 * check_tables.php
 * ─────────────────────────────────────────────────────────
 * Place in: grocery-shop/public/check_tables.php
 * Visit:    http://localhost/grocery-shop/public/check_tables.php
 * DELETE after use.
 * ─────────────────────────────────────────────────────────
 * Shows the exact column names of every relevant table.
 */
require_once __DIR__ . '/../backend/config/database.php';

$db     = Database::connect();
$tables = ['orders', 'Order_Items', 'order_items', 'Products', 'products',
           'Delivery_Slots', 'delivery_slots', 'users', 'Categories',
           'categories', 'Inventory', 'inventory'];

$found = [];

echo '<style>
  body  { font-family: monospace; background:#0f0f0f; color:#e2e8f0; padding:2rem; }
  h2    { color:#4ade80; margin-top:2rem; }
  table { border-collapse:collapse; width:100%; margin-bottom:1rem; }
  th    { background:#1e293b; color:#94a3b8; padding:.5rem 1rem; text-align:left; }
  td    { padding:.4rem 1rem; border-bottom:1px solid #1e293b; }
  .pk   { color:#fbbf24; }
  .null { color:#64748b; }
  .err  { color:#f87171; }
</style>';

echo '<h1 style="color:#4ade80">📋 Table Structure Inspector</h1>';

// First: show ALL tables in the DB
$allTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo '<h2>All tables in freshcart database</h2>';
echo '<p style="color:#94a3b8">' . implode(', ', $allTables) . '</p>';

// Then describe each one
foreach ($allTables as $table) {
    echo "<h2>$table</h2><table>";
    echo '<tr><th>#</th><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
    try {
        $cols = $db->query("DESCRIBE `$table`")->fetchAll();
        foreach ($cols as $i => $col) {
            $isPk  = $col['Key'] === 'PRI';
            $isNull = $col['Null'] === 'YES';
            echo '<tr>';
            echo '<td>' . ($i + 1) . '</td>';
            echo '<td class="' . ($isPk ? 'pk' : '') . '">' . $col['Field'] . ($isPk ? ' 🔑' : '') . '</td>';
            echo '<td>' . $col['Type'] . '</td>';
            echo '<td class="' . ($isNull ? 'null' : '') . '">' . $col['Null'] . '</td>';
            echo '<td>' . $col['Key'] . '</td>';
            echo '<td>' . ($col['Default'] ?? '<em>none</em>') . '</td>';
            echo '</tr>';
        }
    } catch (Exception $e) {
        echo '<tr><td colspan="6" class="err">Error: ' . $e->getMessage() . '</td></tr>';
    }
    echo '</table>';
}