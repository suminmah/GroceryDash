<?php
// backend/models/Setting.php

class Setting {
    // 1. Switched type-hinting to PDO to match your Database class engine
    private PDO $db; 
    private string $table = 'site_settings';

    /**
     * Constructor expects a valid PDO instance
     */
    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Fetch a configuration value by its unique string key
     */
    public function get(string $key): ?string {
        $query = "SELECT `setting_value` FROM {$this->table} WHERE `setting_key` = :setting_key LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        
        // PDO allows executing by passing an associative array directly into execute()
        $stmt->execute([':setting_key' => $key]);
        
        $row = $stmt->fetch(); // Default fetch mode is already FETCH_ASSOC from your config
        
        return $row ? $row['setting_value'] : null;
    }

    /**
     * Update or create a configuration value dynamically
     */
    public function set(string $key, string $value): bool {
        $query = "INSERT INTO {$this->table} (`setting_key`, `setting_value`) 
                  VALUES (:setting_key, :setting_value) 
                  ON DUPLICATE KEY UPDATE `setting_value` = :update_value";
                  
        $stmt = $this->db->prepare($query);
        
        // Execute with mapped parameters safely matching named placeholders
        return $stmt->execute([
            ':setting_key'   => $key,
            ':setting_value' => $value,
            ':update_value'  => $value
        ]);
    }
}