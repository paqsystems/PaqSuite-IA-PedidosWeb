<?php

namespace Tests\Feature\Api\ChatAssistant;

use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\User;
use App\Services\ChatAssistant\ChatAssistantCorpusResolver;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmGateway;
use App\Exceptions\ChatAssistantMessageException;
use App\Support\ChatAssistantMessageErrorCodes;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Api\ChatAssistant\Support\FakesChatAssistantLlmResponses;
use Tests\Support\SeedsChatAssistantProviderCatalog;
use Tests\TestCase;

final class ChatAssistantMessageTest extends TestCase
{
    use FakesChatAssistantLlmResponses;
    use SeedsChatAssistantProviderCatalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedChatAssistantProviderCatalog();
        $this->fakeChatAssistantLlmResponses();
    }

    public function testSendMessageRequiresAuthentication(): void
    {
        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Necesito ayuda con pedidos',
        ], $this->tenantHeaders())->assertUnauthorized();
    }

    public function testSendMessageRejectsEmptyMessage(): void
    {
        Sanctum::actingAs($this->authenticatedUser());

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => '',
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testSendMessageRejectsTextLongerThanTwoThousandCharacters(): void
    {
        Sanctum::actingAs($this->authenticatedUser());

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => str_repeat('a', 2001),
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testSendMessageRejectsWhenConfigurationIsMissing(): void
    {
        Sanctum::actingAs($this->authenticatedUser());

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Como grabo un pedido?',
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'chatAssistant.configurationRequired');
    }

    public function testSendMessageRejectsWhenChatIsDisabled(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedValidConfiguration($user);

        $this->patchJson('/api/v1/chat-assistant/me/configuration/status', [
            'isEnabled' => false,
        ], $this->tenantHeaders())->assertOk();

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Como grabo un pedido?',
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'chatAssistant.configurationRequired');
    }

    public function testSendMessageReturnsOrientativeReplyFromApprovedCorpus(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedValidConfiguration($user);

        $response = $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Necesito ayuda para grabar un pedido en el portal',
        ], $this->tenantHeaders());

        $response
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.requiresSupportFollowup', false)
            ->assertJsonStructure([
                'resultado' => [
                    'reply',
                    'requiresSupportFollowup',
                ],
            ]);

        $this->assertStringContainsString(
            'orientación operativa para grabar pedidos',
            (string) $response->json('resultado.reply'),
        );
    }

    public function testSendMessageReturnsProviderInvocationFailedWhenLlmRequestFails(): void
    {
        $gateway = $this->createMock(ChatAssistantLlmGateway::class);
        $gateway->method('generateReply')
            ->willThrowException(new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInvocationFailed,
                'chatAssistant.providerInvocationFailed',
            ));
        $this->app->instance(ChatAssistantLlmGateway::class, $gateway);

        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedValidConfiguration($user);

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Como grabo un pedido?',
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'chatAssistant.providerInvocationFailed');
    }

    public function testSendMessageReturnsSupportFollowupWhenCorpusHasNoMatch(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedValidConfiguration($user);

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'xyzqplmn',
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.requiresSupportFollowup', true);
    }

    public function testCorpusResolverExcludesTechnicalAndSpecDocumentation(): void
    {
        $documents = app(ChatAssistantCorpusResolver::class)->listApprovedDocuments();

        $paths = array_column($documents, 'path');

        $this->assertNotEmpty($paths);
        $this->assertContains('99-manual-usuario/PedidosWeb.md', $paths);

        foreach ($paths as $path) {
            $this->assertStringNotContainsString('04-tareas', $path);
            $this->assertStringNotContainsString('05-open-spec', $path);
            $this->assertStringNotContainsString('PedidosWeb_Modelo_Datos', $path);
        }
    }

    public function testSendMessageAcceptsValidImageOnlyRequest(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedVisionConfiguration($user);

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => '',
            'images' => [
                $this->validImagePayload('captura.png', 'image/png'),
            ],
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure([
                'resultado' => ['reply', 'requiresSupportFollowup'],
            ]);
    }

    public function testSendMessageRejectsImagesWhenVisionIsUnsupported(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedValidConfiguration($user);

        PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->update(['supports_vision' => false]);

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Ayuda con esta pantalla',
            'images' => [
                $this->validImagePayload('captura.png', 'image/png'),
            ],
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'chatAssistant.visionUnsupported');
    }

    public function testSendMessageRejectsInvalidImageFormat(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedVisionConfiguration($user);

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Ayuda',
            'images' => [
                [
                    'fileName' => 'documento.pdf',
                    'mimeType' => 'application/pdf',
                    'contentBase64' => base64_encode('pdf'),
                ],
            ],
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testSendMessageRejectsImageLargerThanFiveMegabytes(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedVisionConfiguration($user);

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Ayuda',
            'images' => [
                [
                    'fileName' => 'captura.png',
                    'mimeType' => 'image/png',
                    'contentBase64' => base64_encode($this->tinyPngBytes().str_repeat("\0", 5 * 1024 * 1024)),
                ],
            ],
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testSendMessageRejectsMoreThanFourImages(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedVisionConfiguration($user);

        $images = [];

        for ($index = 0; $index < 5; $index++) {
            $images[] = $this->validImagePayload("captura-{$index}.png", 'image/png');
        }

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => 'Ayuda',
            'images' => $images,
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
    }

    public function testSendMessageRejectsTextLongerThanOneThousandCharactersWithImages(): void
    {
        $user = $this->authenticatedUser();
        Sanctum::actingAs($user);
        $this->seedVisionConfiguration($user);

        $this->postJson('/api/v1/chat-assistant/messages', [
            'message' => str_repeat('a', 1001),
            'images' => [
                $this->validImagePayload('captura.png', 'image/png'),
            ],
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('respuesta', 'validation.failed');
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
            'apiKey' => 'secret-groq-key',
            'modelId' => 'llama-3.1-8b-instant',
        ], $this->tenantHeaders())->assertOk();

        $this->assertDatabaseHas('pq_pedidosweb_asistente_ia_credenciales', [
            'user_id' => $user->id,
            'provider_id' => 'groq',
            'is_enabled' => true,
        ]);
    }

    private function seedVisionConfiguration(User $user): void
    {
        $this->putJson('/api/v1/chat-assistant/me/configuration', [
            'providerId' => 'ollama',
            'apiKey' => 'secret-ollama-key',
            'modelId' => 'llama3.1',
            'baseUrl' => 'http://localhost:11434',
        ], $this->tenantHeaders())->assertOk();

        $this->assertDatabaseHas('pq_pedidosweb_asistente_ia_credenciales', [
            'user_id' => $user->id,
            'provider_id' => 'ollama',
            'supports_vision' => true,
            'is_enabled' => true,
        ]);
    }

    /**
     * @return array{fileName: string, mimeType: string, contentBase64: string}
     */
    private function validImagePayload(string $fileName, string $mimeType): array
    {
        return [
            'fileName' => $fileName,
            'mimeType' => $mimeType,
            'contentBase64' => $this->tinyPngBase64(),
        ];
    }

    private function tinyPngBase64(): string
    {
        return base64_encode($this->tinyPngBytes());
    }

    private function tinyPngBytes(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            true,
        ) ?: '';
    }
}
