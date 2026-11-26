# Schemas diseñador emisiones (GEN-15 / GEN-34 / GEN-37)

JSON **canónico v1** desplegado en DIC vía manifiesto `kind: emissionDesignerSchema`.

- Un archivo `{processCode}.json` por proceso emisible con diseñador DX (8 informes PedidosWeb).
- Opcional `{processCode}.port-names.json` para validación V-EM-04 contra el puerto PHP.
- **GEN-37:** cada `fields[]` lleva `label` (Must) y `captionKey` opcional cuando existe clave en `consultas.column.*` / `consultas.detalle.column.*` (i18n producto, 5 locales). Sin catálogo semántico paralelo.

Regenerar schemas + port-names: `php scripts/dev/generate-emission-designer-schemas.php` (desde `backend/`).

Regenerar manifiesto: `php scripts/dev/generate-pedidosweb-host-manifest.php` (desde `backend/`).

Semántica: `emission-field-semantics.php` (reutiliza `frontend/src/locales/es.json` como fallback ES; no hardcode en sidecar).

SoT Framework: `SPEC-001-37` + `docs/02-producto/37-catalogo-semantico-campos-dataset.md` · adopción GEN-15 `adopcion-gen-15-emission-designer-schema-host.md`.

PedidosWeb sin IA diseño de reportes (GEN-15 update-4): **no** requiere `description` / `synonyms` en estos JSON.
