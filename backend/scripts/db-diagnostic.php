<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'users',
    'pq_menus',
    'Pq_Rol',
    'Pq_Permiso',
    'PQ_RolAtributo',
    'pq_pedidosweb_clientes',
    'pq_pedidosweb_vendedores',
    'personal_access_tokens',
    'migrations',
];

echo 'DB: ' . config('database.connections.sqlsrv.database') . PHP_EOL;

foreach ($tables as $table) {
    $exists = Illuminate\Support\Facades\Schema::hasTable($table) ? 'YES' : 'NO';
    echo str_pad($table, 28) . $exists . PHP_EOL;
}

if (Illuminate\Support\Facades\Schema::hasTable('users')) {
    echo 'users count: ' . App\Models\User::query()->count() . PHP_EOL;
}

if (Illuminate\Support\Facades\Schema::hasTable('pq_menus')) {
    echo 'pq_menus enabled: ' . App\Models\PqMenu::query()->where('enabled', true)->count() . PHP_EOL;
}

if (Illuminate\Support\Facades\Schema::hasTable('Pq_Permiso')) {
    echo 'Pq_Permiso count: ' . App\Models\PqPermiso::query()->count() . PHP_EOL;
}

$mvpUsers = ['cliente.mvp', 'supervisor.mvp', 'vendedor.acotado.mvp'];
foreach ($mvpUsers as $codigo) {
    $user = App\Models\User::query()->where('codigo', $codigo)->first();
    echo "user {$codigo}: " . ($user ? 'exists id=' . $user->id : 'MISSING') . PHP_EOL;
}
