<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pq_menus')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            Schema::create('pq_menus', function ($table) {
                $table->integer('id')->primary();
                $table->string('text', 150);
                $table->boolean('expanded')->default(false);
                $table->integer('idparent')->default(0);
                $table->smallInteger('orden')->default(0);
                $table->char('tipo', 3)->default('WEB');
                $table->string('procedimiento', 150)->nullable();
                $table->boolean('enabled')->default(true);
                $table->string('routeName', 50)->nullable();
                $table->integer('estructura')->nullable();
                $table->string('icon_name', 50)->nullable();
                $table->char('tipo_proceso', 1)->nullable();
            });

            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [pq_menus] (
    [id] INT NOT NULL PRIMARY KEY,
    [text] NVARCHAR(150) NOT NULL,
    [expanded] BIT NOT NULL CONSTRAINT [DF_pq_menus_expanded] DEFAULT 0,
    [idparent] INT NOT NULL CONSTRAINT [DF_pq_menus_idparent] DEFAULT 0,
    [orden] SMALLINT NOT NULL CONSTRAINT [DF_pq_menus_orden] DEFAULT 0,
    [tipo] NCHAR(3) NOT NULL CONSTRAINT [DF_pq_menus_tipo] DEFAULT 'WEB',
    [procedimiento] NVARCHAR(150) NULL,
    [enabled] BIT NOT NULL CONSTRAINT [DF_pq_menus_enabled] DEFAULT 1,
    [routeName] NVARCHAR(50) NULL,
    [estructura] INT NULL,
    [icon_name] NVARCHAR(50) NULL,
    [tipo_proceso] NCHAR(1) NULL
)
SQL);

        DB::statement('CREATE UNIQUE INDEX [uq_pq_menus_parent_order] ON [pq_menus] ([idparent], [orden])');
        DB::statement('CREATE INDEX [pq_menus_idparent_index] ON [pq_menus] ([idparent])');
        DB::statement('CREATE INDEX [pq_menus_enabled_index] ON [pq_menus] ([enabled])');
    }

    public function down(): void
    {
        Schema::dropIfExists('pq_menus');
    }
};
