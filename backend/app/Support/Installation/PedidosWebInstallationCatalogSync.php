<?php

declare(strict_types=1);

namespace App\Support\Installation;

use PaqSuite\LaravelCore\Installation\Lifecycle\InstallationCatalogSyncContract;
use PaqSuite\LaravelCore\Tenancy\InstalacionRecord;

/**
 * Hook GEN-34: tras manifiesto, desplegar SQL/catálogo GEN-15 del host.
 */
final class PedidosWebInstallationCatalogSync implements InstallationCatalogSyncContract
{
    public function __construct(
        private readonly PedidosWebSdkSqlDeployer $deployer,
    ) {
    }

    public function afterDictionaryInstall(InstalacionRecord $instalacion, string $dictionaryConnection): void
    {
        unset($instalacion, $dictionaryConnection);
        $this->deployer->deployEmission();
    }

    public function afterDictionaryUpdate(InstalacionRecord $instalacion, string $dictionaryConnection): void
    {
        unset($instalacion, $dictionaryConnection);
        $this->deployer->deployEmission();
    }

    public function afterCompaniesUpdate(
        InstalacionRecord $instalacion,
        string $dictionaryConnection,
        array $companyIds,
    ): void {
        unset($instalacion, $dictionaryConnection, $companyIds);
    }
}
