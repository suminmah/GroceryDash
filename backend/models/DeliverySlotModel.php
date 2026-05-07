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
    public function getAvailableSlots(?string $date = null): array
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $stmt = $this->db->prepare(
            "SELECT slot_id, slot_date, start_time, end_time, capacity, booked,
                    (capacity - booked) AS available
             FROM delivery_slots
             WHERE slot_date >= :date
               AND is_active = 1
               AND capacity > booked
             ORDER BY slot_date ASC, start_time ASC"
        );
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll();
    }

    /**
     * Check if a specific slot is still available
     */
    public function isSlotAvailable(int $slotId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM delivery_slots 
             WHERE slot_id = :id 
               AND is_active = 1 
               AND capacity > booked
               AND slot_date >= CURDATE()"
        );
        $stmt->execute([':id' => $slotId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Get a single slot by ID
     */
    public function findById(int $slotId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM delivery_slots WHERE slot_id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $slotId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Get all slots (admin view)
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT * FROM delivery_slots ORDER BY slot_date ASC, start_time ASC"
        )->fetchAll();
    }

    /**
     * Get capacity summary for date range
     */
    public function getCapacitySummaryByDate(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT slot_date, 
                    SUM(capacity) AS total_capacity, 
                    SUM(booked) AS total_booked
             FROM delivery_slots
             WHERE slot_date BETWEEN :from AND :to
             GROUP BY slot_date
             ORDER BY slot_date ASC"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetchAll();
    }

    /**
     * Increment booked count for a slot (when order is placed)
     */
    public function incrementBooked(int $slotId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE delivery_slots 
             SET booked = booked + 1 
             WHERE slot_id = :id AND capacity > booked"
        );
        return $stmt->execute([':id' => $slotId]);
    }

    /**
     * Decrement booked count (when order is cancelled)
     */
    public function decrementBooked(int $slotId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE delivery_slots 
             SET booked = GREATEST(booked - 1, 0)
             WHERE slot_id = :id"
        );
        return $stmt->execute([':id' => $slotId]);
    }

    /**
     * Create a new delivery slot
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO delivery_slots (slot_date, start_time, end_time, capacity, booked, is_active)
             VALUES (:date, :start, :end, :capacity, :booked, :is_active)"
        );
        $stmt->execute([
            ':date' => $data['slot_date'],
            ':start' => $data['start_time'],
            ':end' => $data['end_time'],
            ':capacity' => $data['capacity'] ?? 10,
            ':booked' => $data['booked'] ?? 0,
            ':is_active' => $data['is_active'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete a slot
     */
    public function delete(int $slotId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM delivery_slots WHERE slot_id = :id");
        return $stmt->execute([':id' => $slotId]);
    }
}
