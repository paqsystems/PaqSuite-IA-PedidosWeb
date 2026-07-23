<?php

return [

    'procedimientos' => [
        'clientes' => 'pw_clientes_visibles',
        'comprobantes' => 'pw_comprobantes_visibles',
        'dashboard' => 'pw_dashboard',
        'cargaComprobantes' => 'pw_cargapedidos',
        'importacionMasiva' => 'pw_importacionmasiva',
        'consultasPedidosIngresados' => 'pw_pedidosingresados',
        'consultasPedidosPendientes' => 'pw_pedidospendientes',
        'consultasPresupuestos' => 'pw_presupuestosingresados',
        'consultasStock' => 'pw_consultastock',
        'consultasDeuda' => 'pw_deudaclientes',
        'consultasCheques' => 'pw_consultacheques',
        'consultasHistorialVentas' => 'pw_historialventas',
        'consultasDetallePedidos' => 'pw_detallepedidos',
        'consultaParametros' => 'pw_consultaparametros',
        'historialImportExcel' => 'pw_historialimportexcel',
        'tratativasPresupuestos' => 'pw_tratativaspresup',
        'logsIntegracion' => 'pw_logsintegracion',
    ],

    'dashboardStates' => [
        'activeQuotes' => [99],
        'enteredOrders' => [-1, 0],
        'pendingOrders' => [1],
    ],

];
