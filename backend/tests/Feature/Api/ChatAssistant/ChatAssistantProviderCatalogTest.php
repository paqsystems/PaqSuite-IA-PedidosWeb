<?php

namespace Tests\Feature\Api\ChatAssistant;

use App\Models\User;
use App\Services\ChatAssistant\ChatAssistantProviderCatalogService;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SeedsChatAssistantProviderCatalog;
use Tests\TestCase;

final class ChatAssistantProviderCatalogTest extends TestCase
{
    use SeedsChatAssistantProviderCatalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedChatAssistantProviderCatalog();
    }

    public function testProvidersRequiresAuthentication(): void
    {
        $this->getJson('/api/v1/chat-assistant/providers', [
            'X-Paq-Cliente' => 'desarrollo',
        ])->assertUnauthorized();
    }

    public function testProvidersReturnsActiveCatalogInStableOrder(): void
    {
        Sanctum::actingAs($this->createAuthenticatedUser());

        $response = $this->getJson('/api/v1/chat-assistant/providers', [
            'X-Paq-Cliente' => 'desarrollo',
        ]);

        $response->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('respuesta', 'ok');

        $items = $response->json('resultado.items');
        $this->assertIsArray($items);
        $this->assertCount(count(ChatAssistantProviderCatalogService::STABLE_ORDER), $items);
        $this->assertSame(
            ChatAssistantProviderCatalogService::STABLE_ORDER,
            array_column($items, 'providerId'),
        );

        $ollama = $items[0];
        $this->assertSame('ollama', $ollama['providerId']);
        $this->assertSame('Ollama', $ollama['displayName']);
        $this->assertTrue($ollama['supportsVision']);
        $this->assertTrue($ollama['requiresBaseUrl']);
        $this->assertSame('https://ollama.com/download', $ollama['supportUrl']);
        $this->assertIsArray($ollama['suggestedModels']);
        $this->assertContains('llama3.1', $ollama['suggestedModels']);

        $openAi = $items[1];
        $this->assertSame('openai', $openAi['providerId']);
        $this->assertFalse($openAi['requiresBaseUrl']);
    }

    public function testProvidersExcludesInactiveEntries(): void
    {
        Sanctum::actingAs($this->createAuthenticatedUser());

        $items = $this->getJson('/api/v1/chat-assistant/providers', [
            'X-Paq-Cliente' => 'desarrollo',
        ])->json('resultado.items');

        $this->assertNotContains('legacyInactive', array_column($items, 'providerId'));
    }

    private function createAuthenticatedUser(): User
    {
        $user = User::query()->where('codigo', 'cliente.mvp')->first();

        if ($user === null) {
            $this->markTestSkipped('Usuario cliente.mvp no disponible en la base de tests.');
        }

        return $user;
    }
}
