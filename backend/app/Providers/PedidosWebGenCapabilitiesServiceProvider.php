<?php

namespace App\Providers;

use App\Repositories\Gen\Emissions\ConfigFallbackEmissionRepository;
use App\Repositories\Gen\Emissions\FakeDxReportingEngine;
use App\Repositories\Gen\Emissions\FakePdfArtifactFactory;
use App\Repositories\Gen\Emissions\FileEmissionArtifactStorage;
use App\Repositories\Gen\Emissions\PedidosWebEmissionInlineTaskDispatcher;
use App\Repositories\Gen\Emissions\PedidosWebNullEmissionAuditPort;
use App\Repositories\Gen\Emissions\PedidosWebNullEmissionMailPort;
use App\Repositories\Gen\Emissions\PedidosWebNullEmissionNotificationPort;
use App\Repositories\Gen\Tasks\InMemoryMonoRdsBackupGateway;
use App\Services\Emissions\PedidosWebConsultaEmissionDatasetPort;
use App\Tenancy\PedidosWebMenuProcedimientoChecker;
use Illuminate\Support\ServiceProvider;
use PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionArtifactStorage;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionAuditPort;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionMailPort;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionNotificationPort;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionPreviewSessionStore;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionRepository;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionTaskDispatcher;
use PaqSuite\LaravelCore\Emissions\EmissionAsyncThreshold;
use PaqSuite\LaravelCore\Emissions\EmissionCapabilityGuard;
use PaqSuite\LaravelCore\Emissions\EmissionDatasetPortRegistry;
use PaqSuite\LaravelCore\Emissions\EmissionOrchestrator;
use PaqSuite\LaravelCore\Emissions\EmissionPreviewService;
use PaqSuite\LaravelCore\Emissions\EmissionSettings;
use PaqSuite\LaravelCore\Emissions\InMemoryEmissionPreviewSessionStore;
use PaqSuite\LaravelCore\Emissions\NoOpEmissionArtifactReadyHook;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionArtifactReadyHook;
use PaqSuite\LaravelCore\Emissions\StoredProcedureEmissionRepository;
use PaqSuite\LaravelCore\Emissions\Tasks\EmissionBatchTaskHandler;
use PaqSuite\LaravelCore\Tasks\Backup\MonoRdsBackupService;
use PaqSuite\LaravelCore\Tasks\Catalog\TaskCatalogValidator;
use PaqSuite\LaravelCore\Tasks\Processes\BackupDatabaseProcess;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\Repositories\TaskProcessRepository;
use PaqSuite\LaravelCore\Tasks\StoredProcedureTaskDefinitionRepository;
use PaqSuite\LaravelCore\Tasks\StoredProcedureTaskExecutionRepository;
use PaqSuite\LaravelCore\Tasks\StoredProcedureTaskProcessRepository;
use PaqSuite\LaravelCore\Tasks\TaskExecutionService;
use PaqSuite\LaravelCore\Tasks\TaskExecutionServiceFactory;
use PaqSuite\LaravelCore\Tasks\TaskProcessRegistry;
use PaqSuite\LaravelCore\Security\EmpresaAdminRepository;
use PaqSuite\LaravelCore\Security\StoredProcedureEmpresaAdminRepository;
use PaqSuite\LaravelCore\Tenancy\MenuProcedimientoChecker;

/**
 * Wiring GEN-13 / GEN-15 (CC PQ #19).
 */
final class PedidosWebGenCapabilitiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            EmpresaAdminRepository::class,
            static fn (): StoredProcedureEmpresaAdminRepository => new StoredProcedureEmpresaAdminRepository(null),
        );

        $this->app->singleton(MenuProcedimientoChecker::class, PedidosWebMenuProcedimientoChecker::class);

        $this->app->singleton(TaskProcessRegistry::class, function (): TaskProcessRegistry {
            $registry = new TaskProcessRegistry();
            $registry->register(BackupDatabaseProcess::PROCESS_CODE, BackupDatabaseProcess::class);

            return $registry;
        });

        $this->app->singleton(TaskProcessRepository::class, StoredProcedureTaskProcessRepository::class);
        $this->app->singleton(TaskDefinitionRepository::class, StoredProcedureTaskDefinitionRepository::class);
        $this->app->singleton(TaskExecutionRepository::class, StoredProcedureTaskExecutionRepository::class);
        $this->app->singleton(TaskCatalogValidator::class);

        $this->app->singleton(InMemoryMonoRdsBackupGateway::class);
        $this->app->singleton(BackupDatabaseProcess::class, function ($app): BackupDatabaseProcess {
            return new BackupDatabaseProcess(
                monoService: new MonoRdsBackupService($app->make(InMemoryMonoRdsBackupGateway::class)),
                tenancyMode: (string) config('paqsuite.tenancy', 'single'),
                defaultS3ArnPrefix: env(
                    'TASKS_BACKUP_S3_ARN_PREFIX',
                    'arn:aws:s3:::paqsuite-pedidosweb-backup/',
                ),
            );
        });

        $this->app->singleton(
            TaskExecutionService::class,
            static fn ($app): TaskExecutionService => TaskExecutionServiceFactory::create($app),
        );

        $this->app->singleton(StoredProcedureEmissionRepository::class);
        $this->app->singleton(EmissionRepository::class, ConfigFallbackEmissionRepository::class);
        $this->app->singleton(EmissionSettings::class);
        $this->app->singleton(EmissionCapabilityGuard::class);
        $this->app->singleton(EmissionAsyncThreshold::class);

        $this->app->singleton(FakePdfArtifactFactory::class);
        $this->app->singleton(DxReportingEngine::class, FakeDxReportingEngine::class);
        $this->app->singleton(EmissionArtifactStorage::class, FileEmissionArtifactStorage::class);
        $this->app->singleton(EmissionPreviewSessionStore::class, InMemoryEmissionPreviewSessionStore::class);
        $this->app->singleton(EmissionAuditPort::class, PedidosWebNullEmissionAuditPort::class);
        $this->app->singleton(EmissionMailPort::class, PedidosWebNullEmissionMailPort::class);
        $this->app->singleton(EmissionNotificationPort::class, PedidosWebNullEmissionNotificationPort::class);
        $this->app->singleton(EmissionArtifactReadyHook::class, NoOpEmissionArtifactReadyHook::class);
        $this->app->singleton(EmissionBatchTaskHandler::class);
        $this->app->singleton(EmissionTaskDispatcher::class, PedidosWebEmissionInlineTaskDispatcher::class);

        $this->app->singleton(EmissionDatasetPortRegistry::class, function ($app): EmissionDatasetPortRegistry {
            $registry = new EmissionDatasetPortRegistry();
            $port = $app->make(PedidosWebConsultaEmissionDatasetPort::class);
            foreach ([
                'pw_presupuestosingresados',
                'pw_pedidosingresados',
                'pw_pedidospendientes',
                'pw_detallepedidos',
                'pw_deudaclientes',
                'pw_consultacheques',
                'pw_historialventas',
                'pw_consultastock',
            ] as $processCode) {
                $registry->register($processCode, $port);
            }

            return $registry;
        });

        $this->app->singleton(EmissionPreviewService::class, function ($app): EmissionPreviewService {
            $ttlMinutes = (int) config('paqsuiteCapabilities.emissions.previewTtlMinutes', 15);
            $ttlSeconds = max(1, min(900, $ttlMinutes * 60));

            return new EmissionPreviewService(
                $app->make(EmissionRepository::class),
                $app->make(EmissionDatasetPortRegistry::class),
                $app->make(DxReportingEngine::class),
                $app->make(EmissionPreviewSessionStore::class),
                $app->make(EmissionCapabilityGuard::class),
                $ttlSeconds,
            );
        });

        $this->app->singleton(EmissionOrchestrator::class, function ($app): EmissionOrchestrator {
            return new EmissionOrchestrator(
                $app->make(EmissionRepository::class),
                $app->make(EmissionDatasetPortRegistry::class),
                $app->make(DxReportingEngine::class),
                $app->make(EmissionArtifactStorage::class),
                $app->make(EmissionCapabilityGuard::class),
                $app->make(EmissionAsyncThreshold::class),
                $app->make(EmissionTaskDispatcher::class),
                $app->make(EmissionPreviewService::class),
                $app->make(EmissionAuditPort::class),
                $app->make(EmissionNotificationPort::class),
                $app->make(EmissionArtifactReadyHook::class),
            );
        });
    }
}
