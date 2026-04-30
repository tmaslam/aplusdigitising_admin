<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Extend the existing password-reset token tables to also carry 2FA codes.
 *
 * Adds:
 *   token_type  VARCHAR(20)  DEFAULT 'password_reset'  — distinguishes purpose
 *   attempts    TINYINT      DEFAULT 0                 — wrong-code counter
 *
 * This avoids a third token table. Both tables now serve two purposes:
 *   token_type = 'password_reset'  → existing password-reset flow (unchanged)
 *   token_type = '2fa_code'        → two-factor authentication step
 *
 * Also drops the two_factor_tokens table if it was created by a prior migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE admin_password_reset_tokens
            ADD COLUMN token_type VARCHAR(20) NOT NULL DEFAULT 'password_reset' AFTER token_hash,
            ADD COLUMN attempts   TINYINT     NOT NULL DEFAULT 0                AFTER token_type");

        DB::statement("ALTER TABLE customer_password_reset_tokens
            ADD COLUMN token_type VARCHAR(20) NOT NULL DEFAULT 'password_reset' AFTER token_hash,
            ADD COLUMN attempts   TINYINT     NOT NULL DEFAULT 0                AFTER token_type");

        // Remove the standalone table created in the previous iteration if present.
        DB::statement('DROP TABLE IF EXISTS two_factor_tokens');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE admin_password_reset_tokens
            DROP COLUMN token_type,
            DROP COLUMN attempts');

        DB::statement('ALTER TABLE customer_password_reset_tokens
            DROP COLUMN token_type,
            DROP COLUMN attempts');
    }
};
