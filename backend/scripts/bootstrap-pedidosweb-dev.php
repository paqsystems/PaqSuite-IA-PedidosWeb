<?php

/**
 * Recrea tablas pq_pedidosweb_* + PQ_parametros_gral y carga seed MVP.
 *
 * Uso interactivo:
 *   php scripts/bootstrap-pedidosweb-dev.php
 *
 * Uso CI / sin confirmación:
 *   php scripts/bootstrap-pedidosweb-dev.php --yes
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$args = array_slice($argv, 1);
$options = [];

if (in_array('--yes', $args, true) || in_array('-y', $args, true)) {
    $options['--no-interaction'] = true;
}

if (in_array('--skip-menus', $args, true)) {
    $options['--skip-menus'] = true;
}

if (in_array('--skip-seguridad', $args, true)) {
    $options['--skip-seguridad'] = true;
}

$exit = $kernel->call('paqsuite:bootstrap-pedidosweb-dev', $options);
echo $kernel->output();

exit($exit);
