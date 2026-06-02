<?php

return [
    'passwordReset' => [
        'subject' => 'Reinitialiser le mot de passe',
        'greeting' => 'Nous avons recu une demande de reinitialisation de votre mot de passe.',
        'instructions' => 'Utilisez le lien suivant pour definir un nouveau mot de passe dans Pedidos Web.',
        'cta' => 'Reinitialiser le mot de passe',
        'expiration' => 'Ce lien expire dans :minutes minutes.',
        'ignore' => 'Si vous n avez pas demande ce changement, vous pouvez ignorer ce message.',
    ],
    'comprobanteNotification' => [
        'subject' => ':nombreEmpresa - :tipoComprobante :accionComprobante',
        'empresaFallback' => 'Entreprise',
        'intro' => [
            'ingresado' => 'Le :tipoComprobante avec le code :guidSufijo a ete enregistre sur le site web de :nombreEmpresa.',
            'modificado' => 'Le :tipoComprobante avec le code :guidSufijo a ete modifie sur le site web de :nombreEmpresa.',
        ],
        'footerConsulta' => 'Consultez notre site pour connaitre l etat du document.',
        'cabecera' => [
            'fecha' => 'Date',
            'cliente' => 'Client',
            'razonSocial' => 'Raison sociale',
            'vendedor' => 'Vendeur',
            'transporte' => 'Transport',
            'listaPrecios' => 'Liste de prix',
            'condicionVenta' => 'Condition de vente',
            'nivel' => 'Niveau',
            'cantidades' => 'Quantites',
            'importeBruto' => 'Montant brut',
            'importeNeto' => 'Montant net',
            'descuento' => 'Remise',
            'observaciones' => 'Observations',
        ],
        'detalle' => [
            'codigo' => 'Code',
            'descripcion' => 'Description',
            'cantidad' => 'Qte.',
            'precio' => 'Prix',
            'porcBonif' => '% Rem.',
            'precioNeto' => 'Prix net',
            'importe' => 'Montant',
        ],
        'tipoComprobante' => [
            'pedido' => 'Commande',
            'presupuesto' => 'Devis',
        ],
        'tipoComprobanteIntro' => [
            'pedido' => 'commande',
            'presupuesto' => 'devis',
        ],
        'accionComprobante' => [
            'ingresado' => 'enregistre',
            'modificado' => 'modifie',
        ],
    ],
];
