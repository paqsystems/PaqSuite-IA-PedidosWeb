<?php

declare(strict_types=1);

/**
 * Inserta CLOUDSMITH_READ_TOKEN en un .env si falta (stdin → stdout).
 * Uso: php inject-cloudsmith-env-block.php < .env.forge > .env.patched
 */

$env = stream_get_contents(STDIN);
if ($env === false || $env === '') {
    fwrite(STDERR, "ERROR: stdin vacío\n");
    exit(1);
}

if (preg_match('/^CLOUDSMITH_READ_TOKEN=/m', $env)) {
    echo $env;
    exit(0);
}

$token = getenv('CLOUDSMITH_READ_TOKEN') ?: '';
if ($token === '') {
    fwrite(STDERR, "ERROR: falta CLOUDSMITH_READ_TOKEN en el entorno\n");
    exit(1);
}

$block = "\n# Cloudsmith — paqsuite/laravel-core\nCLOUDSMITH_READ_TOKEN={$token}\n";
$marker = '# --- Emisiones GEN-15';

if (str_contains($env, $marker)) {
    echo str_replace($marker, rtrim($block) . "\n" . $marker, $env);
    exit(0);
}

echo rtrim($env) . $block;
