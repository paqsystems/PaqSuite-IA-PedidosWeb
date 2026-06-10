<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inactivity Timeout Minutes
    |--------------------------------------------------------------------------
    |
    | Fallback transitorio del parámetro funcional MinutosWeb mientras el
    | slice de configuración global no expone todavía una lectura reusable
    | desde el ERP. Debe mantenerse alineado con la TR-GEN-02-expiracion-
    | inactividad.
    |
    */
    'inactivityTimeoutMinutes' => env('PAQSUITE_AUTH_INACTIVITY_TIMEOUT_MINUTES', 10),
];
