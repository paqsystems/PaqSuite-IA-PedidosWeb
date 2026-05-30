<?php

return [

    'headerName' => env('TENANT_HEADER_NAME', 'X-Paq-Cliente'),

    'defaultClient' => env('TENANT_DEFAULT_CLIENT', 'desarrollo'),

    'allowedClients' => array_values(array_filter(array_map(
        static fn (string $client): string => trim($client),
        explode(',', (string) env('TENANT_ALLOWED_CLIENTS', 'desarrollo'))
    ))),

];
