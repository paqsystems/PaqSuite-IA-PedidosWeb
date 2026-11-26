# Adopción GEN-37 — emission-designer-schemas (PedidosWeb)

SoT Framework: `SPEC-001-37` · `docs/02-producto/37-catalogo-semantico-campos-dataset.md`.

## Qué hizo este host

- Ocho JSON en `backend/database/emission-designer-schemas/*.json` con `fields[].label` (Must).
- `captionKey` reutiliza `consultas.column.*` / `consultas.detalle.column.*` (5 locales ya en `frontend/src/locales/*.json`).
- Generación: `emission-field-semantics.php` + `php scripts/dev/generate-emission-designer-schemas.php`.
- Sin catálogo paralelo; sin `description`/`synonyms` (no hay IA diseño reportes en PedidosWeb v1).

## Checklist deploy

1. `php scripts/dev/generate-emission-designer-schemas.php`
2. `php scripts/dev/generate-pedidosweb-host-manifest.php` → `database/manifest/pedidosweb-{pin laravel-core}.json`
3. Alinear `PAQSUITE_INSTALLATION_HOST_MANIFEST` / `hostManifestPath` al manifiesto nuevo si aplica.
4. `php artisan paqsuite:installation:update --cliente=… --proyecto=… --non-interactive` (o sync local GEN-34).
5. Opcional: `php scripts/dev/deploy-emission-designer-schemas.php` si hace falta upsert directo.
6. Smoke: diseñador → Field List muestra captions (`label` / i18n), no solo `name`.
7. Tests: `php artisan test --filter=EmissionDesignerSchemaGen37Test`

## CC PQ #19

Residual GEN-15; este bloque cubre solo contenido semántico GEN-37. Ver [SPEC-101-gen37-adopcion](../05-open-spec/updates/101-PedidosWeb/SPEC-101-gen37-adopcion-catalogo-semantico-emisiones.md).