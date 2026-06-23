<?php

namespace App\Services\Security;

use App\Models\PqRol;
use Illuminate\Support\Collection;

final class UserRoleUnion
{
    /**
     * @param  Collection<int, PqRol>  $roles
     */
    public function __construct(
        private readonly Collection $roles,
    ) {}

    public function isEmpty(): bool
    {
        return $this->roles->isEmpty();
    }

    public function hasAccesoTotal(): bool
    {
        return $this->roles->contains(static fn (PqRol $rol): bool => (bool) $rol->acceso_total);
    }

    /**
     * @return array<int, string>
     */
    public function getRoleNames(): array
    {
        return $this->roles
            ->pluck('nombre_rol')
            ->filter(static fn ($name): bool => is_string($name) && $name !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function getRoleIds(): array
    {
        return $this->roles->pluck('id')->map(static fn ($id): int => (int) $id)->values()->all();
    }

    public function hasPermission(string $procedimiento, string $tipoPermiso): bool
    {
        if ($this->hasAccesoTotal()) {
            return true;
        }

        $columnByTipoPermiso = [
            'alta' => 'permiso_alta',
            'modi' => 'permiso_modi',
            'baja' => 'permiso_baja',
            'repo' => 'permiso_repo',
        ];

        $permisoColumn = $columnByTipoPermiso[$tipoPermiso] ?? null;

        if ($permisoColumn === null) {
            return false;
        }

        foreach ($this->roles as $rol) {
            if ((bool) $rol->acceso_total) {
                return true;
            }

            $hasAttribute = $rol->atributos
                ->where('procedimiento', $procedimiento)
                ->contains(static fn ($atributo): bool => (bool) $atributo->{$permisoColumn});

            if ($hasAttribute) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, string>
     */
    public function getRepoProcedimientos(): Collection
    {
        if ($this->hasAccesoTotal()) {
            return collect();
        }

        $procedimientos = collect();

        foreach ($this->roles as $rol) {
            if ((bool) $rol->acceso_total) {
                continue;
            }

            foreach ($rol->atributos as $atributo) {
                if ((bool) $atributo->permiso_repo) {
                    $procedimientos->push((string) $atributo->procedimiento);
                }
            }
        }

        return $procedimientos->unique()->values();
    }
}
