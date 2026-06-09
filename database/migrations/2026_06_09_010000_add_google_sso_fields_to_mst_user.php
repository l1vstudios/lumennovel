<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "CREATE TABLE IF NOT EXISTS mst_users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(191) NULL,
                email VARCHAR(191) NULL,
                password VARCHAR(255) NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )"
        );

        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS google_id VARCHAR(191) NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS google_avatar TEXT NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(30) DEFAULT 'local'");

        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS mst_users_google_id_unique
             ON mst_users (google_id)
             WHERE google_id IS NOT NULL"
        );

        DB::statement(
            "CREATE INDEX IF NOT EXISTS mst_users_email_index
             ON mst_users (email)
             WHERE email IS NOT NULL"
        );
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS mst_users_email_index");
        DB::statement("DROP INDEX IF EXISTS mst_users_google_id_unique");
        DB::statement("ALTER TABLE mst_users DROP COLUMN IF EXISTS auth_provider");
        DB::statement("ALTER TABLE mst_users DROP COLUMN IF EXISTS last_login_at");
        DB::statement("ALTER TABLE mst_users DROP COLUMN IF EXISTS email_verified_at");
        DB::statement("ALTER TABLE mst_users DROP COLUMN IF EXISTS google_avatar");
        DB::statement("ALTER TABLE mst_users DROP COLUMN IF EXISTS google_id");
    }
};
