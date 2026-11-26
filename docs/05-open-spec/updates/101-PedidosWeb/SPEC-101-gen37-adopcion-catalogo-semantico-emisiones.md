# SPEC-101-GEN37 — Adopción catálogo semántico en emission-designer-schemas (PedidosWeb)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-GEN37 |
| **GEN Framework** | **GEN-37** — SoT PaqSuite-IA-FRAMEWORK `SPEC-001-37-catalogo-semantico-campos-dataset.md` |
| **Relación CC** | Control Calidad PQ **#19** (residual GEN-15 diseñador / emisión) |
| **Estado** | Implementando (adopción host contenido) |
| **Artefacto** | `backend/database/emission-designer-schemas/{processCode}.json` (mismo que GEN-15) |

## Objetivo

Completar la semántica GEN-37 en los **ocho** `processCode` emisibles con diseñador DX de PedidosWeb, sin fork de contrato ni catálogo paralelo.

## Alcance

1. `fields[].label` en todos los campos (Must).
2. `captionKey` cuando exista clave de producto (`consultas.column.*` / `consultas.detalle.column.*`) — i18n ya en 5 locales.
3. Mantener alineación `fields[].name` ↔ `.port-names.json` (V-EM-04).
4. Regenerar manifiesto host + validate / `installation:update` / smoke Field List.
5. **Sin** IA diseño de reportes en este host → `description` / `synonyms` fuera de alcance.

## Fuera de alcance

- GEN-14 Excel captions.
- Cambiar wire names del dataset.
- Motor semántico / sidecar hardcodeado en el host.

## Trazabilidad

- HU residual: [HU-101-055-emision-reportes-informes](../../03-historias-usuario/101-PedidosWeb/HU-101-055-emision-reportes-informes.md)
- Update emisión: [SPEC-001-06-emision-update](../001-Generaliddes/SPEC-001-06-emision-update.md)

## Verificación

- `php scripts/dev/generate-emission-designer-schemas.php`
- `php scripts/dev/generate-pedidosweb-host-manifest.php`
- `php artisan test --filter=EmissionDesignerSchemaGen37Test`
- Smoke: diseñador → Field List con captions (no solo `name`).