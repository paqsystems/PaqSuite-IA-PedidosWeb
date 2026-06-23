<?php

namespace App\Exceptions;

use RuntimeException;

final class ChatAssistantConfigurationException extends RuntimeException
{
    public function __construct(
        public readonly int $errorCode,
        public readonly string $respuestaKey,
        public readonly int $httpStatus = 422,
    ) {
        parent::__construct($respuestaKey);
    }
}
