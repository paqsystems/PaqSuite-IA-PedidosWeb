<?php

namespace App\Services\Admin;

use App\Exceptions\AuthFlowException;
use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\User;
use App\Support\AuthErrorCodes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class AdminPermisoService
{
    public function __construct(
        private readonly AdminRoleService $adminRoleService,
    ) {}

    /**
     * @return array{items: array<int, array<string, mixed>>}
     */
    public function list(?int $usuarioId = null, ?int $rolId = null): array
    {
        $query = PqPermiso::query()
            ->with(['user', 'rol'])
            ->where('id_empresa', (int) config('paqsuite_seed.monoEmpresaId'))
            ->orderByDesc('id');

        if ($usuarioId !== null) {
            $query->where('id_usuario', $usuarioId);
        }

        if ($rolId !== null) {
            $query->where('id_rol', $rolId);
        }

        $items = $query->get()->map(fn (PqPermiso $permiso): array => $this->mapPermiso($permiso))->values()->all();

        return ['items' => $items];
    }

    /**
     * @param  array{idUsuario: int, idRol: int}  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $idUsuario = (int) ($payload['idUsuario'] ?? 0);
        $idRol = (int) ($payload['idRol'] ?? 0);

        $this->assertValidReferences($idUsuario, $idRol);
        $this->assertUniqueAssignment($idUsuario, $idRol);

        $permiso = PqPermiso::query()->create([
            'id_usuario' => $idUsuario,
            'id_rol' => $idRol,
            'id_empresa' => (int) config('paqsuite_seed.monoEmpresaId'),
        ]);

        $permiso->load(['user', 'rol']);

        return $this->mapPermiso($permiso);
    }

    /**
     * @param  array{idRol?: int}  $payload
     * @return array<string, mixed>
     */
    public function update(int $id, array $payload): array
    {
        $permiso = $this->findPermisoOrFail($id);
        $idRol = (int) ($payload['idRol'] ?? 0);

        if ($idRol <= 0) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.required', 422);
        }

        $this->adminRoleService->findRoleOrFail($idRol);

        if ((int) $permiso->id_rol !== $idRol) {
            $this->assertUniqueAssignment((int) $permiso->id_usuario, $idRol, (int) $permiso->id);
        }

        $permiso->id_rol = $idRol;
        $permiso->save();
        $permiso->load(['user', 'rol']);

        return $this->mapPermiso($permiso);
    }

    public function delete(int $id): void
    {
        $permiso = $this->findPermisoOrFail($id);
        $permiso->delete();
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, page: int, page_size: int, total: int, total_pages: int}
     */
    public function lookupUsuarios(?string $search, int $page, int $pageSize): array
    {
        $pageSize = max(1, min($pageSize, 50));
        $page = max(1, $page);

        $query = User::query()
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->orderBy('codigo');

        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->where('codigo', 'like', $term)
                    ->orWhere('name_user', 'like', $term);
            });
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(static fn (User $user): array => [
                'id' => (int) $user->id,
                'codigo' => (string) $user->codigo,
                'nameUser' => (string) ($user->name_user ?? $user->codigo),
            ])->values()->all(),
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
            'total' => $paginator->total(),
            'total_pages' => $paginator->lastPage(),
        ];
    }

    public function findPermisoOrFail(int $id): PqPermiso
    {
        $permiso = PqPermiso::query()
            ->where('id_empresa', (int) config('paqsuite_seed.monoEmpresaId'))
            ->find($id);

        if ($permiso === null) {
            throw new AuthFlowException(AuthErrorCodes::notFound, 'admin.notFound', 404);
        }

        return $permiso;
    }

    private function assertValidReferences(int $idUsuario, int $idRol): void
    {
        if ($idUsuario <= 0 || $idRol <= 0) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.required', 422);
        }

        $user = User::query()
            ->where('id', $idUsuario)
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->first();

        if ($user === null) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'validation.invalid', 422);
        }

        $this->adminRoleService->findRoleOrFail($idRol);
    }

    private function assertUniqueAssignment(int $idUsuario, int $idRol, ?int $ignorePermisoId = null): void
    {
        $query = PqPermiso::query()
            ->where('id_usuario', $idUsuario)
            ->where('id_rol', $idRol)
            ->where('id_empresa', (int) config('paqsuite_seed.monoEmpresaId'));

        if ($ignorePermisoId !== null) {
            $query->where('id', '!=', $ignorePermisoId);
        }

        if ($query->exists()) {
            throw new AuthFlowException(AuthErrorCodes::validationFailed, 'admin.permisos.duplicateAssignment', 422);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPermiso(PqPermiso $permiso): array
    {
        return [
            'id' => (int) $permiso->id,
            'idUsuario' => (int) $permiso->id_usuario,
            'usuarioCodigo' => (string) ($permiso->user?->codigo ?? ''),
            'usuarioNombre' => (string) ($permiso->user?->name_user ?? $permiso->user?->codigo ?? ''),
            'idRol' => (int) $permiso->id_rol,
            'rolNombre' => (string) ($permiso->rol?->nombre_rol ?? ''),
        ];
    }
}
