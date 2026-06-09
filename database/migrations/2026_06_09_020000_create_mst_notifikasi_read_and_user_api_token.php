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
                google_id VARCHAR(191) NULL,
                google_avatar TEXT NULL,
                email_verified_at TIMESTAMP NULL,
                last_login_at TIMESTAMP NULL,
                api_token VARCHAR(80) NULL,
                auth_provider VARCHAR(30) DEFAULT 'local',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )"
        );

        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS name VARCHAR(191) NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS email VARCHAR(191) NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS google_id VARCHAR(191) NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS google_avatar TEXT NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS api_token VARCHAR(80) NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(30) DEFAULT 'local'");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE mst_users ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL");

        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS mst_users_email_unique
             ON mst_users (LOWER(email))
             WHERE email IS NOT NULL"
        );

        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS mst_users_google_id_unique
             ON mst_users (google_id)
             WHERE google_id IS NOT NULL"
        );

        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS mst_users_api_token_unique
             ON mst_users (api_token)
             WHERE api_token IS NOT NULL"
        );

        DB::statement(
            "CREATE TABLE IF NOT EXISTS mst_notifikasi_read (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                notifikasi_id INTEGER NOT NULL,
                read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )"
        );

        DB::statement(
            "CREATE UNIQUE INDEX IF NOT EXISTS mst_notifikasi_read_user_notifikasi_unique
             ON mst_notifikasi_read (user_id, notifikasi_id)"
        );

        DB::statement(
            "CREATE INDEX IF NOT EXISTS mst_notifikasi_read_user_id_index
             ON mst_notifikasi_read (user_id)"
        );
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS mst_notifikasi_read_user_id_index");
        DB::statement("DROP INDEX IF EXISTS mst_notifikasi_read_user_notifikasi_unique");
        DB::statement("DROP TABLE IF EXISTS mst_notifikasi_read");
        DB::statement("DROP INDEX IF EXISTS mst_users_api_token_unique");
        DB::statement("DROP INDEX IF EXISTS mst_users_google_id_unique");
        DB::statement("DROP INDEX IF EXISTS mst_users_email_unique");
    }
};
