<?php
/**
 * PRODUCTS FETCH DEBUG
 * Tests product retrieval for frontend display
 * DELETE AFTER DEBUGGING
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Products Fetch Test</h1>";
echo "<pre>";

// Step 1: Database connection
echo "=== STEP 1: Database Connection ===\n";
require_once __DIR__ . '/config/database.php';

try {
    $conn = getConnection();
    echo "✓ Database connected\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Step 2: Check products table structure
echo "\n=== STEP 2: Products Table Structure ===\n";
try {
    $columns = $conn->query("SHOW COLUMNS FROM products")->fetchAll();
    echo "Columns in products table:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Step 3: Count all products
echo "\n=== STEP 3: Product Counts ===\n";
try {
    $total = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "Total products: $total\n";

    $active = $conn->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    echo "Active products: $active\n";

    $inactive = $conn->query("SELECT COUNT(*) FROM products WHERE status = 'inactive'")->fetchColumn();
    echo "Inactive products: $inactive\n";

    $noStatus = $conn->query("SELECT COUNT(*) FROM products WHERE status IS NULL")->fetchColumn();
    echo "NULL status products: $noStatus\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Step 4: Test the exact query from products.php
echo "\n=== STEP 4: Exact Frontend Query Test ===\n";
try {
    $query = "
        SELECT p.*, COALESCE(c.name, 'Other Products') as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'
        ORDER BY
            CASE WHEN c.name IS NULL THEN 1 ELSE 0 END,
            c.name ASC,
            p.created_at DESC
    ";
    echo "Query:\n$query\n\n";

    $products = $conn->query($query)->fetchAll();
    echo "✓ Query executed successfully\n";
    echo "Products returned: " . count($products) . "\n";
} catch (PDOException $e) {
    echo "✗ Query FAILED: " . $e->getMessage() . "\n";
    $products = [];
}

// Step 5: Display products with image path check
echo "\n=== STEP 5: Product Details with Image Check ===\n";
if (count($products) > 0) {
    foreach ($products as $i => $product) {
        echo "\n--- Product " . ($i + 1) . " ---\n";
        echo "ID: {$product['id']}\n";
        echo "Name: {$product['name']}\n";
        echo "Category: {$product['category_name']}\n";
        echo "Status: {$product['status']}\n";
        echo "Image DB value: {$product['image']}\n";

        // Check image paths
        $imagePath = '';
        if (!empty($product['image'])) {
            $path1 = __DIR__ . '/uploads/products/' . $product['image'];
            $path2 = __DIR__ . '/' . $product['image'];

            echo "Checking path 1: $path1\n";
            if (file_exists($path1)) {
                echo "  ✓ EXISTS at uploads/products/\n";
                $imagePath = 'uploads/products/' . $product['image'];
            } else {
                echo "  ✗ NOT FOUND\n";
            }

            echo "Checking path 2: $path2\n";
            if (file_exists($path2)) {
                echo "  ✓ EXISTS at root\n";
                $imagePath = $product['image'];
            } else {
                echo "  ✗ NOT FOUND\n";
            }

            if ($imagePath) {
                echo "Final image path: $imagePath\n";
            } else {
                echo "⚠ IMAGE NOT FOUND ANYWHERE\n";
            }
        } else {
            echo "⚠ No image set in database\n";
        }
    }
} else {
    echo "No active products found!\n";
    echo "\nChecking ALL products (ignoring status):\n";
    $allProducts = $conn->query("SELECT id, name, status, image FROM products")->fetchAll();
    foreach ($allProducts as $p) {
        echo "  ID:{$p['id']} | {$p['name']} | Status:{$p['status']} | Image:{$p['image']}\n";
    }
}

// Step 6: Categories check
echo "\n=== STEP 6: Categories Check ===\n";
try {
    $categories = $conn->query("SELECT * FROM categories")->fetchAll();
    echo "Categories found: " . count($categories) . "\n";
    foreach ($categories as $cat) {
        echo "  - ID:{$cat['id']} | {$cat['name']} | Status:{$cat['status']}\n";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== PRODUCTS TEST COMPLETE ===\n";
echo "</pre>";

// Visual test
if (count($products) > 0) {
    echo "<h2>Visual Preview</h2>";
    echo "<div style='display:flex;flex-wrap:wrap;gap:20px;'>";
    foreach ($products as $product) {
        $imagePath = '';
        if (!empty($product['image'])) {
            if (file_exists(__DIR__ . '/uploads/products/' . $product['image'])) {
                $imagePath = 'uploads/products/' . $product['image'];
            } elseif (file_exists(__DIR__ . '/' . $product['image'])) {
                $imagePath = $product['image'];
            }
        }

        echo "<div style='border:1px solid #ccc;padding:10px;width:200px;'>";
        if ($imagePath) {
            echo "<img src='$imagePath' style='width:100%;height:150px;object-fit:cover;'>";
        } else {
            echo "<div style='width:100%;height:150px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;'>No Image</div>";
        }
        echo "<p><strong>{$product['name']}</strong></p>";
        echo "<p style='font-size:12px;color:#666;'>{$product['category_name']}</p>";
        echo "</div>";
    }
    echo "</div>";
}
?>

<p style="margin-top:30px;"><a href="test-all.php">Run All Tests →</a></p>
