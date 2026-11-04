<?php

namespace App\Http\Controllers\Api\V1\Gen;

use App\Http\Controllers\Controller;
use App\Models\Gen\PqEmissionProcess;
use App\Support\Emissions\PedidosWebEmissionJobPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionRepository;
use PaqSuite\LaravelCore\Emissions\Dto\CommunicationIntent;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionRequest;
use PaqSuite\LaravelCore\Emissions\DxReportingMode;
use PaqSuite\LaravelCore\Emissions\EmissionCapabilityGuard;
use PaqSuite\LaravelCore\Emissions\EmissionCommunicationException;
use PaqSuite\LaravelCore\Emissions\EmissionException;
use PaqSuite\LaravelCore\Emissions\EmissionOrchestrator;
use PaqSuite\LaravelCore\Emissions\EmissionPreviewService;
use PaqSuite\LaravelCore\Http\Responses\ApiResponse;
use PaqSuite\LaravelCore\Http\Responses\PaqSuiteEnvelopeCatalog;
use PaqSuite\LaravelCore\Tenancy\MenuProcedimientoChecker;

/**
 * GEN-15 — catálogo, diseño y emisión (CC #19).
 */
final class PedidosWebEmissionsController extends Controller
{
    private const MODE_CONSOLIDATED = 'consolidated';

    public function __construct(
        private readonly EmissionRepository $repository,
        private readonly EmissionOrchestrator $orchestrator,
        private readonly EmissionPreviewService $previewService,
        private readonly EmissionCapabilityGuard $capabilityGuard,
        private readonly MenuProcedimientoChecker $menuProcedimientoChecker,
    ) {
    }

    public function processes(): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $items = [];
        foreach ($this->catalogProcessCodes() as $processCode) {
            $process = $this->repository->findProcess($processCode);
            if ($process === null || ! $process->active) {
                continue;
            }
            $items[] = $this->processPayload($process);
        }

        return ApiResponse::success(['items' => $items]);
    }

    public function showProcess(string $processCode): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        return ApiResponse::success(['item' => $this->processPayload($process)]);
    }

    public function storeJob(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $processCode = (string) $request->input('processCode', '');
        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        $forbidden = $this->forbiddenMenuResponse($process->menuProcessCode);
        if ($forbidden !== null) {
            return $forbidden;
        }

        $deprecatedMail = $this->deprecatedMailFieldsResponse($request);
        if ($deprecatedMail !== null) {
            return $deprecatedMail;
        }

        $companyId = $this->companyId($request);
        $previewSessionId = $request->input('previewSessionId');
        $communicationIntentsRaw = $request->input('communicationIntents', []);
        $communicationIntents = is_array($communicationIntentsRaw)
            ? CommunicationIntent::listFromRequest($communicationIntentsRaw)
            : [];

        try {
            $job = $this->orchestrator->emit(new EmissionRequest(
                processCode: $processCode,
                channel: (string) $request->input('channel', 'pdf'),
                mode: (string) $request->input('mode', self::MODE_CONSOLIDATED),
                companyIds: $companyId !== null ? [$companyId] : [],
                userId: $this->userId(),
                companyId: $companyId,
                groupId: $request->input('groupId'),
                reportId: $request->input('reportId'),
                communicationIntents: $communicationIntents,
                previewSessionId: is_string($previewSessionId) ? $previewSessionId : null,
                mobile: (bool) $request->boolean('mobile'),
            ));
        } catch (EmissionCommunicationException $exception) {
            $stored = $this->repository->findJob($exception->jobId, $companyId);
            $row = $stored === null
                ? ['jobId' => $exception->jobId, 'status' => 'failedCommunication']
                : PedidosWebEmissionJobPresenter::toApiRow($stored);

            return response()->json([
                'error' => $exception->errorCode,
                'respuesta' => $exception->messageKey,
                'resultado' => ['item' => $row],
            ], 200);
        } catch (EmissionException $exception) {
            return $this->renderEmissionException($exception);
        }

        $httpStatus = $job->status === 'queued' ? 202 : 200;
        $stored = $this->repository->findJob($job->jobId, $companyId);

        return ApiResponse::success(
            ['item' => $stored === null ? PedidosWebEmissionJobPresenter::toApiRow($job) : PedidosWebEmissionJobPresenter::toApiRow($stored)],
            PaqSuiteEnvelopeCatalog::RESPUESTA_OK,
            $httpStatus,
        );
    }

    public function showJob(Request $request, string $jobId): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $job = $this->repository->findJob($jobId, $this->companyId($request));
        if ($job === null) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_JOB_NOT_FOUND);
        }

        return ApiResponse::success(['item' => PedidosWebEmissionJobPresenter::toApiRow($job)]);
    }

    public function download(Request $request, string $jobId): JsonResponse|Response
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        try {
            $artifact = $this->orchestrator->download($jobId, $this->companyId($request));
        } catch (EmissionException $exception) {
            return $this->renderEmissionException($exception);
        }

        return response($artifact->content, 200, [
            'Content-Type' => $artifact->mimeType,
            'Content-Disposition' => 'attachment; filename="'.$artifact->fileName.'"',
            'Content-Length' => (string) $artifact->sizeBytes(),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $processCode = (string) $request->input('processCode', '');
        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        $forbidden = $this->forbiddenMenuResponse($process->menuProcessCode);
        if ($forbidden !== null) {
            return $forbidden;
        }

        $companyId = $this->companyId($request);

        try {
            $session = $this->previewService->preview(
                new EmissionContext(
                    $processCode,
                    $companyId !== null ? [$companyId] : [],
                    $this->userId(),
                    (string) $request->input('mode', self::MODE_CONSOLIDATED),
                    (string) $request->input('channel', 'pdf'),
                    $request->input('reportId'),
                    null,
                    null,
                    $request->input('groupId'),
                ),
                $companyId,
            );
        } catch (EmissionException $exception) {
            return $this->renderEmissionException($exception);
        }

        return ApiResponse::success([
            'previewSessionId' => $session->previewSessionId,
            'expiresAt' => $session->expiresAt->format(\DateTimeInterface::ATOM),
            'mimeType' => $session->artifact->mimeType,
            'contentBase64' => base64_encode($session->artifact->content),
        ]);
    }

    public function designReports(string $processCode): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $designForbidden = $this->designForbiddenResponse();
        if ($designForbidden !== null) {
            return $designForbidden;
        }

        $process = $this->repository->findProcess($processCode);
        if ($process === null || ! $process->active) {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_PROCESS_NOT_FOUND);
        }

        return ApiResponse::success([
            'items' => $process->reports,
            'designer' => $this->designerModeLabel(),
        ]);
    }

    public function designUpdateLayout(int $reportId): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $designForbidden = $this->designForbiddenResponse();
        if ($designForbidden !== null) {
            return $designForbidden;
        }

        $layoutMime = request()->input('layoutMime');
        if (! is_string($layoutMime) || $layoutMime === '') {
            return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_VALIDATION_FAILED);
        }

        $layoutDefinition = request()->input('layoutDefinition');
        DB::statement(
            'EXEC dbo.pq_sp_emission_report_layout_update @report_id = ?, @layout_mime = ?, @layout_definition = ?',
            [
                $reportId,
                $layoutMime,
                is_string($layoutDefinition) ? $layoutDefinition : null,
            ],
        );

        return ApiResponse::success([
            'item' => [
                'reportId' => $reportId,
                'layoutMime' => $layoutMime,
                'designer' => $this->designerModeLabel(),
            ],
        ]);
    }

    public function designSetPrincipal(int $reportId): JsonResponse
    {
        $disabled = $this->disabledResponse();
        if ($disabled !== null) {
            return $disabled;
        }

        $designForbidden = $this->designForbiddenResponse();
        if ($designForbidden !== null) {
            return $designForbidden;
        }

        DB::statement('EXEC dbo.pq_sp_emission_report_set_principal @report_id = ?', [$reportId]);

        return ApiResponse::success([
            'item' => [
                'reportId' => $reportId,
                'isPrincipal' => true,
                'designer' => $this->designerModeLabel(),
            ],
        ]);
    }

    private function disabledResponse(): ?JsonResponse
    {
        try {
            $this->capabilityGuard->ensureEnabled();
        } catch (EmissionException $exception) {
            return ApiResponse::error(
                $exception->errorCode,
                $exception->messageKey,
                $exception->httpStatus,
            );
        }

        return null;
    }

    private function designForbiddenResponse(): ?JsonResponse
    {
        if ($this->menuProcedimientoChecker->userMayExecute('emission.design')) {
            return null;
        }

        return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_DESIGN_FORBIDDEN);
    }

    private function forbiddenMenuResponse(string $menuProcessCode): ?JsonResponse
    {
        $menuProcessCode = trim($menuProcessCode);
        if ($menuProcessCode === '') {
            return null;
        }

        if (! $this->menuProcedimientoChecker->existsInMenu($menuProcessCode)) {
            return null;
        }

        if ($this->menuProcedimientoChecker->userMayExecute($menuProcessCode)) {
            return null;
        }

        return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_FORBIDDEN);
    }

    private function deprecatedMailFieldsResponse(Request $request): ?JsonResponse
    {
        foreach (['mail', 'mailTo', 'mailTemplateId'] as $field) {
            if ($request->has($field) && $request->input($field) !== null && $request->input($field) !== '') {
                return ApiResponse::errorFromCatalog(PaqSuiteEnvelopeCatalog::EMISSION_VALIDATION_FAILED);
            }
        }

        return null;
    }

    private function renderEmissionException(EmissionException $exception): JsonResponse
    {
        return ApiResponse::error(
            $exception->errorCode,
            $exception->messageKey,
            $exception->httpStatus,
        );
    }

    private function userId(): int
    {
        return (int) Auth::id();
    }

    private function companyId(Request $request): ?int
    {
        $header = $request->header('X-Company-Id');

        return $header === null || $header === '' ? null : (int) $header;
    }

    private function designerModeLabel(): string
    {
        return DxReportingMode::designerLabel(
            (string) config('paqsuiteCapabilities.emissions.dxReporting.mode', 'fake'),
        );
    }

    /**
     * @return list<string>
     */
    private function catalogProcessCodes(): array
    {
        $configured = config('paqsuiteCapabilities.emissions.catalogProcessCodes', []);

        return is_array($configured) ? array_values(array_filter(array_map('strval', $configured))) : [];
    }

    /**
     * @param  object  $process
     * @return array<string, mixed>
     */
    private function processPayload(object $process): array
    {
        return [
            'processCode' => $process->processCode,
            'name' => $this->resolveProcessDisplayName($process),
            'menuProcessCode' => $process->menuProcessCode,
            'channels' => $process->channels,
            'allowsConsolidated' => $process->allowsConsolidated,
            'allowsSegmented' => $process->allowsSegmented,
            'modes' => [
                'consolidated' => $process->allowsConsolidated,
                'segmented' => $process->allowsSegmented,
            ],
            'requiresPreview' => $process->requiresPreview,
            'outputPolicy' => $process->outputPolicy->toArray(),
            'reports' => $process->reports,
        ];
    }

    /**
     * El DTO GEN-15 no expone `name`; el SDK usa displayExpr="name" (fallback a processCode).
     *
     * @param  object  $process
     */
    private function resolveProcessDisplayName(object $process): string
    {
        $reports = is_array($process->reports ?? null) ? $process->reports : [];
        foreach ($reports as $report) {
            if (! is_array($report)) {
                continue;
            }
            if (($report['isPrincipal'] ?? false) && is_string($report['name'] ?? null) && $report['name'] !== '') {
                return $report['name'];
            }
        }
        foreach ($reports as $report) {
            if (! is_array($report)) {
                continue;
            }
            if (is_string($report['name'] ?? null) && $report['name'] !== '') {
                return $report['name'];
            }
        }

        $fromDb = PqEmissionProcess::query()
            ->where('process_code', $process->processCode)
            ->value('name');
        if (is_string($fromDb) && $fromDb !== '') {
            return $fromDb;
        }

        return (string) $process->processCode;
    }
}
