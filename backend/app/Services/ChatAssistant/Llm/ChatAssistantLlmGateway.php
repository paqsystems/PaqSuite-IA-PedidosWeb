<?php

namespace App\Services\ChatAssistant\Llm;

use App\Exceptions\ChatAssistantMessageException;
use App\Support\ChatAssistantMessageErrorCodes;
use Illuminate\Support\Facades\Http;

class ChatAssistantLlmGateway
{
    public function __construct(
        private readonly ChatAssistantCredentialResolver $credentialResolver,
        private readonly ChatAssistantLlmPromptBuilder $promptBuilder,
        private readonly ChatAssistantLlmProviderEndpoints $providerEndpoints,
    ) {}

    /**
     * @param  list<array{title: string, path: string, excerpt: string}>  $corpusMatches
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     */
    public function generateReply(
        \App\Models\User $user,
        string $message,
        array $corpusMatches,
        array $normalizedImages,
    ): string {
        $context = $this->credentialResolver->resolve($user);
        $systemPrompt = $this->promptBuilder->buildSystemPrompt($corpusMatches);
        $userPrompt = $this->promptBuilder->buildUserPrompt($message, $normalizedImages !== []);

        try {
            $endpoint = $this->providerEndpoints->resolve($context);
        } catch (\InvalidArgumentException) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerUnsupported,
                'chatAssistant.providerUnsupported',
            );
        }

        try {
            return match ($context->providerId) {
                'anthropic' => $this->invokeAnthropic($endpoint, $systemPrompt, $userPrompt, $normalizedImages, $context),
                'googleGemini' => $this->invokeGoogleGemini($endpoint, $systemPrompt, $userPrompt, $normalizedImages, $context),
                'ollama' => $this->invokeOllama($endpoint, $systemPrompt, $userPrompt, $normalizedImages, $context),
                default => $this->invokeOpenAiCompatible($endpoint, $systemPrompt, $userPrompt, $normalizedImages, $context),
            };
        } catch (ChatAssistantMessageException $exception) {
            throw $exception;
        } catch (\Throwable) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
            );
        }
    }

    /**
     * @param  array{url: string, headers: array<string, string>, model: string}  $endpoint
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     */
    private function invokeOpenAiCompatible(
        array $endpoint,
        string $systemPrompt,
        string $userPrompt,
        array $normalizedImages,
        ChatAssistantInvocationContext $context,
    ): string {
        $userContent = $this->buildOpenAiUserContent($userPrompt, $normalizedImages, $context);

        $payload = [
            'model' => $endpoint['model'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContent],
            ],
        ];

        if (! $this->shouldOmitOpenAiTemperature($endpoint['model'])) {
            $payload['temperature'] = 0.2;
        }

        $response = Http::timeout($this->timeoutSeconds())
            ->withHeaders($endpoint['headers'])
            ->post($endpoint['url'], $payload);

        if (! $response->successful()) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
            );
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        return $this->normalizeReplyContent($content);
    }

    /**
     * @param  array{url: string, headers: array<string, string>, model: string}  $endpoint
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     */
    private function invokeOllama(
        array $endpoint,
        string $systemPrompt,
        string $userPrompt,
        array $normalizedImages,
        ChatAssistantInvocationContext $context,
    ): string {
        if ($normalizedImages !== [] && ! $context->supportsVision) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::visionUnsupported,
                'chatAssistant.visionUnsupported',
            );
        }

        $payload = [
            'model' => $endpoint['model'],
            'stream' => false,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        if ($normalizedImages !== [] && $context->supportsVision) {
            $payload['messages'][1]['images'] = array_map(
                static fn (array $image): string => base64_encode($image['content']),
                $normalizedImages,
            );
        }

        $response = Http::timeout($this->timeoutSeconds())
            ->withHeaders($endpoint['headers'])
            ->post($endpoint['url'], $payload);

        if (! $response->successful()) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
            );
        }

        $content = data_get($response->json(), 'message.content');

        return $this->normalizeReplyContent($content);
    }

    /**
     * @param  array{url: string, headers: array<string, string>, model: string}  $endpoint
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     */
    private function invokeAnthropic(
        array $endpoint,
        string $systemPrompt,
        string $userPrompt,
        array $normalizedImages,
        ChatAssistantInvocationContext $context,
    ): string {
        if ($normalizedImages !== []) {
            $this->assertVisionSupported($context);
        }

        $contentBlocks = [['type' => 'text', 'text' => $userPrompt]];

        foreach ($normalizedImages as $image) {
            $contentBlocks[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $image['mimeType'],
                    'data' => base64_encode($image['content']),
                ],
            ];
        }

        $response = Http::timeout($this->timeoutSeconds())
            ->withHeaders($endpoint['headers'])
            ->post($endpoint['url'], [
                'model' => $endpoint['model'],
                'max_tokens' => 1024,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $contentBlocks],
                ],
            ]);

        if (! $response->successful()) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
            );
        }

        $textBlocks = data_get($response->json(), 'content', []);
        $parts = [];

        if (is_array($textBlocks)) {
            foreach ($textBlocks as $block) {
                if (is_array($block) && ($block['type'] ?? '') === 'text') {
                    $parts[] = (string) ($block['text'] ?? '');
                }
            }
        }

        return $this->normalizeReplyContent(implode("\n\n", array_filter($parts)));
    }

    /**
     * @param  array{url: string, headers: array<string, string>, model: string}  $endpoint
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     */
    private function invokeGoogleGemini(
        array $endpoint,
        string $systemPrompt,
        string $userPrompt,
        array $normalizedImages,
        ChatAssistantInvocationContext $context,
    ): string {
        if ($normalizedImages !== []) {
            $this->assertVisionSupported($context);
        }

        $parts = [
            ['text' => $systemPrompt."\n\n".$userPrompt],
        ];

        foreach ($normalizedImages as $image) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $image['mimeType'],
                    'data' => base64_encode($image['content']),
                ],
            ];
        }

        $response = Http::timeout($this->timeoutSeconds())
            ->withHeaders($endpoint['headers'])
            ->post($endpoint['url'], [
                'contents' => [
                    ['role' => 'user', 'parts' => $parts],
                ],
            ]);

        if (! $response->successful()) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
            );
        }

        $content = data_get($response->json(), 'candidates.0.content.parts.0.text');

        return $this->normalizeReplyContent($content);
    }

    /**
     * @param  list<array{fileName: string, mimeType: string, content: string}>  $normalizedImages
     * @return string|list<array<string, mixed>>
     */
    private function buildOpenAiUserContent(
        string $userPrompt,
        array $normalizedImages,
        ChatAssistantInvocationContext $context,
    ): string|array {
        if ($normalizedImages === []) {
            return $userPrompt;
        }

        $this->assertVisionSupported($context);

        $content = [
            ['type' => 'text', 'text' => $userPrompt],
        ];

        foreach ($normalizedImages as $image) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:'.$image['mimeType'].';base64,'.base64_encode($image['content']),
                ],
            ];
        }

        return $content;
    }

    private function assertVisionSupported(ChatAssistantInvocationContext $context): void
    {
        if (! $context->supportsVision) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::visionUnsupported,
                'chatAssistant.visionUnsupported',
            );
        }
    }

    private function normalizeReplyContent(mixed $content): string
    {
        $reply = trim(is_string($content) ? $content : '');

        if ($reply === '') {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
            );
        }

        return $reply;
    }

    private function timeoutSeconds(): int
    {
        return max(5, (int) config('chat_assistant.llm_timeout_seconds', 60));
    }

    private function shouldOmitOpenAiTemperature(string $modelId): bool
    {
        $normalizedModelId = mb_strtolower(trim($modelId));

        return preg_match('/^(gpt-5|o\d)/', $normalizedModelId) === 1;
    }
}
