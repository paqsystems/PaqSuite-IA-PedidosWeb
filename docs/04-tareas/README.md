# Tareas técnicas (TR) — PedidosWeb

Carpeta de **TR** derivadas del flujo OpenSpec **parte C** (`openspec-03-TR-desde-SPEC-y-HU`).

## Convención de rutas

| Bloque | Carpeta TR | Prefijo archivo |
|--------|------------|-----------------|
| Generalidades | `docs/04-tareas/001-Generaliddes/` | `TR-GEN-01-*`, `TR-GEN-02-*` (14 TR; ver README) |
| PedidosWeb MVP | `docs/04-tareas/101-PedidosWeb/` | `TR-SPEC-101-xx-*.md` |

## Documentos normativos (obligatorios)

| Archivo | Uso |
|---------|-----|
| [`_PLANTILLA-TR-SLICE.md`](_PLANTILLA-TR-SLICE.md) | Plantilla base al generar **cualquier** TR de slice |
| [`_NORMAS-TRANSVERSALES-TR.md`](_NORMAS-TRANSVERSALES-TR.md) | Normas que **toda** TR debe cumplir (OpenAPI, seguridad, envelope, tenancy) |
| [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md) | Contrato envelope MONO (`error` / `respuesta` / `resultado`) — fuente canónica |

Al ejecutar **parte C**, copiar la plantilla, completar placeholders y verificar el checklist de normas transversales antes de marcar la TR como lista para **parte D**.

## Referencias

- Formato general: `.cursor/rules/base/00-arquitectura/04-user-story-to-task-breakdown.md`
- Gobernanza OpenSpec: `.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md`
- HU políticas/OpenAPI: `docs/03-historias-usuario/001-Generaliddes/HU-GEN-02-politicas-endpoints.md`
- SPEC seguridad: `docs/05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md`
