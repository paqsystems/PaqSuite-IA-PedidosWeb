<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            return;
        }

        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        if (DB::table('personal_access_tokens')->count() > 0) {
            return;
        }

        Schema::drop('personal_access_tokens');

        DB::statement(<<<'SQL'
CREATE TABLE [personal_access_tokens] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [tokenable_type] NVARCHAR(255) NOT NULL,
    [tokenable_id] BIGINT NOT NULL,
    [name] NVARCHAR(255) NOT NULL,
    [token] NVARCHAR(64) NOT NULL,
    [abilities] NVARCHAR(MAX) NULL,
    [last_used_at] DATETIME2 NULL,
    [expires_at] DATETIME2 NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL
)
SQL);

        DB::statement('CREATE INDEX [personal_access_tokens_tokenable_type_tokenable_id_index] ON [personal_access_tokens] ([tokenable_type], [tokenable_id])');
        DB::statement('CREATE UNIQUE INDEX [personal_access_tokens_token_unique] ON [personal_access_tokens] ([token])');
    }

    public function down(): void
    {
        //
    }
};
