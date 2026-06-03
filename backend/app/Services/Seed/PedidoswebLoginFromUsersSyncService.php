<?php

namespace App\Services\Seed;

use App\Models\PqPedidoswebCliente;
use App\Models\PqPedidoswebLogin;
use App\Models\PqPedidoswebVendedor;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/**
 * Completa pq_pedidosweb_login con filas faltantes a partir de users.
 *
 * Criterio "ya existe": hay fila con usuario = users.codigo.
 * Alineación legacy: si cod_usuario_web = users.codigo pero usuario distinto, actualiza usuario.
 */
final class PedidoswebLoginFromUsersSyncService
{
    /**
     * @return array{inserted: int, aligned: int, skipped: int, conflicts: list<string>}
     */
    public function syncMissing(): array
    {
        if (! Schema::hasTable('pq_pedidosweb_login')) {
            return [
                'inserted' => 0,
                'aligned' => 0,
                'skipped' => 0,
                'conflicts' => ['Tabla pq_pedidosweb_login no existe.'],
            ];
        }

        $inserted = 0;
        $aligned = 0;
        $skipped = 0;
        $conflicts = [];

        foreach (User::query()->orderBy('codigo')->get() as $user) {
            $codigo = trim((string) $user->codigo);

            if ($codigo === '') {
                $skipped++;
                continue;
            }

            $existingByUsuario = PqPedidoswebLogin::query()
                ->where('usuario', $codigo)
                ->first();

            if ($existingByUsuario !== null) {
                $skipped++;
                continue;
            }

            $existingByCodWeb = PqPedidoswebLogin::query()
                ->where('cod_usuario_web', $codigo)
                ->first();

            if ($existingByCodWeb !== null) {
                $existingByCodWeb->usuario = $codigo;
                $existingByCodWeb->e_mail = (string) ($user->email ?? $existingByCodWeb->e_mail);
                if (filled($user->password_hash)) {
                    $existingByCodWeb->password_bcrypt = (string) $user->password_hash;
                }
                $existingByCodWeb->primer_login = (bool) $user->first_login;
                $existingByCodWeb->save();
                $aligned++;

                continue;
            }

            $commercial = $this->resolveCommercialKeys($codigo);
            $codUsuarioWeb = $commercial['codUsuarioWeb'];

            if ($codUsuarioWeb !== $codigo
                && PqPedidoswebLogin::query()->where('cod_usuario_web', $codUsuarioWeb)->exists()) {
                $conflicts[] = "users.codigo={$codigo}: cod_usuario_web={$codUsuarioWeb} ya ocupado por otro login.";
                $skipped++;

                continue;
            }

            PqPedidoswebLogin::query()->create([
                'cod_usuario_web' => $codUsuarioWeb,
                'usuario' => $codigo,
                'password_bcrypt' => (string) ($user->password_hash ?? ''),
                'e_mail' => (string) ($user->email ?? ''),
                'primer_login' => (bool) $user->first_login,
                'tipo_cuenta' => $commercial['tipoCuenta'],
                'cod_asociado' => $commercial['codAsociado'],
                'password' => null,
                'password_sha1' => null,
            ]);

            $inserted++;
        }

        return [
            'inserted' => $inserted,
            'aligned' => $aligned,
            'skipped' => $skipped,
            'conflicts' => $conflicts,
        ];
    }

    /**
     * @return array{codUsuarioWeb: string, tipoCuenta: string, codAsociado: string}
     */
    private function resolveCommercialKeys(string $codigo): array
    {
        if (Schema::hasTable('pq_pedidosweb_clientes')) {
            $cliente = PqPedidoswebCliente::query()
                ->where('cod_login', $codigo)
                ->first();

            if ($cliente !== null) {
                $codClient = (string) $cliente->cod_client;

                return [
                    'codUsuarioWeb' => $codClient,
                    'tipoCuenta' => 'C',
                    'codAsociado' => $codClient,
                ];
            }
        }

        if (Schema::hasTable('pq_pedidosweb_vendedores')) {
            $vendedor = PqPedidoswebVendedor::query()
                ->where('cod_login', $codigo)
                ->first();

            if ($vendedor !== null) {
                $codVended = (string) $vendedor->cod_vended;

                return [
                    'codUsuarioWeb' => $codVended,
                    'tipoCuenta' => 'V',
                    'codAsociado' => $codVended,
                ];
            }
        }

        return [
            'codUsuarioWeb' => $codigo,
            'tipoCuenta' => 'V',
            'codAsociado' => $codigo,
        ];
    }
}
