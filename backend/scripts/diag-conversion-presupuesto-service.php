<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\PedidosWeb\ComprobanteGrabacionValidator;
use App\Services\PedidosWeb\PedidoService;
use Illuminate\Support\Facades\DB;

$presupuestoId = $argv[1] ?? '1fb09641-eb50-441a-9311-6f0cf7618ac3';
$userCodigo = $argv[2] ?? 'PAQ';

$user = User::query()->where('codigo', $userCodigo)->first();
if ($user === null) {
    $sample = User::query()->limit(5)->get(['codigo', 'name_user', 'activo']);
    echo 'Usuario no encontrado: '.$userCodigo.PHP_EOL;
    echo 'Users sample:'.PHP_EOL;
    foreach ($sample as $row) {
        echo '  '.json_encode($row, JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
    exit(1);
}

/** @var PedidoService $pedidoService */
$pedidoService = $app->make(PedidoService::class);
/** @var ComprobanteGrabacionValidator $validator */
$validator = $app->make(ComprobanteGrabacionValidator::class);

echo 'Usuario: '.$user->codigo.PHP_EOL;

try {
    $comprobante = $pedidoService->getComprobante($presupuestoId, $user);
} catch (Throwable $throwable) {
    echo 'getComprobante error: '.$throwable->getMessage().PHP_EOL;
    exit(1);
}

echo 'Presupuesto estado='.$comprobante['cabecera']['estado'].PHP_EOL;
echo 'Renglones:'.PHP_EOL;
foreach ($comprobante['detalle'] as $renglon) {
    echo '  '.json_encode($renglon, JSON_UNESCAPED_UNICODE).PHP_EOL;
}

$errores = $validator->collectComprobanteGrabableErrors($comprobante['cabecera'], $comprobante['detalle']);
echo 'Errores validacion: '.json_encode($errores, JSON_UNESCAPED_UNICODE).PHP_EOL;

if ($errores !== []) {
    exit(0);
}

$payload = [
    'accionGrabacion' => 'pedido',
    'cod_presupuesto_origen' => $presupuestoId,
    'cabecera' => $comprobante['cabecera'],
    'renglones' => $comprobante['detalle'],
];

echo PHP_EOL.'Intentando grabar conversion...'.PHP_EOL;
try {
    $result = $pedidoService->grabarComprobante($payload, $user);
    echo 'OK: '.json_encode($result, JSON_UNESCAPED_UNICODE).PHP_EOL;
} catch (Throwable $throwable) {
    echo 'ERROR: '.$throwable->getMessage().PHP_EOL;
    if (method_exists($throwable, 'respuestaKey')) {
        echo 'respuestaKey: '.$throwable->respuestaKey().PHP_EOL;
    }
    echo $throwable->getTraceAsString().PHP_EOL;
}
