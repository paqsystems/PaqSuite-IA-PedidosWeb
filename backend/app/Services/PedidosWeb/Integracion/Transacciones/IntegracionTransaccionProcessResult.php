<?php

namespace App\Services\PedidosWeb\Integracion\Transacciones;

final class IntegracionTransaccionProcessResult
{
    /**
     * @param  array<int, array{indice: int, clave: string|null, codigo: string}>  $errores
     */
    public function __construct(
        public readonly string $recurso,
        public readonly string $modo,
        public readonly int $recibidos,
        public readonly int $aplicados,
        public readonly int $omitidos,
        public readonly int $rechazados,
        public readonly array $errores,
        public readonly bool $erroresTruncados,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'recurso' => $this->recurso,
            'modo' => $this->modo,
            'recibidos' => $this->recibidos,
            'aplicados' => $this->aplicados,
            'omitidos' => $this->omitidos,
            'rechazados' => $this->rechazados,
            'errores' => $this->errores,
        ];
    }
}
