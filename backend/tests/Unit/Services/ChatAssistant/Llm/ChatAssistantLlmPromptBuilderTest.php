<?php

namespace Tests\Unit\Services\ChatAssistant\Llm;

use App\Services\ChatAssistant\Llm\ChatAssistantLlmPromptBuilder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ChatAssistantLlmPromptBuilderTest extends TestCase
{
    #[Test]
    public function it_builds_system_prompt_with_corpus_excerpts(): void
    {
        $builder = new ChatAssistantLlmPromptBuilder();

        $prompt = $builder->buildSystemPrompt([
            [
                'title' => 'Manual de pedidos',
                'path' => '99-manual-usuario/PedidosWeb.md',
                'excerpt' => 'Para grabar un pedido ingrese a Carga de Pedidos.',
            ],
        ]);

        $this->assertStringContainsString('PedidosWeb', $prompt);
        $this->assertStringContainsString('99-manual-usuario/PedidosWeb.md', $prompt);
        $this->assertStringContainsString('Carga de Pedidos', $prompt);
    }

    #[Test]
    public function it_builds_image_only_user_prompt(): void
    {
        $builder = new ChatAssistantLlmPromptBuilder();

        $prompt = $builder->buildUserPrompt('', true);

        $this->assertStringContainsString('captura', mb_strtolower($prompt));
    }
}
