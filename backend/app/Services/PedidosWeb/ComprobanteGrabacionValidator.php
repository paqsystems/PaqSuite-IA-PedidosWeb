<?php

namespace App\Services\PedidosWeb;

use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebCliente;
use App\Models\PqPedidoswebClienteDireccionEntrega;
use App\Models\PqPedidoswebCondicionVenta;
use App\Models\PqPedidoswebListaPrecios;
use App\Models\PqPedidoswebPerfil;
use App\Models\PqPedidoswebTransporte;
use App\Models\PqPedidoswebVendedor;
use Illuminate\Support\Facades\Schema;

final class ComprobanteGrabacionValidator
{
    public function __construct(
        private readonly PedidosWebParameterService $parameterService,
    ) {}

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<array<string, mixed>>  $renglonesPayload
     */
    public function assertComprobanteGrabable(array $cabeceraPayload, array $renglonesPayload): void
    {
        $codCliente = trim((string) ($cabeceraPayload['cod_cliente'] ?? ''));

        if ($codCliente === '') {
            throw new PedidosWebBusinessException(2000, 'business.clienteRequerido', 422);
        }

        $this->assertClienteHabilitado($codCliente);
        $this->assertVendedorValido($cabeceraPayload);
        $this->assertPerfilValido($cabeceraPayload);
        $this->assertCondicionVentaValida($cabeceraPayload);
        $this->assertTransporteValido($cabeceraPayload);
        $this->assertDireccionEntregaValida($cabeceraPayload, $codCliente);
        $this->assertListaPreciosValida($cabeceraPayload);
        $this->assertNivelValido($cabeceraPayload);
        $this->assertRenglonesGrabables($renglonesPayload);
        $this->assertPreciosRenglonesValidos($renglonesPayload);
    }

    private function assertClienteHabilitado(string $codCliente): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            return;
        }

        $query = PqPedidoswebCliente::query()->where('cod_client', $codCliente);

        if (! Schema::hasColumn('pq_pedidosweb_clientes', 'inhabilitado')) {
            if ($query->doesntExist()) {
                throw new PedidosWebBusinessException(2000, 'business.clienteNotFound', 422);
            }

            return;
        }

        $cliente = $query->first(['cod_client', 'inhabilitado']);

        if ($cliente === null) {
            throw new PedidosWebBusinessException(2000, 'business.clienteNotFound', 422);
        }

        if ((bool) $cliente->inhabilitado) {
            throw new PedidosWebBusinessException(2000, 'business.clienteInhabilitado', 422);
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    private function assertVendedorValido(array $cabeceraPayload): void
    {
        $codVended = trim((string) ($cabeceraPayload['cod_vended'] ?? ''));

        if ($codVended === '') {
            throw new PedidosWebBusinessException(2000, 'business.vendedorRequerido', 422);
        }

        if (! Schema::hasTable('pq_pedidosweb_vendedores')) {
            return;
        }

        if (! PqPedidoswebVendedor::query()->where('cod_vended', $codVended)->exists()) {
            throw new PedidosWebBusinessException(2000, 'business.vendedorInvalido', 422);
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    private function assertPerfilValido(array $cabeceraPayload): void
    {
        $codPerfil = trim((string) ($cabeceraPayload['cod_perfil'] ?? ''));

        if ($codPerfil === '') {
            throw new PedidosWebBusinessException(2000, 'business.perfilRequerido', 422);
        }

        if (! Schema::hasTable('pq_pedidosweb_perfil')) {
            return;
        }

        if (! PqPedidoswebPerfil::query()->where('cod_perfil', $codPerfil)->exists()) {
            throw new PedidosWebBusinessException(2000, 'business.perfilInvalido', 422);
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    private function assertCondicionVentaValida(array $cabeceraPayload): void
    {
        $codCondvta = (int) ($cabeceraPayload['cod_condvta'] ?? 0);

        if ($codCondvta <= 0) {
            throw new PedidosWebBusinessException(2000, 'business.condicionVentaRequerida', 422);
        }

        if (! Schema::hasTable('pq_pedidosweb_condventa')) {
            return;
        }

        if (! PqPedidoswebCondicionVenta::query()->where('codigo', $codCondvta)->exists()) {
            throw new PedidosWebBusinessException(2000, 'business.condicionVentaInvalida', 422);
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    private function assertTransporteValido(array $cabeceraPayload): void
    {
        $codTranspor = trim((string) ($cabeceraPayload['cod_transpor'] ?? ''));

        if ($codTranspor === '') {
            throw new PedidosWebBusinessException(2000, 'business.transporteRequerido', 422);
        }

        if (! Schema::hasTable('pq_pedidosweb_transportes')) {
            return;
        }

        if (! PqPedidoswebTransporte::query()->where('codigo', $codTranspor)->exists()) {
            throw new PedidosWebBusinessException(2000, 'business.transporteInvalido', 422);
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    private function assertDireccionEntregaValida(array $cabeceraPayload, string $codCliente): void
    {
        $idDe = (int) ($cabeceraPayload['id_de'] ?? 0);

        if ($idDe <= 0) {
            throw new PedidosWebBusinessException(2000, 'business.direccionEntregaRequerida', 422);
        }

        if (! Schema::hasTable('pq_pedidosweb_clientesde')) {
            return;
        }

        $exists = PqPedidoswebClienteDireccionEntrega::query()
            ->where('cod_client', $codCliente)
            ->where('id_de', $idDe)
            ->exists();

        if (! $exists) {
            throw new PedidosWebBusinessException(2000, 'business.direccionEntregaInvalida', 422);
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    private function assertListaPreciosValida(array $cabeceraPayload): void
    {
        $codLista = (int) ($cabeceraPayload['lista_precios'] ?? 0);

        if ($codLista <= 0) {
            throw new PedidosWebBusinessException(2000, 'business.listaPreciosRequerida', 422);
        }

        if (! Schema::hasTable('pq_pedidosweb_listaprecios')) {
            return;
        }

        if (! PqPedidoswebListaPrecios::query()->where('cod_lista', $codLista)->exists()) {
            throw new PedidosWebBusinessException(2000, 'business.listaPreciosInvalida', 422);
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     */
    private function assertNivelValido(array $cabeceraPayload): void
    {
        if (! $this->parameterService->getNivelExtremo()) {
            return;
        }

        $nivel = (int) ($cabeceraPayload['nivel'] ?? 0);

        if (! in_array($nivel, [0, 100], true)) {
            throw new PedidosWebBusinessException(2000, 'business.nivelExtremoInvalido', 422);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $renglonesPayload
     */
    private function assertRenglonesGrabables(array $renglonesPayload): void
    {
        $renglonesActivos = array_values(array_filter(
            $renglonesPayload,
            static fn (array $renglon): bool => trim((string) ($renglon['cod_articulo'] ?? $renglon['codArticulo'] ?? '')) !== ''
        ));

        if ($renglonesActivos === []) {
            throw new PedidosWebBusinessException(2000, 'business.sinRenglones', 422);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $renglonesPayload
     */
    private function assertPreciosRenglonesValidos(array $renglonesPayload): void
    {
        if ($this->parameterService->getArticuloPrecioCero() || $this->parameterService->getArticulosSinPrecio()) {
            return;
        }

        foreach ($renglonesPayload as $renglon) {
            $codArticulo = trim((string) ($renglon['cod_articulo'] ?? $renglon['codArticulo'] ?? ''));

            if ($codArticulo === '') {
                continue;
            }

            $precio = (float) ($renglon['precio'] ?? 0);

            if ($precio <= 0) {
                throw new PedidosWebBusinessException(2000, 'business.precioCeroNoPermitido', 422);
            }
        }
    }
}
