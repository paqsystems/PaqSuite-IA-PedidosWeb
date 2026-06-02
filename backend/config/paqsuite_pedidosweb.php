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
        'ModificaPrecioV' => (int) env('PAQSWEB_MODIFICA_PRECIO_V', 1),
        'ModificaPrecioS' => (int) env('PAQSWEB_MODIFICA_PRECIO_S', 1),
        'ModificaBonArtV' => (int) env('PAQSWEB_MODIFICA_BON_ART_V', 1),
        'ModificaBonArtS' => (int) env('PAQSWEB_MODIFICA_BON_ART_S', 1),
        'ModificaBonCliV' => (int) env('PAQSWEB_MODIFICA_BON_CLI_V', 1),
        'ModificaBonCliS' => (int) env('PAQSWEB_MODIFICA_BON_CLI_S', 1),
        'ModificaListaPrecV' => (int) env('PAQSWEB_MODIFICA_LISTA_PREC_V', 1),
        'ModificaListaPrecS' => (int) env('PAQSWEB_MODIFICA_LISTA_PREC_S', 1),
    ],
];
