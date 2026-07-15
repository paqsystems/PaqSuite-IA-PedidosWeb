<?php

namespace App\Support;

use App\Models\PqPedidoswebAsistenteIaCredencial;
use Illuminate\Support\Collection;

/**
 * Selección de credencial BYOK cuando el cliente no envía credentialId.
 */
final class ChatAssistantCredentialSelection
{
    /**
     * Orden preferido para llamadas con imágenes (visión).
     *
     * @var list<string>
     */
    public const VISION_PROVIDER_PRIORITY = [
        'openai',
        'anthropic',
        'googleGemini',
        'azureOpenAi',
        'openRouter',
        'groq',
        'ollama',
        'mistral',
    ];

    /**
     * @param  Collection<int, PqPedidoswebAsistenteIaCredencial>  $credentials
     */
    public static function pickDefault(
        Collection $credentials,
        bool $requiresVision = false,
    ): ?PqPedidoswebAsistenteIaCredencial {
        if ($credentials->isEmpty()) {
            return null;
        }

        $candidates = $credentials->values();

        if ($requiresVision) {
            $withVision = $candidates->filter(
                static fn (PqPedidoswebAsistenteIaCredencial $item): bool => (bool) $item->supports_vision,
            );

            if ($withVision->isNotEmpty()) {
                $candidates = $withVision->values();
            }
        }

        return $candidates
            ->sort(function (
                PqPedidoswebAsistenteIaCredencial $left,
                PqPedidoswebAsistenteIaCredencial $right,
            ) use ($requiresVision): int {
                if ($requiresVision) {
                    $leftRank = self::visionProviderRank((string) $left->provider_id);
                    $rightRank = self::visionProviderRank((string) $right->provider_id);

                    if ($leftRank !== $rightRank) {
                        return $leftRank <=> $rightRank;
                    }
                }

                $byName = strcmp(
                    mb_strtolower((string) $left->display_name),
                    mb_strtolower((string) $right->display_name),
                );

                if ($byName !== 0) {
                    return $byName;
                }

                return ((int) $left->id_credencial) <=> ((int) $right->id_credencial);
            })
            ->first();
    }

    private static function visionProviderRank(string $providerId): int
    {
        $index = array_search($providerId, self::VISION_PROVIDER_PRIORITY, true);

        return $index === false ? 999 : $index;
    }
}
