<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$sqlFile = __DIR__ . '/database/sql/local_development_setup.sql';

echo "=== SETUP DATABASE ===\n\n";

// 1. Drop all existing tables
echo "1. Dropping all existing tables...\n";
$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$key = 'Tables_in_' . $dbName;

DB::statement('SET FOREIGN_KEY_CHECKS = 0');
foreach ($tables as $table) {
    $tableName = $table->$key;
    DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
    echo "  Dropped: {$tableName}\n";
}
DB::statement('SET FOREIGN_KEY_CHECKS = 1');
echo "All tables dropped.\n";

// 2. Execute the base schema SQL file using mysqli multi_query (handles DELIMITER, PREPARE, etc.)
echo "\n2. Executing base schema from SQL file...\n";
if (! file_exists($sqlFile)) {
    echo "ERROR: SQL file not found: {$sqlFile}\n";
    exit(1);
}

// Get connection details from Laravel config
$connection = config('database.connections.' . config('database.default'));
$host = $connection['host'] ?? '127.0.0.1';
$port = (int) ($connection['port'] ?? 3306);
$database = $connection['database'] ?? '';
$username = $connection['username'] ?? '';
$password = $connection['password'] ?? '';

$mysqliOk = false;
if (extension_loaded('mysqli')) {
    $mysqli = new mysqli($host, $username, $password, $database, $port);
    if (! $mysqli->connect_error) {
        $sql = file_get_contents($sqlFile);
        if ($mysqli->multi_query($sql)) {
            // Drain all results
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            if ($mysqli->error) {
                echo "WARNING: mysqli reported error: " . $mysqli->error . "\n";
            } else {
                echo "SQL file executed successfully via mysqli.\n";
                $mysqliOk = true;
            }
        } else {
            echo "WARNING: mysqli multi_query failed: " . $mysqli->error . "\n";
        }
        $mysqli->close();
    } else {
        echo "WARNING: mysqli connection failed: " . $mysqli->connect_error . "\n";
    }
} else {
    echo "WARNING: mysqli extension not loaded.\n";
}

if (! $mysqliOk) {
    echo "Falling back to PHP split execution (indexes will be skipped)...\n";

    $sql = file_get_contents($sqlFile);
    // Remove stored-procedure section
    $sql = preg_replace(
        '/DROP PROCEDURE IF EXISTS add_index_if_missing;.*?DROP PROCEDURE IF EXISTS add_index_if_missing;/s',
        '',
        $sql
    );

    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $executed = 0;
    $skipped = 0;
    foreach ($statements as $statement) {
        if ($statement === '' || preg_match('/^(\-\-|\/\*|SET\s+@)/s', $statement)) {
            $skipped++;
            continue;
        }
        try {
            DB::unprepared($statement);
            $executed++;
        } catch (\Exception $e) {
            echo "  [WARN] " . substr($statement, 0, 60) . "... (" . $e->getMessage() . ")\n";
            $skipped++;
        }
    }
    echo "Fallback executed {$executed} statements, skipped {$skipped}.\n";
}

// 3. Apply missing columns that migrations expect but SQL file doesn't include
echo "\n3. Applying migration changes...\n";

// users table: add password_hash, password_migrated_at, two_factor_enabled
if (! Schema::hasColumn('users', 'password_hash')) {
    DB::statement("ALTER TABLE `users` ADD COLUMN `password_hash` VARCHAR(255) NULL AFTER `user_password`");
    echo "  Added users.password_hash\n";
}
if (! Schema::hasColumn('users', 'password_migrated_at')) {
    DB::statement("ALTER TABLE `users` ADD COLUMN `password_migrated_at` DATETIME NULL AFTER `password_hash`");
    echo "  Added users.password_migrated_at\n";
}
if (! Schema::hasColumn('users', 'two_factor_enabled')) {
    DB::statement("SET SESSION sql_mode=''");
    DB::statement("ALTER TABLE `users` ADD COLUMN `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_migrated_at`");
    echo "  Added users.two_factor_enabled\n";
}

// Create admin_password_reset_tokens table (not in SQL file)
if (! Schema::hasTable('admin_password_reset_tokens')) {
    DB::statement("CREATE TABLE `admin_password_reset_tokens` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
        `admin_user_id` bigint unsigned NOT NULL,
        `selector` varchar(32) NOT NULL,
        `token_hash` varchar(64) NOT NULL,
        `token_type` varchar(20) NOT NULL DEFAULT 'password_reset',
        `attempts` tinyint NOT NULL DEFAULT 0,
        `expires_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `admin_password_reset_selector_unique` (`selector`),
        KEY `admin_password_reset_user_idx` (`admin_user_id`),
        KEY `admin_password_reset_expires_idx` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  Created admin_password_reset_tokens\n";
} else {
    if (! Schema::hasColumn('admin_password_reset_tokens', 'token_type')) {
        DB::statement("ALTER TABLE `admin_password_reset_tokens` ADD COLUMN `token_type` VARCHAR(20) NOT NULL DEFAULT 'password_reset' AFTER `token_hash`");
        echo "  Added admin_password_reset_tokens.token_type\n";
    }
    if (! Schema::hasColumn('admin_password_reset_tokens', 'attempts')) {
        DB::statement("ALTER TABLE `admin_password_reset_tokens` ADD COLUMN `attempts` TINYINT NOT NULL DEFAULT 0 AFTER `token_type`");
        echo "  Added admin_password_reset_tokens.attempts\n";
    }
}

// customer_password_reset_tokens: add token_type and attempts
if (Schema::hasTable('customer_password_reset_tokens')) {
    if (! Schema::hasColumn('customer_password_reset_tokens', 'token_type')) {
        DB::statement("ALTER TABLE `customer_password_reset_tokens` ADD COLUMN `token_type` VARCHAR(20) NOT NULL DEFAULT 'password_reset' AFTER `token_hash`");
        echo "  Added customer_password_reset_tokens.token_type\n";
    }
    if (! Schema::hasColumn('customer_password_reset_tokens', 'attempts')) {
        DB::statement("ALTER TABLE `customer_password_reset_tokens` ADD COLUMN `attempts` TINYINT NOT NULL DEFAULT 0 AFTER `token_type`");
        echo "  Added customer_password_reset_tokens.attempts\n";
    }
}

// Create two_factor_trusted_devices table
if (! Schema::hasTable('two_factor_trusted_devices')) {
    DB::statement("CREATE TABLE `two_factor_trusted_devices` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
        `portal` varchar(20) NOT NULL,
        `site_legacy_key` varchar(100) NULL,
        `user_id` bigint unsigned NOT NULL,
        `selector` varchar(32) NOT NULL,
        `token_hash` varchar(64) NOT NULL,
        `user_agent_hash` varchar(64) NOT NULL,
        `password_signature` varchar(64) NOT NULL,
        `expires_at` datetime NOT NULL,
        `last_used_at` datetime NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `two_factor_trusted_devices_selector_unique` (`selector`),
        KEY `two_factor_trusted_devices_portal_user_idx` (`portal`, `user_id`),
        KEY `trusted_2fa_portal_site_user_idx` (`portal`, `site_legacy_key`, `user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  Created two_factor_trusted_devices\n";
}

// Create customer_topups table with plan_option
if (! Schema::hasTable('customer_topups')) {
    DB::statement("CREATE TABLE `customer_topups` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
        `site_id` bigint unsigned NULL,
        `user_id` bigint unsigned NOT NULL,
        `website` varchar(30) NOT NULL DEFAULT '1dollar',
        `amount` decimal(12,2) NOT NULL,
        `plan_option` varchar(100) NULL,
        `status` varchar(50) NOT NULL DEFAULT 'pending',
        `stripe_reference` varchar(255) NULL,
        `completed_at` datetime NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        KEY `customer_topups_user_status_idx` (`user_id`, `status`),
        KEY `customer_topups_website_status_idx` (`website`, `status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  Created customer_topups\n";
}

// 4. Create migrations table and mark all migrations as run
echo "\n4. Creating migrations table...\n";
if (! Schema::hasTable('migrations')) {
    Schema::create('migrations', function ($table) {
        $table->id();
        $table->string('migration');
        $table->integer('batch');
    });
}

$migrations = [
    '2026_04_11_100000_add_two_factor_enabled_to_users.php',
    '2026_04_11_110000_add_2fa_columns_to_token_tables.php',
    '2026_04_25_190000_create_two_factor_trusted_devices_table.php',
    '2026_04_27_200000_create_customer_topups_table.php',
    '2026_04_28_153715_add_plan_option_to_customer_topups.php',
];

foreach ($migrations as $i => $migration) {
    DB::table('migrations')->updateOrInsert(
        ['migration' => $migration],
        ['batch' => 1]
    );
    echo "  Recorded: {$migration}\n";
}

// 5. Seed site configuration
echo "\n5. Adding site configuration...\n";

// Add primary site
$siteId = DB::table('sites')->insertGetId([
    'legacy_key' => '1dollar',
    'slug' => 'aplus',
    'name' => 'A Plus Digitizing',
    'brand_name' => 'A Plus Digitizing',
    'primary_domain' => 'aplusdigitising.com',
    'website_address' => 'https://aplusdigitising.com',
    'support_email' => 'support@aplusdigitising.com',
    'from_email' => 'support@aplusdigitising.com',
    'is_active' => 1,
    'is_primary' => 1,
    'created_at' => now()->format('Y-m-d H:i:s'),
    'updated_at' => now()->format('Y-m-d H:i:s'),
    'settings_json' => json_encode([
        'logo' => '/images/logo.png',
        'favicon' => '/images/favicon.ico',
        'primary_color' => '#F26522',
        'primary_dark_color' => '#D94E0F',
        'accent_color' => '#2563EB',
    ]),
]);

echo "Created site ID: {$siteId}\n";

// Add domains
DB::table('site_domains')->insert([
    'site_id' => $siteId,
    'host' => 'aplusdigitising.com',
    'is_active' => 1,
    'is_primary' => 1,
    'created_at' => now()->format('Y-m-d H:i:s'),
    'updated_at' => now()->format('Y-m-d H:i:s'),
]);

DB::table('site_domains')->insert([
    'site_id' => $siteId,
    'host' => 'user.aplusdigitising.com',
    'is_active' => 1,
    'is_primary' => 0,
    'created_at' => now()->format('Y-m-d H:i:s'),
    'updated_at' => now()->format('Y-m-d H:i:s'),
]);

DB::table('site_domains')->insert([
    'site_id' => $siteId,
    'host' => 'aplusdigitizing.com',
    'is_active' => 1,
    'is_primary' => 0,
    'created_at' => now()->format('Y-m-d H:i:s'),
    'updated_at' => now()->format('Y-m-d H:i:s'),
]);

echo "Added domains\n";

// Add pricing profiles
$profiles = [
    ['site_id' => $siteId, 'profile_name' => 'Standard Digitizing', 'work_type' => 'digitizing', 'turnaround_code' => 'standard', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 0])],
    ['site_id' => $siteId, 'profile_name' => 'Priority Digitizing', 'work_type' => 'digitizing', 'turnaround_code' => 'priority', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 5])],
    ['site_id' => $siteId, 'profile_name' => 'Super Rush Digitizing', 'work_type' => 'digitizing', 'turnaround_code' => 'superrush', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 10])],
    ['site_id' => $siteId, 'profile_name' => 'Standard Vector', 'work_type' => 'vector', 'turnaround_code' => 'standard', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 6.00, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 0, 'minimum_hours' => 2])],
    ['site_id' => $siteId, 'profile_name' => 'Priority Vector', 'work_type' => 'vector', 'turnaround_code' => 'priority', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 6.00, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 5, 'minimum_hours' => 2])],
    ['site_id' => $siteId, 'profile_name' => 'Super Rush Vector', 'work_type' => 'vector', 'turnaround_code' => 'superrush', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 6.00, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 10, 'minimum_hours' => 2])],
];

foreach ($profiles as $profile) {
    $profile['is_active'] = 1;
    $profile['created_at'] = now()->format('Y-m-d H:i:s');
    $profile['updated_at'] = now()->format('Y-m-d H:i:s');
    DB::table('site_pricing_profiles')->insert($profile);
}

echo "Added pricing profiles\n";

// 6. Clear caches
echo "\n6. Clearing caches...\n";
Artisan::call('config:clear');
Artisan::call('cache:clear');
Artisan::call('view:clear');

echo "\n=== DATABASE SETUP COMPLETE ===\n";
echo "Site ID: {$siteId}\n";
echo "\nNow run: php create-customer.php\n";
