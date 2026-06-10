<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('PQ_RolAtributo')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            Schema::create('PQ_RolAtributo', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('id_rol');
                $table->string('procedimiento');
                $table->boolean('permiso_alta')->default(false);
                $table->boolean('permiso_baja')->default(false);
                $table->boolean('permiso_modi')->default(false);
                $table->boolean('permiso_repo')->default(true);
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->unique(['id_rol', 'procedimiento']);
            });

            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [PQ_RolAtributo] (
    [id] INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [id_rol] INT NOT NULL,
    [procedimiento] NVARCHAR(255) NOT NULL,
    [permiso_alta] BIT NOT NULL CONSTRAINT [DF_PQ_RolAtributo_permiso_alta] DEFAULT 0,
    [permiso_baja] BIT NOT NULL CONSTRAINT [DF_PQ_RolAtributo_permiso_baja] DEFAULT 0,
    [permiso_modi] BIT NOT NULL CONSTRAINT [DF_PQ_RolAtributo_permiso_modi] DEFAULT 0,
    [permiso_repo] BIT NOT NULL CONSTRAINT [DF_PQ_RolAtributo_permiso_repo] DEFAULT 0,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL
)
SQL);

        DB::statement('CREATE UNIQUE INDEX [uq_pq_rolatributo_rol_procedimiento] ON [PQ_RolAtributo] ([id_rol], [procedimiento])');
    }

    public function down(): void
    {
        Schema::dropIfExists('PQ_RolAtributo');
    }
};
