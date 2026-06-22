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
        $errores = $this->collectComprobanteGrabableErrors($cabeceraPayload, $renglonesPayload);

        if ($errores === []) {
            return;
        }

        throw new PedidosWebBusinessException(2000, $errores[0], 422);
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<array<string, mixed>>  $renglonesPayload
     * @return list<string>
     */
    public function collectComprobanteGrabableErrors(array $cabeceraPayload, array $renglonesPayload): array
    {
        $errores = [];
        $codCliente = trim((string) ($cabeceraPayload['cod_cliente'] ?? ''));

        if ($codCliente === '') {
            $errores[] = 'business.clienteRequerido';
        } else {
            $this->collectClienteHabilitadoErrors($codCliente, $errores);
        }

        $this->collectVendedorErrors($cabeceraPayload, $errores);
        $this->collectPerfilErrors($cabeceraPayload, $errores);
        $this->collectCondicionVentaErrors($cabeceraPayload, $errores);
        $this->collectTransporteErrors($cabeceraPayload, $errores);
        $this->collectDireccionEntregaErrors($cabeceraPayload, $codCliente, $errores);
        $this->collectListaPreciosErrors($cabeceraPayload, $errores);
        $this->collectNivelErrors($cabeceraPayload, $errores);
        $this->collectRenglonesGrabablesErrors($renglonesPayload, $errores);
        $this->collectPreciosRenglonesErrors($renglonesPayload, $errores);

        return array_values(array_unique($errores));
    }

    /**
     * @param  list<string>  $errores
     */
    private function collectClienteHabilitadoErrors(string $codCliente, array &$errores): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            return;
        }

        $query = PqPedidoswebCliente::query()->where('cod_client', $codCliente);

        if (! Schema::hasColumn('pq_pedidosweb_clientes', 'inhabilitado')) {
            if ($query->doesntExist()) {
                $errores[] = 'business.clienteNotFound';
            }

            return;
        }

        $cliente = $query->first(['cod_client', 'inhabilitado']);

        if ($cliente === null) {
            $errores[] = 'business.clienteNotFound';

            return;
        }

        if ((bool) $cliente->inhabilitado) {
            $errores[] = 'business.clienteInhabilitado';
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<string>  $errores
     */
    private function collectVendedorErrors(array $cabeceraPayload, array &$errores): void
    {
        $codVended = trim((string) ($cabeceraPayload['cod_vended'] ?? ''));

        if ($codVended === '') {
            $errores[] = 'business.vendedorRequerido';

            return;
        }

        if (! Schema::hasTable('pq_pedidosweb_vendedores')) {
            return;
        }

        if (! PqPedidoswebVendedor::query()->where('cod_vended', $codVended)->exists()) {
            $errores[] = 'business.vendedorInvalido';
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<string>  $errores
     */
    private function collectPerfilErrors(array $cabeceraPayload, array &$errores): void
    {
        $codPerfil = trim((string) ($cabeceraPayload['cod_perfil'] ?? ''));

        if ($codPerfil === '') {
            $errores[] = 'business.perfilRequerido';

            return;
        }

        if (! Schema::hasTable('pq_pedidosweb_perfil')) {
            return;
        }

        if (! PqPedidoswebPerfil::query()->where('cod_perfil', $codPerfil)->exists()) {
            $errores[] = 'business.perfilInvalido';
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<string>  $errores
     */
    private function collectCondicionVentaErrors(array $cabeceraPayload, array &$errores): void
    {
        $codCondvta = (int) ($cabeceraPayload['cod_condvta'] ?? 0);

        if ($codCondvta <= 0) {
            $errores[] = 'business.condicionVentaRequerida';

            return;
        }

        if (! Schema::hasTable('pq_pedidosweb_condventa')) {
            return;
        }

        if (! PqPedidoswebCondicionVenta::query()->where('codigo', $codCondvta)->exists()) {
            $errores[] = 'business.condicionVentaInvalida';
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<string>  $errores
     */
    private function collectTransporteErrors(array $cabeceraPayload, array &$errores): void
    {
        $codTranspor = trim((string) ($cabeceraPayload['cod_transpor'] ?? ''));

        if ($codTranspor === '') {
            $errores[] = 'business.transporteRequerido';

            return;
        }

        if (! Schema::hasTable('pq_pedidosweb_transportes')) {
            return;
        }

        if (! PqPedidoswebTransporte::query()->where('codigo', $codTranspor)->exists()) {
            $errores[] = 'business.transporteInvalido';
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<string>  $errores
     */
    private function collectDireccionEntregaErrors(array $cabeceraPayload, string $codCliente, array &$errores): void
    {
        $idDe = (int) ($cabeceraPayload['id_de'] ?? 0);

        if ($idDe <= 0) {
            $errores[] = 'business.direccionEntregaRequerida';

            return;
        }

        if ($codCliente === '' || ! Schema::hasTable('pq_pedidosweb_clientesde')) {
            return;
        }

        $exists = PqPedidoswebClienteDireccionEntrega::query()
            ->where('cod_client', $codCliente)
            ->where('id_de', $idDe)
            ->exists();

        if (! $exists) {
            $errores[] = 'business.direccionEntregaInvalida';
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<string>  $errores
     */
    private function collectListaPreciosErrors(array $cabeceraPayload, array &$errores): void
    {
        $codLista = (int) ($cabeceraPayload['lista_precios'] ?? 0);

        if ($codLista <= 0) {
            $errores[] = 'business.listaPreciosRequerida';

            return;
        }

        if (! Schema::hasTable('pq_pedidosweb_listaprecios')) {
            return;
        }

        if (! PqPedidoswebListaPrecios::query()->where('cod_lista', $codLista)->exists()) {
            $errores[] = 'business.listaPreciosInvalida';
        }
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<string>  $errores
     */
    private function collectNivelErrors(array $cabeceraPayload, array &$errores): void
    {
        if (! $this->parameterService->getNivelExtremo()) {
            return;
        }

        $nivel = (int) ($cabeceraPayload['nivel'] ?? 0);

        if (! in_array($nivel, [0, 100], true)) {
            $errores[] = 'business.nivelExtremoInvalido';
        }
    }

    /**
     * @param  list<array<string, mixed>>  $renglonesPayload
     * @param  list<string>  $errores
     */
    private function collectRenglonesGrabablesErrors(array $renglonesPayload, array &$errores): void
    {
        $renglonesActivos = array_values(array_filter(
            $renglonesPayload,
            static fn (array $renglon): bool => trim((string) ($renglon['cod_articulo'] ?? $renglon['codArticulo'] ?? '')) !== ''
        ));

        if ($renglonesActivos === []) {
            $errores[] = 'business.sinRenglones';
        }
    }

    /**
     * @param  list<array<string, mixed>>  $renglonesPayload
     * @param  list<string>  $errores
     */
    private function collectPreciosRenglonesErrors(array $renglonesPayload, array &$errores): void
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
                $errores[] = 'business.precioCeroNoPermitido';
            }
        }
    }
}
