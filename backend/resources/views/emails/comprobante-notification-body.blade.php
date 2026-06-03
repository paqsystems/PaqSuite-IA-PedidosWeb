<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('mail.comprobanteNotification.subject', [
        'nombreEmpresa' => $nombreEmpresa,
        'tipoComprobante' => __('mail.comprobanteNotification.tipoComprobante.' . $tipoComprobante),
        'accionComprobante' => __('mail.comprobanteNotification.accionComprobante.' . $accionComprobante),
    ]) }}</title>
</head>
<body>
    <p>{{ __('mail.comprobanteNotification.intro.' . $accionComprobante, [
        'tipoComprobante' => __('mail.comprobanteNotification.tipoComprobanteIntro.' . $tipoComprobante),
        'guidSufijo' => $guidSufijo,
        'nombreEmpresa' => $nombreEmpresa,
    ]) }}</p>

    <table cellpadding="4" cellspacing="0" border="0">
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.fecha') }}</strong></td><td>{{ $cabeceraMail['fecha'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.cliente') }}</strong></td><td>{{ $cabeceraMail['cliente'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.razonSocial') }}</strong></td><td>{{ $cabeceraMail['razonSocial'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.vendedor') }}</strong></td><td>{{ $cabeceraMail['vendedor'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.transporte') }}</strong></td><td>{{ $cabeceraMail['transporte'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.listaPrecios') }}</strong></td><td>{{ $cabeceraMail['listaPrecios'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.condicionVenta') }}</strong></td><td>{{ $cabeceraMail['condicionVenta'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.nivel') }}</strong></td><td>{{ $cabeceraMail['nivel'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.cantidades') }}</strong></td><td>{{ $cabeceraMail['cantidades'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.importeBruto') }}</strong></td><td>{{ $cabeceraMail['importeBruto'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.importeNeto') }}</strong></td><td>{{ $cabeceraMail['importeNeto'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.descuento') }}</strong></td><td>{{ $cabeceraMail['descuento'] ?? '' }}</td></tr>
        <tr><td><strong>{{ __('mail.comprobanteNotification.cabecera.observaciones') }}</strong></td><td>{{ $cabeceraMail['observaciones'] ?? '' }}</td></tr>
    </table>

    @if (($mostrarDetalle ?? false) === true)
        <table cellpadding="4" cellspacing="0" border="1">
            <thead>
                <tr>
                    <th>{{ __('mail.comprobanteNotification.detalle.codigo') }}</th>
                    <th>{{ __('mail.comprobanteNotification.detalle.descripcion') }}</th>
                    <th>{{ __('mail.comprobanteNotification.detalle.cantidad') }}</th>
                    <th>{{ __('mail.comprobanteNotification.detalle.precio') }}</th>
                    <th>{{ __('mail.comprobanteNotification.detalle.porcBonif') }}</th>
                    <th>{{ __('mail.comprobanteNotification.detalle.precioNeto') }}</th>
                    <th>{{ __('mail.comprobanteNotification.detalle.importe') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach (($detalle ?? []) as $renglon)
                    <tr>
                        <td>{{ $renglon['cod_articulo'] ?? '' }}</td>
                        <td>{{ $renglon['descripcion_articulo'] ?? '' }}</td>
                        <td>{{ number_format((float) ($renglon['cantidad'] ?? 0), 2, ',', '.') }}</td>
                        <td>{{ number_format((float) ($renglon['precio'] ?? 0), 2, ',', '.') }}</td>
                        <td>{{ number_format((float) ($renglon['porc_bonif'] ?? 0), 2, ',', '.') }} %</td>
                        <td>{{ number_format((float) ($renglon['precio_neto'] ?? 0), 2, ',', '.') }}</td>
                        <td>{{ number_format((float) ($renglon['importe_total'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p>{{ __('mail.comprobanteNotification.footerConsulta') }}</p>
</body>
</html>
