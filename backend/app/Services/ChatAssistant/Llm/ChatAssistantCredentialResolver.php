<?php

namespace App\Services\ChatAssistant\Llm;

use App\Exceptions\ChatAssistantMessageException;
use App\Models\PqPedidoswebAsistenteIaCredencial;
use App\Models\User;
use App\Support\ChatAssistantMessageErrorCodes;
use Illuminate\Support\Facades\Crypt;

final class ChatAssistantCredentialResolver
{
    public function resolve(User $user, ?int $credentialId = null): ChatAssistantInvocationContext
    {
        $credencial = $this->resolveCredencial($user, $credentialId);

        if ($credencial === null) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::configurationRequired,
                'chatAssistant.configurationRequired',
            );
        }

        $encryptedApiKey = (string) $credencial->getRawOriginal('api_key_encrypted');

        if ($encryptedApiKey === '') {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::configurationRequired,
                'chatAssistant.configurationRequired',
            );
        }

        try {
            $apiKey = Crypt::decryptString($encryptedApiKey);
        } catch (\Throwable) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::configurationRequired,
                'chatAssistant.configurationRequired',
            );
        }

        return new ChatAssistantInvocationContext(
            providerId: (string) $credencial->provider_id,
            modelId: (string) $credencial->model_id,
            baseUrl: trim((string) ($credencial->base_url ?? '')),
            apiKey: $apiKey,
            supportsVision: (bool) $credencial->supports_vision,
        );
    }

    private function resolveCredencial(User $user, ?int $credentialId): ?PqPedidoswebAsistenteIaCredencial
    {
        if ($credentialId !== null) {
            return PqPedidoswebAsistenteIaCredencial::query()
                ->where('user_id', $user->id)
                ->where('id_credencial', $credentialId)
                ->where('is_enabled', true)
                ->first();
        }

        return PqPedidoswebAsistenteIaCredencial::query()
            ->where('user_id', $user->id)
            ->where('is_enabled', true)
            ->orderBy('display_name')
            ->orderBy('id_credencial')
            ->first();
    }
}
