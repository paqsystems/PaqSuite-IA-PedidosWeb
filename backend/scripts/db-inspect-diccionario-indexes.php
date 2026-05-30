<?php

$pdo = new PDO(
    'sqlsrv:Server=192.168.41.2,1433;Database=Diccionario_000205_012;TrustServerCertificate=1',
    'Axoft',
    'Axoft',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$indexes = $pdo->query(
    "SELECT t.name AS table_name, i.name AS index_name, i.is_unique, c.name AS column_name
     FROM sys.indexes i
     JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
     JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
     JOIN sys.tables t ON i.object_id = t.object_id
     WHERE t.name IN ('Pq_Permiso','Pq_Rol','users','pq_menus')
       AND i.is_primary_key = 0
     ORDER BY t.name, i.name, ic.key_ordinal"
)->fetchAll(PDO::FETCH_OBJ);

foreach ($indexes as $index) {
    echo $index->table_name . ' | ' . $index->index_name . ' | unique=' . ($index->is_unique ? 'Y' : 'N') . ' | ' . $index->column_name . PHP_EOL;
}

$empresa = $pdo->query(
    "SELECT c.name, c.is_identity FROM sys.columns c
     JOIN sys.tables t ON c.object_id = t.object_id
     WHERE t.name = 'PQ_Empresa' ORDER BY c.column_id"
)->fetchAll(PDO::FETCH_OBJ);

echo PHP_EOL . 'PQ_Empresa columns:' . PHP_EOL;
foreach ($empresa as $col) {
    echo $col->name . ' identity=' . ($col->is_identity ? 'Y' : 'N') . PHP_EOL;
}
