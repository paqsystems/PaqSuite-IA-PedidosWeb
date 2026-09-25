<?php

declare(strict_types=1);

/**
 * Inventario OpenAPI vs rutas api/v1 — informe para revisión de tags y calidad de resultado.
 *
 * Uso: php scripts/generate-openapi-revision-report.php
 */

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$specPath = $root . '/storage/api-docs/api-docs.json';
if (! is_file($specPath)) {
    passthru('php "' . $root . '/artisan" l5-swagger:generate', $exitCode);
    if ($exitCode !== 0 || ! is_file($specPath)) {
        fwrite(STDERR, "No se pudo generar api-docs.json\n");
        exit(1);
    }
}

$spec = json_decode((string) file_get_contents($specPath), true, 512, JSON_THROW_ON_ERROR);

$routes = [];
foreach (Illuminate\Support\Facades\Route::getRoutes() as $route) {
    $uri = '/' . ltrim((string) $route->uri(), '/');
    if (! str_starts_with($uri, '/api/v1')) {
        continue;
    }
    foreach ($route->methods() as $method) {
        $method = strtolower((string) $method);
        if (! in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true)) {
            continue;
        }
        $routes[] = [
            'uri' => ltrim($uri, '/'),
            'method' => strtoupper($method),
        ];
    }
}

function normPath(string $u): string
{
    $u = '/' . ltrim($u, '/');

    return (string) preg_replace('/\{([^}:]+):[^}]+\}/', '{$1}', $u);
}

function getRefName(?array $schema): ?string
{
    if ($schema === null || empty($schema['$ref'])) {
        return null;
    }
    $parts = explode('/', (string) $schema['$ref']);

    return end($parts) ?: null;
}

function resolveSchema(?array $schema, array $spec): ?array
{
    if ($schema === null) {
        return null;
    }
    $name = getRefName($schema);
    if ($name !== null) {
        return $spec['components']['schemas'][$name] ?? null;
    }

    return $schema;
}

function schemaHasProperties(?array $schema, array $spec, int $depth = 0): bool
{
    if ($schema === null || $depth > 5) {
        return false;
    }
    $resolved = resolveSchema($schema, $spec);
    if ($resolved === null) {
        return false;
    }
    if (! empty($resolved['properties'])) {
        return true;
    }
    if (! empty($resolved['items']) && schemaHasProperties($resolved['items'], $spec, $depth + 1)) {
        return true;
    }
    if (! empty($resolved['allOf'])) {
        foreach ($resolved['allOf'] as $part) {
            if (schemaHasProperties($part, $spec, $depth + 1)) {
                return true;
            }
        }
    }

    return false;
}

function getResultadoSchema(?array $opSchema, array $spec): ?array
{
    $resolved = resolveSchema($opSchema, $spec);
    if ($resolved === null) {
        return null;
    }

    $candidates = [];
    if (isset($resolved['properties']['resultado'])) {
        $candidates[] = $resolved['properties']['resultado'];
    }
    if (! empty($resolved['allOf'])) {
        foreach ($resolved['allOf'] as $part) {
            // Preferir el resultado del allOf tipado; el de ApiEnvelope base es object vacío.
            if (getRefName($part) === 'ApiEnvelope') {
                continue;
            }
            $p = resolveSchema($part, $spec);
            if (isset($p['properties']['resultado'])) {
                $candidates[] = $p['properties']['resultado'];
            } elseif (isset($part['properties']['resultado'])) {
                $candidates[] = $part['properties']['resultado'];
            }
        }
    }

    foreach ($candidates as $candidate) {
        if (getRefName($candidate) || schemaHasProperties($candidate, $spec)) {
            return $candidate;
        }
    }

    return $candidates[0] ?? null;
}

/**
 * @return array{detail:string,severity:string,envelope:string}
 */
function analyzeResultado(?array $op, array $spec): array
{
    if ($op === null || empty($op['responses']['200'])) {
        return ['detail' => 'sin_200', 'severity' => 'alto', 'envelope' => 'none'];
    }
    $schema = $op['responses']['200']['content']['application/json']['schema'] ?? null;
    if ($schema === null) {
        return ['detail' => 'sin_schema_json', 'severity' => 'alto', 'envelope' => 'none'];
    }
    $refName = getRefName($schema) ?? '(inline)';
    if ($refName === 'ApiEnvelopeEmpty') {
        return ['detail' => 'envelope_vacio_ok', 'severity' => 'ok', 'envelope' => $refName];
    }
    if ($refName === 'ApiEnvelope') {
        return ['detail' => 'ApiEnvelope_generico_sin_estructura', 'severity' => 'critico', 'envelope' => $refName];
    }
    $resultado = getResultadoSchema($schema, $spec);
    if ($resultado === null) {
        $resolved = resolveSchema($schema, $spec);
        if (! empty($resolved['example']['resultado'])) {
            return ['detail' => 'solo_example_sin_schema_resultado', 'severity' => 'alto', 'envelope' => $refName];
        }

        return ['detail' => 'sin_prop_resultado', 'severity' => 'alto', 'envelope' => $refName];
    }
    if (schemaHasProperties($resultado, $spec)) {
        return ['detail' => 'estructura_tipada', 'severity' => 'ok', 'envelope' => $refName];
    }
    $r = resolveSchema($resultado, $spec);
    if (($r['type'] ?? null) === 'array') {
        return ['detail' => 'array_items_sin_props', 'severity' => 'critico', 'envelope' => $refName];
    }

    return ['detail' => 'resultado_object_sin_props', 'severity' => 'critico', 'envelope' => $refName];
}

function analyzeBody(?array $op, array $spec): string
{
    if (empty($op['requestBody'])) {
        return 'n/a';
    }
    $schema = $op['requestBody']['content']['application/json']['schema'] ?? null;
    if ($schema === null) {
        return 'n/a';
    }
    $resolved = resolveSchema($schema, $spec) ?? $schema;
    if (empty($resolved['properties'])) {
        return getRefName($schema) ? 'body_ref' : 'body_sin_properties';
    }
    $shallow = false;
    $typed = false;
    foreach ($resolved['properties'] as $v) {
        if (getRefName($v) || schemaHasProperties($v, $spec)) {
            $typed = true;
            continue;
        }
        if (($v['type'] ?? null) === 'object' && empty($v['properties']) && ! getRefName($v)) {
            $shallow = true;
            continue;
        }
        if (($v['type'] ?? null) === 'array' && isset($v['items'])) {
            $it = $v['items'];
            if (getRefName($it) || schemaHasProperties($it, $spec)) {
                $typed = true;
            } elseif (($it['type'] ?? null) === 'object' && empty($it['properties'])) {
                $shallow = true;
            }
            continue;
        }
        if (in_array($v['type'] ?? '', ['string', 'integer', 'number', 'boolean'], true)) {
            $typed = true;
        }
    }
    if ($shallow) {
        return 'body_object_vacio';
    }
    if ($typed) {
        return 'body_tipado';
    }

    return 'body_superficial';
}

$oaIndex = [];
foreach ($spec['paths'] as $path => $methods) {
    foreach ($methods as $method => $op) {
        if (! in_array(strtolower((string) $method), ['get', 'post', 'put', 'patch', 'delete'], true)) {
            continue;
        }
        $oaIndex[$path][strtolower((string) $method)] = $op;
    }
}

$rows = [];
$seen = [];
foreach ($routes as $route) {
    $path = normPath((string) $route['uri']);
    foreach (preg_split('/\|/', strtolower((string) $route['method'])) as $method) {
        if (! in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true)) {
            continue;
        }
        $key = $method . '|' . $path;
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;

        if (isset($oaIndex[$path][$method])) {
            $op = $oaIndex[$path][$method];
            $tag = ! empty($op['tags']) ? implode(', ', $op['tags']) : '(sin tag)';
            $analysis = analyzeResultado($op, $spec);
            $bodyQuality = analyzeBody($op, $spec);
            $severity = $analysis['severity'];
            if ($bodyQuality === 'body_object_vacio') {
                $severity = 'critico';
            }
            $accion = match ($severity) {
                'ok' => 'OK',
                'alto' => 'Completar estructura de resultado o examples',
                'critico' => $bodyQuality === 'body_object_vacio'
                    ? 'Tipar request body + resultado'
                    : (str_starts_with($analysis['detail'], 'ApiEnvelope_generico')
                        ? 'Crear ApiEnvelopeXxx con estructura de resultado'
                        : 'Tipar resultado / body'),
                default => 'Revisar',
            };
            $rows[] = [
                'metodo' => strtoupper($method),
                'path' => $path,
                'tagActualOpenApi' => $tag,
                'tagPropuesto' => '',
                'summary' => (string) ($op['summary'] ?? ''),
                'estadoCobertura' => 'documentado',
                'envelope200' => $analysis['envelope'],
                'calidadResultado' => $analysis['detail'],
                'calidadRequestBody' => $bodyQuality,
                'severidad' => $severity,
                'accionSugerida' => $accion,
            ];
        } else {
            $rows[] = [
                'metodo' => strtoupper($method),
                'path' => $path,
                'tagActualOpenApi' => '(sin documentar)',
                'tagPropuesto' => '',
                'summary' => '',
                'estadoCobertura' => 'faltante_en_openapi',
                'envelope200' => '',
                'calidadResultado' => 'path_ausente',
                'calidadRequestBody' => 'n/a',
                'severidad' => 'faltante',
                'accionSugerida' => 'Agregar path OpenAPI + schema resultado tipado',
            ];
        }
    }
}

usort($rows, static function (array $a, array $b): int {
    $ta = $a['tagActualOpenApi'] === '(sin documentar)' ? 'ZZZ' : $a['tagActualOpenApi'];
    $tb = $b['tagActualOpenApi'] === '(sin documentar)' ? 'ZZZ' : $b['tagActualOpenApi'];

    return [$ta, $a['path'], $a['metodo']] <=> [$tb, $b['path'], $b['metodo']];
});

$counts = ['ok' => 0, 'alto' => 0, 'critico' => 0, 'faltante' => 0];
$tagCounts = [];
foreach ($rows as $row) {
    $counts[$row['severidad']] = ($counts[$row['severidad']] ?? 0) + 1;
    $tagCounts[$row['tagActualOpenApi']] = ($tagCounts[$row['tagActualOpenApi']] ?? 0) + 1;
}
ksort($tagCounts);

$outDir = dirname($root) . '/docs/06-operacion';
if (! is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

file_put_contents(
    $outDir . '/openapi-revision-inventario.json',
    json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

$csv = fopen($outDir . '/openapi-revision-inventario.csv', 'wb');
fputcsv($csv, array_keys($rows[0]));
foreach ($rows as $row) {
    fputcsv($csv, $row);
}
fclose($csv);

$md = [];
$md[] = '# Informe de revision OpenAPI — PedidosWeb';
$md[] = '';
$md[] = 'Fecha: 2026-07-31';
$md[] = 'Fuente: rutas Laravel `api/v1` vs `storage/api-docs/api-docs.json` (L5-Swagger).';
$md[] = '';
$md[] = '## Como usar este informe';
$md[] = '';
$md[] = '1. Completa la columna **tagPropuesto** en el CSV (o en las tablas) con el nombre de grupo deseado en Swagger.';
$md[] = '2. Devuelve el archivo corregido (o la tabla de renombres de tags) para aplicar nomenclatura + schemas.';
$md[] = '3. Archivos companion: [openapi-revision-inventario.csv](./openapi-revision-inventario.csv) · [openapi-revision-inventario.json](./openapi-revision-inventario.json).';
$md[] = '4. Regenerar: `php backend/scripts/generate-openapi-revision-report.php`.';
$md[] = '';
$md[] = '## Resumen';
$md[] = '';
$md[] = '| Severidad | Cantidad | Significado |';
$md[] = '|-----------|----------|-------------|';
$md[] = '| ok | ' . $counts['ok'] . ' | Path documentado con estructura tipada de `resultado` (o envelope vacio valido) |';
$md[] = '| alto | ' . $counts['alto'] . ' | Documentado pero incompleto |';
$md[] = '| critico | ' . $counts['critico'] . ' | `ApiEnvelope` generico, body `{}`, o resultado sin propiedades |';
$md[] = '| faltante | ' . $counts['faltante'] . ' | Ruta Laravel sin path OpenAPI |';
$md[] = '| **Total** | **' . count($rows) . '** | Operaciones HTTP unicas `api/v1` |';
$md[] = '';
$md[] = '## Tags actuales (nomenclatura a corregir)';
$md[] = '';
$md[] = 'Completa **Tag propuesto**. Ejemplo: `Visibilidad` → `Maestros`.';
$md[] = '';
$md[] = '| Tag actual (OpenAPI) | Ops | Tag propuesto (completar) | Notas |';
$md[] = '|----------------------|-----|---------------------------|-------|';
foreach ($tagCounts as $tag => $n) {
    $hint = match (true) {
        $tag === 'Visibilidad' => 'Candidato a Maestros / Catalogos',
        $tag === '(sin documentar)' => 'Asignar tag al documentar',
        $tag === 'PedidosWeb' => 'Puede subdividirse (Carga, Consultas, Config, ...)',
        default => '',
    };
    $md[] = '| `' . $tag . '` | ' . $n . ' |  | ' . $hint . ' |';
}
$md[] = '';
$md[] = '## Inventario por tag';
$md[] = '';
$md[] = 'Columna **tagPropuesto**: vacia = usar el tag del grupo; o override por operacion.';
$current = null;
foreach ($rows as $row) {
    if ($row['tagActualOpenApi'] !== $current) {
        $current = $row['tagActualOpenApi'];
        $md[] = '';
        $md[] = '### Tag: `' . $current . '`';
        $md[] = '';
        $md[] = '| Metodo | Path | Summary | tagPropuesto | Severidad | Resultado | Body | Accion |';
        $md[] = '|--------|------|---------|--------------|-----------|-----------|------|--------|';
    }
    $sum = str_replace('|', '/', $row['summary']);
    if (mb_strlen($sum) > 60) {
        $sum = mb_substr($sum, 0, 57) . '...';
    }
    $md[] = '| ' . $row['metodo']
        . ' | `' . $row['path'] . '`'
        . ' | ' . $sum
        . ' |  '
        . ' | ' . $row['severidad']
        . ' | ' . $row['calidadResultado']
        . ' | ' . $row['calidadRequestBody']
        . ' | ' . $row['accionSugerida']
        . ' |';
}
$md[] = '';
$md[] = '## Hallazgos destacados (confirmados)';
$md[] = '';
$md[] = '1. `POST /api/v1/comprobantes/grabar`: request con `cabecera`/`renglones` como object vacio; response `ApiEnvelope` generico.';
$md[] = '2. `GET /api/v1/articulos`: path existe (tag PedidosWeb) pero sin estructura de `resultado`.';
$md[] = '3. `GET /api/v1/clientes`: documentado bajo tag **Visibilidad** (no PedidosWeb).';
$md[] = '4. `GET /api/v1/config/parametros`: documentado con `ApiEnvelope` generico (sin shape de parametros).';
$md[] = '5. Decenas de operaciones sin documentar (admin, grid-layouts, pivot-configs, excel staging, etc.).';
$md[] = '';
$md[] = '## Regla de calidad';
$md[] = '';
$md[] = 'Si `resultado` transporta datos de negocio, OpenAPI MUST declarar schema tipado (no `ApiEnvelope` generico).';
$md[] = 'Regla Cursor: `.cursor/rules/openapi-resultado-estructura.mdc`.';
$md[] = '';

file_put_contents($outDir . '/openapi-revision-informe.md', implode(PHP_EOL, $md));

echo 'TOTAL=' . count($rows) . PHP_EOL;
echo 'STATS=' . json_encode($counts, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'TAGS=' . json_encode($tagCounts, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo 'OUT=' . $outDir . PHP_EOL;
