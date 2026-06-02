<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('mail.comprobanteNotification.subject', [
        'nombreEmpresa' => $nombreEmpresa,
        'tipoComprobante' => $tipoComprobanteLabel,
        'accionComprobante' => $accionComprobanteLabel,
    ]) }}</title>
</head>
<body>
    <p>{{ __('mail.comprobanteNotification.intro.' . $accionComprobante, [
        'tipoComprobante' => $tipoComprobanteIntroLabel,
        'guidSufijo' => $guidSufijo,
        'nombreEmpresa' => $nombreEmpresa,
    ]) }}</p>

    {{-- Cabecera (13 campos) y detalle (7 columnas) — ver TR-SPEC-101-13 §3.2 --}}

    <p>{{ __('mail.comprobanteNotification.footerConsulta') }}</p>
</body>
</html>
