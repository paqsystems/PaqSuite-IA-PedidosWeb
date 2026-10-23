<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasTable('pq_asistente_ia_credenciales')) {
    echo "Tabla pq_asistente_ia_credenciales no existe.\n";
    exit(1);
}

$rows = DB::table('pq_asistente_ia_credenciales as c')
    ->join('users as u', 'u.id', '=', 'c.user_id')
    ->select(
        'u.codigo',
        'c.provider_id',
        'c.model_id',
        'c.base_url',
        'c.supports_vision',
        'c.is_enabled',
        'c.api_key_encrypted',
        'c.updated_at',
    )
    ->get();

if ($rows->isEmpty()) {
    echo "Sin configuraciones guardadas.\n";
    exit(0);
}

echo "Tabla: pq_asistente_ia_credenciales\n";
echo "Columna sensible: api_key_encrypted (Laravel Crypt::encryptString)\n\n";

foreach ($rows as $row) {
    $encrypted = (string) $row->api_key_encrypted;
    $looksPlainSk = str_starts_with($encrypted, 'sk-');
    $looksLaravelPayload = str_starts_with($encrypted, 'eyJ');

    echo "usuario: {$row->codigo}\n";
    echo "provider_id: {$row->provider_id}\n";
    echo "model_id: {$row->model_id}\n";
    echo 'base_url: '.($row->base_url ?? '(null)')."\n";
    echo 'supports_vision: '.($row->supports_vision ? 'true' : 'false')."\n";
    echo 'is_enabled: '.($row->is_enabled ? 'true' : 'false')."\n";
    echo "updated_at: {$row->updated_at}\n";
    echo 'api_key_encrypted_len: '.strlen($encrypted)."\n";
    echo 'parece_texto_plano_sk: '.($looksPlainSk ? 'SI (INSEGURO)' : 'no')."\n";
    echo 'parece_payload_laravel: '.($looksLaravelPayload ? 'si' : 'revisar')."\n";
    echo 'preview: '.substr($encrypted, 0, 32)."...\n";
    echo "---\n";
}
