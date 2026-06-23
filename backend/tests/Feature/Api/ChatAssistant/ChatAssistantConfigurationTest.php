<?php

namespace Tests\Feature\Api\ChatAssistant;

use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\Support\SeedsChatAssistantProviderCatalog;
use Tests\TestCase;

final class ChatAssistantConfigurationTest extends TestCase
{
    use SeedsChatAssistantProviderCatalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedChatAssistantProviderCatalog();
    }

    public function testListConfigurationsReturnsItems(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/chat-assistant/me/configurations', [
            'displayName' => 'Groq laboral',
            'providerId' => 'groq',
            'apiKey' => 'secret-groq-key',
            'modelId' => 'llama-3.1-8b-instant',
        ], $this->tenantHeaders())->assertOk();

        $this->postJson('/api/v1/chat-assistant/me/configurations', [
            'displayName' => 'Ollama local',
            'providerId' => 'ollama',
            'apiKey' => 'secret-ollama-key',
            'modelId' => 'llama3.1',
            'baseUrl' => 'http://localhost:11434',
        ], $this->tenantHeaders())->assertOk();

        $this->getJson('/api/v1/chat-assistant/me/configurations', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonCount(2, 'resultado.items');
    }

    public function testDeleteConfigurationRemovesRecord(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/v1/chat-assistant/me/configurations', [
            'displayName' => 'Groq laboral',
            'providerId' => 'groq',
            'apiKey' => 'secret-groq-key',
            'modelId' => 'llama-3.1-8b-instant',
        ], $this->tenantHeaders())->assertOk();

        $credentialId = (int) $createResponse->json('resultado.credentialId');

        $this->deleteJson("/api/v1/chat-assistant/me/configurations/{$credentialId}", [], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('respuesta', 'chatAssistant.configurationDeleted');

        $this->assertDatabaseMissing('pq_pedidosweb_asistente_ia_credenciales', [
            'id_credencial' => $credentialId,
            'user_id' => $user->id,
        ]);
    }

    public function testShowConfigurationRequiresAuthentication(): void
    {
        $this->getJson('/api/v1/chat-assistant/me/configuration', [
            'X-Paq-Cliente' => 'desarrollo',
        ])->assertUnauthorized();
    }

    public function testShowConfigurationReturnsEmptyShapeWhenMissing(): void
    {
        Sanctum::actingAs($this->authenticatedUser());

        $this->getJson('/api/v1/chat-assistant/me/configuration', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.hasConfiguration', false)
            ->assertJsonPath('resultado.hasApiKey', false)
            ->assertJsonPath('resultado.providerId', '')
            ->assertJsonPath('resultado.isEnabled', false);
    }

    public function testUpsertConfigurationCreatesEncryptedRecord(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/chat-assistant/me/configuration', [
            'providerId' => 'ollama',
            'apiKey' => 'secret-ollama-key',
            'modelId' => 'llama3.1',
            'baseUrl' => 'http://localhost:11434',
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('respuesta', 'chatAssistant.configurationSaved')
            ->assertJsonPath('resultado.hasConfiguration', true)
            ->assertJsonPath('resultado.hasApiKey', true)
            ->assertJsonPath('resultado.providerId', 'ollama')
            ->assertJsonPath('resultado.modelId', 'llama3.1')
            ->assertJsonPath('resultado.baseUrl', 'http://localhost:11434')
            ->assertJsonPath('resultado.supportsVision', true)
            ->assertJsonPath('resultado.isEnabled', true);

        $credencial = PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($credencial);
        $this->assertNotSame('secret-ollama-key', $credencial->getRawOriginal('api_key_encrypted'));
    }

    public function testUpsertConfigurationRejectsMissingBaseUrlForOllama(): void
    {
        Sanctum::actingAs($this->authenticatedUser());

        $this->putJson('/api/v1/chat-assistant/me/configuration', [
            'providerId' => 'ollama',
            'apiKey' => 'secret-ollama-key',
            'modelId' => 'llama3.1',
            'baseUrl' => '',
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'chatAssistant.baseUrlRequired');
    }

    public function testUpsertConfigurationPreservesApiKeyWhenOmittedOnEdit(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/chat-assistant/me/configuration', [
            'providerId' => 'openai',
            'apiKey' => 'secret-openai-key',
            'modelId' => 'gpt-4o-mini',
        ], $this->tenantHeaders())->assertOk();

        $previousEncrypted = (string) PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->value('api_key_encrypted');

        $this->putJson('/api/v1/chat-assistant/me/configuration', [
            'providerId' => 'openai',
            'modelId' => 'gpt-4.1-mini',
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.modelId', 'gpt-4.1-mini')
            ->assertJsonPath('resultado.hasApiKey', true);

        $this->assertSame(
            $previousEncrypted,
            PqPedidoswebAsistenteIaCredencial::query()->where('user_id', $user->id)->value('api_key_encrypted'),
        );
    }

    public function testUpdateStatusDisablesWithoutDeletingCredential(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/chat-assistant/me/configuration', [
            'providerId' => 'groq',
            'apiKey' => 'secret-groq-key',
            'modelId' => 'llama-3.1-8b-instant',
        ], $this->tenantHeaders())->assertOk();

        $encryptedBefore = (string) PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->value('api_key_encrypted');

        $this->patchJson('/api/v1/chat-assistant/me/configuration/status', [
            'isEnabled' => false,
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('respuesta', 'chatAssistant.configurationStatusUpdated')
            ->assertJsonPath('resultado.isEnabled', false)
            ->assertJsonPath('resultado.hasApiKey', true);

        $credencial = PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($credencial);
        $this->assertSame($encryptedBefore, $credencial->getRawOriginal('api_key_encrypted'));
    }

    /**
     * @return array<string, string>
     */
    private function tenantHeaders(): array
    {
        return ['X-Paq-Cliente' => 'desarrollo'];
    }

    private function authenticatedUser(): User
    {
        $user = User::query()->where('codigo', 'cliente.mvp')->first();

        if ($user === null) {
            $this->markTestSkipped('Usuario cliente.mvp no disponible en la base de tests.');
        }

        PqPedidoswebAsistenteIaCredencial::query()->where('user_id', $user->id)->delete();

        return $user;
    }
}
