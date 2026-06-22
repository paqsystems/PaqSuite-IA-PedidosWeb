<?php

namespace App\Support;

use App\Models\PqPedidoswebAsistenteIaCredencial;

final class ChatAssistantConfigurationMapper
{
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
    public function emptyResult(): array
    {
        return [
            'hasConfiguration' => false,
            'hasApiKey' => false,
            'apiKeyHint' => '',
            'providerId' => '',
            'modelId' => '',
            'baseUrl' => '',
            'supportsVision' => false,
            'isEnabled' => false,
        ];
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
    public function fromModel(PqPedidoswebAsistenteIaCredencial $credencial): array
    {
        $encryptedApiKey = (string) $credencial->getRawOriginal('api_key_encrypted');

        return [
            'hasConfiguration' => true,
            'hasApiKey' => $encryptedApiKey !== '',
            'apiKeyHint' => $this->buildApiKeyHint($encryptedApiKey),
            'providerId' => (string) $credencial->provider_id,
            'modelId' => (string) $credencial->model_id,
            'baseUrl' => (string) ($credencial->base_url ?? ''),
            'supportsVision' => (bool) $credencial->supports_vision,
            'isEnabled' => (bool) $credencial->is_enabled,
        ];
    }

    public function buildApiKeyHint(string $encryptedApiKey): string
    {
        if ($encryptedApiKey === '') {
            return '';
        }

        return '••••••••';
    }
}
