<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('PQ_Empresa')) {
            if (DB::table('PQ_Empresa')->count() === 0) {
                $this->seedMonoEmpresa();
            }

            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            Schema::create('PQ_Empresa', function ($table) {
                $table->increments('IDEmpresa');
                $table->string('NombreEmpresa', 100);
                $table->string('NombreBD', 100);
                $table->integer('Habilita')->nullable();
                $table->string('imagen', 100)->nullable();
                $table->string('theme')->nullable();
            });

            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [PQ_Empresa] (
    [IDEmpresa] INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [NombreEmpresa] VARCHAR(100) NOT NULL,
    [NombreBD] VARCHAR(100) NOT NULL,
    [Habilita] INT NULL,
    [imagen] VARCHAR(100) NULL,
    [theme] NVARCHAR(255) NULL
)
SQL);

        $this->seedMonoEmpresa();
    }

    private function seedMonoEmpresa(): void
    {
        $monoEmpresaId = (int) env('PAQSUITE_MONO_EMPRESA_ID', 8);
        $nombreBd = str_replace("'", "''", (string) env('DB_DATABASE', ''));

        DB::unprepared('SET IDENTITY_INSERT [PQ_Empresa] ON');
        DB::unprepared(
            "INSERT INTO [PQ_Empresa] ([IDEmpresa], [NombreEmpresa], [NombreBD], [Habilita], [imagen], [theme])
             VALUES ({$monoEmpresaId}, N'Empresa MONO', N'{$nombreBd}', 1, NULL, NULL)"
        );
        DB::unprepared('SET IDENTITY_INSERT [PQ_Empresa] OFF');
    }

    public function down(): void
    {
        Schema::dropIfExists('PQ_Empresa');
    }
};
