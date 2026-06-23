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
    public function getConfiguration(User $user, ?int $credentialId = null): array
    {
        return $this->configurationService->getConfiguration($user, $credentialId);
    }

    public function isOperational(User $user, ?int $credentialId = null): bool
    {
        if ($credentialId !== null) {
            return $this->isConfigurationOperational($this->getConfiguration($user, $credentialId));
        }

        $configurations = $this->configurationService->listConfigurations($user)['items'];

        foreach ($configurations as $configuration) {
            if ($this->isConfigurationOperational($configuration)) {
                return true;
            }
        }

        return false;
    }

    public function assertOperational(User $user, ?int $credentialId = null): void
    {
        $configuration = $this->getConfiguration($user, $credentialId);

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

    /**
     * @param  array{
     *     credentialId?: int,
     *     displayName?: string,
     *     hasConfiguration: bool,
     *     hasApiKey: bool,
     *     apiKeyHint: string,
     *     providerId: string,
     *     modelId: string,
     *     baseUrl: string,
     *     supportsVision: bool,
     *     isEnabled: bool
     * }  $configuration
     */
    private function isConfigurationOperational(array $configuration): bool
    {
        return $configuration['hasConfiguration']
            && $configuration['hasApiKey']
            && $configuration['isEnabled']
            && $configuration['providerId'] !== ''
            && $this->providerCatalogService->isActiveProvider($configuration['providerId']);
    }
}
