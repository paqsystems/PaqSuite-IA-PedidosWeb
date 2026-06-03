<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Auth\LoginService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo 'DB='.config('database.connections.sqlsrv.database').PHP_EOL;
echo 'SEED_MVP_PASSWORD='.config('paqsuite_seed.mvpPassword').PHP_EOL.PHP_EOL;

$totalUsers = DB::table('users')->count();
echo 'users.total='.$totalUsers.PHP_EOL;

$users = DB::table('users')
    ->orderBy('codigo')
    ->limit(30)
    ->get(['id', 'codigo', 'email', 'activo', 'inhabilitado']);

foreach ($users as $user) {
    $permiso = DB::table('Pq_Permiso')
        ->where('id_usuario', $user->id)
        ->first(['id_rol', 'id_empresa']);
    echo sprintf(
        "codigo=%s activo=%s inhab=%s permiso=%s email=%s\n",
        $user->codigo,
        $user->activo,
        $user->inhabilitado,
        $permiso ? 'si' : 'no',
        $user->email ?? ''
    );
}

$passwords = [
    (string) config('paqsuite_seed.mvpPassword'),
    'TestSeedPassword123',
    'Paqsystems26*',
    'PaqSystems*',
];

$tryCodigos = ['cliente.mvp', 'supervisor.mvp', 'vendedor.acotado.mvp', 'VHS', 'PAQ'];

echo PHP_EOL.'--- Prueba login ---'.PHP_EOL;
$loginService = $app->make(LoginService::class);

foreach ($tryCodigos as $codigo) {
    $row = DB::table('users')->where('codigo', $codigo)->first();
    if ($row === null) {
        echo "{$codigo}: NO EXISTE\n";
        continue;
    }
    $matched = false;
    foreach ($passwords as $password) {
        if ($row->password_hash === '' || ! Hash::check($password, (string) $row->password_hash)) {
            continue;
        }
        $matched = true;
        try {
            $loginService->login($codigo, $password);
            echo "{$codigo} + [{$password}]: LOGIN OK\n";
        } catch (Throwable $e) {
            echo "{$codigo} + [{$password}]: ".$e->getMessage()."\n";
        }
        break;
    }
    if (! $matched) {
        echo "{$codigo}: password no coincide con candidatos\n";
    }
}
