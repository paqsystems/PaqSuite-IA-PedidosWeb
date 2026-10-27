<?php

namespace App\Support;

final class IntegracionMaestroErrorCodes
{
    public const jsonInvalido = 1003;

    public const modoInvalido = 1004;

    public const modoNoSoportado = 1005;

    public const snapshotVacio = 1006;

    public const loteDemasiadoGrande = 1007;

    public const recursoDesconocido = 4001;

    public const payloadProhibido = 1008;

    public const conflictoImportacion = 1009;

    public const importacionNoEncontrada = 1010;

    public const fatal = 9001;
}
