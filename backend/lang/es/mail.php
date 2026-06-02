<?php

return [
    'passwordReset' => [
        'subject' => 'Restablecer contrasena',
        'greeting' => 'Recibimos una solicitud para restablecer tu contrasena.',
        'instructions' => 'Hace clic en el siguiente enlace para definir una nueva clave en Pedidos Web.',
        'cta' => 'Restablecer contrasena',
        'expiration' => 'Este enlace vence en :minutes minutos.',
        'ignore' => 'Si no solicitaste este cambio, podes ignorar este mensaje.',
    ],
    'comprobanteNotification' => [
        'subject' => ':nombreEmpresa - :tipoComprobante :accionComprobante',
        'empresaFallback' => 'Empresa',
        'intro' => [
            'ingresado' => 'Se ha ingresado el :tipoComprobante con codigo :guidSufijo a la web de :nombreEmpresa.',
            'modificado' => 'Se ha modificado el :tipoComprobante con codigo :guidSufijo a la web de :nombreEmpresa.',
        ],
        'footerConsulta' => 'Consulte en nuestro sitio por el estado del mismo.',
        'cabecera' => [
            'fecha' => 'Fecha',
            'cliente' => 'Cliente',
            'razonSocial' => 'Razon Social',
            'vendedor' => 'Vendedor',
            'transporte' => 'Transporte',
            'listaPrecios' => 'Lista de Precios',
            'condicionVenta' => 'Condicion de Venta',
            'nivel' => 'Nivel',
            'cantidades' => 'Cantidades',
            'importeBruto' => 'Importe Bruto',
            'importeNeto' => 'Importe Neto',
            'descuento' => 'Bonificacion',
            'observaciones' => 'Observaciones',
        ],
        'detalle' => [
            'codigo' => 'Codigo',
            'descripcion' => 'Descripcion',
            'cantidad' => 'Cant.',
            'precio' => 'Precio',
            'porcBonif' => '% Bonif.',
            'precioNeto' => 'Precio neto',
            'importe' => 'Importe',
        ],
        'tipoComprobante' => [
            'pedido' => 'Pedido',
            'presupuesto' => 'Presupuesto',
        ],
        'tipoComprobanteIntro' => [
            'pedido' => 'pedido',
            'presupuesto' => 'presupuesto',
        ],
        'accionComprobante' => [
            'ingresado' => 'ingresado',
            'modificado' => 'modificado',
        ],
    ],
];
