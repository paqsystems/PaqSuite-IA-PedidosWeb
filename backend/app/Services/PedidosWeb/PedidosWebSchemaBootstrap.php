<?php

namespace App\Services\PedidosWeb;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PedidosWebSchemaBootstrap
{
    public function ensureMvpSchema(): void
    {
        $this->ensureCabeceraColumns();
        $this->ensureClienteColumns();
        $this->ensureMvpTables();
        $this->ensureMotivosCierreSeed();
        $this->ensureArticulosFeatureFixtures();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function filterAttributes(string $table, array $attributes): array
    {
        if (! Schema::hasTable($table)) {
            return $attributes;
        }

        $filtered = [];

        foreach ($attributes as $key => $value) {
            if (Schema::hasColumn($table, (string) $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public function cabeceraSupportsNumeroVisible(): bool
    {
        return Schema::hasTable('pq_pedidosweb_pedidoscabecera')
            && Schema::hasColumn('pq_pedidosweb_pedidoscabecera', 'nro_visible');
    }

    public function ventaDetalladaUsesErpColumns(): bool
    {
        return Schema::hasTable('pq_pedidosweb_ventadetallada')
            && Schema::hasColumn('pq_pedidosweb_ventadetallada', 'id_gva53');
    }

    /** @deprecated Use ventaDetalladaUsesErpColumns(); ERP export uses cod_cli / fecha_emi. */
    public function ventaDetalladaUsesLegacyColumns(): bool
    {
        return Schema::hasTable('pq_pedidosweb_ventadetallada')
            && ! Schema::hasColumn('pq_pedidosweb_ventadetallada', 'fecha')
            && Schema::hasColumn('pq_pedidosweb_ventadetallada', 'fecha_emi');
    }

    public function deudaUsesErpColumns(): bool
    {
        return Schema::hasTable('pq_pedidosweb_deuda')
            && Schema::hasColumn('pq_pedidosweb_deuda', 't_comp');
    }

    /**
     * @return array{tipo: string, numero: string, fecha: string}
     */
    public function deudaColumnMap(): array
    {
        if ($this->deudaUsesErpColumns()) {
            return [
                'tipo' => 't_comp',
                'numero' => 'n_comp',
                'fecha' => 'fecha_emis',
            ];
        }

        return [
            'tipo' => 'tipo_comprobante',
            'numero' => 'nro_comprobante',
            'fecha' => 'fecha',
        ];
    }

    public function clienteRazonSocialColumn(): string
    {
        if (Schema::hasTable('pq_pedidosweb_clientes')
            && Schema::hasColumn('pq_pedidosweb_clientes', 'razon_soci')) {
            return 'razon_soci';
        }

        return 'nombre';
    }

    /**
     * @return array{cliente: string, banco: string, importe: string, origen: string, estado: string}
     */
    public function chequesColumnMap(): array
    {
        return [
            'cliente' => $this->resolveTableColumn('pq_pedidosweb_cheques', 'cod_cliente', 'cod_client'),
            'banco' => $this->resolveTableColumn('pq_pedidosweb_cheques', 'Banco', 'banco'),
            'importe' => $this->resolveTableColumn('pq_pedidosweb_cheques', 'Importe', 'importe'),
            'origen' => $this->resolveTableColumn('pq_pedidosweb_cheques', 'Origen', 'origen'),
            'estado' => $this->resolveTableColumn('pq_pedidosweb_cheques', 'Estado', 'estado'),
        ];
    }

    private function resolveTableColumn(string $table, string $preferred, string $fallback): string
    {
        if (! Schema::hasTable($table)) {
            return $fallback;
        }

        if (Schema::hasColumn($table, $preferred)) {
            return $preferred;
        }

        return Schema::hasColumn($table, $fallback) ? $fallback : $fallback;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function mapDetalleAttributes(array $attributes): array
    {
        $table = 'pq_pedidosweb_pedidosdetalle';

        if (
            Schema::hasTable($table)
            && Schema::hasColumn($table, 'bonificacion')
            && ! Schema::hasColumn($table, 'porc_bonif')
            && array_key_exists('porc_bonif', $attributes)
        ) {
            $attributes['bonificacion'] = $attributes['porc_bonif'];
            unset($attributes['porc_bonif']);
        }

        return $this->filterAttributes($table, $attributes);
    }

    public function ensureArticulosFeatureFixtures(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_articulos')) {
            return;
        }

        foreach (['ART-HP-001', 'ART-HP-SEED'] as $codigo) {
            $this->upsertArticuloFixture($codigo);
        }
    }

    private function upsertArticuloFixture(string $codigo): void
    {
        DB::table('pq_pedidosweb_articulos')->updateOrInsert(
            ['codigo' => $codigo],
            [
                'descripcion' => 'Articulo fixture '.$codigo,
                'bonificacion' => 0,
                'usa_esc' => false,
                'base' => 'L',
                'valor1' => 100,
                'valor2' => 0,
                'porc_iva' => 21,
            ]
        );
    }

    private function ensureCabeceraColumns(): void
    {
        $table = 'pq_pedidosweb_pedidoscabecera';

        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = [
            'nro_visible' => 'bigint NULL',
            'usuario_creacion' => 'nvarchar(50) NULL',
            'fecha_creacion' => 'datetime NULL',
            'usuario_modificacion' => 'nvarchar(50) NULL',
            'fechahora_inicio_proceso' => 'datetime NULL',
            'fechahora_ultima_actividad' => 'datetime NULL',
            'origen_comprobante' => 'nvarchar(50) NULL',
            'cod_pedido_origen' => 'nvarchar(50) NULL',
            'cod_presupuesto_origen' => 'nvarchar(50) NULL',
        ];

        foreach ($columns as $column => $definition) {
            $this->addColumnIfMissing($table, $column, $definition);
        }
    }

    private function ensureClienteColumns(): void
    {
        $table = 'pq_pedidosweb_clientes';

        if (! Schema::hasTable($table)) {
            return;
        }

        $this->addColumnIfMissing($table, 'razon_soci', 'nvarchar(120) NULL');
    }

    private function ensureMvpTables(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_motivos_cierre')) {
            DB::statement(<<<'SQL'
CREATE TABLE pq_pedidosweb_motivos_cierre (
    id_motivo int IDENTITY(1,1) NOT NULL PRIMARY KEY,
    tipo_cierre varchar(20) NOT NULL,
    descripcion varchar(100) NOT NULL,
    activo bit NOT NULL CONSTRAINT DF_pq_pw_motivos_cierre_activo DEFAULT (1)
)
SQL);
        }

        if (! Schema::hasTable('pq_pedidosweb_logs_integracion')) {
            DB::statement(<<<'SQL'
CREATE TABLE pq_pedidosweb_logs_integracion (
    id_log bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    fecha datetime NOT NULL,
    tipo varchar(50) NOT NULL,
    severidad varchar(20) NOT NULL,
    origen varchar(50) NOT NULL,
    mensaje nvarchar(max) NOT NULL,
    payload nvarchar(max) NULL,
    procesado bit NOT NULL CONSTRAINT DF_pq_pw_logs_integracion_procesado DEFAULT (0)
)
SQL);
        }

        if (! Schema::hasTable('pq_pedidosweb_tratativas_resultados')) {
            DB::statement(<<<'SQL'
CREATE TABLE pq_pedidosweb_tratativas_resultados (
    id_resultado int IDENTITY(1,1) NOT NULL PRIMARY KEY,
    descripcion varchar(80) NOT NULL,
    activo bit NOT NULL CONSTRAINT DF_pq_pw_trat_resultados_activo DEFAULT (1)
)
SQL);
        }

        if (! Schema::hasTable('pq_pedidosweb_tratativas')) {
            DB::statement(<<<'SQL'
CREATE TABLE pq_pedidosweb_tratativas (
    id_tratativa bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    cod_pedido nvarchar(50) NOT NULL,
    fecha_hora datetime NOT NULL,
    cod_usuario_web nvarchar(50) NOT NULL,
    comentario nvarchar(max) NOT NULL,
    id_resultado int NULL,
    proxima_fecha datetime NULL,
    proxima_accion nvarchar(255) NULL,
    created_at datetime NULL,
    updated_at datetime NULL
)
SQL);
        }

        if (! Schema::hasTable('pq_pedidosweb_presupuestos_cierres')) {
            DB::statement(<<<'SQL'
CREATE TABLE pq_pedidosweb_presupuestos_cierres (
    id_cierre bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    cod_presupuesto nvarchar(50) NOT NULL,
    cod_pedido_generado nvarchar(50) NULL,
    tipo_cierre varchar(20) NOT NULL,
    id_motivo int NULL,
    fecha_cierre datetime NOT NULL,
    cod_usuario_web nvarchar(50) NOT NULL,
    observacion nvarchar(max) NULL
)
SQL);
        }
    }

    private function ensureMotivosCierreSeed(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_motivos_cierre')) {
            return;
        }

        $this->upsertMotivoCierreByDescription('positivo', 'Cierre exitoso MVP');
        $this->upsertMotivoCierreByDescription('negativo', 'Rechazo feature test');
    }

    private function upsertMotivoCierreByDescription(string $tipoCierre, string $descripcion): void
    {
        $table = 'pq_pedidosweb_motivos_cierre';
        $payload = [
            'tipo_cierre' => $tipoCierre,
            'descripcion' => $descripcion,
            'activo' => true,
        ];

        $existing = DB::table($table)->where('descripcion', $descripcion)->first();

        if ($existing !== null) {
            DB::table($table)->where('id_motivo', $existing->id_motivo)->update($payload);

            return;
        }

        DB::table($table)->insert($payload);
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        if (Schema::hasColumn($table, $column)) {
            return;
        }

        DB::statement("ALTER TABLE [{$table}] ADD [{$column}] {$definition}");
    }
}
