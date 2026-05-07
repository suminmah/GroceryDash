<?php
// ============================================================
//  backend/models/DeliverySlotModel.php
//  Table: Delivery_Slots (slot_id, slot_date, start_time,
//                         end_time, max_capacity)
// ============================================================

require_once __DIR__ . '/../config/database.php';

class DeliverySlotModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  single row
    // ─────────────────────────────────────────────────────────

    /**
     * Fetch one delivery slot by primary key.
     * Includes how many orders have already booked this slot.
     * Used by: checkout slot picker, admin slot detail
     *
     * @return array|null
     */
    public function findById(int $slotId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT  ds.slot_id,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     ds.max_capacity,
                     COUNT(o.order_id)                      AS booked_count,
                     ds.max_capacity - COUNT(o.order_id)    AS remaining_capacity
             FROM    Delivery_Slots ds
             LEFT    JOIN Orders o
                     ON  o.delivery_slot_id = ds.slot_id
                     AND o.status NOT IN ('Cancelled')
             WHERE   ds.slot_id = :id
             GROUP   BY ds.slot_id
             LIMIT   1"
        );
        $stmt->execute([':id' => $slotId]);
        return $stmt->fetch() ?: null;
    }

    // ─────────────────────────────────────────────────────────
    //  READ  —  list / availability
    // ─────────────────────────────────────────────────────────

    /**
     * Return all slots for a specific date, each annotated with
     * how many orders are booked and how many places remain.
     * Used by: checkout slot picker calendar
     *
     * @param  string $date  'YYYY-MM-DD'
     * @return array[]
     */
    public function getByDate(string $date): array
    {
        $stmt = $this->db->prepare(
            "SELECT  ds.slot_id,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     ds.max_capacity,
                     COUNT(o.order_id)                   AS booked_count,
                     ds.max_capacity - COUNT(o.order_id) AS remaining_capacity,
                     CASE
                         WHEN COUNT(o.order_id) >= ds.max_capacity THEN 1
                         ELSE 0
                     END AS is_full
             FROM    Delivery_Slots ds
             LEFT    JOIN Orders o
                     ON  o.delivery_slot_id = ds.slot_id
                     AND o.status NOT IN ('Cancelled')
             WHERE   ds.slot_date = :date
             GROUP   BY ds.slot_id
             ORDER   BY ds.start_time ASC"
        );
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll();
    }

    /**
     * Return available (not fully booked) slots from today onward.
     * Used by: checkout — show only bookable slots to the customer
     *
     * @param  int $daysAhead  How many future days to look ahead (default 7)
     * @return array[]  Grouped by slot_date
     */
    public function getAvailable(int $daysAhead = 7): array
    {
        $stmt = $this->db->prepare(
            "SELECT  ds.slot_id,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     ds.max_capacity,
                     COUNT(o.order_id)                   AS booked_count,
                     ds.max_capacity - COUNT(o.order_id) AS remaining_capacity
             FROM    Delivery_Slots ds
             LEFT    JOIN Orders o
                     ON  o.delivery_slot_id = ds.slot_id
                     AND o.status NOT IN ('Cancelled')
             WHERE   ds.slot_date BETWEEN CURDATE()
                     AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
             GROUP   BY ds.slot_id
             HAVING  remaining_capacity > 0
             ORDER   BY ds.slot_date ASC, ds.start_time ASC"
        );
        $stmt->bindValue(':days', $daysAhead, PDO::PARAM_INT);
        $stmt->execute();

        // Group results by date for easy frontend rendering
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[$row['slot_date']][] = $row;
        }
        return $grouped;
    }

    /**
     * Return all slots (past + future) — admin view.
     * Used by: admin slot management page
     *
     * @return array[]
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT  ds.slot_id,
                     ds.slot_date,
                     ds.start_time,
                     ds.end_time,
                     ds.max_capacity,
                     COUNT(o.order_id) AS booked_count
             FROM    Delivery_Slots ds
             LEFT    JOIN Orders o
                     ON  o.delivery_slot_id = ds.slot_id
                     AND o.status NOT IN ('Cancelled')
             GROUP   BY ds.slot_id
             ORDER   BY ds.slot_date DESC, ds.start_time ASC"
        )->fetchAll();
    }

    /**
     * Check whether a specific slot still has capacity for one more order.
     * Used by: checkout POST validation before accepting an order
     *
     * @return bool
     */
    public function hasCapacity(int $slotId): bool
    {
        $slot = $this->findById($slotId);
        return $slot && ((int) $slot['remaining_capacity'] > 0);
    }

    /**
     * Return slot count per date for a date range.
     * Used by: admin calendar / availability heatmap
     *
     * @return array[]  [{slot_date, total_slots, total_capacity, booked_count}]
     */
    public function getCapacitySummaryByDate(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT  ds.slot_date,
                     COUNT(DISTINCT ds.slot_id)           AS total_slots,
                     SUM(ds.max_capacity)                 AS total_capacity,
                     COUNT(o.order_id)                    AS booked_count,
                     SUM(ds.max_capacity) - COUNT(o.order_id) AS remaining
             FROM    Delivery_Slots ds
             LEFT    JOIN Orders o
                     ON  o.delivery_slot_id = ds.slot_id
                     AND o.status NOT IN ('Cancelled')
             WHERE   ds.slot_date BETWEEN :from AND :to
             GROUP   BY ds.slot_date
             ORDER   BY ds.slot_date ASC"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    //  WRITE  —  create / update / delete
    // ─────────────────────────────────────────────────────────

    /**
     * Create a new delivery slot.
     *
     * @param  array{slot_date:string, start_time:string,
     *                end_time:string, max_capacity:int} $data
     * @return int  New slot_id
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Delivery_Slots (slot_date, start_time, end_time, max_capacity)
             VALUES (:date, :start, :end, :cap)"
        );
        $stmt->execute([
            ':date'  => $data['slot_date'],
            ':start' => $data['start_time'],
            ':end'   => $data['end_time'],
            ':cap'   => (int) $data['max_capacity'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Bulk-create slots for a date range (e.g. generate a week's worth).
     * Each date gets the same time slots passed in $slotTemplates.
     *
     * @param  string   $fromDate  'YYYY-MM-DD'
     * @param  string   $toDate    'YYYY-MM-DD'
     * @param  array[]  $slotTemplates  [{start_time, end_time, max_capacity}, …]
     * @return int  Number of slots created
     */
    public function bulkCreateForDateRange(
        string $fromDate,
        string $toDate,
        array  $slotTemplates
    ): int {
        $stmt    = $this->db->prepare(
            "INSERT INTO Delivery_Slots (slot_date, start_time, end_time, max_capacity)
             VALUES (:date, :start, :end, :cap)"
        );
        $created = 0;
        $current = new DateTime($fromDate);
        $end     = new DateTime($toDate);

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            foreach ($slotTemplates as $tpl) {
                $stmt->execute([
                    ':date'  => $dateStr,
                    ':start' => $tpl['start_time'],
                    ':end'   => $tpl['end_time'],
                    ':cap'   => (int) $tpl['max_capacity'],
                ]);
                $created++;
            }
            $current->modify('+1 day');
        }
        return $created;
    }

    /**
     * Update an existing slot's capacity or times.
     *
     * @return bool
     */
    public function update(int $slotId, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Delivery_Slots
             SET    slot_date    = :date,
                    start_time   = :start,
                    end_time     = :end,
                    max_capacity = :cap
             WHERE  slot_id = :id"
        );
        return $stmt->execute([
            ':date'  => $data['slot_date'],
            ':start' => $data['start_time'],
            ':end'   => $data['end_time'],
            ':cap'   => (int) $data['max_capacity'],
            ':id'    => $slotId,
        ]);
    }

    /**
     * Delete a slot (only safe if no orders reference it).
     *
     * @return bool
     */
    public function delete(int $slotId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM Delivery_Slots WHERE slot_id = :id"
        );
        return $stmt->execute([':id' => $slotId]);
    }
}
