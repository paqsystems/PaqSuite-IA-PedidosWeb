<?php

namespace App\Services\PedidosWeb;

final class PedidosWebParameterService
{
    public function getMinutosWeb(): int
    {
        return $this->getInt('MinutosWeb', 30, 1);
    }

    public function getCodMotivoCierreExitoso(): int
    {
        return $this->getInt('CodMotivoCierreExitoso', 1, 1);
    }

    public function getNoEliminaPedido(): bool
    {
        return $this->getInt('NOeliminaPedido', 0) === 1;
    }

    public function getNoModificaPedido(): bool
    {
        return $this->getInt('NOmodificaPedido', 0) === 1;
    }

    public function getDetallePorMail(): bool
    {
        return $this->getInt('DetallePorMail', 1) === 1;
    }

    /**
     * @return list<string>
     */
    public function getMailDestinatariosAdicionales(): array
    {
        $rawValue = (string) $this->value('MailDestinatariosAdicionales', '');
        $parts = preg_split('/[;,]/', $rawValue) ?: [];

        return array_values(array_filter(array_map(static fn (string $mail): string => trim($mail), $parts)));
    }

    public function getMailCco(): ?string
    {
        $mailCco = trim((string) $this->value('mailCCO', ''));

        return $mailCco !== '' ? $mailCco : null;
    }

    public function getMailDireccionRemitente(): ?string
    {
        $mail = trim((string) $this->value('Mail_DireccionRemitente', ''));

        return $mail !== '' ? $mail : null;
    }

    public function getDiasVentasDetalladas(): int
    {
        return $this->getInt('DiasVentasDetalladas', 90, 1);
    }

    public function getMonedaSimbolo(): string
    {
        return trim((string) $this->value('MonedaSimbolo', '$')) ?: '$';
    }

    public function getMonedaCodigo(): string
    {
        return trim((string) $this->value('MonedaCodigo', 'ARS')) ?: 'ARS';
    }

    private function getInt(string $key, int $defaultValue, ?int $minValue = null): int
    {
        $value = (int) $this->value($key, $defaultValue);

        if ($minValue !== null && $value < $minValue) {
            return $defaultValue;
        }

        return $value;
    }

    private function value(string $key, mixed $defaultValue): mixed
    {
        return config('paqsuite_pedidosweb.defaults.'.$key, $defaultValue);
    }
}
