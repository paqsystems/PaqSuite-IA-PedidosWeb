<?php

/**
 * Prueba real — Chat Asistente IA con OpenAI (BYOK desde .env local, solo script).
 * Uso: php scripts/real-test-chat-openai.php [--model=gpt-4o-mini]
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = 'http://127.0.0.1:8000';
$tenant = 'desarrollo';
$loginCodigo = 'cliente.mvp';
$loginPassword = (string) env('SEED_MVP_PASSWORD', 'ChangeMeInLocalEnv');
$openAiApiKey = trim((string) env('CHAT_ASSISTANT_LLM_OPENAI_API_KEY', ''));
$modelId = 'gpt-4o-mini';

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--model=')) {
        $modelId = trim(substr($arg, 8));
    }
}

if ($openAiApiKey === '') {
    fwrite(STDERR, "Falta CHAT_ASSISTANT_LLM_OPENAI_API_KEY en backend/.env\n");
    exit(1);
}

function realRequest(string $method, string $url, array $headers = [], ?string $body = null): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 120,
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

function realJson(array $response, int $expectedStatus = 200): array
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

echo "Prueba real OpenAI — Chat Asistente IA\n";
echo "Backend: {$baseUrl}\n";
echo "Usuario: {$loginCodigo}\n";
echo "Modelo: {$modelId}\n\n";

echo "=== 1. Login ===\n";
$loginJson = realJson(realRequest(
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

$token = (string) ($loginJson['resultado']['token'] ?? '');

if ($token === '') {
    throw new RuntimeException('Login sin token');
}

echo "OK\n\n";

$authHeaders = [
    'Content-Type: application/json',
    'Accept: application/json',
    "X-Paq-Cliente: {$tenant}",
    "Authorization: Bearer {$token}",
];

echo "=== 2. Configurar OpenAI (BYOK) ===\n";
realJson(realRequest(
    'PUT',
    "{$baseUrl}/api/v1/chat-assistant/me/configuration",
    $authHeaders,
    json_encode([
        'providerId' => 'openai',
        'apiKey' => $openAiApiKey,
        'modelId' => $modelId,
    ], JSON_THROW_ON_ERROR),
));
echo "OK\n\n";

echo "=== 3. Consulta real al proveedor ===\n";
$startedAt = microtime(true);
$messageJson = realJson(realRequest(
    'POST',
    "{$baseUrl}/api/v1/chat-assistant/messages",
    $authHeaders,
    json_encode([
        'message' => '¿Cómo grabo un pedido en PedidosWeb? Respondé en pasos concretos.',
    ], JSON_THROW_ON_ERROR),
));
$elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);

$resultado = $messageJson['resultado'] ?? [];
$reply = (string) ($resultado['reply'] ?? '');
$references = $resultado['references'] ?? [];
$requiresSupportFollowup = (bool) ($resultado['requiresSupportFollowup'] ?? false);

echo "Tiempo: {$elapsedMs} ms\n";
echo 'Referencias: '.(is_array($references) ? count($references) : 0)."\n";
echo 'requiresSupportFollowup: '.($requiresSupportFollowup ? 'true' : 'false')."\n\n";
echo "--- Respuesta ---\n";
echo $reply."\n";
echo "--- Fin respuesta ---\n\n";

if (is_array($references) && $references !== []) {
    echo "Referencias documentales:\n";
    foreach ($references as $reference) {
        echo '- '.((string) ($reference['title'] ?? '')).' ('.((string) ($reference['path'] ?? '')).")\n";
    }
}

echo "\nPrueba real completada.\n";
echo "UI: http://localhost:5173/chat-assistant\n";
