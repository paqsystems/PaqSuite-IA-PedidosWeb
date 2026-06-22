<?php

namespace App\Services\ChatAssistant\Llm;

final class ChatAssistantInvocationContext
{
    public function __construct(
        public readonly string $providerId,
        public readonly string $modelId,
        public readonly string $baseUrl,
        public readonly string $apiKey,
        public readonly bool $supportsVision,
    ) {}
}
