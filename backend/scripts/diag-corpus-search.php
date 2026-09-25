<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$query = $argv[1] ?? 'Por que no aparecen las leyendas que tiene cargadas el cliente';

$resolver = app(\App\Services\ChatAssistant\ChatAssistantCorpusResolver::class);
$matches = $resolver->searchRelevantDocuments($query, 5);

echo "Query: {$query}\n";
echo 'Matches: '.count($matches)."\n\n";

foreach ($matches as $index => $match) {
    echo ($index + 1).". {$match['path']} (score {$match['score']})\n";
    echo $match['excerpt']."\n---\n";
}

$promptBuilder = new \App\Services\ChatAssistant\Llm\ChatAssistantLlmPromptBuilder();
echo "\n=== System prompt (primeros 2500 chars) ===\n";
echo substr($promptBuilder->buildSystemPrompt($matches), 0, 2500)."\n";
