<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

final class IntegracionTransaccionItemResult
{
    private function __construct(
        public readonly string $status,
        public readonly ?string $clave = null,
        public readonly ?string $errorCode = null,
    ) {}

    public static function applied(?string $clave): self
    {
        return new self('applied', $clave);
    }

    public static function rejected(?string $clave, string $errorCode): self
    {
        return new self('rejected', $clave, $errorCode);
    }

    public static function omitted(?string $clave, ?string $errorCode = null): self
    {
        return new self('omitted', $clave, $errorCode);
    }

    public function isApplied(): bool
    {
        return $this->status === 'applied';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isOmitted(): bool
    {
        return $this->status === 'omitted';
    }
}
