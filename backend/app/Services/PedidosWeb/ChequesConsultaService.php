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
 * Consulta de cheques — join a clientes y columnas ERP/legacy.
 *
 * @see docs/02-producto/PedidosWeb/consulta-cheques.md
 */
final class ChequesConsultaService
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
        if (! Schema::hasTable('pq_pedidosweb_cheques')) {
            return $this->emptyResult($filters);
        }

        $page = max(1, (int) ($filters['page'] ?? 1));
        $pageSize = min(100, max(1, (int) ($filters['page_size'] ?? 20)));

        $codCliente = $this->resolveCodCliente($user, $filters);
        $query = $this->buildChequesQuery($codCliente, $user);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        $items = collect($paginator->items())
            ->map(fn (object $row): array => $this->mapRow($row))
            ->values()
            ->all();

        $fechaProceso = DB::table('pq_pedidosweb_cheques')->max('fecha_proceso');

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

    private function buildChequesQuery(?string $codCliente, User $user): Builder
    {
        $columns = $this->schemaBootstrap->chequesColumnMap();
        $hasClientes = Schema::hasTable('pq_pedidosweb_clientes');

        $query = DB::table('pq_pedidosweb_cheques as ch');

        if ($hasClientes) {
            $query->leftJoin(
                'pq_pedidosweb_clientes as c',
                'c.cod_client',
                '=',
                "ch.{$columns['cliente']}"
            );
        }

        $nombreExpr = $hasClientes ? "COALESCE(c.nombre, '')" : "''";

        $query->selectRaw(<<<SQL
ch.interno AS interno,
ch.numero AS numero,
ch.[{$columns['cliente']}] AS cod_cliente,
{$nombreExpr} AS nombre_cliente,
ch.[{$columns['banco']}] AS banco,
ch.fecha AS fecha,
ch.[{$columns['importe']}] AS importe,
ch.[{$columns['origen']}] AS origen,
ch.[{$columns['estado']}] AS estado,
ch.fecha_proceso AS fecha_proceso
SQL)
            ->where(function (Builder $builder) use ($columns): void {
                $builder
                    ->where("ch.{$columns['estado']}", 'En cartera')
                    ->orWhereRaw('ch.fecha >= CAST(GETDATE() AS date)');
            })
            ->orderByDesc('ch.fecha')
            ->orderBy("ch.{$columns['cliente']}")
            ->orderBy('ch.numero');

        if ($codCliente !== null) {
            $query->where("ch.{$columns['cliente']}", $codCliente);
        } else {
            $query->whereIn(
                "ch.{$columns['cliente']}",
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
            'interno' => (string) ($row->interno ?? ''),
            'numero' => (string) ($row->numero ?? ''),
            'codCliente' => (string) ($row->cod_cliente ?? ''),
            'nombreCliente' => (string) ($row->nombre_cliente ?? ''),
            'banco' => (string) ($row->banco ?? ''),
            'fecha' => $row->fecha !== null
                ? Carbon::parse((string) $row->fecha)->toIso8601String()
                : null,
            'importe' => round((float) ($row->importe ?? 0), 2),
            'origen' => (string) ($row->origen ?? ''),
            'estado' => (string) ($row->estado ?? ''),
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
