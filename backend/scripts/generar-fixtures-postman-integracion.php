<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$generatedAt = CarbonImmutable::now('America/Argentina/Buenos_Aires');
$outputRoot = storage_path('app/postman/integracion/'.$generatedAt->format('Ymd-His'));
$tenant = (string) env('TENANT_DEFAULT_CLIENT', 'quento');
$companyRows = DB::table('PQ_Empresa')
    ->select(['IDEmpresa', 'NombreEmpresa', 'NombreBD', 'Habilita'])
    ->where('Habilita', 1)
    ->orderBy('IDEmpresa')
    ->get();

if ($companyRows->isEmpty()) {
    throw new RuntimeException('No hay empresas activas en PQ_Empresa.');
}

$companyDirectory = preg_replace('/[^A-Za-z0-9._-]+/', '_', $tenant) ?: 'empresa';
$companyPath = $outputRoot.'/'.$companyDirectory;
if (! is_dir($companyPath) && ! mkdir($companyPath, 0777, true) && ! is_dir($companyPath)) {
    throw new RuntimeException('No se pudo crear la carpeta de salida.');
}

$fechaProceso = $generatedAt->toIso8601String();
$resources = [
    'maestros' => [
        'provincias' => [
            'table' => 'pq_pedidosweb_provincias',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'codProvin' => (string) $row->cod_provin,
                'nombrePro' => $row->nombre_pro,
            ],
            'key' => fn (array $item): string => $item['codProvin'],
        ],
        'condventa' => [
            'table' => 'pq_pedidosweb_condventa',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => ['codigo' => $row->codigo, 'descripcion' => $row->descripcion],
            'key' => fn (array $item): string => (string) $item['codigo'],
        ],
        'transportes' => [
            'table' => 'pq_pedidosweb_transportes',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => ['codigo' => $row->codigo, 'descripcion' => $row->descripcion],
            'key' => fn (array $item): string => (string) $item['codigo'],
        ],
        'perfiles' => [
            'table' => 'pq_pedidosweb_perfil',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => ['codPerfil' => $row->cod_perfil, 'descripcion' => $row->descripcion],
            'key' => fn (array $item): string => (string) $item['codPerfil'],
        ],
        'vendedores' => [
            'table' => 'pq_pedidosweb_vendedores',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'codVended' => $row->cod_vended,
                'nombre' => $row->nombre,
                'supervisor' => (bool) $row->supervisor,
                'mailSupervisor' => $row->mail_supervisor,
                'eMail' => $row->e_mail,
            ],
            'key' => fn (array $item): string => (string) $item['codVended'],
        ],
        'listaprecios' => [
            'table' => 'pq_pedidosweb_listaprecios',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'codLista' => (int) $row->cod_lista,
                'incluyeIva' => (bool) $row->incluye_iva,
                'moneda' => $row->moneda,
                'descripcion' => $row->descripcion,
                'decimales' => (int) $row->decimales,
            ],
            'key' => fn (array $item): string => (string) $item['codLista'],
        ],
        'escalas' => [
            'table' => 'pq_pedidosweb_escalas_cabecera',
            'mode' => 'snapshot',
            'map' => function (object $row): array {
                $values = DB::table('pq_pedidosweb_escalas_detalle')
                    ->where('cod_escala', $row->cod_escala)
                    ->orderBy('cod_valor')
                    ->get()
                    ->map(fn (object $value): array => [
                        'codValor' => $value->cod_valor,
                        'descValor' => $value->desc_valor,
                    ])
                    ->all();

                return [
                    'codEscala' => $row->cod_escala,
                    'descripcion' => $row->descrip_es,
                    'nroEscala' => $row->nro_escala,
                    'valores' => $values,
                ];
            },
            'key' => fn (array $item): string => (string) $item['codEscala'],
        ],
        'articulos' => [
            'table' => 'pq_pedidosweb_articulos',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'codigo' => $row->codigo,
                'descripcion' => $row->descripcion,
                'bonificacion' => (float) $row->bonificacion,
                'usaEsc' => $row->usa_esc,
                'base' => (float) $row->base,
                'valor1' => (float) $row->valor1,
                'valor2' => (float) $row->valor2,
                'porcIva' => (float) $row->porc_iva,
                'equivalenciaVentas' => (float) $row->equivalencia_ventas,
                'stockeable' => (bool) $row->stockeable,
                'especial' => (bool) $row->especial,
            ],
            'key' => fn (array $item): string => (string) $item['codigo'],
        ],
        'listaprecios-articulos' => [
            'table' => 'pq_pedidosweb_listaprecios_articulos',
            'mode' => 'delta',
            'map' => fn (object $row): array => [
                'codLista' => (int) $row->cod_lista,
                'codArticulo' => $row->cod_articulo,
                'precio' => (float) $row->precio,
            ],
            'key' => fn (array $item): string => $item['codLista'].'|'.$item['codArticulo'],
        ],
        'descuentocantidad' => [
            'table' => 'pq_pedidosweb_descuentocantidad',
            'mode' => 'delta',
            'map' => fn (object $row): array => [
                'codArticu' => $row->cod_articu,
                'cantidad' => (float) $row->cantidad,
                'descuento' => (float) $row->descuento,
            ],
            'key' => fn (array $item): string => $item['codArticu'].'|'.$item['cantidad'],
        ],
        'clientes' => [
            'table' => 'pq_pedidosweb_clientes',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'codClient' => $row->cod_client,
                'nombre' => $row->nombre,
                'fantasia' => $row->fantasia,
                'codVended' => $row->cod_vended,
                'listaPrecios' => $row->lista_precios !== null ? (int) $row->lista_precios : null,
                'codCondvta' => $row->cod_condvta !== null ? (int) $row->cod_condvta : null,
                'codTranspor' => $row->cod_transpor,
                'bonificacion' => $row->bonificacion !== null ? (float) $row->bonificacion : null,
                'nivel' => $row->nivel !== null ? (int) $row->nivel : null,
                'expreso' => $row->expreso,
                'expresoDire' => $row->expreso_dire,
                'eMail' => $row->e_mail,
                'razonSoci' => $row->razon_soci,
                'leyenda1' => $row->leyenda_1,
                'leyenda2' => $row->leyenda_2,
                'leyenda3' => $row->leyenda_3,
                'leyenda4' => $row->leyenda_4,
                'leyenda5' => $row->leyenda_5,
            ],
            'key' => fn (array $item): string => (string) $item['codClient'],
        ],
        'clientes-direcciones' => [
            'table' => 'pq_pedidosweb_clientesde',
            'mode' => 'delta',
            'map' => fn (object $row): array => [
                'codClient' => $row->cod_client,
                'idDe' => (int) $row->id_de,
                'codDe' => $row->cod_DE,
                'direccion' => $row->direccion,
                'localidad' => $row->localidad,
                'cPostal' => $row->c_postal,
                'codProvin' => $row->cod_provin,
                'habitual' => $row->habitual,
            ],
            'key' => fn (array $item): string => $item['codClient'].'|'.$item['idDe'],
        ],
        'clientes-contactos' => [
            'table' => 'pq_pedidosweb_clientescontactos',
            'mode' => 'delta',
            'map' => fn (object $row): array => [
                'codClient' => $row->cod_client,
                'codContacto' => $row->cod_contacto,
                'nombre' => $row->nombre,
                'telefono' => $row->telefono,
                'mail' => $row->mail,
            ],
            'key' => fn (array $item): string => $item['codClient'].'|'.$item['codContacto'],
        ],
        'parametros' => [
            'table' => 'PQ_PARAMETROS_GRAL',
            'mode' => 'delta',
            'map' => function (object $row): ?array {
                $types = ['B' => 'valorBool', 'I' => 'valorInt', 'N' => 'valorDecimal', 'S' => 'valorString', 'T' => 'valorText', 'D' => 'valorDateTime'];
                $field = $types[(string) $row->tipo_valor] ?? null;
                if ($field === null || (string) $row->Programa !== 'PedidosWeb') {
                    return null;
                }
                $valueColumns = ['B' => 'Valor_Bool', 'I' => 'Valor_Int', 'N' => 'Valor_Decimal', 'S' => 'Valor_String', 'T' => 'Valor_Text', 'D' => 'Valor_DateTime'];
                $value = $row->{$valueColumns[$row->tipo_valor]};
                return ['clave' => $row->Clave, $field => $value];
            },
            'key' => fn (array $item): string => (string) $item['clave'],
        ],
    ],
    'transacciones' => [
        'stock' => [
            'table' => 'pq_pedidosweb_stock',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'codArticulo' => $row->cod_articulo,
                'stock' => (float) $row->stock,
                'comprometido' => (float) $row->comprometido,
            ],
            'key' => fn (array $item): string => (string) $item['codArticulo'],
        ],
        'deuda' => [
            'table' => 'pq_pedidosweb_deuda',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'codCliente' => $row->cod_cliente,
                'tComp' => $row->t_comp,
                'nComp' => $row->n_comp,
                'fechaEmis' => $row->fecha_emis,
                'fechaVto' => $row->fecha_vto,
                'saldo' => (float) $row->saldo,
            ],
            'key' => fn (array $item): string => implode('|', [$item['codCliente'], $item['tComp'], $item['nComp'], $item['fechaVto']]),
        ],
        'cheques' => [
            'table' => 'pq_pedidosweb_cheques',
            'mode' => 'snapshot',
            'map' => fn (object $row): array => [
                'interno' => $row->interno,
                'numero' => $row->numero,
                'codCliente' => $row->cod_client,
                'banco' => $row->banco,
                'fecha' => $row->fecha,
                'importe' => (float) $row->importe,
                'origen' => $row->origen,
                'estado' => $row->estado,
            ],
            'key' => fn (array $item): string => $item['interno'].'|'.$item['numero'],
        ],
        'historial-ventas' => [
            'table' => 'pq_pedidosweb_ventadetallada',
            'mode' => 'delta',
            'map' => fn (object $row): array => [
                'idGva53' => (int) $row->id_gva53,
                'codCliente' => $row->cod_cli,
                'razonSoci' => $row->razon_soci,
                'nRemito' => $row->n_remito,
                'tComp' => $row->t_comp,
                'nComp' => $row->n_comp,
                'fechaEmision' => $row->fecha_emi,
                'condVta' => $row->cond_vta,
                'porcDesc' => $row->porc_desc !== null ? (float) $row->porc_desc : null,
                'cotiz' => $row->cotiz !== null ? (float) $row->cotiz : null,
                'moneda' => $row->moneda,
                'totalComp' => $row->total_comp !== null ? (float) $row->total_comp : null,
                'codTransp' => $row->cod_transp,
                'nomTransp' => $row->nom_transp,
                'codArticu' => $row->cod_articu,
                'descripcio' => $row->descripcio,
                'codDep' => $row->cod_dep,
                'um' => $row->um,
                'cantidad' => $row->cantidad !== null ? (float) $row->cantidad : null,
                'precio' => $row->precio !== null ? (float) $row->precio : null,
                'totSImp' => $row->tot_s_imp !== null ? (float) $row->tot_s_imp : null,
                'nCompRem' => $row->n_comp_rem,
                'cantRem' => $row->cant_rem !== null ? (float) $row->cant_rem : null,
                'fechaRem' => $row->fecha_rem,
            ],
            'key' => fn (array $item): string => (string) $item['idGva53'],
        ],
    ],
];

$manifest = [
    'generatedAt' => $fechaProceso,
    'database' => [
        'connection' => (string) env('DB_CONNECTION'),
        'host' => (string) env('DB_HOST'),
        'database' => (string) env('DB_DATABASE'),
        'companyCatalogDatabase' => (string) ($companyRows->first()->NombreBD ?? ''),
        'readOnly' => true,
        'readIsolation' => 'READ UNCOMMITTED / configuración SQL Server del proyecto',
    ],
    'companies' => [],
    'resources' => [],
    'omissions' => [],
    'warnings' => [
        'El catálogo PQ_Empresa referencia Ankas_del_sur; la extracción usa la base configurada en backend/.env, confirmada por el usuario como backup DEMO coincidente.',
    ],
];

foreach ($companyRows as $company) {
    $manifest['companies'][] = [
        'tenant' => $tenant,
        'id' => (int) $company->IDEmpresa,
        'name' => $company->NombreEmpresa,
        'active' => true,
        'databaseFromCatalog' => $company->NombreBD,
    ];
}

foreach ($resources as $kind => $kindResources) {
    $kindPath = $companyPath.'/'.$kind;
    mkdir($kindPath, 0777, true);

    foreach ($kindResources as $resource => $definition) {
        $rows = DB::table($definition['table'])->get();
        $items = [];
        $keys = [];
        $omitted = [];

        foreach ($rows as $index => $row) {
            $item = $definition['map']($row);
            if ($item === null) {
                $omitted[] = ['index' => $index, 'reason' => 'fuera_del_contrato_o_whitelist'];
                continue;
            }
            $key = $definition['key']($item);
            if ($key === '' || isset($keys[$key])) {
                $omitted[] = ['index' => $index, 'key' => $key, 'reason' => 'clave_natural_invalida_o_duplicada'];
                continue;
            }
            $keys[$key] = true;
            $items[] = $item;
        }

        if ($items === []) {
            $manifest['omissions'][] = [
                'resource' => $resource,
                'kind' => $kind,
                'reason' => 'sin_filas_contractualmente_validas',
                'rowsRead' => $rows->count(),
                'rowsExported' => 0,
                'rowsOmitted' => count($omitted),
                'details' => $omitted,
            ];
            continue;
        }

        foreach (array_chunk($items, 1500) as $partIndex => $part) {
            $partNumber = str_pad((string) ($partIndex + 1), 3, '0', STR_PAD_LEFT);
            $fileName = $resource.'-'.$partNumber.'.json';
            $filePath = $kindPath.'/'.$fileName;
            $payload = [
                'modo' => $definition['mode'],
                'fechaProceso' => $fechaProceso,
                'origen' => 'postman-fixture-db',
                'items' => $part,
            ];
            file_put_contents($filePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);

            $manifest['resources'][] = [
                'tenant' => $tenant,
                'kind' => $kind,
                'resource' => $resource,
                'file' => $companyDirectory.'/'.$kind.'/'.$fileName,
                'mode' => $definition['mode'],
                'items' => count($part),
                'rowsRead' => $rows->count(),
                'rowsExported' => count($items),
                'rowsOmitted' => count($omitted),
                'sha256' => hash_file('sha256', $filePath),
            ];
        }

        if ($omitted !== []) {
            $manifest['omissions'][] = [
                'resource' => $resource,
                'kind' => $kind,
                'rowsRead' => $rows->count(),
                'rowsExported' => count($items),
                'rowsOmitted' => count($omitted),
                'details' => $omitted,
            ];
        }
    }
}

file_put_contents($outputRoot.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
echo json_encode([
    'output' => str_replace(base_path().'/', '', $outputRoot).'/',
    'companies' => count($manifest['companies']),
    'resources' => count(array_unique(array_column($manifest['resources'], 'resource'))),
    'files' => count($manifest['resources']),
    'omissions' => count($manifest['omissions']),
], JSON_UNESCAPED_UNICODE).PHP_EOL;
