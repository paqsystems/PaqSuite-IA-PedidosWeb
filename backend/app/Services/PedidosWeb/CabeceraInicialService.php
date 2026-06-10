<?php

namespace App\Services\PedidosWeb;

use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebCliente;
use App\Models\PqPedidoswebClienteDireccionEntrega;
use App\Models\PqPedidoswebCondicionVenta;
use App\Models\PqPedidoswebListaPrecios;
use App\Models\PqPedidoswebPerfil;
use App\Models\PqPedidoswebTransporte;
use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use Illuminate\Support\Facades\Schema;

final class CabeceraInicialService
{
    public function __construct(
        private readonly PedidosWebVisibilityGuard $visibilityGuard,
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildForCliente(string $codCliente, User $user): array
    {
        $this->visibilityGuard->ensureCodClienteVisible($user, $codCliente);

        $cliente = PqPedidoswebCliente::query()
            ->with(['vendedor', 'direccionesEntrega'])
            ->where('cod_client', $codCliente)
            ->first();

        if ($cliente === null) {
            throw new PedidosWebBusinessException(4000, 'business.clienteNotFound', 404);
        }

        $listaPrecios = $this->resolveListaPrecios($cliente);
        $direccionHabitual = $this->resolveDireccionHabitual($cliente);
        $codPerfil = $this->parameterService->getCodPerfilPedidos();

        return [
            'cabecera' => [
                'cod_cliente' => (string) $cliente->cod_client,
                'cod_vended' => $cliente->cod_vended,
                'vendedor_nombre' => (string) ($cliente->vendedor?->nombre ?? ''),
                'cod_condvta' => (int) ($cliente->cod_condvta ?? 0),
                'cod_transpor' => $cliente->cod_transpor,
                'id_de' => $direccionHabitual['id_de'],
                'direccion_entrega' => $direccionHabitual['direccion'],
                'expreso' => $cliente->expreso,
                'expreso_dire' => $cliente->expreso_dire,
                'nivel' => (int) ($cliente->nivel ?? 0),
                'lista_precios' => (int) $listaPrecios->cod_lista,
                'lista_precios_descripcion' => (string) $listaPrecios->descripcion,
                'moneda' => (int) $listaPrecios->moneda,
                'incluye_iva' => (bool) $listaPrecios->incluye_iva,
                'bonif_1' => (float) ($cliente->bonificacion ?? 0),
                'bonif_2' => 0.0,
                'bonif_3' => 0.0,
                'descuento' => 0.0,
                'observaciones' => '',
                'cod_perfil' => $codPerfil,
                'leyenda_1' => $this->resolveLeyendaCliente($cliente, 1),
                'leyenda_2' => $this->resolveLeyendaCliente($cliente, 2),
                'leyenda_3' => $this->resolveLeyendaCliente($cliente, 3),
                'leyenda_4' => $this->resolveLeyendaCliente($cliente, 4),
                'leyenda_5' => $this->resolveLeyendaCliente($cliente, 5),
                'fecha_entrega' => null,
            ],
            'catalogos' => $this->buildCatalogos($codCliente),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mapCabeceraFromPedido(PqPedidoswebCliente $cliente, object $pedido): array
    {
        return [
            'cod_cliente' => (string) $pedido->cod_cliente,
            'cod_vended' => $pedido->cod_vended,
            'vendedor_nombre' => (string) ($pedido->vendedor?->nombre ?? $cliente->vendedor?->nombre ?? ''),
            'cod_condvta' => (int) ($pedido->cod_condvta ?? 0),
            'cod_transpor' => $pedido->cod_transpor,
            'id_de' => $pedido->id_de,
            'expreso' => $pedido->expreso,
            'expreso_dire' => $pedido->expreso_dire,
            'nivel' => (int) ($pedido->nivel ?? 0),
            'lista_precios' => $pedido->lista_precios,
            'lista_precios_descripcion' => (string) ($pedido->listaPrecios?->descripcion ?? ''),
            'moneda' => (int) ($pedido->moneda ?? 1),
            'incluye_iva' => (bool) ($pedido->incluye_iva ?? false),
            'bonif_1' => (float) ($pedido->bonif_1 ?? 0),
            'bonif_2' => (float) ($pedido->bonif_2 ?? 0),
            'bonif_3' => (float) ($pedido->bonif_3 ?? 0),
            'descuento' => (float) ($pedido->descuento ?? 0),
            'observaciones' => $pedido->observaciones,
            'cod_perfil' => $pedido->cod_perfil,
            'leyenda_1' => $pedido->leyenda_1,
            'leyenda_2' => $pedido->leyenda_2,
            'leyenda_3' => $pedido->leyenda_3,
            'leyenda_4' => $pedido->leyenda_4,
            'leyenda_5' => $pedido->leyenda_5,
            'fecha_entrega' => optional($pedido->fecha_entrega)?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function catalogosForCliente(string $codCliente): array
    {
        return $this->buildCatalogos($codCliente);
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function buildCatalogos(string $codCliente): array
    {
        $condiciones = Schema::hasTable('pq_pedidosweb_condventa')
            ? PqPedidoswebCondicionVenta::query()
                ->orderBy('descripcion')
                ->get()
                ->map(static fn ($row): array => [
                    'codigo' => (int) $row->codigo,
                    'descripcion' => (string) $row->descripcion,
                ])
                ->values()
                ->all()
            : [];

        $transportes = Schema::hasTable('pq_pedidosweb_transportes')
            ? PqPedidoswebTransporte::query()
                ->orderBy('descripcion')
                ->get()
                ->map(static fn ($row): array => [
                    'codigo' => (string) $row->codigo,
                    'descripcion' => (string) $row->descripcion,
                ])
                ->values()
                ->all()
            : [];

        $listas = Schema::hasTable('pq_pedidosweb_listaprecios')
            ? PqPedidoswebListaPrecios::query()
                ->orderBy('descripcion')
                ->get()
                ->map(static fn ($row): array => [
                    'cod_lista' => (int) $row->cod_lista,
                    'descripcion' => (string) $row->descripcion,
                    'moneda' => (int) $row->moneda,
                    'incluye_iva' => (bool) $row->incluye_iva,
                ])
                ->values()
                ->all()
            : [];

        $direcciones = Schema::hasTable('pq_pedidosweb_clientesde')
            ? PqPedidoswebClienteDireccionEntrega::query()
                ->where('cod_client', $codCliente)
                ->orderByDesc('habitual')
                ->orderBy('direccion')
                ->get()
                ->map(static fn ($row): array => [
                    'id_de' => (int) $row->id_de,
                    'direccion' => trim((string) $row->direccion),
                    'localidad' => (string) ($row->localidad ?? ''),
                    'habitual' => (bool) $row->habitual,
                ])
                ->values()
                ->all()
            : [];

        $perfiles = Schema::hasTable('pq_pedidosweb_perfil')
            ? PqPedidoswebPerfil::query()
                ->orderBy('descripcion')
                ->get()
                ->map(static fn ($row): array => [
                    'cod_perfil' => (string) $row->cod_perfil,
                    'descripcion' => (string) $row->descripcion,
                ])
                ->values()
                ->all()
            : [];

        return [
            'condicionesVenta' => $condiciones,
            'transportes' => $transportes,
            'listasPrecios' => $listas,
            'direccionesEntrega' => $direcciones,
            'perfiles' => $perfiles,
        ];
    }

    private function resolveListaPrecios(PqPedidoswebCliente $cliente): PqPedidoswebListaPrecios
    {
        $codLista = (int) ($cliente->lista_precios ?? 0);

        if ($codLista > 0) {
            $lista = PqPedidoswebListaPrecios::query()->find($codLista);
            if ($lista !== null) {
                return $lista;
            }
        }

        $lista = PqPedidoswebListaPrecios::query()->orderBy('cod_lista')->first();

        if ($lista === null) {
            throw new PedidosWebBusinessException(2000, 'business.listaPreciosNoConfigurada', 422);
        }

        return $lista;
    }

    /**
     * @return array{id_de: int|null, direccion: string}
     */
    private function resolveLeyendaCliente(PqPedidoswebCliente $cliente, int $numero): ?string
    {
        if (! $this->parameterService->getClienteLeyendaInicializa($numero)) {
            return null;
        }

        $attribute = "leyenda_{$numero}";
        $valor = trim((string) ($cliente->{$attribute} ?? ''));

        return $valor !== '' ? $valor : null;
    }

    /**
     * @return array{id_de: int|null, direccion: string}
     */
    private function resolveDireccionHabitual(PqPedidoswebCliente $cliente): array
    {
        $direccion = $cliente->direccionesEntrega
            ->sortByDesc(static fn ($row) => (int) $row->habitual)
            ->first();

        if ($direccion === null) {
            return ['id_de' => null, 'direccion' => ''];
        }

        return [
            'id_de' => (int) $direccion->id_de,
            'direccion' => trim((string) $direccion->direccion),
        ];
    }
}
