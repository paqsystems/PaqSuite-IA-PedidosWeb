<?php

namespace App\Services\PedidosWeb;

use App\Mail\ComprobanteNotificationMail;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Support\LocaleNormalizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class ComprobanteMailService
{
    public function __construct(
        private readonly PedidosWebParameterService $parameterService,
        private readonly LogIntegracionService $logIntegracionService,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $detalle
     */
    public function enviarComprobante(
        PqPedidoswebPedidoCabecera $cabecera,
        array $detalle,
        string $tipoComprobante,
        string $accionComprobante,
        User $user
    ): bool {
        $destinatarios = $this->resolveDestinatarios($cabecera);

        if ($destinatarios === []) {
            $this->logIntegracionService->registrar(
                'mail_error',
                'warning',
                'pedidosweb',
                'No hay destinatarios validos para comprobante',
                [
                    'cod_pedido' => $cabecera->cod_pedido,
                ]
            );

            return false;
        }

        $locale = LocaleNormalizer::normalize($user->locale, 'es');
        $viewData = $this->buildViewData($cabecera, $detalle, $tipoComprobante, $accionComprobante);

        try {
            $mailer = Mail::to($destinatarios);
            $bcc = $this->parameterService->getMailCco();

            if ($bcc !== null) {
                $mailer->bcc($bcc);
            }

            $mailer
                ->locale($locale)
                ->send(new ComprobanteNotificationMail(
                    $viewData,
                    $this->parameterService->getMailDireccionRemitente(),
                    (string) config('mail.from.name', config('app.name')),
                    $locale,
                ));

            return true;
        } catch (\Throwable $throwable) {
            Log::warning('pedidosweb.mail.send_failed', [
                'cod_pedido' => $cabecera->cod_pedido,
                'message' => $throwable->getMessage(),
            ]);

            $this->logIntegracionService->registrar(
                'mail_error',
                'error',
                'pedidosweb',
                'Fallo de envio de mail de comprobante',
                [
                    'cod_pedido' => $cabecera->cod_pedido,
                    'error' => $throwable->getMessage(),
                ]
            );

            return false;
        }
    }

    /**
     * @return list<string>
     */
    private function resolveDestinatarios(PqPedidoswebPedidoCabecera $cabecera): array
    {
        $cliente = $cabecera->cliente;
        $vendedor = $cliente?->vendedor;
        $candidatos = [
            $cliente?->e_mail,
            $vendedor?->e_mail,
            $vendedor?->mail_supervisor,
            ...$this->parameterService->getMailDestinatariosAdicionales(),
        ];

        $unicos = [];

        foreach ($candidatos as $mail) {
            $normalized = strtolower(trim((string) $mail));

            if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $unicos[$normalized] = $normalized;
        }

        return array_values($unicos);
    }

    /**
     * @param  list<array<string, mixed>>  $detalle
     * @return array<string, mixed>
     */
    private function buildViewData(
        PqPedidoswebPedidoCabecera $cabecera,
        array $detalle,
        string $tipoComprobante,
        string $accionComprobante
    ): array {
        $nombreEmpresa = $this->resolveNombreEmpresa();
        $cantidadTotal = array_sum(array_map(static fn (array $row): float => (float) ($row['cantidad'] ?? 0), $detalle));
        $importeBruto = $this->resolveImporteBrutoCabecera($detalle);
        $importeNeto = round((float) $cabecera->total + (float) $cabecera->total_iva, 2);

        return [
            'nombreEmpresa' => $nombreEmpresa,
            'tipoComprobante' => $tipoComprobante,
            'accionComprobante' => $accionComprobante,
            'guidSufijo' => strtoupper(substr((string) $cabecera->cod_pedido, -6)),
            'mostrarDetalle' => $this->parameterService->getDetallePorMail(),
            'detalle' => $detalle,
            'cabeceraMail' => [
                'fecha' => optional($cabecera->fecha)?->format('m/d/Y'),
                'cliente' => (string) $cabecera->cod_cliente,
                'razonSocial' => (string) ($cabecera->cliente?->nombre ?? ''),
                'vendedor' => $this->formatCodigoDescripcion(
                    $cabecera->cod_vended,
                    $cabecera->vendedor?->nombre
                ),
                'transporte' => $this->formatCodigoDescripcion(
                    $cabecera->cod_transpor,
                    $cabecera->transporte?->descripcion
                ),
                'listaPrecios' => $this->formatCodigoDescripcion(
                    $cabecera->lista_precios,
                    $cabecera->listaPrecios?->descripcion
                ),
                'condicionVenta' => $this->formatCodigoDescripcion(
                    $cabecera->cod_condvta,
                    $cabecera->condicionVenta?->descripcion
                ),
                'nivel' => (int) $cabecera->nivel,
                'cantidades' => $cantidadTotal,
                'importeBruto' => $this->formatImporte($importeBruto),
                'importeNeto' => $this->formatImporte($importeNeto),
                'descuento' => number_format((float) $cabecera->descuento, 2, ',', '.').' %',
                'observaciones' => (string) ($cabecera->observaciones ?? ''),
            ],
        ];
    }

    private function resolveNombreEmpresa(): string
    {
        $tenant = request()->header('X-Paq-Cliente');

        if (is_string($tenant) && trim($tenant) !== '') {
            return ucfirst(trim($tenant));
        }

        return 'Empresa';
    }

    private function formatCodigoDescripcion(mixed $codigo, mixed $descripcion): string
    {
        $codigoText = $codigo !== null ? (string) $codigo : '';
        $descripcionText = $descripcion !== null ? (string) $descripcion : '';

        if ($codigoText === '' && $descripcionText === '') {
            return '';
        }

        if ($descripcionText === '') {
            return $codigoText;
        }

        return $codigoText.' ( '.$descripcionText.' )';
    }

    private function formatImporte(float $importe): string
    {
        return $this->parameterService->getMonedaSimbolo().' '.number_format($importe, 2, ',', '.');
    }

    /**
     * Importe bruto cabecera: Σ importe_neto de renglones (post descuentos renglón y cabecera).
     *
     * @param  list<array<string, mixed>>  $detalle
     */
    private function resolveImporteBrutoCabecera(array $detalle): float
    {
        $total = 0.0;

        foreach ($detalle as $renglon) {
            if (array_key_exists('importe_neto', $renglon) && $renglon['importe_neto'] !== null && $renglon['importe_neto'] !== '') {
                $total += (float) $renglon['importe_neto'];
                continue;
            }

            $cantidad = (float) ($renglon['cantidad'] ?? 0);
            $precioNeto = (float) ($renglon['precio_neto'] ?? 0);

            if ($precioNeto > 0) {
                $total += round($cantidad * $precioNeto, 2);
                continue;
            }

            $precio = (float) ($renglon['precio'] ?? 0);
            $porcBonif = (float) ($renglon['porc_bonif'] ?? 0);
            $total += round($cantidad * $precio * (1 - ($porcBonif / 100)), 2);
        }

        return round($total, 2);
    }
}
