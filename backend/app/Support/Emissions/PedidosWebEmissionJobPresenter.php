<?php

namespace App\Support\Emissions;

use PaqSuite\LaravelCore\Emissions\Dto\EmissionJob;

final class PedidosWebEmissionJobPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function toApiRow(EmissionJob $job): array
    {
        return [
            'jobId' => $job->jobId,
            'processCode' => $job->processCode,
            'companyId' => $job->companyId,
            'status' => $job->status,
            'mode' => $job->mode,
            'channel' => $job->channel,
            'groupId' => $job->groupId,
            'reportId' => $job->reportId,
            'datasetRowCount' => $job->datasetRowCount,
            'estimatedSizeBytes' => $job->estimatedSizeBytes,
            'fileName' => $job->fileName,
            'mimeType' => 'application/pdf',
            'messageKey' => $job->messageKey,
            'previewSessionId' => $job->previewSessionId,
            'notificationId' => $job->notificationId,
            'segmentKey' => $job->segmentKey,
        ];
    }
}
