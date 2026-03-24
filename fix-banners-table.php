<?php
/**
 * Fix banners table - add missing columns
 * DELETE after running!
 */
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
$conn = getConnection();

echo "<h2>Fixing Banners Table</h2><pre>";

// Get current columns
$cols = $conn->query("SHOW COLUMNS FROM banners")->fetchAll(PDO::FETCH_COLUMN);
echo "Current columns: " . implode(', ', $cols) . "\n\n";

// Required columns and their SQL
$required = [
    'image'       => "ADD COLUMN `image` varchar(255) NOT NULL AFTER `id`",
    'title'       => "ADD COLUMN `title` varchar(255) DEFAULT NULL AFTER `image`",
    'subtitle'    => "ADD COLUMN `subtitle` varchar(500) DEFAULT NULL AFTER `title`",
    'button_text' => "ADD COLUMN `button_text` varchar(100) DEFAULT 'Learn More' AFTER `subtitle`",
    'button_link' => "ADD COLUMN `button_link` varchar(255) DEFAULT '#' AFTER `button_text`",
    'status'      => "ADD COLUMN `status` enum('active','inactive') DEFAULT 'active' AFTER `button_link`",
    'sort_order'  => "ADD COLUMN `sort_order` int(11) DEFAULT 0 AFTER `status`",
    'created_at'  => "ADD COLUMN `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `sort_order`",
];

$fixed = 0;
foreach ($required as $col => $sql) {
    if (!in_array($col, $cols)) {
        try {
            $conn->exec("ALTER TABLE `banners` $sql");
            echo "✅ Added missing column: $col\n";
            $fixed++;
        } catch (Exception $e) {
            echo "❌ Failed to add $col: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✓  Column exists: $col\n";
    }
}

// Show final state
echo "\n=== Final Table Structure ===\n";
$structure = $conn->query("DESCRIBE banners")->fetchAll(PDO::FETCH_ASSOC);
foreach ($structure as $col) {
    echo $col['Field'] . " | " . $col['Type'] . " | " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " | Default: " . ($col['Default'] ?? 'none') . "\n";
}

// Show existing data
echo "\n=== Existing Banners ===\n";
$banners = $conn->query("SELECT * FROM banners")->fetchAll(PDO::FETCH_ASSOC);
echo count($banners) . " banners found\n";
foreach ($banners as $b) {
    echo "ID: " . $b['id'] . " | Image: " . ($b['image'] ?? 'null') . " | Status: " . ($b['status'] ?? 'null') . "\n";
}

echo "\n";
if ($fixed > 0) {
    echo "✅ Fixed $fixed missing columns!\n";
} else {
    echo "✅ All columns are correct!\n";
}

echo "\n</pre>";
echo "<p><a href='admin/banners.php'>→ Go to Banners</a> | <a href='api/banners.php'>→ Test API</a></p>";
echo "<p style='color:red;'>⚠️ DELETE this file after running!</p>";
