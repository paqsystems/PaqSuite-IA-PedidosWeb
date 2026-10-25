<?php

namespace App\Services\PedidosWeb\Integracion\Maestros;

final class IntegracionMaestroItemResult
{
    public const STATUS_APPLIED = 'applied';

    public const STATUS_OMITTED = 'omitted';

    public const STATUS_DELETED = 'deleted';

    public const STATUS_REJECTED = 'rejected';

    private function __construct(
        public readonly string $status,
        public readonly ?string $errorCode = null,
        public readonly ?string $clave = null,
        public readonly string $operation = 'modified',
    ) {}

    public static function applied(?string $clave = null, string $operation = 'modified'): self
    {
        return new self(self::STATUS_APPLIED, null, $clave, $operation);
    }

    public static function omitted(?string $clave = null): self
    {
        return new self(self::STATUS_OMITTED, null, $clave, 'omitted');
    }

    public static function deleted(?string $clave = null): self
    {
        return new self(self::STATUS_DELETED, null, $clave, 'deleted');
    }

    public static function rejected(
        string $errorCode,
        ?string $clave = null,
        string $operation = 'modified',
    ): self
    {
        return new self(self::STATUS_REJECTED, $errorCode, $clave, $operation);
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isOmitted(): bool
    {
        return $this->status === self::STATUS_OMITTED;
    }

    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
