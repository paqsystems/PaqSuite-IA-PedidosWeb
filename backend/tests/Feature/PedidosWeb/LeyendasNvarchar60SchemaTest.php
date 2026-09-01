<?php

namespace Tests\Feature\PedidosWeb;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LeyendasNvarchar60SchemaTest extends TestCase
{
    #[Test]
    public function columnasLeyendaCabeceraYClientesSonNvarchar60(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlsrv') {
            $this->markTestSkipped('Requiere SQL Server.');
        }

        $columnas = ['leyenda_1', 'leyenda_2', 'leyenda_3', 'leyenda_4', 'leyenda_5'];
        $tablas = ['pq_pedidosweb_pedidoscabecera', 'pq_pedidosweb_clientes'];

        foreach ($tablas as $tabla) {
            if (! Schema::hasTable($tabla)) {
                $this->markTestSkipped("Tabla {$tabla} no disponible.");
            }

            foreach ($columnas as $columna) {
                if (! Schema::hasColumn($tabla, $columna)) {
                    $this->markTestSkipped("Columna {$tabla}.{$columna} no disponible.");
                }

                $row = DB::selectOne(
                    <<<'SQL'
SELECT c.max_length AS max_length
FROM sys.columns c WITH (NOLOCK)
INNER JOIN sys.tables t WITH (NOLOCK) ON t.object_id = c.object_id
INNER JOIN sys.schemas s WITH (NOLOCK) ON s.schema_id = t.schema_id
WHERE s.name = N'dbo' AND t.name = ? AND c.name = ?
SQL,
                    [$tabla, $columna]
                );

                $this->assertNotNull($row, "{$tabla}.{$columna}");
                $this->assertSame(
                    120,
                    (int) ($row->max_length ?? 0),
                    "{$tabla}.{$columna} debe ser nvarchar(60) (max_length=120)"
                );
            }
        }
    }
}
