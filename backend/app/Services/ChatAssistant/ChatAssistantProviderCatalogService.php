<?php

namespace App\Services\ChatAssistant;

use App\Models\PqPedidoswebAsistenteIaProveedor;
use Illuminate\Support\Facades\Schema;

final class ChatAssistantProviderCatalogService
{
    /**
     * @var list<string>
     */
    public const STABLE_ORDER = [
        'ollama',
        'openai',
        'anthropic',
        'googleGemini',
        'azureOpenAi',
        'openRouter',
        'groq',
        'mistral',
    ];

    /**
     * @return list<array{
     *     providerId: string,
     *     displayName: string,
     *     supportsVision: bool,
     *     requiresBaseUrl: bool,
     *     supportUrl: string
     * }>
     */
    public function listActiveProviders(): array
    {
        if (! Schema::hasTable('pq_pedidosweb_asistente_ia_proveedores')) {
            return [];
        }

        $providers = PqPedidoswebAsistenteIaProveedor::query()
            ->where('activo', true)
            ->get();

        return $providers
            ->sortBy(fn (PqPedidoswebAsistenteIaProveedor $provider): int => $this->resolveSortIndex($provider->provider_id))
            ->values()
            ->map(fn (PqPedidoswebAsistenteIaProveedor $provider): array => $this->toApiItem($provider))
            ->all();
    }

    /**
     * @return array{
     *     providerId: string,
     *     displayName: string,
     *     supportsVision: bool,
     *     requiresBaseUrl: bool,
     *     supportUrl: string
     * }
     */
    public function toApiItem(PqPedidoswebAsistenteIaProveedor $provider): array
    {
        return [
            'providerId' => (string) $provider->provider_id,
            'displayName' => (string) $provider->nombre_visible,
            'supportsVision' => (bool) $provider->soporta_imagenes,
            'requiresBaseUrl' => (bool) $provider->requiere_base_url_editable,
            'supportUrl' => (string) ($provider->url_onboarding ?? ''),
            'suggestedModels' => $this->resolveSuggestedModels((string) $provider->provider_id),
        ];
    }

    /**
     * @return list<string>
     */
    private function resolveSuggestedModels(string $providerId): array
    {
        $models = config('chat_assistant.provider_suggested_models.'.$providerId, []);

        return is_array($models) ? array_values(array_filter($models, 'is_string')) : [];
    }

    private function resolveSortIndex(string $providerId): int
    {
        $index = array_search($providerId, self::STABLE_ORDER, true);

        return $index === false ? 999 : $index;
    }

    public function isActiveProvider(string $providerId): bool
    {
        if ($providerId === '' || ! Schema::hasTable('pq_pedidosweb_asistente_ia_proveedores')) {
            return false;
        }

        return PqPedidoswebAsistenteIaProveedor::query()
            ->where('provider_id', $providerId)
            ->where('activo', true)
            ->exists();
    }
}
