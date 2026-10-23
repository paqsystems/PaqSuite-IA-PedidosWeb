<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

final class IntegracionMaestroItemResult
{
    public const STATUS_APPLIED = 'applied';

    public const STATUS_OMITTED = 'omitted';

    public const STATUS_REJECTED = 'rejected';

    private function __construct(
        public readonly string $status,
        public readonly ?string $errorCode = null,
        public readonly ?string $clave = null,
    ) {}

    public static function applied(?string $clave = null): self
    {
        return new self(self::STATUS_APPLIED, null, $clave);
    }

    public static function omitted(?string $clave = null): self
    {
        return new self(self::STATUS_OMITTED, null, $clave);
    }

    public static function rejected(string $errorCode, ?string $clave = null): self
    {
        return new self(self::STATUS_REJECTED, $errorCode, $clave);
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isOmitted(): bool
    {
        return $this->status === self::STATUS_OMITTED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
