<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasTable('pq_pedidosweb_articulos')) {
    fwrite(STDERR, "ERROR: tabla pq_pedidosweb_articulos no existe\n");
    exit(1);
}

if (! Schema::hasColumn('pq_pedidosweb_articulos', 'especial')) {
    DB::unprepared((string) file_get_contents(__DIR__.'/sql/alter-pq-pedidosweb-articulos-especial.sql'));
}

$candidato = DB::selectOne(<<<'SQL'
SELECT TOP 1 a.codigo, a.descripcion
FROM pq_pedidosweb_articulos a WITH (NOLOCK)
WHERE (a.usa_esc IS NULL OR UPPER(LTRIM(RTRIM(CAST(a.usa_esc AS NVARCHAR(20))))) <> 'B')
  AND a.descripcion IS NOT NULL
  AND EXISTS (
      SELECT 1
      FROM pq_pedidosweb_pedidosdetalle d WITH (NOLOCK)
      WHERE d.cod_articulo = a.codigo
  )
ORDER BY a.descripcion
SQL);

if ($candidato === null) {
    $candidato = DB::selectOne(<<<'SQL'
SELECT TOP 1 a.codigo, a.descripcion
FROM pq_pedidosweb_articulos a WITH (NOLOCK)
WHERE (a.usa_esc IS NULL OR UPPER(LTRIM(RTRIM(CAST(a.usa_esc AS NVARCHAR(20))))) <> 'B')
  AND a.descripcion IS NOT NULL
ORDER BY a.descripcion
SQL);
}

if ($candidato === null) {
    fwrite(STDERR, "ERROR: no hay artículos candidatos\n");
    exit(1);
}

$codigo = (string) $candidato->codigo;

DB::table('pq_pedidosweb_articulos')
    ->where('codigo', $codigo)
    ->update(['especial' => 1]);

$pedido = DB::selectOne(<<<'SQL'
SELECT TOP 1 d.cod_pedido, d.renglon, c.estado
FROM pq_pedidosweb_pedidosdetalle d WITH (NOLOCK)
INNER JOIN pq_pedidosweb_pedidoscabecera c WITH (NOLOCK) ON c.cod_pedido = d.cod_pedido
WHERE d.cod_articulo = ?
ORDER BY c.fecha DESC
SQL, [$codigo]);

echo "OK columna especial: ".(Schema::hasColumn('pq_pedidosweb_articulos', 'especial') ? 'si' : 'no').PHP_EOL;
echo "Articulo prueba: {$codigo} — {$candidato->descripcion}".PHP_EOL;
echo "especial=1 aplicado".PHP_EOL;

if ($pedido !== null) {
    echo "Pedido detalle sugerido: {$pedido->cod_pedido} renglon {$pedido->renglon} (estado {$pedido->estado})".PHP_EOL;
} else {
    echo "Sin pedido existente con ese artículo; probar en carga de pedidos lookup.".PHP_EOL;
}
