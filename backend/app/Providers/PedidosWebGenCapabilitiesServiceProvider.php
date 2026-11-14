<?php

namespace App\Providers;

use App\Repositories\Gen\Emissions\ConfigFallbackEmissionRepository;
use App\Repositories\Gen\Emissions\FakePdfArtifactFactory;
use App\Services\Emissions\EmissionDxReportingEngineFactory;
use App\Repositories\Gen\Emissions\FileEmissionArtifactStorage;
use App\Repositories\Gen\Emissions\PedidosWebEmissionInlineTaskDispatcher;
use App\Repositories\Gen\Emissions\PedidosWebNullEmissionAuditPort;
use App\Repositories\Gen\Emissions\PedidosWebNullEmissionMailPort;
use App\Repositories\Gen\Emissions\PedidosWebNullEmissionNotificationPort;
use App\Repositories\Gen\Tasks\InMemoryMonoRdsBackupGateway;
use App\Repositories\Gen\Tasks\InMemoryWindowsMultiDiskBackupGateway;
use App\Repositories\Gen\Tasks\PedidosWebMonoBackupCompanyTargetResolver;
use App\Repositories\Gen\Tasks\RdsBackupCacheLock;
use App\Repositories\Gen\Tasks\RdsSqlServerBackupGateway;
use App\Repositories\Gen\Tasks\WindowsSqlServerMultiDiskBackupGateway;
use App\Services\Emissions\PedidosWebConsultaEmissionDatasetPort;
use App\Support\Installation\PedidosWebInstallationCatalogSync;
use App\Support\Installation\PedidosWebSdkSqlDeployer;
use App\Tenancy\PedidosWebMenuProcedimientoChecker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use PaqSuite\LaravelCore\Installation\Lifecycle\InstallationCatalogSyncContract;
use PaqSuite\LaravelCore\Mail\DefaultMailEngine;
use PaqSuite\LaravelCore\Mail\MailEngine;
use PaqSuite\LaravelCore\Mail\MailMessage;
use PaqSuite\LaravelCore\Mail\MailSendResult;
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
use PaqSuite\LaravelCore\Tasks\Backup\BackupCompanyTargetResolver;
use PaqSuite\LaravelCore\Tasks\Backup\BackupInstallationProfileResolver;
use PaqSuite\LaravelCore\Tasks\Backup\ConfigBackupInstallationProfileResolver;
use PaqSuite\LaravelCore\Tasks\Backup\EmpresaAdminBackupCompanyTargetResolver;
use PaqSuite\LaravelCore\Tasks\Backup\MonoRdsBackupGateway;
use PaqSuite\LaravelCore\Tasks\Backup\MonoRdsBackupService;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupExecutionLogSupport;
use PaqSuite\LaravelCore\Tasks\Backup\RdsDeferredBackupExecutionPoller;
use PaqSuite\LaravelCore\Tasks\Backup\MultiDiskBackupGateway;
use PaqSuite\LaravelCore\Tasks\Backup\MultiDiskBackupService;
use PaqSuite\LaravelCore\Tasks\Backup\RdsBackupLock;
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
use PaqSuite\LaravelCore\Tasks\TaskMailNotifier;
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
        $this->app->singleton(PedidosWebSdkSqlDeployer::class);
        $this->app->singleton(InstallationCatalogSyncContract::class, PedidosWebInstallationCatalogSync::class);

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
        $this->app->singleton(InMemoryWindowsMultiDiskBackupGateway::class);

        $this->app->singleton(MonoRdsBackupGateway::class, function ($app): MonoRdsBackupGateway {
            $mode = strtolower(trim((string) config('paqsuite.tasks.backup.rdsGateway', 'rds')));

            return match ($mode) {
                'memory' => $app->make(InMemoryMonoRdsBackupGateway::class),
                default => $app->make(RdsSqlServerBackupGateway::class),
            };
        });

        $this->app->singleton(MultiDiskBackupGateway::class, function ($app): MultiDiskBackupGateway {
            $mode = strtolower(trim((string) config('paqsuite.tasks.backup.windowsGateway', 'sqlserver')));

            return match ($mode) {
                'memory' => $app->make(InMemoryWindowsMultiDiskBackupGateway::class),
                default => $app->make(WindowsSqlServerMultiDiskBackupGateway::class),
            };
        });

        $this->app->singleton(BackupInstallationProfileResolver::class, function () {
            return new ConfigBackupInstallationProfileResolver(static function (): string {
                $header = (string) config('paqsuite.headers.cliente', 'X-Paq-Cliente');
                try {
                    $fromRequest = trim((string) request()->headers->get($header, ''));
                } catch (\Throwable) {
                    $fromRequest = '';
                }
                if ($fromRequest !== '') {
                    return $fromRequest;
                }

                return trim((string) env('PAQSUITE_CLIENTE', ''));
            });
        });

        $this->app->singleton(BackupCompanyTargetResolver::class, static fn ($app) => new PedidosWebMonoBackupCompanyTargetResolver(
            new EmpresaAdminBackupCompanyTargetResolver($app->make(EmpresaAdminRepository::class)),
        ));

        $this->app->singleton(RdsBackupLock::class, RdsBackupCacheLock::class);

        $this->app->singleton(RdsBackupExecutionLogSupport::class, static fn ($app) => new RdsBackupExecutionLogSupport(
            $app->make(TaskExecutionRepository::class),
            $app->make(MonoRdsBackupGateway::class),
        ));

        $this->app->singleton(RdsDeferredBackupExecutionPoller::class, static fn ($app) => new RdsDeferredBackupExecutionPoller(
            $app->make(TaskExecutionRepository::class),
            $app->make(MonoRdsBackupGateway::class),
            $app->make(RdsBackupExecutionLogSupport::class),
            $app->make(RdsBackupLock::class),
            $app->make(BackupInstallationProfileResolver::class),
        ));

        $this->app->singleton(MonoRdsBackupService::class, static fn ($app) => new MonoRdsBackupService(
            $app->make(MonoRdsBackupGateway::class),
            max(1, (int) config('paqsuite.tasks.backup.pollSeconds', 5)),
            $app->make(RdsBackupLock::class),
        ));

        $this->app->singleton(MultiDiskBackupService::class, static fn ($app) => new MultiDiskBackupService(
            $app->make(MultiDiskBackupGateway::class),
        ));

        $this->app->singleton(BackupDatabaseProcess::class, static fn ($app) => new BackupDatabaseProcess(
            monoService: $app->make(MonoRdsBackupService::class),
            windowsService: $app->make(MultiDiskBackupService::class),
            tenancyMode: (string) config('paqsuite.tenancy', 'single'),
            defaultS3ArnPrefix: (string) config('paqsuite.tasks.backup.s3ArnPrefix', ''),
            profileResolver: $app->make(BackupInstallationProfileResolver::class),
            targetResolver: $app->make(BackupCompanyTargetResolver::class),
        ));

        if (! $this->app->bound(MailEngine::class)) {
            $this->app->singleton(MailEngine::class, static function (): MailEngine {
                $mailer = (string) config('mail.default', '');
                $configured = $mailer !== '' && $mailer !== 'array';

                return new DefaultMailEngine(
                    transport: static function (MailMessage $message) use ($configured): MailSendResult {
                        if (! $configured) {
                            return MailSendResult::failure(
                                \PaqSuite\LaravelCore\Mail\MailErrorCodes::CONFIG_MISSING,
                                \PaqSuite\LaravelCore\Mail\MailErrorCodes::RESPUESTA_CONFIG_MISSING,
                            );
                        }
                        try {
                            $title = (string) ($message->viewData['taskTitle'] ?? '');
                            $status = (string) ($message->viewData['statusCode'] ?? '');
                            $executionId = (string) ($message->viewData['executionId'] ?? '');
                            $body = trim($title.' ['.$status.'] #'.$executionId);

                            Mail::html(
                                '<p>'.e($body !== '' ? $body : $message->subject).'</p>',
                                static function ($mail) use ($message): void {
                                    $mail->to($message->to)->subject($message->subject);
                                },
                            );

                            return MailSendResult::success('laravel-mail');
                        } catch (\Throwable) {
                            return MailSendResult::failure(
                                \PaqSuite\LaravelCore\Mail\MailErrorCodes::SEND_FAILED,
                                \PaqSuite\LaravelCore\Mail\MailErrorCodes::RESPUESTA_SEND_FAILED,
                            );
                        }
                    },
                    mailerConfigured: $configured,
                );
            });
        }
        if (! $this->app->bound(TaskMailNotifier::class)) {
            $this->app->singleton(TaskMailNotifier::class, static function ($app): TaskMailNotifier {
                return new TaskMailNotifier(
                    $app->make(MailEngine::class),
                    $app->make(TaskDefinitionRepository::class),
                );
            });
        }
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
        $this->app->singleton(
            DxReportingEngine::class,
            static fn ($app): DxReportingEngine => EmissionDxReportingEngineFactory::make($app),
        );
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
