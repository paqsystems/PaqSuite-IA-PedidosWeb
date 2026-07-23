<?php

namespace App\Exceptions;

use RuntimeException;

final class CargaAsistenteException extends RuntimeException
{
    public function __construct(
        public readonly int $errorCode,
        public readonly string $respuestaKey,
        public readonly int $httpStatus = 422,
        /** @var array<string, mixed> */
        public readonly array $resultado = [],
    ) {
        parent::__construct($respuestaKey);
    }
}
