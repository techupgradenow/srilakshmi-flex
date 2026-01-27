<?php
/**
 * Migration: Add image column to categories table
 */

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    // Check if column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM categories LIKE 'image'");
    if ($stmt->rowCount() == 0) {
        // Add image column
        $conn->exec("ALTER TABLE categories ADD COLUMN image VARCHAR(255) NULL AFTER name");
        echo "Successfully added image column to categories table.\n";
    } else {
        echo "Image column already exists in categories table.\n";
    }

    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
