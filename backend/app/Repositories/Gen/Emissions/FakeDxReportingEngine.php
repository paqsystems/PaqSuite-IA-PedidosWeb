<?php

namespace App\Repositories\Gen\Emissions;

use PaqSuite\LaravelCore\Emissions\Contracts\DxReportingEngine;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionArtifact;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionContext;
use PaqSuite\LaravelCore\Emissions\Dto\EmissionDataset;

final class FakeDxReportingEngine implements DxReportingEngine
{
    public function __construct(private readonly FakePdfArtifactFactory $factory)
    {
    }

    public function render(
        string $format,
        EmissionDataset $dataset,
        EmissionContext $context,
    ): EmissionArtifact {
        $seed = (string) ($context->jobId ?? $context->processCode);
        $fileName = sprintf(
            '%s-%s.pdf',
            $context->processCode,
            substr($seed, 0, 8),
        );

        return $this->factory->make($seed.'-rows'.$dataset->rowCount(), $fileName);
    }
}
