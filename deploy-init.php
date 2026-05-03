<?php
/**
 * One-time server initialization script for Laravel deployment.
 *
 * Usage: https://user.aplusdigitising.com/deploy-init.php?token=YOUR_SECRET_TOKEN
 *
 * IMPORTANT: Delete this file after successful initialization.
 */

$expectedToken = $_ENV['DEPLOY_INIT_TOKEN'] ?? $_SERVER['DEPLOY_INIT_TOKEN'] ?? '';

if (!$expectedToken) {
    http_response_code(403);
    echo "Error: DEPLOY_INIT_TOKEN is not configured on the server. Set it in the environment or .env file.";
    exit;
}

$providedToken = $_GET['token'] ?? '';

if (!hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    echo "Error: Invalid token.";
    exit;
}

$basePath = __DIR__;
$results = [];

function logResult(string $message, bool $success = true): string {
    $status = $success ? 'OK' : 'FAIL';
    return "[{$status}] {$message}";
}

// 1. Ensure storage directories exist
$storageDirs = [
    'storage/app',
    'storage/app/public',
    'storage/app/private',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/testing',
    'storage/framework/legacy-sessions',
    'storage/logs',
    'storage/pail',
];

foreach ($storageDirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (!is_dir($fullPath)) {
        $success = @mkdir($fullPath, 0755, true);
        $results[] = logResult("Create directory: {$dir}", $success);
    } else {
        $results[] = logResult("Directory exists: {$dir}");
    }
}

// 2. Ensure bootstrap/cache exists
$cacheDir = $basePath . '/bootstrap/cache';
if (!is_dir($cacheDir)) {
    $success = @mkdir($cacheDir, 0755, true);
    $results[] = logResult("Create directory: bootstrap/cache", $success);
} else {
    $results[] = logResult("Directory exists: bootstrap/cache");
}

// 3. Check .env exists
$envPath = $basePath . '/.env';
if (!file_exists($envPath)) {
    $results[] = logResult(".env file is missing! Please upload it manually.", false);
} else {
    $results[] = logResult(".env file exists");
}

// 4. Run artisan commands if .env exists and artisan exists
$artisanPath = $basePath . '/artisan';
if (file_exists($artisanPath) && file_exists($envPath)) {
    $phpBin = PHP_BINARY;

    $commands = [
        'key:generate --force' => 'Generate APP_KEY',
        'optimize:clear' => 'Clear optimized caches',
        'view:clear' => 'Clear view cache',
        'route:clear' => 'Clear route cache',
        'config:clear' => 'Clear config cache',
        'cache:clear' => 'Clear application cache',
    ];

    foreach ($commands as $cmd => $desc) {
        $output = [];
        $returnCode = 0;
        $escapedCmd = escapeshellcmd("{$phpBin} {$artisanPath} {$cmd}");
        @exec($escapedCmd . " 2>&1", $output, $returnCode);
        $success = ($returnCode === 0);
        $results[] = logResult("{$desc}: " . implode(' ', array_slice($output, 0, 2)), $success);
    }

    // Optional: rebuild view cache
    $output = [];
    $returnCode = 0;
    $escapedCacheCmd = escapeshellcmd("{$phpBin} {$artisanPath} view:cache");
    @exec($escapedCacheCmd . " 2>&1", $output, $returnCode);
    $results[] = logResult("Rebuild view cache: " . implode(' ', array_slice($output, 0, 2)), $returnCode === 0);
} else {
    $results[] = logResult("Skipping artisan commands (artisan or .env missing)", false);
}

// 5. Output results
header('Content-Type: text/plain');
echo "=== Laravel Deployment Initialization ===\n\n";
echo "Base path: {$basePath}\n";
echo "PHP version: " . PHP_VERSION . "\n\n";

foreach ($results as $result) {
    echo $result . "\n";
}

echo "\n=== IMPORTANT ===\n";
echo "Delete this file (deploy-init.php) after successful initialization.\n";
