<?php

$host = '192.168.41.2';
$port = '1433';
$database = 'Diccionario_000205_012';
$username = 'Axoft';
$password = 'Axoft';

try {
    $pdo = new PDO(
        "sqlsrv:Server={$host},{$port};Database={$database};TrustServerCertificate=1",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Throwable $exception) {
    echo 'Connection failed: ' . $exception->getMessage() . PHP_EOL;
    exit(1);
}

echo 'DB: ' . $database . PHP_EOL;

$tables = ['users', 'pq_menus', 'Pq_Rol', 'Pq_Permiso', 'PQ_RolAtributo', 'PQ_Empresa'];

foreach ($tables as $table) {
    echo PHP_EOL . '=== ' . $table . ' ===' . PHP_EOL;

    $statement = $pdo->prepare(
        'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_NAME = ?
         ORDER BY ORDINAL_POSITION'
    );
    $statement->execute([$table]);
    $cols = $statement->fetchAll(PDO::FETCH_OBJ);

    if ($cols === []) {
        echo '  (table missing)' . PHP_EOL;
        continue;
    }

    foreach ($cols as $col) {
        $len = $col->CHARACTER_MAXIMUM_LENGTH ?? '';
        echo '  ' . $col->COLUMN_NAME . ' (' . $col->DATA_TYPE . ($len !== '' ? ':' . $len : '') . ') nullable=' . $col->IS_NULLABLE . PHP_EOL;
    }
}
