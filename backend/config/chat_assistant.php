<?php

return [
    'llm_timeout_seconds' => (int) env('CHAT_ASSISTANT_LLM_TIMEOUT_SECONDS', 60),

    /**
     * Modelos sugeridos por proveedor para la UI de configuración (BYOK).
     *
     * @var array<string, list<string>>
     */
    'provider_suggested_models' => [
        'ollama' => ['llama3.1', 'llama3.2', 'mistral', 'qwen2.5', 'gemma2'],
        'openai' => ['gpt-4.1', 'gpt-4.1-mini', 'gpt-4o', 'gpt-4o-mini', 'o1-mini'],
        'anthropic' => ['claude-3-5-sonnet-latest', 'claude-3-5-haiku-latest', 'claude-3-opus-latest'],
        'googleGemini' => ['gemini-2.0-flash', 'gemini-1.5-pro', 'gemini-1.5-flash'],
        'azureOpenAi' => ['gpt-4.1', 'gpt-4.1-mini', 'gpt-4o', 'gpt-4o-mini'],
        'openRouter' => ['openai/gpt-4o-mini', 'anthropic/claude-3.5-sonnet', 'google/gemini-2.0-flash-001'],
        'groq' => ['llama-3.1-8b-instant', 'llama-3.3-70b-versatile', 'mixtral-8x7b-32768'],
        'mistral' => ['mistral-large-latest', 'mistral-small-latest', 'codestral-latest'],
    ],
];
