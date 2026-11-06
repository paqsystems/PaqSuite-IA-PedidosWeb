<?php

namespace App\Exceptions;

use Exception;

final class IntegracionMaestroAbortException extends Exception
{
    public function __construct(
        private readonly int $errorCode,
        private readonly string $respuestaKey,
        private readonly int $httpStatus,
    ) {
        parent::__construct($respuestaKey);
    }

    public function errorCode(): int
    {
        return $this->errorCode;
    }

    public function respuestaKey(): string
    {
        return $this->respuestaKey;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }
}
