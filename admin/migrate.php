<?php
/**
 * Database Migration Script
 * Run this once to update the database schema
 */

require_once __DIR__ . '/../config/database.php';

echo "<h2>Database Migration</h2>";
echo "<pre>";

try {
    $conn = getConnection();

    // Create categories table if not exists
    echo "Creating categories table...\n";
    $conn->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Categories table ready\n\n";

    // Add new columns to products table if they don't exist
    echo "Updating products table...\n";

    // Check if category_id column exists
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'category_id'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE products ADD COLUMN category_id INT AFTER name");
        echo "✓ Added category_id column\n";
    } else {
        echo "- category_id column already exists\n";
    }

    // Check if description column exists
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'description'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE products ADD COLUMN description TEXT AFTER category_id");
        echo "✓ Added description column\n";
    } else {
        echo "- description column already exists\n";
    }

    // Check if size column exists
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'size'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE products ADD COLUMN size VARCHAR(50) AFTER image");
        echo "✓ Added size column\n";
    } else {
        echo "- size column already exists\n";
    }

    // Check if status column exists
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'status'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER size");
        echo "✓ Added status column\n";
    } else {
        echo "- status column already exists\n";
    }

    // Check if updated_at column exists
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'updated_at'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE products ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "✓ Added updated_at column\n";
    } else {
        echo "- updated_at column already exists\n";
    }

    echo "\n";

    // Insert default categories if empty
    echo "Checking categories...\n";
    $count = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($count == 0) {
        $categories = [
            'Thamboolam Bags',
            'Wedding Invitation Cards',
            'Flex Banners',
            'Posters',
            'Medals & Trophies',
            'UV Prints',
            'Custom Designs'
        ];

        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat]);
        }
        echo "✓ Added " . count($categories) . " default categories\n";
    } else {
        echo "- Categories already exist ($count found)\n";
    }

    echo "\n<strong style='color: green;'>Migration completed successfully!</strong>\n";
    echo "\n<a href='index.php'>Go to Admin Dashboard</a>";

} catch (PDOException $e) {
    echo "<strong style='color: red;'>Error: " . $e->getMessage() . "</strong>\n";
}

echo "</pre>";
?>
