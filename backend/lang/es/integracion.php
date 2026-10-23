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
        'log' => [
            'procesado' => 'Lote de maestros procesado.',
        ],
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
