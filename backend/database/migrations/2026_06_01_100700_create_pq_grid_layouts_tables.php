<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pq_grid_layouts')) {
            if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
                DB::statement(<<<'SQL'
CREATE TABLE [pq_grid_layouts] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [proceso] NVARCHAR(128) NOT NULL,
    [grid_id] NVARCHAR(64) NOT NULL,
    [layout_name] NVARCHAR(128) NOT NULL,
    [created_by_user_id] BIGINT NOT NULL,
    [state_json] NVARCHAR(MAX) NOT NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [FK_pq_grid_layouts_user] FOREIGN KEY ([created_by_user_id]) REFERENCES [users]([id])
);
SQL);
                DB::statement(<<<'SQL'
CREATE UNIQUE INDEX [UX_pq_grid_layouts_proceso_grid_layout]
    ON [pq_grid_layouts] ([proceso], [grid_id], [layout_name]);
SQL);
            } else {
                Schema::create('pq_grid_layouts', function (Blueprint $table): void {
                    $table->id();
                    $table->string('proceso', 128);
                    $table->string('grid_id', 64);
                    $table->string('layout_name', 128);
                    $table->foreignId('created_by_user_id')->constrained('users');
                    $table->longText('state_json');
                    $table->timestamps();
                    $table->unique(['proceso', 'grid_id', 'layout_name'], 'UX_pq_grid_layouts_proceso_grid_layout');
                });
            }
        }

        if (! Schema::hasTable('pq_grid_layout_last_used')) {
            if (Schema::getConnection()->getDriverName() === 'sqlsrv') {
                DB::statement(<<<'SQL'
CREATE TABLE [pq_grid_layout_last_used] (
    [user_id] BIGINT NOT NULL,
    [proceso] NVARCHAR(128) NOT NULL,
    [grid_id] NVARCHAR(64) NOT NULL,
    [layout_id] BIGINT NULL,
    [updated_at] DATETIME2 NULL,
    CONSTRAINT [PK_pq_grid_layout_last_used] PRIMARY KEY ([user_id], [proceso], [grid_id]),
    CONSTRAINT [FK_pq_grid_layout_last_used_user] FOREIGN KEY ([user_id]) REFERENCES [users]([id]),
    CONSTRAINT [FK_pq_grid_layout_last_used_layout] FOREIGN KEY ([layout_id]) REFERENCES [pq_grid_layouts]([id]) ON DELETE SET NULL
);
SQL);
            } else {
                Schema::create('pq_grid_layout_last_used', function (Blueprint $table): void {
                    $table->foreignId('user_id')->constrained('users');
                    $table->string('proceso', 128);
                    $table->string('grid_id', 64);
                    $table->foreignId('layout_id')->nullable()->constrained('pq_grid_layouts')->nullOnDelete();
                    $table->timestamp('updated_at')->nullable();
                    $table->primary(['user_id', 'proceso', 'grid_id']);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_grid_layout_last_used');
        Schema::dropIfExists('pq_grid_layouts');
    }
};
