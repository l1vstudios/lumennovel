<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'trx_read_novel',
        'trx_vote_novel',
        'trx_share_novel',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            $this->createTableIfMissing($table);
            $this->mergeDuplicateRows($table);
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS {$table}_novel_id_unique ON {$table} (novel_id)");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::statement("DROP INDEX IF EXISTS {$table}_novel_id_unique");
        }
    }

    private function mergeDuplicateRows(string $table): void
    {
        DB::statement(
            "WITH merged AS (
                SELECT
                    MIN(id) AS keep_id,
                    novel_id,
                    SUM(
                        CASE
                            WHEN \"count\"::text ~ '^[0-9]+$' THEN \"count\"::integer
                            ELSE 0
                        END
                    ) AS total_count,
                    MIN(created_at) AS first_created_at,
                    MAX(updated_at) AS last_updated_at
                FROM {$table}
                WHERE novel_id IS NOT NULL
                GROUP BY novel_id
                HAVING COUNT(*) > 1
            ),
            updated AS (
                UPDATE {$table} trx
                SET \"count\" = merged.total_count::varchar,
                    created_at = COALESCE(merged.first_created_at, trx.created_at),
                    updated_at = COALESCE(merged.last_updated_at, trx.updated_at)
                FROM merged
                WHERE trx.id = merged.keep_id
                RETURNING trx.id
            )
            DELETE FROM {$table} trx
            USING merged
            WHERE trx.novel_id = merged.novel_id
              AND trx.id <> merged.keep_id"
        );
    }

    private function createTableIfMissing(string $table): void
    {
        DB::statement(
            "CREATE TABLE IF NOT EXISTS {$table} (
                id SERIAL PRIMARY KEY,
                novel_id INTEGER,
                \"count\" VARCHAR DEFAULT '0',
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )"
        );
    }
};
