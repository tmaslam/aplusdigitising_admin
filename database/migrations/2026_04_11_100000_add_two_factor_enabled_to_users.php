<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The users table has legacy zero-date defaults on columns like
        // date_added, which cause MySQL strict mode to reject any ALTER TABLE.
        // Temporarily clear sql_mode so the column can be added.
        \Illuminate\Support\Facades\DB::statement("SET SESSION sql_mode=''");
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER password_migrated_at'
        );
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("SET SESSION sql_mode=''");
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE users DROP COLUMN two_factor_enabled'
        );
    }
};
