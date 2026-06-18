<?php

namespace App\Services\ExcelImport\PedidoIndividual;

use App\Contracts\PedidosWeb\ArticuloRepositoryInterface;
use App\Models\PqPedidoswebArticulo;
use App\Models\User;
use App\Services\Auth\CommercialProfileResolver;
use App\Services\ExcelImport\Dto\ExcelRowError;
use App\Services\PedidosWeb\CabeceraInicialService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\Visibility\PedidosWebVisibilityGuard;

final class PedidoIndividualRowResolver
{
    /** @var array<string, array{cabecera: array<string, mixed>, catalogos: array<string, mixed>}> */
    private array $cabeceraCacheByCliente = [];

    public function __construct(
        private readonly CabeceraInicialService $cabeceraInicialService,
        private readonly ArticuloRepositoryInterface $articuloRepository,
        private readonly PedidosWebParameterService $parameterService,
        private readonly PedidosWebVisibilityGuard $visibilityGuard,
        private readonly CommercialProfileResolver $commercialProfileResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     * @return ExcelRowError[]
     */
    public function validateBusinessRow(array $row, User $user): array
    {
        $errors = [];
        $profile = $this->resolveFunctionalProfile($user);
        $modifica = $this->parameterService->resolveModificaFlags($profile);

        $codCliente = trim((string) ($row['cod_cliente'] ?? ''));
        if ($codCliente === '') {
            return $errors;
        }

        try {
            $this->visibilityGuard->ensureCodClienteVisible($user, $codCliente);
        } catch (\Throwable) {
            $errors[] = $this->businessError('cod_cliente', 'codigo cliente', 'excel_import.pedidoIndividual.clienteSesion');

            return $errors;
        }

        $commercial = $this->commercialProfileResolver->resolveForUser($user);
        if ($profile === 'cliente' && $commercial['cliente'] !== null) {
            $sessionCliente = trim((string) $commercial['cliente']->cod_client);
            if ($sessionCliente !== '' && strcasecmp($sessionCliente, $codCliente) !== 0) {
                $errors[] = $this->businessError('cod_cliente', 'codigo cliente', 'excel_import.pedidoIndividual.clienteSesion');
            }
        }

        if ($profile === 'cliente') {
            foreach (['cod_lista', 'precio_lista', 'bonif_renglon'] as $field) {
                if ($this->isFilled($row[$field] ?? null)) {
                    $errors[] = $this->businessError($field, $field, 'excel_import.pedidoIndividual.campoNoEditable');
                }
            }
        } else {
            if (! $modifica['modificaListaPrec'] && $this->isFilled($row['cod_lista'] ?? null)) {
                $errors[] = $this->businessError('cod_lista', 'codigo lista', 'excel_import.pedidoIndividual.campoNoEditable');
            }
            if (! $modifica['modificaPrecio'] && $this->isFilled($row['precio_lista'] ?? null)) {
                $errors[] = $this->businessError('precio_lista', 'precio lista', 'excel_import.pedidoIndividual.campoNoEditable');
            }
            if (! $modifica['modificaBonArt'] && $this->isFilled($row['bonif_renglon'] ?? null)) {
                $errors[] = $this->businessError('bonif_renglon', 'bonif renglon', 'excel_import.pedidoIndividual.campoNoEditable');
            }
            if (! $modifica['modificaBonCli']) {
                foreach (['bonif1', 'bonif2', 'bonif3'] as $field) {
                    if ($this->isFilled($row[$field] ?? null)) {
                        $errors[] = $this->businessError($field, $field, 'excel_import.pedidoIndividual.campoNoEditable');
                    }
                }
            }
        }

        $cantidad = (float) ($row['cantidad'] ?? 0);
        if ($cantidad <= 0) {
            $errors[] = $this->businessError('cantidad', 'cantidad', 'excel_import.pedidoIndividual.cantidadInvalida');
        }

        $codArticulo = trim((string) ($row['cod_articulo'] ?? ''));
        if ($codArticulo !== '') {
            $allowed = PqPedidoswebArticulo::query()
                ->excluirArticulosBaseCarga()
                ->where('codigo', $codArticulo)
                ->exists();

            if (! $allowed) {
                $exists = $this->articuloRepository->findByCodigo($codArticulo) !== null;
                $errors[] = $this->businessError(
                    'cod_articulo',
                    'codigo de articulo',
                    $exists ? 'excel_import.pedidoIndividual.articuloBase' : 'excel_import.pedidoIndividual.articuloNoEncontrado'
                );
            }
        }

        if ($this->parameterService->getNivelExtremo()) {
            $nivel = $row['nivel'] ?? null;
            if ($nivel !== null && $nivel !== '' && ! in_array((int) $nivel, [0, 100], true)) {
                $errors[] = $this->businessError('nivel', 'nivel', 'excel_import.pedidoIndividual.nivelInvalido');
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        try {
            $resolved = $this->resolveRow($row, $user);
            $precio = (float) ($resolved['precio'] ?? 0);
            if ($precio <= 0 && ! $this->parameterService->getArticuloPrecioCero() && ! $this->parameterService->getArticulosSinPrecio()) {
                $errors[] = $this->businessError('precio_lista', 'precio lista', 'excel_import.pedidoIndividual.precioCero');
            }
        } catch (\Throwable) {
            $errors[] = $this->businessError('cod_cliente', 'codigo cliente', 'excel_import.pedidoIndividual.clienteSesion');
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function enrichRow(array $row, User $user): array
    {
        return $this->resolveRow($row, $user);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function resolveRow(array $row, User $user): array
    {
        $codCliente = trim((string) ($row['cod_cliente'] ?? ''));
        $cabeceraBundle = $this->resolveCabeceraBundle($codCliente, $user);
        $cabecera = $cabeceraBundle['cabecera'];

        $codLista = (int) ($row['cod_lista'] ?? $cabecera['lista_precios'] ?? 0);
        $codArticulo = trim((string) ($row['cod_articulo'] ?? ''));
        $cantidad = (float) ($row['cantidad'] ?? 0);

        $articulo = $this->articuloRepository->findByCodigo($codArticulo);
        $precioLista = $row['precio_lista'] ?? null;
        $precio = $precioLista !== null && $precioLista !== ''
            ? (float) $precioLista
            : (float) ($this->articuloRepository->findPrecioLista($codLista, $codArticulo)?->precio ?? 0);

        $bonifRenglon = $row['bonif_renglon'] ?? null;
        if ($bonifRenglon === null || $bonifRenglon === '') {
            $descuentoCantidad = $this->articuloRepository->findDescuentoCantidad($codArticulo, $cantidad);
            $porcBonif = $descuentoCantidad !== null
                ? (float) $descuentoCantidad->descuento
                : (float) ($articulo?->bonificacion ?? 0);
        } else {
            $porcBonif = (float) $bonifRenglon;
        }

        return [
            'cod_cliente' => $codCliente,
            'cod_articulo' => $codArticulo,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'porc_bonif' => $porcBonif,
            'porc_iva' => (float) ($articulo?->porc_iva ?? 0),
            'descripcion_articulo' => (string) ($articulo?->descripcion ?? ''),
            'cod_condvta' => (int) ($row['cod_condvta'] ?? $cabecera['cod_condvta'] ?? 0),
            'cod_transpor' => $row['cod_transpor'] ?? $cabecera['cod_transpor'] ?? null,
            'id_de' => (int) ($row['id_de'] ?? $cabecera['id_de'] ?? 0),
            'cod_lista' => $codLista,
            'nivel' => (int) ($row['nivel'] ?? $cabecera['nivel'] ?? 0),
            'bonif1' => (float) ($row['bonif1'] ?? $cabecera['bonif_1'] ?? 0),
            'bonif2' => (float) ($row['bonif2'] ?? $cabecera['bonif_2'] ?? 0),
            'bonif3' => (float) ($row['bonif3'] ?? $cabecera['bonif_3'] ?? 0),
            'expreso' => $row['expreso'] ?? $cabecera['expreso'] ?? null,
            'expreso_dire' => $row['expreso_dire'] ?? $cabecera['expreso_dire'] ?? null,
            'fecha_entrega' => $row['fecha_entrega'] ?? $cabecera['fecha_entrega'] ?? null,
            'observaciones' => (string) ($row['observaciones'] ?? $cabecera['observaciones'] ?? ''),
            'cod_perfil' => (string) ($row['cod_perfil'] ?? $cabecera['cod_perfil'] ?? $this->parameterService->getCodPerfilPedidos()),
            'leyenda1' => $row['leyenda1'] ?? $cabecera['leyenda_1'] ?? null,
            'leyenda2' => $row['leyenda2'] ?? $cabecera['leyenda_2'] ?? null,
            'leyenda3' => $row['leyenda3'] ?? $cabecera['leyenda_3'] ?? null,
            'leyenda4' => $row['leyenda4'] ?? $cabecera['leyenda_4'] ?? null,
            'leyenda5' => $row['leyenda5'] ?? $cabecera['leyenda_5'] ?? null,
        ];
    }

    /**
     * @return array{cabecera: array<string, mixed>, catalogos: array<string, mixed>}
     */
    private function resolveCabeceraBundle(string $codCliente, User $user): array
    {
        if (isset($this->cabeceraCacheByCliente[$codCliente])) {
            return $this->cabeceraCacheByCliente[$codCliente];
        }

        $bundle = $this->cabeceraInicialService->buildForCliente($codCliente, $user);
        $this->cabeceraCacheByCliente[$codCliente] = $bundle;

        return $bundle;
    }

    private function resolveFunctionalProfile(User $user): string
    {
        $commercialProfile = $this->commercialProfileResolver->resolveForUser($user);

        if ($commercialProfile['cliente'] !== null) {
            return 'cliente';
        }

        if ($commercialProfile['vendedor'] !== null) {
            return $commercialProfile['vendedor']->supervisor ? 'supervisor' : 'vendedor';
        }

        return 'vendedor';
    }

    private function isFilled(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        return true;
    }

    private function businessError(string $field, string $columnExcel, string $messageKey): ExcelRowError
    {
        return new ExcelRowError(
            'negocio',
            trans($messageKey),
            null,
            $field,
            $columnExcel
        );
    }
}
