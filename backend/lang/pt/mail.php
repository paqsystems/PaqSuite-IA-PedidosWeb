<?php

return [
    'passwordReset' => [
        'subject' => 'Redefinir senha',
        'greeting' => 'Recebemos uma solicitacao para redefinir sua senha.',
        'instructions' => 'Use o link a seguir para definir uma nova senha no Pedidos Web.',
        'cta' => 'Redefinir senha',
        'expiration' => 'Este link expira em :minutes minutos.',
        'ignore' => 'Se voce nao solicitou esta alteracao, pode ignorar esta mensagem.',
    ],
    'comprobanteNotification' => [
        'subject' => ':nombreEmpresa - :tipoComprobante :accionComprobante',
        'empresaFallback' => 'Empresa',
        'intro' => [
            'ingresado' => 'O :tipoComprobante com codigo :guidSufijo foi registrado no site de :nombreEmpresa.',
            'modificado' => 'O :tipoComprobante com codigo :guidSufijo foi modificado no site de :nombreEmpresa.',
        ],
        'footerConsulta' => 'Consulte em nosso site o estado do mesmo.',
        'cabecera' => [
            'fecha' => 'Data',
            'cliente' => 'Cliente',
            'razonSocial' => 'Razao social',
            'vendedor' => 'Vendedor',
            'transporte' => 'Transporte',
            'listaPrecios' => 'Lista de precos',
            'condicionVenta' => 'Condicao de venda',
            'nivel' => 'Nivel',
            'cantidades' => 'Quantidades',
            'importeBruto' => 'Valor bruto',
            'importeNeto' => 'Valor liquido',
            'descuento' => 'Bonificacao',
            'observaciones' => 'Observacoes',
        ],
        'detalle' => [
            'codigo' => 'Codigo',
            'descripcion' => 'Descricao',
            'cantidad' => 'Qtd.',
            'precio' => 'Preco',
            'porcBonif' => '% Bonif.',
            'precioNeto' => 'Preco liquido',
            'importe' => 'Valor',
        ],
        'tipoComprobante' => [
            'pedido' => 'Pedido',
            'presupuesto' => 'Orcamento',
        ],
        'tipoComprobanteIntro' => [
            'pedido' => 'pedido',
            'presupuesto' => 'orcamento',
        ],
        'accionComprobante' => [
            'ingresado' => 'registrado',
            'modificado' => 'modificado',
        ],
    ],
];
