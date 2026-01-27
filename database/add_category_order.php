<?php
/**
 * Migration: Add display_order column to categories table
 */

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    // Check if column already exists
    $stmt = $conn->query("SHOW COLUMNS FROM categories LIKE 'display_order'");
    if ($stmt->rowCount() == 0) {
        // Add display_order column with default value
        $conn->exec("ALTER TABLE categories ADD COLUMN display_order INT NOT NULL DEFAULT 0 AFTER status");
        echo "Successfully added display_order column to categories table.\n";

        // Set initial order values based on current created_at
        $conn->exec("SET @row_number = 0");
        $conn->exec("UPDATE categories SET display_order = (@row_number:=@row_number + 1) ORDER BY created_at ASC");
        echo "Successfully set initial display order values.\n";
    } else {
        echo "display_order column already exists in categories table.\n";
    }

    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
