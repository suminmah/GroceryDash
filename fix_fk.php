<?php
require_once __DIR__ . '/backend/config/database.php';

try {
    $db = Database::connect();
    
    // Drop the incorrect foreign key constraint
    $db->exec("ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1");
    echo "Dropped old foreign key constraint.\n";
    
    // Add the correct foreign key constraint pointing to the 'users' table
    $db->exec("ALTER TABLE orders ADD CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
    echo "Successfully added correct foreign key constraint pointing to users(id).\n";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
