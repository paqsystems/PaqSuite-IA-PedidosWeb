<?php

namespace App\Exceptions;

use Exception;

final class PedidosWebBusinessValidationException extends Exception
{
    /**
     * @param  list<string>  $respuestaKeys
     */
    public function __construct(
        private readonly int $errorCode,
        private readonly array $respuestaKeys,
        private readonly int $httpStatus = 422,
    ) {
        parent::__construct($respuestaKeys[0] ?? 'business.validationFailed');
    }

    public function errorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return list<string>
     */
    public function respuestaKeys(): array
    {
        return $this->respuestaKeys;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }
}
