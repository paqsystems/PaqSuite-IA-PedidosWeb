<?php

/**
 * Smoke API + guía — Chat Asistente IA (SPEC-001-10 / GEN-10).
 * Uso: php scripts/smoke-chat-assistant.php [--base-url=http://127.0.0.1:8000]
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = 'http://127.0.0.1:8000';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--base-url=')) {
        $baseUrl = rtrim(substr($arg, 11), '/');
    }
}

$tenant = 'desarrollo';
$loginCodigo = 'cliente.mvp';
$loginPassword = (string) env('SEED_MVP_PASSWORD', 'ChangeMeInLocalEnv');

function smokeStep(string $label, callable $fn): void
{
    echo "\n=== {$label} ===\n";
    $fn();
    echo "OK\n";
}

function smokeRequest(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null,
): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $responseBody = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($responseBody === false) {
        throw new RuntimeException("curl failed for {$method} {$url}");
    }

    return [
        'status' => $status,
        'body' => $responseBody,
        'json' => json_decode($responseBody, true),
    ];
}

function smokeJson(array $response, int $expectedStatus = 200): array
{
    if ($response['status'] !== $expectedStatus) {
        throw new RuntimeException(
            "Expected HTTP {$expectedStatus}, got {$response['status']}: {$response['body']}",
        );
    }

    $json = $response['json'];

    if (! is_array($json)) {
        throw new RuntimeException('Response is not JSON: '.$response['body']);
    }

    return $json;
}

function tinyPngBase64(): string
{
    return 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
}

echo "Chat Asistente IA — smoke API\n";
echo "Base URL: {$baseUrl}\n";
echo "Usuario: {$loginCodigo}\n";

$token = '';

smokeStep('1. Login', function () use ($baseUrl, $tenant, $loginCodigo, $loginPassword, &$token): void {
    $json = smokeJson(smokeRequest(
        'POST',
        "{$baseUrl}/api/v1/auth/login",
        [
            'Content-Type: application/json',
            'Accept: application/json',
            "X-Paq-Cliente: {$tenant}",
        ],
        json_encode([
            'codigo' => $loginCodigo,
            'password' => $loginPassword,
        ], JSON_THROW_ON_ERROR),
    ));

    $token = (string) ($json['resultado']['token'] ?? '');

    if ($token === '') {
        throw new RuntimeException('Login sin token. Verificá SEED_MVP_PASSWORD en backend/.env');
    }

    echo "Token obtenido\n";
});

$authHeaders = [
    'Content-Type: application/json',
    'Accept: application/json',
    "X-Paq-Cliente: {$tenant}",
    "Authorization: Bearer {$token}",
];

smokeStep('2. Catálogo de proveedores', function () use ($baseUrl, $authHeaders): void {
    $json = smokeJson(smokeRequest('GET', "{$baseUrl}/api/v1/chat-assistant/providers", $authHeaders));
    $items = $json['resultado']['items'] ?? [];

    if (! is_array($items) || $items === []) {
        throw new RuntimeException('Catálogo vacío. Ejecutá seed del catálogo de proveedores.');
    }

    echo 'Proveedores activos: '.count($items)."\n";
});

smokeStep('3. Configuración personal (lectura)', function () use ($baseUrl, $authHeaders): void {
    $json = smokeJson(smokeRequest('GET', "{$baseUrl}/api/v1/chat-assistant/me/configuration", $authHeaders));
    $resultado = $json['resultado'] ?? [];

    echo 'hasConfiguration='.(($resultado['hasConfiguration'] ?? false) ? 'true' : 'false');
    echo ', supportsVision='.(($resultado['supportsVision'] ?? false) ? 'true' : 'false')."\n";
});

smokeStep('4. Guardar configuración Ollama (visión)', function () use ($baseUrl, $authHeaders): void {
    $json = smokeJson(smokeRequest(
        'PUT',
        "{$baseUrl}/api/v1/chat-assistant/me/configuration",
        $authHeaders,
        json_encode([
            'providerId' => 'ollama',
            'apiKey' => 'smoke-local-key',
            'modelId' => 'llama3.1',
            'baseUrl' => 'http://localhost:11434',
        ], JSON_THROW_ON_ERROR),
    ));

    if (($json['resultado']['supportsVision'] ?? false) !== true) {
        throw new RuntimeException('Se esperaba supportsVision=true para Ollama');
    }

    echo "Configuración guardada\n";
});

smokeStep('5. Consulta textual con referencias', function () use ($baseUrl, $authHeaders): void {
    $json = smokeJson(smokeRequest(
        'POST',
        "{$baseUrl}/api/v1/chat-assistant/messages",
        $authHeaders,
        json_encode([
            'message' => 'Necesito ayuda para grabar un pedido en el portal',
        ], JSON_THROW_ON_ERROR),
    ));

    $reply = (string) ($json['resultado']['reply'] ?? '');
    $references = $json['resultado']['references'] ?? [];

    if ($reply === '') {
        throw new RuntimeException('Reply vacío');
    }

    echo 'Reply chars: '.strlen($reply).', references: '.(is_array($references) ? count($references) : 0)."\n";
});

smokeStep('6. Consulta solo con imagen', function () use ($baseUrl, $authHeaders): void {
    smokeJson(smokeRequest(
        'POST',
        "{$baseUrl}/api/v1/chat-assistant/messages",
        $authHeaders,
        json_encode([
            'message' => '',
            'images' => [[
                'fileName' => 'smoke.png',
                'mimeType' => 'image/png',
                'contentBase64' => tinyPngBase64(),
            ]],
        ], JSON_THROW_ON_ERROR),
    ));

    echo "Imagen aceptada\n";
});

smokeStep('7. Chat deshabilitado → 422', function () use ($baseUrl, $authHeaders): void {
    smokeJson(smokeRequest(
        'PATCH',
        "{$baseUrl}/api/v1/chat-assistant/me/configuration/status",
        $authHeaders,
        json_encode(['isEnabled' => false], JSON_THROW_ON_ERROR),
    ));

    $json = smokeJson(smokeRequest(
        'POST',
        "{$baseUrl}/api/v1/chat-assistant/messages",
        $authHeaders,
        json_encode(['message' => 'Consulta con chat deshabilitado'], JSON_THROW_ON_ERROR),
    ), 422);

    if (($json['respuesta'] ?? '') !== 'chatAssistant.configurationRequired') {
        throw new RuntimeException('Respuesta inesperada: '.($json['respuesta'] ?? ''));
    }

    smokeJson(smokeRequest(
        'PATCH',
        "{$baseUrl}/api/v1/chat-assistant/me/configuration/status",
        $authHeaders,
        json_encode(['isEnabled' => true], JSON_THROW_ON_ERROR),
    ));

    echo "configurationRequired confirmado; chat re-habilitado\n";
});

smokeStep('8. Imagen sin visión → 422', function () use ($baseUrl, $authHeaders, $loginCodigo): void {
    $userId = \App\Models\User::query()->where('codigo', $loginCodigo)->value('id');

    if ($userId === null) {
        throw new RuntimeException('Usuario no encontrado');
    }

    \Illuminate\Support\Facades\DB::table('pq_asistente_ia_credenciales')
        ->where('user_id', $userId)
        ->update(['supports_vision' => false]);

    $json = smokeJson(smokeRequest(
        'POST',
        "{$baseUrl}/api/v1/chat-assistant/messages",
        $authHeaders,
        json_encode([
            'message' => 'Pantalla con error',
            'images' => [[
                'fileName' => 'smoke.png',
                'mimeType' => 'image/png',
                'contentBase64' => tinyPngBase64(),
            ]],
        ], JSON_THROW_ON_ERROR),
    ), 422);

    if (($json['respuesta'] ?? '') !== 'chatAssistant.visionUnsupported') {
        throw new RuntimeException('Respuesta inesperada: '.($json['respuesta'] ?? ''));
    }

    \Illuminate\Support\Facades\DB::table('pq_asistente_ia_credenciales')
        ->where('user_id', $userId)
        ->update(['supports_vision' => true]);

    echo "visionUnsupported confirmado; supportsVision restaurado\n";
});

echo "\n=== Smoke API completado ===\n";
echo "\n--- Guía smoke manual en navegador (http://localhost:5173) ---\n";
echo "1. Login: {$loginCodigo} / contraseña SEED_MVP_PASSWORD (backend/.env)\n";
echo "2. Avatar → «Chat Asistente IA» → nueva pestaña /chat-assistant\n";
echo "3. Ver mensaje inicial y composer\n";
echo "4. /preferences → verificar config Ollama guardada por este smoke\n";
echo "5. Enviar: «¿Cómo grabo un pedido?» → respuesta + referencias documentales\n";
echo "6. Adjuntar captura PNG → enviar → verificar contador 1000 con imágenes\n";
echo "7. Cambiar a proveedor sin visión (o supportsVision=false) → aviso en picker\n";
