<?php

namespace App\Services\PedidosWeb;

use App\Models\PqParametrosGral;
use App\Support\ParametrosGralTipoValor;
use Illuminate\Support\Facades\Schema;

final class PedidosWebParameterService
{
    /** @var array<string, PqParametrosGral>|null */
    private ?array $parametrosPorClave = null;

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
        return $this->getBool('NOeliminaPedido', false);
    }

    public function getNoModificaPedido(): bool
    {
        return $this->getBool('NOmodificaPedido', false);
    }

    public function getCargaRecurrente(): bool
    {
        return $this->getBool('CargaRecurrente', true);
    }

    public function getActualizarPrecioCopia(): bool
    {
        return $this->getBool('ActualizarPrecioCopia', false);
    }

    public function getDetallePorMail(): bool
    {
        return $this->getBool('DetallePorMail', true);
    }

    /**
     * @return list<string>
     */
    public function getMailDestinatariosAdicionales(): array
    {
        $rawValue = (string) $this->resolveValue('MailDestinatariosAdicionales', '');
        $parts = preg_split('/[;,]/', $rawValue) ?: [];

        return array_values(array_filter(array_map(static fn (string $mail): string => trim($mail), $parts)));
    }

    public function getMailCco(): ?string
    {
        $mailCco = trim((string) $this->resolveValue('mailCCO', ''));

        return $mailCco !== '' ? $mailCco : null;
    }

    public function getMailDireccionRemitente(): ?string
    {
        $mail = trim((string) $this->resolveValue('Mail_DireccionRemitente', ''));

        return $mail !== '' && filter_var($mail, FILTER_VALIDATE_EMAIL) ? $mail : null;
    }

    public function getDiasVentasDetalladas(): int
    {
        return $this->getInt('DiasVentasDetalladas', 90, 1);
    }

    public function getCodPerfilPedidos(): string
    {
        $row = $this->findParametro('CodPerfilPedidos');

        if ($row !== null && ParametrosGralTipoValor::fromRow($row) === 'I') {
            $intValue = (int) $row->Valor_Int;

            return $intValue === 0 ? '' : (string) $intValue;
        }

        $resolved = trim((string) $this->resolveValue('CodPerfilPedidos', ''));

        if ($resolved === '0') {
            return '';
        }

        return $resolved !== '' ? $resolved : 'MVP';
    }

    public function getClienteLeyendaInicializa(int $numero): bool
    {
        if ($numero < 1 || $numero > 5) {
            return false;
        }

        return $this->getBool("ClienteLeyenda{$numero}", true);
    }

    public function getNivelExtremo(): bool
    {
        return $this->getBool('NivelExtremo', false);
    }

    public function getArticuloPrecioCero(): bool
    {
        return $this->getBoolConAliasCanonico('ArticulosPrecioCero', 'Articulopreciocero', false);
    }

    public function getArticulosSinPrecio(): bool
    {
        return $this->getBoolConAliasCanonico('ArticulosSinPrecio', 'Articulossinprecio', false);
    }

    /**
     * @return array{clienteLeyenda1: bool, clienteLeyenda2: bool, clienteLeyenda3: bool, clienteLeyenda4: bool, clienteLeyenda5: bool}
     */
    public function resolveClienteLeyendaFlags(): array
    {
        return [
            'clienteLeyenda1' => $this->getClienteLeyendaInicializa(1),
            'clienteLeyenda2' => $this->getClienteLeyendaInicializa(2),
            'clienteLeyenda3' => $this->getClienteLeyendaInicializa(3),
            'clienteLeyenda4' => $this->getClienteLeyendaInicializa(4),
            'clienteLeyenda5' => $this->getClienteLeyendaInicializa(5),
        ];
    }

    /**
     * @return array{
     *     modificaPrecio: bool,
     *     modificaBonArt: bool,
     *     modificaBonCli: bool,
     *     modificaListaPrec: bool,
     *     modificaCondVta: bool,
     *     modificaDirEntr: bool,
     *     modificaExpreso: bool
     * }
     */
    public function resolveModificaFlags(string $functionalProfile): array
    {
        $suffix = match ($functionalProfile) {
            'cliente' => 'C',
            'supervisor' => 'S',
            default => 'V',
        };

        if ($functionalProfile === 'cliente') {
            return [
                'modificaPrecio' => false,
                'modificaBonArt' => false,
                'modificaBonCli' => false,
                'modificaListaPrec' => false,
                'modificaCondVta' => $this->getBool("ModificaCondVta{$suffix}", false),
                'modificaDirEntr' => $this->getBool("ModificaDirEntr{$suffix}", true),
                'modificaExpreso' => $this->getBool("ModificaExpreso{$suffix}", true),
            ];
        }

        return [
            'modificaPrecio' => $this->getBool("ModificaPrecio{$suffix}", true),
            'modificaBonArt' => $this->getBool("ModificaBonArt{$suffix}", true),
            'modificaBonCli' => $this->getBool("ModificaBonCli{$suffix}", true),
            'modificaListaPrec' => $this->getBool("ModificaListaPrec{$suffix}", true),
            'modificaCondVta' => $this->getBool("ModificaCondVta{$suffix}", true),
            'modificaDirEntr' => $this->getBool("ModificaDirEntr{$suffix}", true),
            'modificaExpreso' => $this->getBool("ModificaExpreso{$suffix}", true),
        ];
    }

    public function getMonedaSimbolo(): string
    {
        return trim((string) $this->resolveValue('MonedaSimbolo', '$')) ?: '$';
    }

    public function getMonedaCodigo(): string
    {
        return trim((string) $this->resolveValue('MonedaCodigo', 'ARS')) ?: 'ARS';
    }

    private function getInt(string $key, int $defaultValue, ?int $minValue = null): int
    {
        $value = (int) $this->resolveValue($key, $defaultValue);

        if ($minValue !== null && $value < $minValue) {
            return $defaultValue;
        }

        return $value;
    }

    private function getBool(string $key, bool $defaultValue): bool
    {
        $row = $this->findParametro($key);

        if ($row === null) {
            return $this->boolFromConfigDefault($key, $defaultValue);
        }

        return $this->boolFromRow($row, $defaultValue);
    }

    private function getBoolConAliasCanonico(string $canonicalKey, string $legacyKey, bool $defaultValue): bool
    {
        if ($this->canReadFromErp()) {
            $index = $this->parametrosIndexados();

            if (isset($index[$canonicalKey])) {
                return $this->boolFromRow($index[$canonicalKey], $defaultValue);
            }

            if (isset($index[$legacyKey])) {
                return $this->boolFromRow($index[$legacyKey], $defaultValue);
            }
        }

        return $this->boolFromConfigDefault($canonicalKey, $defaultValue);
    }

    private function boolFromRow(PqParametrosGral $row, bool $defaultValue): bool
    {
        $tipoValor = ParametrosGralTipoValor::fromRow($row);

        return match ($tipoValor) {
            'B' => (bool) $row->Valor_Bool,
            'I' => (int) $row->Valor_Int === 1,
            'N' => (float) $row->Valor_Decimal !== 0.0,
            'S' => in_array(strtolower(trim((string) $row->Valor_String)), ['1', 'true', 'si', 'sí', 'yes'], true),
            default => $defaultValue,
        };
    }

    private function boolFromConfigDefault(string $key, bool $defaultValue): bool
    {
        $configValue = config('paqsuite_pedidosweb.defaults.'.$key, $defaultValue);

        if (is_bool($configValue)) {
            return $configValue;
        }

        if (is_int($configValue)) {
            return $configValue === 1;
        }

        return (bool) $configValue;
    }

    private function resolveValue(string $key, mixed $defaultValue): mixed
    {
        $row = $this->findParametro($key);

        if ($row === null) {
            return config('paqsuite_pedidosweb.defaults.'.$key, $defaultValue);
        }

        return $this->resolveValueFromRow($row, $key, $defaultValue);
    }

    private function resolveValueFromRow(PqParametrosGral $row, string $key, mixed $defaultValue): mixed
    {
        $tipoValor = ParametrosGralTipoValor::fromRow($row);

        return match ($tipoValor) {
            'B' => (bool) $row->Valor_Bool,
            'I' => $row->Valor_Int,
            'N' => $row->Valor_Decimal,
            'D' => $row->Valor_DateTime,
            'T' => $row->Valor_Text ?? '',
            default => $row->Valor_String ?? '',
        } ?? config('paqsuite_pedidosweb.defaults.'.$key, $defaultValue);
    }

    private function findParametro(string $clave): ?PqParametrosGral
    {
        if (! $this->canReadFromErp()) {
            return null;
        }

        return $this->parametrosIndexados()[$clave] ?? null;
    }

    private function canReadFromErp(): bool
    {
        if (! config('paqsuite_pedidosweb.readFromErp', true)) {
            return false;
        }

        try {
            return Schema::hasTable('PQ_parametros_gral');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, PqParametrosGral>
     */
    private function parametrosIndexados(): array
    {
        if ($this->parametrosPorClave !== null) {
            return $this->parametrosPorClave;
        }

        $programa = (string) config('paqsuite_pedidosweb.programa', 'PedidosWeb');

        $this->parametrosPorClave = PqParametrosGral::query()
            ->where('Programa', $programa)
            ->get()
            ->keyBy(static fn (PqParametrosGral $parametro): string => (string) $parametro->Clave)
            ->all();

        return $this->parametrosPorClave;
    }
}
