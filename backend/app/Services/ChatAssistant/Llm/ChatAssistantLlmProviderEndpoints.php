<?php

namespace App\Services\ChatAssistant\Llm;

final class ChatAssistantLlmProviderEndpoints
{
    /**
     * @return array{url: string, headers: array<string, string>, model: string}
     */
    public function resolve(ChatAssistantInvocationContext $context): array
    {
        return match ($context->providerId) {
            'openai' => $this->openAiCompatible(
                'https://api.openai.com/v1/chat/completions',
                $context,
            ),
            'groq' => $this->openAiCompatible(
                'https://api.groq.com/openai/v1/chat/completions',
                $context,
            ),
            'openRouter' => $this->openRouter($context),
            'mistral' => $this->openAiCompatible(
                'https://api.mistral.ai/v1/chat/completions',
                $context,
            ),
            'azureOpenAi' => $this->azureOpenAi($context),
            'ollama' => $this->ollama($context),
            'anthropic' => $this->anthropic($context),
            'googleGemini' => $this->googleGemini($context),
            default => throw new \InvalidArgumentException('Unsupported provider: '.$context->providerId),
        };
    }

    /**
     * @return array{url: string, headers: array<string, string>, model: string}
     */
    private function openAiCompatible(string $url, ChatAssistantInvocationContext $context): array
    {
        return [
            'url' => $url,
            'headers' => [
                'Authorization' => 'Bearer '.$context->apiKey,
                'Content-Type' => 'application/json',
            ],
            'model' => $this->normalizeModelId($context->providerId, $context->modelId),
        ];
    }

    /**
     * @return array{url: string, headers: array<string, string>, model: string}
     */
    private function openRouter(ChatAssistantInvocationContext $context): array
    {
        return [
            'url' => 'https://openrouter.ai/api/v1/chat/completions',
            'headers' => [
                'Authorization' => 'Bearer '.$context->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => (string) config('app.url', 'http://localhost'),
                'X-Title' => (string) config('app.name', 'PedidosWeb'),
            ],
            'model' => $this->normalizeModelId($context->providerId, $context->modelId),
        ];
    }

    /**
     * @return array{url: string, headers: array<string, string>, model: string}
     */
    private function azureOpenAi(ChatAssistantInvocationContext $context): array
    {
        $baseUrl = rtrim($context->baseUrl, '/');
        $deployment = rawurlencode($context->modelId);
        $apiVersion = '2024-02-15-preview';

        return [
            'url' => "{$baseUrl}/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}",
            'headers' => [
                'api-key' => $context->apiKey,
                'Content-Type' => 'application/json',
            ],
            'model' => $this->normalizeModelId($context->providerId, $context->modelId),
        ];
    }

    /**
     * @return array{url: string, headers: array<string, string>, model: string}
     */
    private function ollama(ChatAssistantInvocationContext $context): array
    {
        $baseUrl = rtrim($context->baseUrl !== '' ? $context->baseUrl : 'http://127.0.0.1:11434', '/');

        $headers = ['Content-Type' => 'application/json'];

        if ($context->apiKey !== '') {
            $headers['Authorization'] = 'Bearer '.$context->apiKey;
        }

        return [
            'url' => "{$baseUrl}/api/chat",
            'headers' => $headers,
            'model' => $this->normalizeModelId($context->providerId, $context->modelId),
        ];
    }

    /**
     * @return array{url: string, headers: array<string, string>, model: string}
     */
    private function anthropic(ChatAssistantInvocationContext $context): array
    {
        return [
            'url' => 'https://api.anthropic.com/v1/messages',
            'headers' => [
                'x-api-key' => $context->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'model' => $this->normalizeModelId($context->providerId, $context->modelId),
        ];
    }

    /**
     * @return array{url: string, headers: array<string, string>, model: string}
     */
    private function googleGemini(ChatAssistantInvocationContext $context): array
    {
        $model = rawurlencode($context->modelId);

        return [
            'url' => "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($context->apiKey),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'model' => $this->normalizeModelId($context->providerId, $context->modelId),
        ];
    }

    private function normalizeModelId(string $providerId, string $modelId): string
    {
        $normalizedModelId = trim($modelId);

        if ($providerId === 'azureOpenAi') {
            return $normalizedModelId;
        }

        return mb_strtolower($normalizedModelId);
    }
}
