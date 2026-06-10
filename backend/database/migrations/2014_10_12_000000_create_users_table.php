<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('codigo')->unique();
                $table->string('name_user');
                $table->string('email')->unique();
                $table->string('password')->nullable();
                $table->string('token')->nullable();
                $table->boolean('first_login')->nullable();
                $table->boolean('supervisor')->nullable();
                $table->boolean('activo')->nullable();
                $table->boolean('inhabilitado')->nullable();
                $table->boolean('menu_abrir_nueva_pestana')->nullable();
                $table->string('locale', 10)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('password_hash')->nullable();
                $table->boolean('sidebar_collapsed')->default(false);
                $table->string('theme', 32)->default('generic.light');
            });

            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE [users] (
    [id] BIGINT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    [codigo] NVARCHAR(255) NOT NULL,
    [name_user] NVARCHAR(255) NOT NULL,
    [email] NVARCHAR(255) NOT NULL,
    [password] NVARCHAR(255) NULL,
    [token] NVARCHAR(255) NULL,
    [first_login] BIT NULL,
    [supervisor] BIT NULL,
    [activo] BIT NULL,
    [inhabilitado] BIT NULL,
    [menu_abrir_nueva_pestana] BIT NULL,
    [locale] NVARCHAR(10) NULL,
    [created_at] DATETIME2 NULL,
    [updated_at] DATETIME2 NULL,
    [password_hash] NVARCHAR(255) NULL,
    [sidebar_collapsed] BIT NOT NULL CONSTRAINT [DF_users_sidebar_collapsed] DEFAULT 0,
    [theme] NVARCHAR(32) NOT NULL CONSTRAINT [DF_users_theme] DEFAULT 'generic.light'
)
SQL);

        DB::statement('CREATE UNIQUE INDEX [users_codigo_unique] ON [users] ([codigo])');
        DB::statement('CREATE UNIQUE INDEX [users_email_unique] ON [users] ([email])');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
