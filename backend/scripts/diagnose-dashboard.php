<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\PedidosWeb\DashboardOperativoService;
use App\Services\Visibility\VisibilityPermissionGuard;
use Illuminate\Support\Facades\Schema;

$codigo = $argv[1] ?? 'supervisor.mvp';

echo 'pq_pedidosweb_pedidoscabecera: '.(Schema::hasTable('pq_pedidosweb_pedidoscabecera') ? 'si' : 'NO').PHP_EOL;
echo 'pq_pedidosweb_clientes: '.(Schema::hasTable('pq_pedidosweb_clientes') ? 'si' : 'NO').PHP_EOL;

$user = User::query()->where('codigo', $codigo)->first();
if ($user === null) {
    echo "Usuario {$codigo} no existe\n";
    exit(1);
}

try {
    $app->make(VisibilityPermissionGuard::class)->ensurePermission(
        $user,
        (string) config('paqsuite_visibility.procedimientos.dashboard'),
        'repo'
    );
    echo "Permiso pw_dashboard repo: OK\n";
} catch (Throwable $e) {
    echo 'Permiso: '.$e->getMessage()."\n";
}

try {
    $result = $app->make(DashboardOperativoService::class)->obtener($user);
    echo 'Dashboard OK: '.json_encode($result, JSON_UNESCAPED_UNICODE)."\n";
} catch (Throwable $e) {
    echo 'Dashboard ERROR: '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
}
