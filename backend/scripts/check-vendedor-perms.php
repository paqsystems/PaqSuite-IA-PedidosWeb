<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::query()->where('codigo', 'vendedor.acotado.mvp')->first();
if ($user === null) {
    echo "user not found\n";
    exit(1);
}

$perm = App\Models\PqPermiso::query()->where('id_usuario', $user->id)->first();
echo 'rol='.($perm?->rol?->nombre_rol ?? 'null').PHP_EOL;
echo 'empresa='.$perm?->id_empresa.PHP_EOL;
echo 'monoEmpresaId='.config('paqsuite_seed.monoEmpresaId').PHP_EOL;

$attrs = App\Models\PqRolAtributo::query()
    ->where('id_rol', $perm?->id_rol)
    ->where('procedimiento', 'pw_cargapedidos')
    ->first();

echo json_encode($attrs?->only(['permiso_alta', 'permiso_modi', 'permiso_repo', 'permiso_baja']), JSON_PRETTY_PRINT).PHP_EOL;
