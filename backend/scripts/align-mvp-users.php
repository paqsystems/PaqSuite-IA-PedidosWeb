<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo 'DB='.config('database.connections.sqlsrv.database').PHP_EOL;

$seedUsers = config('paqsuite_mvp.users', []);
$codigos = array_column($seedUsers, 'codigo');
$passwordHash = Hash::make((string) config('paqsuite_seed.mvpPassword'));

$updated = 0;
$missing = [];

foreach ($seedUsers as $userSeed) {
    $codigo = (string) $userSeed['codigo'];
    $user = User::query()->where('codigo', $codigo)->first();

    if ($user === null) {
        $missing[] = $codigo;
        continue;
    }

    $changes = [];
    $fields = [
        'name_user' => $userSeed['name'],
        'email' => $userSeed['email'],
        'activo' => $userSeed['activo'],
        'inhabilitado' => $userSeed['inhabilitado'],
        'first_login' => $userSeed['firstLogin'],
        'locale' => $userSeed['locale'],
        'theme' => $userSeed['theme'],
        'menu_abrir_nueva_pestana' => $userSeed['openInNewTab'] ?? null,
    ];

    foreach ($fields as $key => $expected) {
        $actual = $user->getAttribute($key);
        $expectedNorm = $expected === null ? null : (is_bool($expected) ? ($expected ? 1 : 0) : $expected);
        $actualNorm = $actual === null ? null : (is_bool($actual) ? ($actual ? 1 : 0) : $actual);

        if ((string) $expectedNorm !== (string) $actualNorm) {
            $changes[$key] = $expected;
        }
    }

    if ($changes !== []) {
        echo "Actualizando {$codigo}: ".json_encode($changes, JSON_UNESCAPED_UNICODE).PHP_EOL;
        $user->fill($changes);
        $user->password_hash = $passwordHash;
        $user->save();
        $updated++;
    } else {
        echo "OK {$codigo}".PHP_EOL;
    }
}

echo PHP_EOL."Usuarios actualizados: {$updated}".PHP_EOL;
if ($missing !== []) {
    echo 'Usuarios ausentes (se crearán con seed): '.implode(', ', $missing).PHP_EOL;
}

if (Illuminate\Support\Facades\Schema::hasTable('pq_pedidosweb_clientes')) {
    $clienteMvpLogin = 'CLIMVP001';
    $cliente = Illuminate\Support\Facades\DB::table('pq_pedidosweb_clientes')
        ->where('cod_client', $clienteMvpLogin)
        ->first();

    if ($cliente !== null && $cliente->cod_vended !== null) {
        Illuminate\Support\Facades\DB::table('pq_pedidosweb_clientes')
            ->where('cod_client', $clienteMvpLogin)
            ->update(['cod_vended' => null]);
        echo "Cliente {$clienteMvpLogin}: cod_vended limpiado (era {$cliente->cod_vended})".PHP_EOL;
    }
}

if (Illuminate\Support\Facades\Schema::hasTable('pq_pedidosweb_login')) {
    foreach ($seedUsers as $userSeed) {
        if (($userSeed['codLogin'] ?? null) === null) {
            continue;
        }

        $login = Illuminate\Support\Facades\DB::table('pq_pedidosweb_login')
            ->where('usuario', $userSeed['codigo'])
            ->first();

        if ($login === null) {
            continue;
        }

        $expectedEmail = (string) $userSeed['email'];
        if ((string) $login->e_mail !== $expectedEmail) {
            Illuminate\Support\Facades\DB::table('pq_pedidosweb_login')
                ->where('usuario', $userSeed['codigo'])
                ->update(['e_mail' => $expectedEmail]);
            echo "Login {$userSeed['codigo']}: e_mail alineado a {$expectedEmail}".PHP_EOL;
        }
    }
}
