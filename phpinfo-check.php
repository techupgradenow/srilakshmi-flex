<?php
header('Content-Type: application/json');
echo json_encode([
    'php_version' => PHP_VERSION,
    'error_log' => ini_get('error_log'),
    'display_errors' => ini_get('display_errors'),
    'file_check' => [
        'add-banner' => file_exists(__DIR__ . '/admin/add-banner.php') ? filesize(__DIR__ . '/admin/add-banner.php') . ' bytes' : 'MISSING',
        'banners' => file_exists(__DIR__ . '/admin/banners.php') ? filesize(__DIR__ . '/admin/banners.php') . ' bytes' : 'MISSING',
        'edit-banner' => file_exists(__DIR__ . '/admin/edit-banner.php') ? filesize(__DIR__ . '/admin/edit-banner.php') . ' bytes' : 'MISSING',
        'delete-banner' => file_exists(__DIR__ . '/admin/delete-banner.php') ? filesize(__DIR__ . '/admin/delete-banner.php') . ' bytes' : 'MISSING',
        'toggle-banner' => file_exists(__DIR__ . '/admin/toggle-banner.php') ? filesize(__DIR__ . '/admin/toggle-banner.php') . ' bytes' : 'MISSING',
        'api-banners' => file_exists(__DIR__ . '/api/banners.php') ? filesize(__DIR__ . '/api/banners.php') . ' bytes' : 'MISSING',
        'login' => file_exists(__DIR__ . '/admin/login.php') ? filesize(__DIR__ . '/admin/login.php') . ' bytes' : 'MISSING',
    ],
    'last_error' => error_get_last()
], JSON_PRETTY_PRINT);
