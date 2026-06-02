<?php

return [
    'passwordReset' => [
        'subject' => 'Reset password',
        'greeting' => 'We received a request to reset your password.',
        'instructions' => 'Use the following link to choose a new password in Pedidos Web.',
        'cta' => 'Reset password',
        'expiration' => 'This link expires in :minutes minutes.',
        'ignore' => 'If you did not request this change, you can ignore this message.',
    ],
    'comprobanteNotification' => [
        'subject' => ':nombreEmpresa - :tipoComprobante :accionComprobante',
        'empresaFallback' => 'Company',
        'intro' => [
            'ingresado' => 'The :tipoComprobante with code :guidSufijo has been entered on the :nombreEmpresa web.',
            'modificado' => 'The :tipoComprobante with code :guidSufijo has been modified on the :nombreEmpresa web.',
        ],
        'footerConsulta' => 'Check our website for its current status.',
        'cabecera' => [
            'fecha' => 'Date',
            'cliente' => 'Client',
            'razonSocial' => 'Business name',
            'vendedor' => 'Sales rep',
            'transporte' => 'Carrier',
            'listaPrecios' => 'Price list',
            'condicionVenta' => 'Payment terms',
            'nivel' => 'Level',
            'cantidades' => 'Quantities',
            'importeBruto' => 'Gross amount',
            'importeNeto' => 'Net amount',
            'descuento' => 'Discount',
            'observaciones' => 'Remarks',
        ],
        'detalle' => [
            'codigo' => 'Code',
            'descripcion' => 'Description',
            'cantidad' => 'Qty.',
            'precio' => 'Price',
            'porcBonif' => '% Disc.',
            'precioNeto' => 'Net price',
            'importe' => 'Amount',
        ],
        'tipoComprobante' => [
            'pedido' => 'Order',
            'presupuesto' => 'Quote',
        ],
        'tipoComprobanteIntro' => [
            'pedido' => 'order',
            'presupuesto' => 'quote',
        ],
        'accionComprobante' => [
            'ingresado' => 'entered',
            'modificado' => 'modified',
        ],
    ],
];
