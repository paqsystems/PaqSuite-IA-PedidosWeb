<?php

return [

    'monoEmpresaId' => (int) env('PAQSUITE_MONO_EMPRESA_ID', 8),

    'mvpPassword' => env('SEED_MVP_PASSWORD', 'ChangeMeInLocalEnv'),

    'syncCommercial' => filter_var(env('SEED_MVP_SYNC_COMMERCIAL', false), FILTER_VALIDATE_BOOL),
    'refreshPasswords' => filter_var(env('SEED_MVP_REFRESH_PASSWORDS', false), FILTER_VALIDATE_BOOL),

];
