<?php

$pdo = new PDO(
    'sqlsrv:Server=192.168.41.2,1433;Database=Diccionario_000205_012;TrustServerCertificate=1',
    'Axoft',
    'Axoft',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$fks = $pdo->query(
    "SELECT
        fk.name AS fk_name,
        tp.name AS parent_table,
        cp.name AS parent_column,
        tr.name AS ref_table,
        cr.name AS ref_column
     FROM sys.foreign_keys fk
     INNER JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
     INNER JOIN sys.tables tp ON fkc.parent_object_id = tp.object_id
     INNER JOIN sys.columns cp ON fkc.parent_object_id = cp.object_id AND fkc.parent_column_id = cp.column_id
     INNER JOIN sys.tables tr ON fkc.referenced_object_id = tr.object_id
     INNER JOIN sys.columns cr ON fkc.referenced_object_id = cr.object_id AND fkc.referenced_column_id = cr.column_id
     WHERE tp.name IN ('users','pq_menus','Pq_Rol','Pq_Permiso','PQ_RolAtributo')
     ORDER BY tp.name"
)->fetchAll(PDO::FETCH_OBJ);

foreach ($fks as $fk) {
    echo $fk->parent_table . '.' . $fk->parent_column . ' -> ' . $fk->ref_table . '.' . $fk->ref_column . PHP_EOL;
}

if ($fks === []) {
    echo 'No FKs found on security tables.' . PHP_EOL;
}
