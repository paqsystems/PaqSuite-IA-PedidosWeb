<?php

declare(strict_types=1);

namespace App\Services\Visibility;

/**
 * Mapper de ítem GET /clientes (CC PQ #11).
 *
 * Lectura de maestros: misma excepción Eloquent que el listado vigente
 * `GET /api/v1/clientes` (VisibilityDataService / VisibleClientsResolver).
 */
final class VisibleClientPayloadMapper
{
    /**
     * @param  array<int, array<string, mixed>>  $contactos
     * @return array<string, mixed>
     */
    public static function mapCliente(object $cliente, array $contactos): array
    {
        return [
            'codCliente' => (string) $cliente->cod_client,
            'nombre' => (string) $cliente->nombre,
            'razonSocial' => trim((string) ($cliente->razon_soci ?? '')) !== ''
                ? (string) $cliente->razon_soci
                : (string) $cliente->nombre,
            'fantasia' => $cliente->fantasia !== null ? (string) $cliente->fantasia : null,
            'codVendedor' => $cliente->cod_vended !== null ? (string) $cliente->cod_vended : null,
            'email' => $cliente->e_mail !== null ? (string) $cliente->e_mail : null,
            'contactos' => $contactos,
        ];
    }

    /**
     * @return array{id: int, codContacto: string, nombre: string, telefono: string|null, mail: string|null}
     */
    public static function mapContacto(object $contacto): array
    {
        return [
            'id' => (int) $contacto->id,
            'codContacto' => (string) $contacto->cod_contacto,
            'nombre' => (string) $contacto->nombre,
            'telefono' => $contacto->telefono !== null && $contacto->telefono !== ''
                ? (string) $contacto->telefono
                : null,
            'mail' => $contacto->mail !== null && $contacto->mail !== ''
                ? (string) $contacto->mail
                : null,
        ];
    }
}
