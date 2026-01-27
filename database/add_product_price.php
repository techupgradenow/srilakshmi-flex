<?php
/**
 * Migration: Add price column to products table
 */

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    // Check if column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'price'");
    if ($stmt->rowCount() == 0) {
        // Add price column with default value
        $conn->exec("ALTER TABLE products ADD COLUMN price DECIMAL(10, 2) DEFAULT 0.00 AFTER size");
        echo "Successfully added price column to products table.\n";
    } else {
        echo "price column already exists in products table.\n";
    }

    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
