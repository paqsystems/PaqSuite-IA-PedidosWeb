<?php

return [
    'passwordReset' => [
        'subject' => 'Reimposta password',
        'greeting' => 'Abbiamo ricevuto una richiesta per reimpostare la tua password.',
        'instructions' => 'Usa il seguente link per definire una nuova password in Pedidos Web.',
        'cta' => 'Reimposta password',
        'expiration' => 'Questo link scade tra :minutes minuti.',
        'ignore' => 'Se non hai richiesto questa modifica, puoi ignorare questo messaggio.',
    ],
    'comprobanteNotification' => [
        'subject' => ':nombreEmpresa - :tipoComprobante :accionComprobante',
        'empresaFallback' => 'Azienda',
        'intro' => [
            'ingresado' => 'E stato inserito il :tipoComprobante con codice :guidSufijo sul sito web di :nombreEmpresa.',
            'modificado' => 'E stato modificato il :tipoComprobante con codice :guidSufijo sul sito web di :nombreEmpresa.',
        ],
        'footerConsulta' => 'Consulta il nostro sito per lo stato del documento.',
        'cabecera' => [
            'fecha' => 'Data',
            'cliente' => 'Cliente',
            'razonSocial' => 'Ragione sociale',
            'vendedor' => 'Venditore',
            'transporte' => 'Trasporto',
            'listaPrecios' => 'Listino prezzi',
            'condicionVenta' => 'Condizione di vendita',
            'nivel' => 'Livello',
            'cantidades' => 'Quantita',
            'importeBruto' => 'Importo lordo',
            'importeNeto' => 'Importo netto',
            'descuento' => 'Sconto',
            'observaciones' => 'Osservazioni',
        ],
        'detalle' => [
            'codigo' => 'Codice',
            'descripcion' => 'Descrizione',
            'cantidad' => 'Qta.',
            'precio' => 'Prezzo',
            'porcBonif' => '% Sconto',
            'precioNeto' => 'Prezzo netto',
            'importe' => 'Importo',
        ],
        'tipoComprobante' => [
            'pedido' => 'Ordine',
            'presupuesto' => 'Preventivo',
        ],
        'tipoComprobanteIntro' => [
            'pedido' => 'ordine',
            'presupuesto' => 'preventivo',
        ],
        'accionComprobante' => [
            'ingresado' => 'inserito',
            'modificado' => 'modificato',
        ],
    ],
];
