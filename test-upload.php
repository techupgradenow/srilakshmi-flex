<?php
/**
 * FILE UPLOAD DEBUG
 * Tests upload directory and file upload functionality
 * DELETE AFTER DEBUGGING
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>File Upload Test</h1>";
echo "<pre>";

// Step 1: Check paths
echo "=== STEP 1: Path Information ===\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Directory: " . __DIR__ . "\n";
echo "Current Working Dir: " . getcwd() . "\n";

// Step 2: Check upload directories
echo "\n=== STEP 2: Upload Directory Check ===\n";
$uploadDir = __DIR__ . '/uploads';
$productDir = __DIR__ . '/uploads/products';

// uploads/
if (is_dir($uploadDir)) {
    echo "✓ uploads/ EXISTS\n";
    echo "  Path: $uploadDir\n";
    echo "  Readable: " . (is_readable($uploadDir) ? "YES" : "NO") . "\n";
    echo "  Writable: " . (is_writable($uploadDir) ? "YES" : "NO") . "\n";
    echo "  Permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n";
} else {
    echo "✗ uploads/ NOT FOUND\n";
    echo "  Attempting to create...\n";
    if (@mkdir($uploadDir, 0755, true)) {
        echo "  ✓ Created successfully\n";
    } else {
        echo "  ✗ Failed to create - check parent permissions\n";
    }
}

// uploads/products/
if (is_dir($productDir)) {
    echo "✓ uploads/products/ EXISTS\n";
    echo "  Path: $productDir\n";
    echo "  Readable: " . (is_readable($productDir) ? "YES" : "NO") . "\n";
    echo "  Writable: " . (is_writable($productDir) ? "YES" : "NO") . "\n";
    echo "  Permissions: " . substr(sprintf('%o', fileperms($productDir)), -4) . "\n";
} else {
    echo "✗ uploads/products/ NOT FOUND\n";
    echo "  Attempting to create...\n";
    if (@mkdir($productDir, 0755, true)) {
        echo "  ✓ Created successfully\n";
    } else {
        echo "  ✗ Failed to create\n";
    }
}

// Step 3: List existing files
echo "\n=== STEP 3: Existing Uploaded Files ===\n";
if (is_dir($productDir)) {
    $files = scandir($productDir);
    $files = array_diff($files, ['.', '..']);
    echo "Files in uploads/products/: " . count($files) . "\n";
    foreach ($files as $file) {
        $fullPath = $productDir . '/' . $file;
        echo "  - $file (" . filesize($fullPath) . " bytes)\n";
    }
} else {
    echo "Cannot list - directory doesn't exist\n";
}

// Step 4: PHP Upload Settings
echo "\n=== STEP 4: PHP Upload Settings ===\n";
echo "file_uploads: " . ini_get('file_uploads') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) . "\n";

// Step 5: Test file write
echo "\n=== STEP 5: Write Test ===\n";
$testFile = $productDir . '/test_write_' . time() . '.txt';
if (@file_put_contents($testFile, 'test')) {
    echo "✓ Can write to uploads/products/\n";
    unlink($testFile);
    echo "✓ Cleaned up test file\n";
} else {
    echo "✗ Cannot write to uploads/products/\n";
    echo "  Error: " . error_get_last()['message'] . "\n";
}

echo "</pre>";

// Step 6: Upload form test
echo "\n<h2>Step 6: Upload Test Form</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<pre>";
    echo "=== UPLOAD ATTEMPT ===\n";
    echo "POST data received:\n";
    print_r($_POST);
    echo "\nFILES data received:\n";
    print_r($_FILES);

    $file = $_FILES['test_image'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        echo "\n✓ File uploaded to temp: " . $file['tmp_name'] . "\n";
        echo "  Original name: " . $file['name'] . "\n";
        echo "  Size: " . $file['size'] . " bytes\n";
        echo "  Type: " . $file['type'] . "\n";

        // Try to move
        $newName = 'test_' . time() . '_' . $file['name'];
        $destination = $productDir . '/' . $newName;
        echo "\nAttempting move to: $destination\n";

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo "✓ File moved successfully!\n";
            echo "✓ File exists: " . (file_exists($destination) ? "YES" : "NO") . "\n";
            echo "\nAccess URL: uploads/products/$newName\n";

            // Show image
            echo "</pre>";
            echo "<p><strong>Uploaded Image:</strong></p>";
            echo "<img src='uploads/products/$newName' style='max-width:300px;border:1px solid #ccc;'>";
            echo "<pre>";
        } else {
            echo "✗ move_uploaded_file FAILED\n";
            echo "  Error: " . error_get_last()['message'] . "\n";
        }
    } else {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (php.ini limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temp directory',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension blocked upload'
        ];
        echo "✗ Upload error: " . ($errors[$file['error']] ?? 'Unknown error') . "\n";
    }
    echo "</pre>";
}
?>

<form method="POST" enctype="multipart/form-data" style="margin:20px;padding:20px;border:1px solid #ccc;background:#f9f9f9;">
    <p><strong>Test Image Upload:</strong></p>
    <input type="file" name="test_image" accept="image/*" required>
    <button type="submit" style="padding:10px 20px;background:#ffc107;border:none;cursor:pointer;">Upload Test Image</button>
</form>

<pre>
=== UPLOAD TEST COMPLETE ===
</pre>
<p><a href="test-products.php">Next: Test Products Display →</a></p>
