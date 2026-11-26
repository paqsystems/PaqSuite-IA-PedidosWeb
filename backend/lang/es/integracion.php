<?php

return [
    'maestro' => [
        'forbidden' => 'Sin permiso para importar maestros ERP.',
        'jsonInvalido' => 'Cuerpo JSON del lote inválido.',
        'modoInvalido' => 'Modo de lote inválido.',
        'modoNoSoportado' => 'El recurso no admite modo snapshot en esta versión.',
        'recursoDesconocido' => 'Recurso de maestros desconocido.',
        'loteDemasiadoGrande' => 'El lote supera el máximo de ítems permitidos.',
        'snapshotVacio' => 'Snapshot con lote vacío no permitido.',
        'payloadProhibido' => 'El lote contiene contenido no permitido.',
        'erroresTruncados' => 'Errores parciales: lista truncada.',
        'errorFatal' => 'Error al procesar el lote de maestros.',
        'itemInvalido' => 'Ítem del lote inválido.',
        'direccionHabitualDuplicada' => 'Solo puede haber una dirección de entrega habitual por cliente.',
        'log' => [
            'procesado' => 'Lote de maestros procesado.',
        ],
    ],
    'export' => [
        'ok' => 'Exportación de comprobantes realizada.',
        'forbidden' => 'Sin permiso para exportar comprobantes ERP.',
        'estadoRangosRequerido' => 'El parámetro estadoRangos es obligatorio.',
        'rangoInvalido' => 'Rango de estados inválido.',
        'rangoFechasInvalido' => 'Rango de fechas inválido.',
        'paginacionInvalida' => 'Paginación inválida.',
        'errorFatal' => 'Error al exportar comprobantes.',
    ],
    'transaccion' => [
        'forbidden' => 'Sin permiso para importar transacciones ERP.',
        'jsonInvalido' => 'Cuerpo JSON del lote inválido.',
        'modoInvalido' => 'Modo de lote inválido.',
        'modoNoSoportado' => 'El recurso no admite modo snapshot en esta versión.',
        'recursoDesconocido' => 'Recurso de transacciones desconocido.',
        'loteDemasiadoGrande' => 'El lote supera el máximo de ítems permitidos.',
        'snapshotVacio' => 'Snapshot con lote vacío no permitido.',
        'snapshotAbortado' => 'El snapshot no fue aplicado porque el lote es inválido.',
        'itemInvalido' => 'Ítem de transacción inválido.',
        'campoNoPermitido' => 'El ítem contiene un campo no permitido.',
        'duplicado' => 'La clave de transacción está duplicada en el lote.',
        'erroresTruncados' => 'Errores parciales: lista truncada.',
        'errorFatal' => 'Error al procesar el lote de transacciones.',
        'log' => [
            'procesado' => 'Lote de transacciones procesado.',
        ],
    ],
];
