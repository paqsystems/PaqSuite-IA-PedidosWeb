<?php

declare(strict_types=1);

/**
 * GEN-37 — semántica de campos para emission-designer-schemas (PedidosWeb).
 * Reutiliza i18n de consultas (5 locales); no catálogo paralelo ni sidecar hardcode.
 *
 * @return array<string, string>
 */
function emissionDesignerEsLocaleCatalog(string $frontendRoot): array
{
    $path = $frontendRoot.'/src/locales/es.json';
    if (! is_readable($path)) {
        return [];
    }
    $decoded = json_decode((string) file_get_contents($path), true);

    return is_array($decoded) ? $decoded : [];
}

/**
 * captionKey distinto de consultas.column.{name} cuando la grilla usa otra clave.
 *
 * @return array<string, string>
 */
function emissionDesignerCaptionKeyOverrides(): array
{
    return [
        'nombreCliente' => 'consultas.column.nombre',
        'porcBonif' => 'consultas.detalle.column.descuento',
        'codArticulo' => 'consultas.detalle.column.codArticulo',
        'descripcionArticulo' => 'consultas.detalle.column.descripcionArticulo',
        'especial' => 'consultas.detalle.column.especial',
        'cantidadVenta' => 'consultas.detalle.column.cantidadVenta',
        'precioLista' => 'consultas.detalle.column.precioLista',
        'precioNeto' => 'consultas.detalle.column.precioNeto',
        'importeBruto' => 'consultas.detalle.column.importeBruto',
        'importeNeto' => 'consultas.detalle.column.importeNeto',
        'ivaNeto' => 'consultas.detalle.column.ivaNeto',
        'importeNetoConIva' => 'consultas.detalle.column.importeNetoConIva',
        'renglon' => 'consultas.detalle.column.renglon',
        'codTranspor' => 'consultas.column.codTransp',
    ];
}

/**
 * @param array<string, string> $esCatalog
 * @return array{name: string, type: string, label: string, captionKey?: string}
 */
function enrichEmissionDesignerField(array $field, array $esCatalog): array
{
    $name = (string) $field['name'];
    $type = (string) $field['type'];
    $overrides = emissionDesignerCaptionKeyOverrides();

    $captionKey = $overrides[$name] ?? null;
    if ($captionKey === null) {
        $columnKey = 'consultas.column.'.$name;
        if (isset($esCatalog[$columnKey]) && $esCatalog[$columnKey] !== '') {
            $captionKey = $columnKey;
        } else {
            $detalleKey = 'consultas.detalle.column.'.$name;
            if (isset($esCatalog[$detalleKey]) && $esCatalog[$detalleKey] !== '') {
                $captionKey = $detalleKey;
            }
        }
    }

    $label = null;
    if ($captionKey !== null && isset($esCatalog[$captionKey])) {
        $label = $esCatalog[$captionKey];
    }
    if ($label === null) {
        $columnKey = 'consultas.column.'.$name;
        $label = $esCatalog[$columnKey] ?? null;
    }
    if ($label === null) {
        $detalleKey = 'consultas.detalle.column.'.$name;
        $label = $esCatalog[$detalleKey] ?? null;
    }
    if ($label === null || trim($label) === '') {
        throw new InvalidArgumentException("GEN-37: missing label for emission field \"{$name}\"");
    }

    $enriched = [
        'name' => $name,
        'type' => $type,
        'label' => trim($label),
    ];
    if ($captionKey !== null && $captionKey !== '') {
        $enriched['captionKey'] = $captionKey;
    }

    return $enriched;
}