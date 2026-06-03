<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PqPedidoswebLogin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$codigo = $argv[1] ?? 'VHS';
$password = $argv[2] ?? 'Paqsystems26*';

echo 'DB='.config('database.connections.sqlsrv.database').PHP_EOL;

$users = User::query()
    ->where('codigo', $codigo)
    ->orWhereRaw('LOWER(codigo) = ?', [mb_strtolower($codigo)])
    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($codigo).'%'])
    ->get(['id', 'codigo', 'email', 'activo', 'inhabilitado', 'first_login', 'password_hash']);

if ($users->isEmpty()) {
    echo "No hay filas en users para codigo/email relacionado con [{$codigo}]".PHP_EOL;
} else {
    foreach ($users as $user) {
        $hash = (string) $user->getAuthPassword();
        $hashPreview = $hash === '' ? '(vacio)' : substr($hash, 0, 7).'... (len '.strlen($hash).')';
        $check = $hash !== '' && Hash::check($password, $hash);
        echo "users.id={$user->id} codigo={$user->codigo} email={$user->email} activo=".($user->activo ? '1' : '0')
            .' inhabilitado='.($user->inhabilitado ? '1' : '0').' first_login='.($user->first_login ? '1' : '0').PHP_EOL;
        echo "  password_hash: {$hashPreview} Hash::check=".($check ? 'OK' : 'FAIL').PHP_EOL;
    }
}

$logins = PqPedidoswebLogin::query()
    ->where('usuario', $codigo)
    ->orWhere('cod_usuario_web', $codigo)
    ->orWhereRaw('LOWER(usuario) = ?', [mb_strtolower($codigo)])
    ->get();

if ($logins->isEmpty()) {
    echo "No hay filas en pq_pedidosweb_login para [{$codigo}]".PHP_EOL;
} else {
    foreach ($logins as $login) {
        $bcrypt = (string) ($login->password_bcrypt ?? '');
        $bcryptCheck = $bcrypt !== '' && Hash::check($password, $bcrypt);
        echo "login usuario={$login->usuario} cod_usuario_web={$login->cod_usuario_web} tipo={$login->tipo_cuenta}".PHP_EOL;
        echo '  password_bcrypt: '.($bcrypt === '' ? '(vacio)' : substr($bcrypt, 0, 7).'...').' check='.($bcryptCheck ? 'OK' : 'FAIL').PHP_EOL;
        echo '  password_sha1 set='.(filled($login->password_sha1) ? 'yes' : 'no').' legacy password set='.(filled($login->password) ? 'yes' : 'no').PHP_EOL;
    }
}

try {
    $loginService = $app->make(App\Services\Auth\LoginService::class);
    $result = $loginService->login($codigo, $password);
    echo 'LoginService OK, token len='.strlen((string) ($result['token'] ?? '')).PHP_EOL;
} catch (Throwable $e) {
    echo 'LoginService ERROR: '.$e->getMessage().PHP_EOL;
}
