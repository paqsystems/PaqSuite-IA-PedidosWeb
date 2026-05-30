<?php

$pdo = new PDO(
    'sqlsrv:Server=192.168.41.2,1433;Database=Diccionario_000205_012;TrustServerCertificate=1',
    'Axoft',
    'Axoft',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$tables = ['users', 'pq_menus', 'Pq_Rol', 'Pq_Permiso', 'PQ_RolAtributo'];

foreach ($tables as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;
    $rows = $pdo->query(
        "SELECT c.name AS column_name, c.is_identity, t.name AS type_name, c.max_length, c.is_nullable
         FROM sys.columns c
         JOIN sys.types t ON c.user_type_id = t.user_type_id
         WHERE c.object_id = OBJECT_ID('{$table}')
         ORDER BY c.column_id"
    )->fetchAll(PDO::FETCH_OBJ);

    foreach ($rows as $row) {
        echo $row->column_name
            . ' | ' . $row->type_name
            . ' | identity=' . ($row->is_identity ? 'YES' : 'NO')
            . ' | nullable=' . ($row->is_nullable ? 'YES' : 'NO')
            . PHP_EOL;
    }
}

$pks = $pdo->query(
    "SELECT tc.TABLE_NAME, kcu.COLUMN_NAME
     FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
     JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
       ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
     WHERE tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
       AND tc.TABLE_NAME IN ('users','pq_menus','Pq_Rol','Pq_Permiso','PQ_RolAtributo','pq_pedidosweb_clientes','pq_pedidosweb_vendedores')
     ORDER BY tc.TABLE_NAME"
)->fetchAll(PDO::FETCH_OBJ);

echo PHP_EOL . '=== PRIMARY KEYS ===' . PHP_EOL;
foreach ($pks as $pk) {
    echo $pk->TABLE_NAME . ' -> ' . $pk->COLUMN_NAME . PHP_EOL;
}
