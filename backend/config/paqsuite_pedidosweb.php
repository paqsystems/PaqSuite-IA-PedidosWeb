<?php

return [
    'programa' => env('PAQSWEB_ERP_PROGRAMA', 'PedidosWeb'),
    'readFromErp' => filter_var(env('PAQSWEB_READ_PARAMS_FROM_ERP', true), FILTER_VALIDATE_BOOL),

    'defaults' => [
        'MinutosWeb' => (int) env('PAQSWEB_MINUTOS_WEB', 30),
        'CodMotivoCierreExitoso' => (int) env('PAQSWEB_COD_MOTIVO_CIERRE_EXITOSO', 1),
        'NOeliminaPedido' => (int) env('PAQSWEB_NO_ELIMINA_PEDIDO', 0),
        'NOmodificaPedido' => (int) env('PAQSWEB_NO_MODIFICA_PEDIDO', 0),
        'DetallePorMail' => (int) env('PAQSWEB_DETALLE_POR_MAIL', 1),
        'MailDestinatariosAdicionales' => (string) env('PAQSWEB_MAIL_DESTINATARIOS_ADICIONALES', ''),
        'mailCCO' => (string) env('PAQSWEB_MAIL_CCO', ''),
        'Mail_DireccionRemitente' => (string) env('PAQSWEB_MAIL_DIRECCION_REMITENTE', ''),
        'DiasVentasDetalladas' => (int) env('PAQSWEB_DIAS_VENTAS_DETALLADAS', 90),
        'MonedaSimbolo' => (string) env('PAQSWEB_MONEDA_SIMBOLO', '$'),
        'MonedaCodigo' => (string) env('PAQSWEB_MONEDA_CODIGO', 'ARS'),
    ],
];
