<?php

namespace App\Services\Admin;

use App\Exceptions\AuthFlowException;
use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Support\AuthErrorCodes;
use Illuminate\Support\Collection;

final class AdminRoleService
{
    /**
     * @return array{items: array<int, array<string, mixed>>}
     */
    public function list(?string $search = null): array
    {
        $query = PqRol::query()->orderBy('nombre_rol');

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->where('nombre_rol', 'like', $term)
                    ->orWhere('descripcion_rol', 'like', $term);
            });
        }

        $rolesInUse = PqPermiso::query()
            ->select('id_rol')
            ->distinct()
            ->pluck('id_rol')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $rolesInUseLookup = array_fill_keys($rolesInUse, true);

        $items = $query->get()->map(static function (PqRol $rol) use ($rolesInUseLookup): array {
            return [
                'id' => (int) $rol->id,
                'nombreRol' => (string) $rol->nombre_rol,
                'descripcionRol' => (string) ($rol->descripcion_rol ?? ''),
                'accesoTotal' => (bool) $rol->acceso_total,
                'enUso' => isset($rolesInUseLookup[(int) $rol->id]),
            ];
        })->values()->all();

        return ['items' => $items];
    }

    /**
     * @param  array{nombreRol?: string, descripcionRol?: string|null, accesoTotal?: bool}  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $nombreRol = trim((string) ($payload['nombreRol'] ?? ''));

        if ($nombreRol === '') {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.required', 422);
        }

        $this->assertUniqueName($nombreRol);

        $rol = PqRol::query()->create([
            'nombre_rol' => $nombreRol,
            'descripcion_rol' => (string) ($payload['descripcionRol'] ?? ''),
            'acceso_total' => (bool) ($payload['accesoTotal'] ?? false),
        ]);

        return $this->mapRole($rol, false);
    }

    /**
     * @param  array{nombreRol?: string, descripcionRol?: string|null, accesoTotal?: bool}  $payload
     * @return array<string, mixed>
     */
    public function update(int $id, array $payload): array
    {
        $rol = $this->findRoleOrFail($id);

        if (array_key_exists('nombreRol', $payload)) {
            $nombreRol = trim((string) $payload['nombreRol']);

            if ($nombreRol === '') {
                throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.required', 422);
            }

            if (strcasecmp($nombreRol, (string) $rol->nombre_rol) !== 0) {
                $this->assertUniqueName($nombreRol);
            }

            $rol->nombre_rol = $nombreRol;
        }

        if (array_key_exists('descripcionRol', $payload)) {
            $rol->descripcion_rol = (string) ($payload['descripcionRol'] ?? '');
        }

        if (array_key_exists('accesoTotal', $payload)) {
            $rol->acceso_total = (bool) $payload['accesoTotal'];
        }

        $rol->save();

        $enUso = PqPermiso::query()->where('id_rol', $rol->id)->exists();

        return $this->mapRole($rol, $enUso);
    }

    public function delete(int $id): void
    {
        $rol = $this->findRoleOrFail($id);

        if (PqPermiso::query()->where('id_rol', $rol->id)->exists()) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'admin.roles.deleteInUse', 422);
        }

        $rol->delete();
    }

    public function findRoleOrFail(int $id): PqRol
    {
        $rol = PqRol::query()->find($id);

        if ($rol === null) {
            throw new AuthFlowException(AuthErrorCodes::notFound, 'admin.notFound', 404);
        }

        return $rol;
    }

    private function assertUniqueName(string $nombreRol): void
    {
        $exists = PqRol::query()
            ->whereRaw('LOWER(nombre_rol) = ?', [mb_strtolower($nombreRol)])
            ->exists();

        if ($exists) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'admin.roles.duplicateRoleName', 422);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRole(PqRol $rol, bool $enUso): array
    {
        return [
            'id' => (int) $rol->id,
            'nombreRol' => (string) $rol->nombre_rol,
            'descripcionRol' => (string) ($rol->descripcion_rol ?? ''),
            'accesoTotal' => (bool) $rol->acceso_total,
            'enUso' => $enUso,
        ];
    }
}
