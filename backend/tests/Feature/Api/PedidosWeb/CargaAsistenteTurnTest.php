<?php

namespace Tests\Feature\Api\PedidosWeb;

use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Api\ChatAssistant\Support\FakesChatAssistantLlmResponses;
use Tests\Support\SeedsChatAssistantProviderCatalog;
use Tests\TestCase;

final class CargaAsistenteTurnTest extends TestCase
{
    use FakesChatAssistantLlmResponses;
    use SeedsChatAssistantProviderCatalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedChatAssistantProviderCatalog();
        $this->fakeChatAssistantLlmResponses();
    }

    public function testTurnRequiresAuthentication(): void
    {
        $this->postJson('/api/v1/pedidos/carga/asistente/turn', $this->validPayload([
            'message' => 'stock tornillo',
        ]), $this->tenantHeaders())->assertUnauthorized();
    }

    public function testTurnRejectsWhenConfigurationIsMissing(): void
    {
        Sanctum::actingAs($this->authenticatedUser());

        $this->postJson('/api/v1/pedidos/carga/asistente/turn', $this->validPayload([
            'message' => 'stock tornillo',
        ]), $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'pedidos.carga.asistente.configurationRequired')
            ->assertJsonPath('resultado.configurationRequired', true)
            ->assertJsonPath('resultado.preferencesPath', '/preferences');
    }

    public function testTurnReturnsEnvelopeForStockOrUnknownWithConfiguration(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedValidConfiguration($user);

        $response = $this->postJson('/api/v1/pedidos/carga/asistente/turn', $this->validPayload([
            'message' => 'stock tornillo',
        ]), $this->tenantHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.configurationRequired', false)
            ->assertJsonStructure([
                'resultado' => [
                    'replyText',
                    'actions',
                    'pendingChoice',
                    'configurationRequired',
                ],
            ]);

        $this->assertNotSame('', (string) $response->json('resultado.replyText'));
    }

    public function testTurnReturnsHelpForUnknownIntent(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedValidConfiguration($user);

        $this->postJson('/api/v1/pedidos/carga/asistente/turn', $this->validPayload([
            'message' => 'xyzqplmn ayuda random',
        ]), $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.replyText', 'pedidos.carga.asistente.help')
            ->assertJsonPath('resultado.configurationRequired', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'message' => 'hola',
            'modality' => 'texto',
            'draftContext' => [
                'modo' => 'nuevo',
                'perfilUsuario' => 'V',
                'codCliente' => null,
                'cabecera' => [],
                'renglones' => [],
                'readOnly' => false,
                'codLista' => 1,
            ],
        ], $overrides);
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

    private function seedValidConfiguration(User $user): void
    {
        $this->putJson('/api/v1/chat-assistant/me/configuration', [
            'providerId' => 'groq',
            'apiKey' => 'sk-test-groq-key',
            'modelId' => 'llama-3.1-8b-instant',
        ], $this->tenantHeaders())->assertOk();

        $this->assertDatabaseHas('pq_asistente_ia_credenciales', [
            'user_id' => $user->id,
            'provider_id' => 'groq',
            'is_enabled' => true,
        ]);
    }
}
