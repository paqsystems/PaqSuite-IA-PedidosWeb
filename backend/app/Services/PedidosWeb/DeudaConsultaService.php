<?php

namespace App\Services\PedidosWeb;

use App\Models\User;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use App\Services\Visibility\VisibleClientsResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consulta de deuda — join a clientes y columnas ERP/legacy.
 *
 * @see docs/02-producto/PedidosWeb/consulta-deuda.md
 */
final class DeudaConsultaService
{
    public function __construct(
        private readonly VisibleClientsResolver $visibleClientsResolver,
        private readonly PedidosWebVisibilityGuard $visibilityGuard,
        private readonly PedidosWebSchemaBootstrap $schemaBootstrap,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listar(User $user, array $filters): array
    {
        if (! Schema::hasTable('pq_pedidosweb_deuda')) {
            return $this->emptyResult($filters);
        }

        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        $codCliente = $this->resolveCodCliente($user, $filters);
        $query = $this->buildDeudaQuery($codCliente, $user);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        $items = collect($paginator->items())
            ->map(fn (object $row): array => $this->mapRow($row))
            ->values()
            ->all();

        $fechaProceso = DB::table('pq_pedidosweb_deuda')->max('fecha_proceso');

        return [
            'items' => $items,
            'page' => (int) $paginator->currentPage(),
            'page_size' => (int) $paginator->perPage(),
            'total' => (int) $paginator->total(),
            'total_pages' => (int) $paginator->lastPage(),
            'metadata' => [
                'fecha_proceso' => $fechaProceso !== null ? (string) $fechaProceso : null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function resolveCodCliente(User $user, array $filters): ?string
    {
        $codCliente = filled($filters['cod_cliente'] ?? null)
            ? trim((string) $filters['cod_cliente'])
            : null;

        if ($codCliente !== null) {
            $this->visibilityGuard->ensureCodClienteVisible($user, $codCliente);

            return $codCliente;
        }

        return null;
    }

    private function buildDeudaQuery(?string $codCliente, User $user): Builder
    {
        $columns = $this->schemaBootstrap->deudaColumnMap();
        $razonColumn = $this->schemaBootstrap->clienteRazonSocialColumn();
        $hasClientes = Schema::hasTable('pq_pedidosweb_clientes');

        $query = DB::table('pq_pedidosweb_deuda as d');

        if ($hasClientes) {
            $query->leftJoin('pq_pedidosweb_clientes as c', 'd.cod_cliente', '=', 'c.cod_client');
        }

        $razonExpr = $hasClientes
            ? "COALESCE(c.[{$razonColumn}], c.nombre, '')"
            : "''";

        $query->selectRaw(<<<SQL
d.cod_cliente AS cod_cliente,
{$razonExpr} AS razon_social,
d.[{$columns['tipo']}] AS tipo,
d.[{$columns['numero']}] AS numero,
d.[{$columns['fecha']}] AS fecha,
d.fecha_vto AS vencimiento,
d.saldo AS saldo,
d.fecha_proceso AS fecha_proceso
SQL)
            ->orderByDesc('d.fecha_vto')
            ->orderBy('d.cod_cliente');

        if ($codCliente !== null) {
            $query->where('d.cod_cliente', $codCliente);
        } else {
            $query->whereIn(
                'd.cod_cliente',
                $this->visibleClientsResolver->visibleClientsForUser($user)->select('cod_client')
            );
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRow(object $row): array
    {
        return [
            'codCliente' => (string) $row->cod_cliente,
            'razonSocial' => (string) ($row->razon_social ?? ''),
            'tipo' => (string) ($row->tipo ?? ''),
            'numero' => (string) ($row->numero ?? ''),
            'fecha' => $row->fecha !== null
                ? Carbon::parse((string) $row->fecha)->toIso8601String()
                : null,
            'vencimiento' => $row->vencimiento !== null
                ? Carbon::parse((string) $row->vencimiento)->toIso8601String()
                : null,
            'saldo' => round((float) $row->saldo, 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function emptyResult(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        return [
            'items' => [],
            'page' => $page,
            'page_size' => $pageSize,
            'total' => 0,
            'total_pages' => 0,
            'metadata' => ['fecha_proceso' => null],
        ];
    }
}
