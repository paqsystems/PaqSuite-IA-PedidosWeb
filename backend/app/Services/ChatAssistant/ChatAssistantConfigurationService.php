<?php

namespace App\Services\ChatAssistant;

use App\Exceptions\ChatAssistantConfigurationException;
use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\PqPedidoswebAsistenteIaProveedor;
use App\Models\User;
use App\Support\ChatAssistantConfigurationErrorCodes;
use App\Support\ChatAssistantConfigurationMapper;
use App\Support\ChatAssistantCredentialSelection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

final class ChatAssistantConfigurationService
{
    public function __construct(
        private readonly ChatAssistantConfigurationMapper $mapper,
    ) {}

    /**
     * @return array{items: list<array{
     *     credentialId: int,
     *     displayName: string,
     *     hasConfiguration: bool,
     *     hasApiKey: bool,
     *     apiKeyHint: string,
     *     providerId: string,
     *     modelId: string,
     *     baseUrl: string,
     *     supportsVision: bool,
     *     isEnabled: bool
     * }>}
     */
    public function listConfigurations(User $user): array
    {
        $this->assertTableAvailable();

        $items = PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->orderBy('display_name')
            ->orderBy('id_credencial')
            ->get()
            ->map(fn (PqPedidoswebAsistenteIaCredencial $credencial): array => $this->mapper->fromModel($credencial))
            ->all();

        return ['items' => $items];
    }

    /**
     * @return array{
     *     credentialId: int,
     *     displayName: string,
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
    public function getConfiguration(User $user, ?int $credentialId = null, bool $requiresVision = false): array
    {
        $this->assertTableAvailable();

        if ($credentialId !== null) {
            $credencial = $this->findCredencialForUser($user, $credentialId);

            return $credencial === null
                ? $this->mapper->emptyResult()
                : $this->mapper->fromModel($credencial);
        }

        $credencial = $this->findFirstEnabledCredencialForUser($user, $requiresVision)
            ?? $this->findCredencialesForUser($user)->first();

        if ($credencial === null) {
            return $this->mapper->emptyResult();
        }

        return $this->mapper->fromModel($credencial);
    }

    /**
     * @param  array{
     *     displayName?: mixed,
     *     providerId?: mixed,
     *     apiKey?: mixed,
     *     modelId?: mixed,
     *     baseUrl?: mixed
     * }  $payload
     * @return array{
     *     credentialId: int,
     *     displayName: string,
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
    public function upsertConfiguration(User $user, array $payload, ?int $credentialId = null, bool $createNew = false): array
    {
        $this->assertTableAvailable();

        $displayName = trim((string) ($payload['displayName'] ?? ''));
        $providerId = trim((string) ($payload['providerId'] ?? ''));
        $modelId = trim((string) ($payload['modelId'] ?? ''));
        $baseUrl = trim((string) ($payload['baseUrl'] ?? ''));
        $apiKey = trim((string) ($payload['apiKey'] ?? ''));

        if ($displayName === '') {
            $displayName = $providerId.' / '.$modelId;
        }

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

        $existing = $createNew
            ? null
            : ($credentialId !== null
                ? $this->findCredencialForUser($user, $credentialId)
                : $this->findCredencialesForUser($user)->first());

        if ($credentialId !== null && $existing === null) {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::configurationNotFound,
                'chatAssistant.configurationNotFound',
            );
        }

        $attributes = [
            'user_id' => $user->id,
            'display_name' => $displayName,
            'provider_id' => $providerId,
            'base_url' => $baseUrl === '' ? null : $baseUrl,
            'model_id' => $this->normalizeModelIdForProvider($providerId, $modelId),
            'supports_vision' => (bool) $provider->soporta_imagenes,
            'is_enabled' => $existing?->is_enabled ?? true,
        ];

        if ($apiKey !== '') {
            if ($this->apiKeyLooksLikeSitePassword($providerId, $apiKey)) {
                throw new ChatAssistantConfigurationException(
                    ChatAssistantConfigurationErrorCodes::apiKeyRequired,
                    'chatAssistant.settings.apiKeyInvalidFormat',
                );
            }

            $attributes['api_key_encrypted'] = Crypt::encryptString($apiKey);
        } else {
            $this->assertExistingApiKeyAvailable($existing);
        }

        if ($existing !== null) {
            $existing->fill($attributes);
            $existing->save();
            $credencial = $existing->fresh() ?? $existing;
        } else {
            $credencial = PqPedidoswebAsistenteIaCredencial::query()->create($attributes);
        }

        return $this->mapper->fromModel($credencial);
    }

    /**
     * @return array{
     *     credentialId: int,
     *     displayName: string,
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
    public function updateStatus(User $user, bool $isEnabled, ?int $credentialId = null): array
    {
        $this->assertTableAvailable();

        $credencial = $credentialId !== null
            ? $this->findCredencialForUser($user, $credentialId)
            : ($this->findFirstEnabledCredencialForUser($user) ?? $this->findCredencialesForUser($user)->first());

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

    public function deleteConfiguration(User $user, int $credentialId): void
    {
        $this->assertTableAvailable();

        $credencial = $this->findCredencialForUser($user, $credentialId);

        if ($credencial === null) {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::configurationNotFound,
                'chatAssistant.configurationNotFound',
            );
        }

        $credencial->delete();
    }

    private function assertTableAvailable(): void
    {
        if (! Schema::hasTable('pq_asistente_ia_credenciales')) {
            throw new ChatAssistantConfigurationException(
                ChatAssistantConfigurationErrorCodes::configurationUnavailable,
                'chatAssistant.configurationUnavailable',
                503,
            );
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, PqPedidoswebAsistenteIaCredencial>
     */
    private function findCredencialesForUser(User $user)
    {
        return PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->orderBy('display_name')
            ->orderBy('id_credencial')
            ->get();
    }

    private function findFirstEnabledCredencialForUser(
        User $user,
        bool $requiresVision = false,
    ): ?PqPedidoswebAsistenteIaCredencial {
        $enabled = PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->where('is_enabled', true)
            ->get();

        return ChatAssistantCredentialSelection::pickDefault($enabled, $requiresVision);
    }

    private function findCredencialForUser(User $user, int $credentialId): ?PqPedidoswebAsistenteIaCredencial
    {
        return PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->where('id_credencial', $credentialId)
            ->first();
    }

    private function findActiveProvider(string $providerId): ?PqPedidoswebAsistenteIaProveedor
    {
        if (! Schema::hasTable('pq_asistente_ia_proveedores')) {
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

    private function apiKeyLooksLikeSitePassword(string $providerId, string $apiKey): bool
    {
        return match ($providerId) {
            'openai', 'openRouter' => ! str_starts_with($apiKey, 'sk-'),
            'anthropic' => ! (str_starts_with($apiKey, 'sk-ant-') || str_starts_with($apiKey, 'sk-')),
            'groq' => ! (str_starts_with($apiKey, 'gsk_') || str_starts_with($apiKey, 'sk-')),
            default => false,
        };
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
