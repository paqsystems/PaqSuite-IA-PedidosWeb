<?php

$ch = curl_init('http://127.0.0.1:8088/api/v1/auth/login');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Paq-Cliente: desarrollo',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'codigo' => 'supervisor.mvp',
        'password' => 'ChangeMeInLocalEnv',
    ]),
]);
$login = json_decode((string) curl_exec($ch), true);
curl_close($ch);

$token = $login['resultado']['token'] ?? '';
if ($token === '') {
    echo "login_failed\n";
    exit(1);
}

$ch = curl_init('http://127.0.0.1:8088/api/v1/consultas/pedidos-ingresados?page_size=5');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer '.$token,
        'X-Paq-Cliente: desarrollo',
    ],
]);
$data = json_decode((string) curl_exec($ch), true);
curl_close($ch);

foreach (($data['resultado']['items'] ?? []) as $item) {
    echo json_encode([
        'nro' => $item['numeroVisible'] ?? $item['codPedido'] ?? null,
        'estado' => $item['estado'] ?? null,
        'puedeEditar' => $item['puedeEditar'] ?? null,
        'puedeCopiar' => $item['puedeCopiar'] ?? null,
        'puedeEliminar' => $item['puedeEliminar'] ?? null,
    ], JSON_UNESCAPED_UNICODE).PHP_EOL;
}
