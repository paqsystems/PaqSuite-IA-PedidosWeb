<?php

namespace App\Services\PedidosWeb;

use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PedidoService
{
    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
        private readonly PedidoDetalleRepositoryInterface $pedidoDetalleRepository,
        private readonly PedidosWebParameterService $parameterService,
        private readonly CalculoTotalesService $calculoTotalesService,
        private readonly ComprobanteCopiaService $comprobanteCopiaService,
        private readonly PresupuestoCierreService $presupuestoCierreService,
        private readonly ComprobanteMailService $comprobanteMailService,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function grabarComprobante(array $payload, User $user): array
    {
        $accionGrabacion = (string) ($payload['accionGrabacion'] ?? '');
        $cabeceraPayload = (array) ($payload['cabecera'] ?? []);
        $renglonesPayload = (array) ($payload['renglones'] ?? []);

        if (! in_array($accionGrabacion, ['pedido', 'presupuesto'], true)) {
            throw new PedidosWebBusinessException(2000, 'business.accionGrabacionInvalida', 422);
        }

        if (($cabeceraPayload['cod_cliente'] ?? '') === '' || $renglonesPayload === []) {
            throw new PedidosWebBusinessException(2000, 'business.invalidComprobanteData', 422);
        }

        $calculo = $this->calculoTotalesService->calcular($renglonesPayload);
        $codPedidoOrigen = $this->nullableString($payload['cod_pedido_origen'] ?? null);
        $codPresupuestoOrigen = $this->nullableString($payload['cod_presupuesto_origen'] ?? null);
        $codComprobante = $this->nullableString($payload['cod_pedido'] ?? null);
        $mailEnviado = false;

        /** @var array<string, mixed> $result */
        $result = DB::transaction(function () use (
            $accionGrabacion,
            $cabeceraPayload,
            $calculo,
            $codComprobante,
            $codPedidoOrigen,
            $codPresupuestoOrigen,
            $user,
            &$mailEnviado
        ): array {
            if ($accionGrabacion === 'pedido' && $codPresupuestoOrigen !== null) {
                $result = $this->convertirPresupuestoAPedido(
                    $codPresupuestoOrigen,
                    $cabeceraPayload,
                    $calculo['renglones'],
                    (float) $calculo['total'],
                    (float) $calculo['totalIva'],
                    $user
                );
                $mailEnviado = $this->sendMailForResult($result, 'pedido', 'ingresado', $user);

                return [...$result, 'mailEnviado' => $mailEnviado];
            }

            if ($accionGrabacion === 'presupuesto' && $codPedidoOrigen !== null) {
                $result = $this->convertirPedidoAPresupuesto(
                    $codPedidoOrigen,
                    $cabeceraPayload,
                    $calculo['renglones'],
                    (float) $calculo['total'],
                    (float) $calculo['totalIva'],
                    $user
                );
                $mailEnviado = $this->sendMailForResult($result, 'presupuesto', 'ingresado', $user);

                return [...$result, 'mailEnviado' => $mailEnviado];
            }

            $estadoObjetivo = $accionGrabacion === 'pedido' ? 0 : 99;
            $accionComprobante = $codComprobante === null ? 'ingresado' : 'modificado';

            $cabecera = $this->upsertComprobante(
                $codComprobante,
                $cabeceraPayload,
                $calculo['renglones'],
                (float) $calculo['total'],
                (float) $calculo['totalIva'],
                $estadoObjetivo,
                $accionGrabacion,
                $user
            );

            $mailEnviado = $this->comprobanteMailService->enviarComprobante(
                $cabecera,
                $calculo['renglones'],
                $accionGrabacion,
                $accionComprobante,
                $user
            );

            return [
                'cod_pedido' => (string) $cabecera->cod_pedido,
                'estado' => (int) $cabecera->estado,
                'nro_visible' => (int) ($cabecera->nro_visible ?? 0),
                'total' => (float) $cabecera->total,
                'total_iva' => (float) $cabecera->total_iva,
                'guidSufijo' => strtoupper(substr((string) $cabecera->cod_pedido, -6)),
                'mailEnviado' => $mailEnviado,
            ];
        });

        return $result;
    }

    public function eliminarPedido(string $codPedido): void
    {
        if ($this->parameterService->getNoEliminaPedido()) {
            throw new PedidosWebBusinessException(2000, 'business.eliminacionDeshabilitada', 422);
        }

        $pedido = $this->pedidoRepository->findByCodPedido($codPedido, true);

        if ($pedido === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        if ((int) $pedido->estado !== 0) {
            throw new PedidosWebBusinessException(2000, 'business.onlyEstadoCeroDelete', 422);
        }

        DB::transaction(function () use ($codPedido): void {
            $this->pedidoDetalleRepository->deleteByCodPedido($codPedido);
            $this->pedidoRepository->deleteFisicoCabecera($codPedido);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function iniciarEdicion(string $codPedido, User $user): array
    {
        $pedido = $this->pedidoRepository->findByCodPedido($codPedido, true);

        if ($pedido === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        if ((int) $pedido->estado === -1 && $this->hasEdicionActiva($pedido, $user)) {
            throw new PedidosWebBusinessException(4000, 'business.edicionEnCurso', 409);
        }

        if (! in_array((int) $pedido->estado, [0, -1], true)) {
            throw new PedidosWebBusinessException(2000, 'business.estadoNoEditable', 422);
        }

        $timestamp = now()->format('Ymd H:i:s');

        $this->pedidoRepository->updateCabecera($codPedido, [
            'estado' => -1,
            'cod_usuario_web' => $user->codigo,
            'fechahora_inicio_proceso' => $timestamp,
            'fechahora_ultima_actividad' => $timestamp,
            'fecha_modif' => $timestamp,
            'usuario_modificacion' => $user->codigo,
        ]);

        return [
            'cod_pedido' => $codPedido,
            'estado' => -1,
            'fechahora_ultima_actividad' => $timestamp,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function touchActividad(string $codPedido, User $user): array
    {
        $pedido = $this->pedidoRepository->findByCodPedido($codPedido, true);

        if ($pedido === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        if ((int) $pedido->estado !== -1) {
            throw new PedidosWebBusinessException(2000, 'business.touchOnlyForEstadoMenosUno', 422);
        }

        $timestamp = now()->format('Ymd H:i:s');

        $this->pedidoRepository->updateCabecera($codPedido, [
            'fechahora_ultima_actividad' => $timestamp,
            'fecha_modif' => $timestamp,
            'usuario_modificacion' => $user->codigo,
        ]);

        return [
            'cod_pedido' => $codPedido,
            'fechahora_ultima_actividad' => $timestamp,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cancelarEdicion(string $codPedido, User $user): array
    {
        $pedido = $this->pedidoRepository->findByCodPedido($codPedido, true);

        if ($pedido === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        if ((int) $pedido->estado !== -1) {
            throw new PedidosWebBusinessException(2000, 'business.cancelOnlyForEstadoMenosUno', 422);
        }

        $timestamp = now()->format('Ymd H:i:s');

        $this->pedidoRepository->updateCabecera($codPedido, [
            'estado' => 0,
            'fecha_modif' => $timestamp,
            'usuario_modificacion' => $user->codigo,
        ]);

        return [
            'cod_pedido' => $codPedido,
            'estado' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getComprobante(string $codPedido): array
    {
        $pedido = $this->pedidoRepository->findWithDetalle($codPedido);

        if ($pedido === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        return [
            'cabecera' => [
                'cod_pedido' => (string) $pedido->cod_pedido,
                'cod_cliente' => (string) $pedido->cod_cliente,
                'estado' => (int) $pedido->estado,
                'fecha' => optional($pedido->fecha)?->toIso8601String(),
                'observaciones' => $pedido->observaciones,
                'nro_visible' => (int) ($pedido->nro_visible ?? 0),
                'total' => (float) $pedido->total,
                'total_iva' => (float) $pedido->total_iva,
            ],
            'detalle' => $pedido->detalles
                ->map(static fn ($row): array => [
                    'renglon' => (int) $row->renglon,
                    'cod_articulo' => (string) $row->cod_articulo,
                    'descripcion_articulo' => (string) ($row->descripcion_articulo ?? ''),
                    'cantidad' => (float) $row->cantidad,
                    'porc_bonif' => (float) $row->porc_bonif,
                    'precio' => (float) $row->precio,
                    'precio_neto' => (float) $row->precio_neto,
                    'porc_iva' => (float) $row->porc_iva,
                    'importe_total' => (float) $row->importe_total,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function copiarComprobante(array $payload): array
    {
        $codComprobanteOrigen = (string) ($payload['codComprobanteOrigen'] ?? $payload['cod_pedido_origen'] ?? '');
        $tipoDestino = (string) ($payload['tipoDestino'] ?? $payload['tipo_destino'] ?? 'pedido');

        if ($codComprobanteOrigen === '' || ! in_array($tipoDestino, ['pedido', 'presupuesto'], true)) {
            throw new PedidosWebBusinessException(2000, 'business.invalidCopyRequest', 422);
        }

        return [
            'borrador' => $this->comprobanteCopiaService->copiarBorrador($codComprobanteOrigen, $tipoDestino),
        ];
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<array<string, mixed>>  $renglones
     * @return array<string, mixed>
     */
    private function convertirPresupuestoAPedido(
        string $codPresupuestoOrigen,
        array $cabeceraPayload,
        array $renglones,
        float $total,
        float $totalIva,
        User $user
    ): array {
        $origen = $this->pedidoRepository->findByCodPedido($codPresupuestoOrigen, true);

        if ($origen === null || (int) $origen->estado !== 99) {
            throw new PedidosWebBusinessException(2000, 'business.presupuestoOrigenInvalido', 422);
        }

        $pedido = $this->upsertComprobante(
            null,
            $cabeceraPayload,
            $renglones,
            $total,
            $totalIva,
            0,
            'pedido',
            $user,
            [
                'origen_comprobante' => 'conversion_presupuesto_a_pedido',
                'cod_presupuesto_origen' => $codPresupuestoOrigen,
            ]
        );

        $this->presupuestoCierreService->cerrarPorConversion(
            $codPresupuestoOrigen,
            (string) $pedido->cod_pedido,
            $user
        );

        return [
            'cod_pedido' => (string) $pedido->cod_pedido,
            'estado' => (int) $pedido->estado,
            'nro_visible' => (int) ($pedido->nro_visible ?? 0),
            'total' => (float) $pedido->total,
            'total_iva' => (float) $pedido->total_iva,
            'guidSufijo' => strtoupper(substr((string) $pedido->cod_pedido, -6)),
            'cod_presupuesto_origen' => $codPresupuestoOrigen,
        ];
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<array<string, mixed>>  $renglones
     * @return array<string, mixed>
     */
    private function convertirPedidoAPresupuesto(
        string $codPedidoOrigen,
        array $cabeceraPayload,
        array $renglones,
        float $total,
        float $totalIva,
        User $user
    ): array {
        $origen = $this->pedidoRepository->findByCodPedido($codPedidoOrigen, true);

        if ($origen === null || (int) $origen->estado !== 0) {
            throw new PedidosWebBusinessException(2000, 'business.pedidoOrigenInvalido', 422);
        }

        $presupuesto = $this->upsertComprobante(
            null,
            $cabeceraPayload,
            $renglones,
            $total,
            $totalIva,
            99,
            'presupuesto',
            $user,
            [
                'origen_comprobante' => 'conversion_pedido_a_presupuesto',
                'cod_pedido_origen' => $codPedidoOrigen,
            ]
        );

        $this->pedidoDetalleRepository->deleteByCodPedido($codPedidoOrigen);
        $this->pedidoRepository->deleteFisicoCabecera($codPedidoOrigen);

        return [
            'cod_pedido' => (string) $presupuesto->cod_pedido,
            'estado' => (int) $presupuesto->estado,
            'nro_visible' => (int) ($presupuesto->nro_visible ?? 0),
            'total' => (float) $presupuesto->total,
            'total_iva' => (float) $presupuesto->total_iva,
            'guidSufijo' => strtoupper(substr((string) $presupuesto->cod_pedido, -6)),
            'cod_pedido_origen' => $codPedidoOrigen,
        ];
    }

    /**
     * @param  array<string, mixed>  $cabeceraPayload
     * @param  list<array<string, mixed>>  $renglones
     * @param  array<string, mixed>  $extraAttributes
     */
    private function upsertComprobante(
        ?string $codComprobante,
        array $cabeceraPayload,
        array $renglones,
        float $total,
        float $totalIva,
        int $estado,
        string $tipoComprobante,
        User $user,
        array $extraAttributes = []
    ): PqPedidoswebPedidoCabecera {
        if ($this->parameterService->getNoModificaPedido() && $codComprobante !== null) {
            throw new PedidosWebBusinessException(2000, 'business.modificacionDeshabilitada', 422);
        }

        $timestamp = now()->format('Ymd H:i:s');
        $codPedido = $codComprobante ?? (string) Str::uuid();
        $nroVisible = $codComprobante === null ? $this->nextNumeroVisible() : null;
        $isInsert = $codComprobante === null;

        if (! $isInsert) {
            $actual = $this->pedidoRepository->findByCodPedido($codPedido, true);

            if ($actual === null) {
                throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
            }

            if (
                ($tipoComprobante === 'pedido' && ! in_array((int) $actual->estado, [0, -1], true))
                || ($tipoComprobante === 'presupuesto' && (int) $actual->estado !== 99)
            ) {
                throw new PedidosWebBusinessException(2000, 'business.estadoNoEditable', 422);
            }
        }

        $attributes = [
            'cod_pedido' => $codPedido,
            'cod_cliente' => (string) $cabeceraPayload['cod_cliente'],
            'fecha' => $timestamp,
            'nivel' => (int) ($cabeceraPayload['nivel'] ?? 0),
            'observaciones' => $cabeceraPayload['observaciones'] ?? null,
            'incluye_iva' => (bool) ($cabeceraPayload['incluye_iva'] ?? false),
            'moneda' => (int) ($cabeceraPayload['moneda'] ?? 1),
            'estado' => $estado,
            'cod_usuario_web' => $user->codigo,
            'fecha_modif' => $timestamp,
            'total' => $total,
            'total_iva' => $totalIva,
            'descuento' => (float) ($cabeceraPayload['descuento'] ?? 0),
            'bonif_1' => (float) ($cabeceraPayload['bonif_1'] ?? 0),
            'bonif_2' => (float) ($cabeceraPayload['bonif_2'] ?? 0),
            'bonif_3' => (float) ($cabeceraPayload['bonif_3'] ?? 0),
            'cod_vended' => $cabeceraPayload['cod_vended'] ?? null,
            'cod_condvta' => $cabeceraPayload['cod_condvta'] ?? null,
            'id_de' => $cabeceraPayload['id_de'] ?? null,
            'cod_transpor' => $cabeceraPayload['cod_transpor'] ?? null,
            'lista_precios' => $cabeceraPayload['lista_precios'] ?? null,
            'usuario_modificacion' => $user->codigo,
            ...$extraAttributes,
        ];

        if ($isInsert) {
            $attributes['fecha_creacion'] = $timestamp;
            $attributes['usuario_creacion'] = $user->codigo;
            $attributes['nro_visible'] = $nroVisible;
            $cabecera = $this->pedidoRepository->insertCabecera($attributes);
        } else {
            $this->pedidoRepository->updateCabecera($codPedido, $attributes);
            $cabecera = $this->pedidoRepository->findByCodPedido($codPedido, true);
        }

        if ($cabecera === null) {
            throw new PedidosWebBusinessException(4000, 'business.notFound', 404);
        }

        $this->pedidoDetalleRepository->syncDetalle($codPedido, $renglones);

        return $cabecera->fresh(['cliente', 'cliente.vendedor', 'vendedor', 'transporte', 'listaPrecios', 'condicionVenta']);
    }

    private function nextNumeroVisible(): int
    {
        $max = (int) PqPedidoswebPedidoCabecera::query()->max('nro_visible');

        return $max + 1;
    }

    private function hasEdicionActiva(PqPedidoswebPedidoCabecera $pedido, User $user): bool
    {
        $ultimaActividad = $pedido->fechahora_ultima_actividad;

        if ($ultimaActividad === null) {
            return false;
        }

        $minutosWeb = $this->parameterService->getMinutosWeb();
        $isWithinWindow = $ultimaActividad->copy()->addMinutes($minutosWeb)->isFuture();
        $isOtherUser = $pedido->cod_usuario_web !== null
            && (string) $pedido->cod_usuario_web !== (string) $user->codigo;

        return $isWithinWindow && $isOtherUser;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function sendMailForResult(array $result, string $tipoComprobante, string $accionComprobante, User $user): bool
    {
        $codPedido = (string) ($result['cod_pedido'] ?? '');

        if ($codPedido === '') {
            return false;
        }

        $comprobante = $this->pedidoRepository->findWithDetalle($codPedido);

        if ($comprobante === null) {
            return false;
        }

        $detalle = $comprobante->detalles
            ->map(static fn ($row): array => [
                'cod_articulo' => (string) $row->cod_articulo,
                'descripcion_articulo' => (string) ($row->descripcion_articulo ?? ''),
                'cantidad' => (float) $row->cantidad,
                'porc_bonif' => (float) $row->porc_bonif,
                'precio' => (float) $row->precio,
                'precio_neto' => (float) $row->precio_neto,
                'importe_total' => (float) $row->importe_total,
            ])
            ->values()
            ->all();

        return $this->comprobanteMailService->enviarComprobante(
            $comprobante,
            $detalle,
            $tipoComprobante,
            $accionComprobante,
            $user
        );
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
