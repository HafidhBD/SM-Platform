<?php
/**
 * Setup Script - Run this once after deploying to configure the database
 * 
 * Usage: php setup.php
 * Or visit: https://yourdomain.com/setup.php (delete after setup!)
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/app/core/Database.php';

echo "=== Market Intelligence Platform Setup ===\n\n";

// Step 1: Check PHP version
echo "[1/5] Checking PHP version... ";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo PHP_VERSION . " ✓\n";
} else {
    echo PHP_VERSION . " ✗ (Requires 7.4+)\n";
    exit(1);
}

// Step 2: Check required extensions
echo "[2/5] Checking PHP extensions... ";
$required = ['pdo_mysql', 'curl', 'json', 'mbstring'];
$missing = [];
foreach ($required as $ext) {
    if (!extension_loaded($ext)) {
        $missing[] = $ext;
    }
}
if (empty($missing)) {
    echo "All required ✓\n";
} else {
    echo "Missing: " . implode(', ', $missing) . " ✗\n";
    exit(1);
}

// Step 3: Connect to database
echo "[3/5] Connecting to database... ";
$db = Database::getInstance();
if ($db->isConnected()) {
    echo "Connected ✓\n";
} else {
    echo "Failed: " . $db->getLastError() . " ✗\n";
    echo "  Please configure config/env.php with correct database credentials.\n";
    exit(1);
}

// Step 4: Import schema
echo "[4/5] Importing database schema... ";
$schemaFile = APP_ROOT . '/database/schema.sql';
if (file_exists($schemaFile)) {
    $schema = file_get_contents($schemaFile);
    
    // Split by semicolons and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) { return !empty($stmt) && $stmt !== ';'; }
    );
    
    $errors = 0;
    $pdo = $db->getConnection();
    foreach ($statements as $stmt) {
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            // Ignore "already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors++;
                echo "\n  Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    if ($errors === 0) {
        echo "Schema imported ✓\n";
    } else {
        echo "Imported with {$errors} errors\n";
    }
} else {
    echo "Schema file not found ✗\n";
    exit(1);
}

// Step 5: Create admin user
echo "[5/5] Creating admin user... ";
require_once APP_ROOT . '/app/core/Auth.php';
$userId = Auth::createAdmin();
if ($userId) {
    echo "Admin user ready (ID: {$userId}) ✓\n";
} else {
    echo "Admin user already exists ✓\n";
}

echo "\n=== Setup Complete! ===\n";
echo "Login credentials:\n";
echo "  Username: " . ADMIN_USERNAME . "\n";
echo "  Password: " . ADMIN_PASSWORD . "\n";
echo "\nIMPORTANT: Change the password after first login!\n";
echo "DELETE this setup.php file after setup for security.\n";
