<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('Pq_Permiso')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            Schema::create('Pq_Permiso', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('id_rol');
                $table->unsignedInteger('id_empresa');
                $table->unsignedBigInteger('id_usuario');
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });

            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [Pq_Permiso] (
    [id] INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [id_rol] INT NOT NULL,
    [id_empresa] INT NOT NULL,
    [id_usuario] BIGINT NOT NULL,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL,
    CONSTRAINT [FK_Pq_Permiso_Pq_Rol] FOREIGN KEY ([id_rol]) REFERENCES [Pq_Rol]([id]),
    CONSTRAINT [FK_Pq_Permiso_PQ_Empresa] FOREIGN KEY ([id_empresa]) REFERENCES [PQ_Empresa]([IDEmpresa]),
    CONSTRAINT [FK_Pq_Permiso_users] FOREIGN KEY ([id_usuario]) REFERENCES [users]([id])
)
SQL);

        DB::statement('CREATE UNIQUE INDEX [uq_pq_permiso_rol_empresa_usuario] ON [Pq_Permiso] ([id_rol], [id_empresa], [id_usuario])');
    }

    public function down(): void
    {
        Schema::dropIfExists('Pq_Permiso');
    }
};
