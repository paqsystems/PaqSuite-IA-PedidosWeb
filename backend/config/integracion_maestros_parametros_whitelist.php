<?php

/*
 * TR-22c / SPEC-101-22.
 * Fuente: docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json
 * Contrato de tipos: docs/_base/pq-parametros-gral-tipo-valor.md
 */

$booleanas = [
    'ArticulosPrecioCero', 'ArticulosSinPrecio', 'ActualizarPrecioCopia',
    'CargaUnidadesVenta', 'IncluyeArticulosNoStockeables', 'CargaRecurrente',
    'ClienteLeyenda1', 'ClienteLeyenda2', 'ClienteLeyenda3', 'ClienteLeyenda4',
    'ClienteLeyenda5', 'ClientesInhabilitados', 'DetallePorMail',
    'ModificaBonArtS', 'ModificaBonArtV', 'ModificaBonCliS', 'ModificaBonCliV',
    'ModificaCondVtaC', 'ModificaCondVtaS', 'ModificaCondVtaV',
    'ModificaDirEntrC', 'ModificaDirEntrS', 'ModificaDirEntrV',
    'ModificaExpresoC', 'ModificaExpresoS', 'ModificaExpresoV',
    'ModificaListaPrecS', 'ModificaListaPrecV', 'ModificaNivelC',
    'ModificaNivelDesktop', 'ModificaNivelS', 'ModificaNivelV',
    'ModificaPrecioS', 'ModificaPrecioV', 'ModificaTranspC',
    'ModificaTranspS', 'ModificaTranspV', 'NivelExtremo', 'NOeliminaPedido',
    'NOmodificaPedido', 'RedistribucionManual',
];

$enteros = [
    'CodClasifArticulos', 'CodMotivoCierreExitoso', 'CodPerfilPedidos',
    'CodTransporte', 'DiasResumenCuenta', 'DiasVentasDetalladas',
    'ListaPrecios', 'MinutosAviso', 'MinutosBloqueo', 'MinutosWeb',
];

$strings = [
    'Mail_DireccionRemitente', 'MailDestinatariosAdicionales', 'mailCCO',
    'MonedaCodigo', 'MonedaSimbolo', 'TalonarioFacturaA', 'TalonarioFacturaB',
    'TalonarioFacturaE',
];

return array_merge(
    array_fill_keys($booleanas, 'B'),
    array_fill_keys($enteros, 'I'),
    array_fill_keys($strings, 'S'),
    ['FechaControl' => 'D'],
);
