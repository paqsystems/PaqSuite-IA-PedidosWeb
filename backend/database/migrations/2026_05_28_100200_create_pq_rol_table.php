<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('Pq_Rol')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            Schema::create('Pq_Rol', function ($table) {
                $table->increments('id');
                $table->string('nombre_rol', 100)->nullable();
                $table->string('descripcion_rol', 100)->nullable();
                $table->boolean('acceso_total')->default(false);
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });

            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [Pq_Rol] (
    [id] INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [nombre_rol] NVARCHAR(100) NULL,
    [descripcion_rol] NVARCHAR(100) NULL,
    [acceso_total] BIT NOT NULL CONSTRAINT [DF_Pq_Rol_acceso_total] DEFAULT 0,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL
)
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('Pq_Rol');
    }
};
