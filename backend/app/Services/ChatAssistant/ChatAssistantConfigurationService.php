<?php

namespace App\Services\ChatAssistant;

use App\Exceptions\ChatAssistantConfigurationException;
use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\PqPedidoswebAsistenteIaProveedor;
use App\Models\User;
use App\Support\ChatAssistantConfigurationErrorCodes;
use App\Support\ChatAssistantConfigurationMapper;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

final class ChatAssistantConfigurationService
{
    public function __construct(
        private readonly ChatAssistantConfigurationMapper $mapper,
    ) {}

    /**
     * @return array{
     *     hasConfiguration: bool,
     *     hasApiKey: bool,
     *     apiKeyHint: string,
     *     providerId: string,
     *     modelId: string,
     *     baseUrl: string,
     *     supportsVision: bool,
     *     isEnabled: bool
     * }
     */
    public function getConfiguration(User $user): array
    {
        $this->assertTableAvailable();

        $credencial = $this->findCredencialForUser($user);

        if ($credencial === null) {
            return $this->mapper->emptyResult();
        }

        return $this->mapper->fromModel($credencial);
    }

    /**
     * @param  array{
     *     providerId?: mixed,
     *     apiKey?: mixed,
     *     modelId?: mixed,
     *     baseUrl?: mixed
     * }  $payload
     * @return array{
     *     hasConfiguration: bool,
     *     hasApiKey: bool,
     *     apiKeyHint: string,
     *     providerId: string,
     *     modelId: string,
     *     baseUrl: string,
     *     supportsVision: bool,
     *     isEnabled: bool
     * }
     */
    public function upsertConfiguration(User $user, array $payload): array
    {
        $this->assertTableAvailable();

        $providerId = trim((string) ($payload['providerId'] ?? ''));
        $modelId = trim((string) ($payload['modelId'] ?? ''));
        $baseUrl = trim((string) ($payload['baseUrl'] ?? ''));
        $apiKey = trim((string) ($payload['apiKey'] ?? ''));

        if ($providerId === '') {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::providerInvalid,
                'chatAssistant.providerRequired',
            );
        }

        $provider = $this->findActiveProvider($providerId);

        if ($provider === null) {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::providerInvalid,
                'chatAssistant.providerInvalid',
            );
        }

        if ($modelId === '') {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::modelIdRequired,
                'chatAssistant.modelIdRequired',
            );
        }

        if ($provider->requiere_base_url_editable && $baseUrl === '') {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::baseUrlRequired,
                'chatAssistant.baseUrlRequired',
            );
        }

        $existing = $this->findCredencialForUser($user);
        $attributes = [
            'provider_id' => $providerId,
            'base_url' => $baseUrl === '' ? null : $baseUrl,
            'model_id' => $this->normalizeModelIdForProvider($providerId, $modelId),
            'supports_vision' => (bool) $provider->soporta_imagenes,
            'is_enabled' => $existing?->is_enabled ?? true,
        ];

        if ($apiKey !== '') {
            $attributes['api_key_encrypted'] = Crypt::encryptString($apiKey);
        } else {
            $this->assertExistingApiKeyAvailable($existing);
        }

        $credencial = PqPedidoswebAsistenteIaCredencial::query()->updateOrCreate(
            ['user_id' => $user->id],
            $attributes,
        );

        return $this->mapper->fromModel($credencial->fresh() ?? $credencial);
    }

    /**
     * @return array{
     *     hasConfiguration: bool,
     *     hasApiKey: bool,
     *     apiKeyHint: string,
     *     providerId: string,
     *     modelId: string,
     *     baseUrl: string,
     *     supportsVision: bool,
     *     isEnabled: bool
     * }
     */
    public function updateStatus(User $user, bool $isEnabled): array
    {
        $this->assertTableAvailable();

        $credencial = $this->findCredencialForUser($user);

        if ($credencial === null) {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::configurationNotFound,
                'chatAssistant.configurationNotFound',
            );
        }

        $credencial->is_enabled = $isEnabled;
        $credencial->save();

        return $this->mapper->fromModel($credencial->fresh() ?? $credencial);
    }

    private function assertTableAvailable(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_asistente_ia_credenciales')) {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::configurationUnavailable,
                'chatAssistant.configurationUnavailable',
                503,
            );
        }
    }

    private function findCredencialForUser(User $user): ?PqPedidoswebAsistenteIaCredencial
    {
        return PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->first();
    }

    private function findActiveProvider(string $providerId): ?PqPedidoswebAsistenteIaProveedor
    {
        if (! Schema::hasTable('pq_pedidosweb_asistente_ia_proveedores')) {
            return null;
        }

        return PqPedidoswebAsistenteIaProveedor::query()
            ->where('provider_id', $providerId)
            ->where('activo', true)
            ->first();
    }

    private function assertExistingApiKeyAvailable(?PqPedidoswebAsistenteIaCredencial $existing): void
    {
        if ($existing === null) {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::apiKeyRequired,
                'chatAssistant.apiKeyRequired',
            );
        }

        if ((string) $existing->getRawOriginal('api_key_encrypted') === '') {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::apiKeyRequired,
                'chatAssistant.apiKeyRequired',
            );
        }
    }

    private function normalizeModelIdForProvider(string $providerId, string $modelId): string
    {
        $normalizedModelId = trim($modelId);

        if ($providerId === 'azureOpenAi') {
            return $normalizedModelId;
        }

        return mb_strtolower($normalizedModelId);
    }
}
