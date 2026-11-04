<?php

namespace App\Services\Emissions;

use App\Repositories\Gen\Emissions\FakeDxReportingEngine;
use App\Repositories\Gen\Emissions\FakePdfArtifactFactory;
use Illuminate\Contracts\Foundation\Application;
use PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine;
use PaqSuite\LaravelCore\Emissions\Contracts\EmissionRepository;
use PaqSuite\LaravelCore\Emissions\DxReportingMode;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\RemoteDxReportingEngine;

final class EmissionDxReportingEngineFactory
{
    public static function make(Application $app): DxReportingEngine
    {
        $mode = DxReportingMode::normalize(
            (string) $app->make('config')->get('paqsuite.emissions.dxReporting.mode', 'fake'),
        );
        if ($mode !== DxReportingMode::REMOTE) {
            return new FakeDxReportingEngine($app->make(FakePdfArtifactFactory::class));
        }

        /** @var EmissionRepository $reports */
        $reports = $app->make(EmissionRepository::class);
        $httpClient = $app->make(PedidosWebDxReportingHttpClient::class);

        $layoutResolver = static function (EmissionContext $context) use ($reports): ?array {
            if (! method_exists($reports, 'findReportLayoutForRender')
                && ! method_exists($reports, 'findReportLayoutForRenderByCode')) {
                return null;
            }

            $reportId = $context->reportId;
            if (is_numeric($reportId) && method_exists($reports, 'findReportLayoutForRender')) {
                /** @var callable $finder */
                $finder = [$reports, 'findReportLayoutForRender'];

                return $finder((int) $reportId);
            }

            if (is_string($reportId) && $reportId !== '' && method_exists($reports, 'findReportLayoutForRenderByCode')) {
                /** @var callable $finder */
                $finder = [$reports, 'findReportLayoutForRenderByCode'];

                return $finder($reportId);
            }

            return null;
        };

        return new RemoteDxReportingEngine(
            (string) $app->make('config')->get('paqsuite.emissions.dxReporting.serviceUrl', ''),
            $httpClient,
            (string) $app->make('config')->get('paqsuite.emissions.dxReporting.apiKey', ''),
            (int) $app->make('config')->get('paqsuite.emissions.dxReporting.timeoutSeconds', 60),
            $layoutResolver,
        );
    }
}
