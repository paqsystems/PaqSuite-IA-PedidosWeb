<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Auth\LoginService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

$password = (string) config('paqsuite_seed.mvpPassword');

echo "BD: ".config('database.connections.sqlsrv.database').PHP_EOL;
echo "Contraseña seed (.env SEED_MVP_PASSWORD): {$password}".PHP_EOL;
echo 'users: '.DB::table('users')->count().PHP_EOL;
echo 'pq_pedidosweb_login: '.(Schema::hasTable('pq_pedidosweb_login') ? 'si' : 'NO').PHP_EOL;
echo 'pq_pedidosweb_vendedores: '.(Schema::hasTable('pq_pedidosweb_vendedores') ? 'si' : 'NO').PHP_EOL;
echo 'pq_pedidosweb_clientes: '.(Schema::hasTable('pq_pedidosweb_clientes') ? 'si' : 'NO').PHP_EOL.PHP_EOL;

$loginService = $app->make(LoginService::class);

foreach (['supervisor.mvp', 'cliente.mvp', 'vendedor.acotado.mvp'] as $codigo) {
    $user = DB::table('users')->where('codigo', $codigo)->first();
    if ($user === null) {
        echo "{$codigo}: no existe\n";
        continue;
    }
    $okPass = Hash::check($password, (string) $user->password_hash);
    echo "{$codigo}: activo={$user->activo} pass=".($okPass ? 'OK' : 'FAIL').' ';
    try {
        $loginService->login($codigo, $password);
        echo "LOGIN OK\n";
    } catch (Throwable $e) {
        echo 'LOGIN: '.$e->getMessage()."\n";
    }
}
