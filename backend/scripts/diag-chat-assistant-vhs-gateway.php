<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\ChatAssistant\ChatAssistantCorpusResolver;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmGateway;

$user = User::query()->where('codigo', 'VHS')->first();

if ($user === null) {
    fwrite(STDERR, "Usuario VHS no encontrado\n");
    exit(1);
}

$corpus = app(ChatAssistantCorpusResolver::class)->searchRelevantDocuments(
    'como pasar de presupuesto a pedido',
);

try {
    $reply = app(ChatAssistantLlmGateway::class)->generateReply(
        $user,
        '¿cómo puedo pasar de un presupuesto a un pedido?',
        $corpus,
        [],
    );

    echo "OK\n";
    echo substr($reply, 0, 400)."...\n";
} catch (Throwable $exception) {
    echo 'ERROR: '.$exception->getMessage()."\n";
    exit(1);
}
