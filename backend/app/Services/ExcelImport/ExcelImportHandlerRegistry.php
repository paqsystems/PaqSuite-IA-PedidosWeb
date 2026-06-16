<?php

namespace App\Services\ExcelImport;

use App\Exceptions\ExcelImportFlowException;
use App\Services\ExcelImport\Contracts\ExcelImportHandlerInterface;
use App\Support\ExcelImportErrorCodes;
use Illuminate\Contracts\Container\Container;

final class ExcelImportHandlerRegistry
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function resolve(?string $handlerBackend): ExcelImportHandlerInterface
    {
        if ($handlerBackend === null || trim($handlerBackend) === '') {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.handlerNotConfigured',
                422
            );
        }

        $class = config('excel_import.handlers.'.$handlerBackend);

        if (! is_string($class) || $class === '') {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.handlerNotFound',
                422
            );
        }

        $handler = $this->container->make($class);

        if (! $handler instanceof ExcelImportHandlerInterface) {
            throw new ExcelImportFlowException(
                ExcelImportErrorCodes::processNotAllowed,
                'excelImport.handlerInvalid',
                422
            );
        }

        return $handler;
    }
}
