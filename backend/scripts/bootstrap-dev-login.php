<?php

/**
 * Crea tablas comerciales mínimas en dev (Ankas sin script ERP) y re-sincroniza usuarios MVP.
 * Uso: php scripts/bootstrap-dev-login.php
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function ensureTable(string $name, string $ddl): void
{
    if (Schema::hasTable($name)) {
        echo "OK tabla existente: {$name}\n";
        return;
    }

    DB::statement($ddl);
    echo "Creada tabla: {$name}\n";
}

ensureTable('pq_pedidosweb_listaprecios', <<<'SQL'
CREATE TABLE pq_pedidosweb_listaprecios (
    cod_lista int NOT NULL PRIMARY KEY,
    incluye_iva bit NOT NULL DEFAULT 0,
    moneda int NOT NULL DEFAULT 1,
    descripcion nvarchar(100) NULL,
    decimales int NOT NULL DEFAULT 2
)
SQL);

ensureTable('pq_pedidosweb_condventa', <<<'SQL'
CREATE TABLE pq_pedidosweb_condventa (
    codigo int NOT NULL PRIMARY KEY,
    descripcion nvarchar(100) NULL
)
SQL);

ensureTable('pq_pedidosweb_vendedores', <<<'SQL'
CREATE TABLE pq_pedidosweb_vendedores (
    cod_vended nvarchar(20) NOT NULL PRIMARY KEY,
    nombre nvarchar(120) NULL,
    supervisor bit NOT NULL DEFAULT 0,
    mail_supervisor nvarchar(120) NULL,
    cod_login nvarchar(50) NULL,
    e_mail nvarchar(120) NULL
)
SQL);

ensureTable('pq_pedidosweb_clientes', <<<'SQL'
CREATE TABLE pq_pedidosweb_clientes (
    cod_client nvarchar(20) NOT NULL PRIMARY KEY,
    nombre nvarchar(120) NULL,
    fantasia nvarchar(120) NULL,
    cod_vended nvarchar(20) NULL,
    lista_precios int NULL,
    cod_condvta int NULL,
    cod_transpor nvarchar(20) NULL,
    bonificacion decimal(18,4) NULL,
    nivel int NULL,
    expreso nvarchar(80) NULL,
    expreso_dire nvarchar(200) NULL,
    cod_login nvarchar(50) NULL,
    e_mail nvarchar(120) NULL
)
SQL);

ensureTable('pq_pedidosweb_login', <<<'SQL'
CREATE TABLE pq_pedidosweb_login (
    cod_usuario_web nvarchar(50) NOT NULL PRIMARY KEY,
    usuario nvarchar(120) NULL,
    password nvarchar(200) NULL,
    e_mail nvarchar(120) NULL,
    primer_login bit NOT NULL DEFAULT 0,
    tipo_cuenta char(1) NULL,
    cod_asociado nvarchar(50) NULL,
    password_bcrypt nvarchar(255) NULL,
    password_sha1 nvarchar(64) NULL
)
SQL);

config(['paqsuite_seed.syncCommercial' => true]);

$menuExit = $kernel->call('paqsuite:seed-menus-mvp');
echo "seed-menus-mvp exit={$menuExit}\n";

$secExit = $kernel->call('paqsuite:seed-seguridad-mvp');
echo "seed-seguridad-mvp exit={$secExit}\n";

$syncExit = $kernel->call('paqsuite:sync-pedidosweb-login-from-users');
echo "sync-pedidosweb-login-from-users exit={$syncExit}\n";

echo "\nListo. Probar login con codigo + SEED_MVP_PASSWORD del .env\n";
