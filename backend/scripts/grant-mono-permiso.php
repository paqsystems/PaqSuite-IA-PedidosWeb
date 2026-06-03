<?php

/**
 * Asigna Pq_Permiso en empresa MONO a un usuario legacy (por codigo).
 * Uso: php scripts/grant-mono-permiso.php VHS Supervisor
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\User;

$codigo = $argv[1] ?? '';
$rolNombre = $argv[2] ?? 'Supervisor';

if ($codigo === '') {
    fwrite(STDERR, "Uso: php scripts/grant-mono-permiso.php <codigo> [nombreRol]\n");
    exit(1);
}

$user = User::query()->where('codigo', $codigo)->first();
if ($user === null) {
    fwrite(STDERR, "Usuario no encontrado: {$codigo}\n");
    exit(1);
}

$rol = PqRol::query()->where('nombre_rol', $rolNombre)->first();
if ($rol === null) {
    fwrite(STDERR, "Rol no encontrado: {$rolNombre}\n");
    exit(1);
}

$monoEmpresaId = (int) config('paqsuite_seed.monoEmpresaId');

if (! $user->activo) {
    $user->activo = true;
    $user->save();
    echo "users.activo actualizado a 1\n";
}

$permiso = PqPermiso::query()->updateOrCreate(
    ['id_usuario' => $user->id],
    ['id_rol' => $rol->id, 'id_empresa' => $monoEmpresaId],
);

echo "OK users.id={$user->id} codigo={$user->codigo} permiso.id={$permiso->id} rol={$rolNombre} empresa={$monoEmpresaId}\n";

try {
    $login = $app->make(App\Services\Auth\LoginService::class)->login($codigo, $argv[3] ?? '');
    echo 'LoginService OK (perfil='.$login['functionalProfile'].")\n";
} catch (Throwable $e) {
    echo 'LoginService (sin probar clave si no se pasó 3er arg): '.$e->getMessage()."\n";
}
