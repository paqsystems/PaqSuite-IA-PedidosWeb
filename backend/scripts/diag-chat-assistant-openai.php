<?php

declare(strict_types=1);

/**
 * Diagnóstico OpenAI por usuario — sin exponer API key.
 * Uso: php scripts/diag-chat-assistant-openai.php VHS
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\User;
use App\Services\ChatAssistant\Llm\ChatAssistantCredentialResolver;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmProviderEndpoints;
use Illuminate\Support\Facades\Http;

$codigo = $argv[1] ?? 'VHS';

$user = User::query()->where('codigo', $codigo)->first();

if ($user === null) {
    fwrite(STDERR, "Usuario no encontrado: {$codigo}\n");
    exit(1);
}

$credencial = PqPedidoswebAsistenteIaCredencial::query()->where('user_id', $user->id)->first();

if ($credencial === null) {
    fwrite(STDERR, "Sin configuración en pq_asistente_ia_credenciales\n");
    exit(1);
}

echo "Usuario: {$codigo} (id {$user->id})\n";
echo "provider_id: {$credencial->provider_id}\n";
echo "model_id guardado: {$credencial->model_id}\n";
echo "is_enabled: ".($credencial->is_enabled ? 'true' : 'false')."\n\n";

$resolver = new ChatAssistantCredentialResolver();
$endpoints = new ChatAssistantLlmProviderEndpoints();

try {
    $context = $resolver->resolve($user);
} catch (Throwable $exception) {
    fwrite(STDERR, 'No se pudo resolver credencial: '.$exception->getMessage()."\n");
    exit(1);
}

$endpoint = $endpoints->resolve($context);

$modelsToTry = array_values(array_unique([
    (string) $credencial->model_id,
    mb_strtolower((string) $credencial->model_id),
    'gpt-4o-mini',
]));

foreach ($modelsToTry as $modelId) {
    echo "=== Prueba modelo: {$modelId} ===\n";

    $payload = [
        'model' => $modelId,
        'messages' => [
            ['role' => 'user', 'content' => 'Responde solo OK'],
        ],
    ];

    if ($modelId !== 'gpt-5.5') {
        $payload['temperature'] = 0.2;
    }

    $response = Http::timeout(45)
        ->withHeaders($endpoint['headers'])
        ->post($endpoint['url'], $payload);

    echo 'HTTP: '.$response->status()."\n";

    $json = $response->json();

    if (is_array($json)) {
        $errorMessage = data_get($json, 'error.message') ?? data_get($json, 'message');
        $errorType = data_get($json, 'error.type') ?? data_get($json, 'error.code');

        if ($errorMessage !== null) {
            echo 'error: '.(string) $errorMessage."\n";
        }

        if ($errorType !== null) {
            echo 'error_type: '.(string) $errorType."\n";
        }

        $content = data_get($json, 'choices.0.message.content');

        if (is_string($content) && $content !== '') {
            echo 'content: '.$content."\n";
        }
    } else {
        echo "body: ".substr($response->body(), 0, 300)."\n";
    }

    echo "\n";
}
