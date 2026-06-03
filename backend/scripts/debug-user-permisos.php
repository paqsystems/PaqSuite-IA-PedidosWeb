<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userId = (int) ($argv[1] ?? 1103);
$monoEmpresaId = (int) config('paqsuite_seed.monoEmpresaId');

echo 'monoEmpresaId='.$monoEmpresaId.PHP_EOL;

$permisos = Illuminate\Support\Facades\DB::table('pq_permiso')
    ->where('id_usuario', $userId)
    ->get();

echo 'pq_permiso (all empresas): '.json_encode($permisos, JSON_UNESCAPED_UNICODE).PHP_EOL;

$roles = Illuminate\Support\Facades\DB::table('pq_rol')->limit(20)->get();
echo 'pq_rol sample: '.json_encode($roles, JSON_UNESCAPED_UNICODE).PHP_EOL;

$vendedor = Illuminate\Support\Facades\DB::table('pq_pedidosweb_vendedores')
    ->where('cod_login', 'VHS')
    ->first();
echo 'vendedor cod_login=VHS: '.json_encode($vendedor, JSON_UNESCAPED_UNICODE).PHP_EOL;

$atributos = Illuminate\Support\Facades\DB::table('pq_rolatributo')
    ->whereIn('id_rol', [84, 86])
    ->count();
echo 'pq_rolatributo count (roles 84,86): '.$atributos.PHP_EOL;
