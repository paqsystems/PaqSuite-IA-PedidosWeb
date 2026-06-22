<?php

namespace Tests\Unit\Services\ChatAssistant\Llm;

use App\Exceptions\ChatAssistantMessageException;
use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\User;
use App\Services\ChatAssistant\Llm\ChatAssistantCredentialResolver;
use App\Services\ChatAssistant\Llm\ChatAssistantInvocationContext;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmGateway;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmPromptBuilder;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmProviderEndpoints;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ChatAssistantLlmGatewayTest extends TestCase
{
    #[Test]
    public function it_throws_when_openai_compatible_provider_returns_error(): void
    {
        Http::fake([
            'https://api.groq.com/*' => Http::response(['error' => 'invalid'], 401),
        ]);

        $user = User::query()->where('codigo', 'cliente.mvp')->first();

        if ($user === null) {
            $this->markTestSkipped('Usuario cliente.mvp no disponible en la base de tests.');
        }

        PqPedidoswebAsistenteIaCredencial::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'provider_id' => 'groq',
                'base_url' => null,
                'api_key_encrypted' => Crypt::encryptString('secret-groq-key'),
                'model_id' => 'llama-3.1-8b-instant',
                'supports_vision' => true,
                'is_enabled' => true,
            ],
        );

        $gateway = new ChatAssistantLlmGateway(
            new ChatAssistantCredentialResolver(),
            new ChatAssistantLlmPromptBuilder(),
            new ChatAssistantLlmProviderEndpoints(),
        );

        $this->expectException(ChatAssistantMessageException::class);

        try {
            $gateway->generateReply($user, 'Como grabo un pedido?', [], []);
        } catch (ChatAssistantMessageException $exception) {
            $this->assertSame('chatAssistant.providerInvocationFailed', $exception->respuestaKey);
            throw $exception;
        }
    }
}
