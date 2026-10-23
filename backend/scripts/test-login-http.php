<?php

require __DIR__.'/../vendor/autoload.php';

$start = microtime(true);

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
    CURLOPT_TIMEOUT => 60,
]);

$body = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

$elapsed = round(microtime(true) - $start, 2);

echo "seconds={$elapsed}\n";
echo "http_status={$status}\n";
if ($error !== '') {
    echo "curl_error={$error}\n";
}
echo "body={$body}\n";
