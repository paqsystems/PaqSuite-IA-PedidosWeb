<?php

namespace App\Support;

use App\Exceptions\ChatAssistantMessageException;
use App\Models\User;
use App\Services\ChatAssistant\ChatAssistantConfigurationService;
use App\Services\ChatAssistant\ChatAssistantProviderCatalogService;

final class ChatAssistantConfigurationReadiness
{
    public function __construct(
        private readonly ChatAssistantConfigurationService $configurationService,
        private readonly ChatAssistantProviderCatalogService $providerCatalogService,
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
        return $this->configurationService->getConfiguration($user);
    }

    public function isOperational(User $user): bool
    {
        $configuration = $this->getConfiguration($user);

        return $configuration['hasConfiguration']
            && $configuration['hasApiKey']
            && $configuration['isEnabled']
            && $configuration['providerId'] !== ''
            && $this->providerCatalogService->isActiveProvider($configuration['providerId']);
    }

    public function assertOperational(User $user): void
    {
        $configuration = $this->getConfiguration($user);

        if (
            ! $configuration['hasConfiguration']
            || ! $configuration['hasApiKey']
            || ! $configuration['isEnabled']
        ) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::configurationRequired,
                'chatAssistant.configurationRequired',
            );
        }

        if (
            $configuration['providerId'] === ''
            || ! $this->providerCatalogService->isActiveProvider($configuration['providerId'])
        ) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::providerInactive,
                'chatAssistant.providerInactive',
            );
        }
    }
}
