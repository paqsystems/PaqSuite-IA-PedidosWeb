# Cierre C — SPEC-101-22 — Importación maestros ERP

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-21 |
| **Épica** | APIs importación maestros (PIAPEDWEB-34) |
| **B1** | [F-101-22-cierre-b1](F-101-22-cierre-b1-importacion-maestros-erp.md) |
| **Veredicto C** | **Generada** — 3 TR |
| **C1** | [F-101-22-cierre-c1](F-101-22-cierre-c1-importacion-maestros-erp.md) — **Apto con observaciones** |

## TR generadas

| ID | Archivo | HU | Orden D1 |
|----|---------|-----|----------|
| 22a | [TR-SPEC-101-22-integracion-maestros-plataforma](TR-SPEC-101-22-integracion-maestros-plataforma.md) | 046 | 1 |
| 22b | [TR-SPEC-101-22-integracion-maestros-catalogo-articulos](TR-SPEC-101-22-integracion-maestros-catalogo-articulos.md) | 047 | 2 |
| 22c | [TR-SPEC-101-22-integracion-maestros-clientes-parametros](TR-SPEC-101-22-integracion-maestros-clientes-parametros.md) | 048 | 3 |

## Temas para C1 (revisión TR)

| Tema | TR | Nota |
|------|-----|------|
| Firma exacta parámetros SP | 22b/22c | Definir en implementación; contrato lógico OK |
| CI sin SQL Server + SP | todas | `@group integracion` + skip documentado |
| Whitelist parámetros: PHP array vs config generado | 22c | Preferir array versionado en repo |
| Gateway agente | 22a | Misma firma SP; fuera D1 PedidosWeb si no hay gateway local |

## Cobertura HU → TR

| HU | TR |
|----|-----|
| 046 | 22a |
| 047 | 22b |
| 048 | 22c |

## Siguiente paso

**D1** — [`ai-planning-mode`](../../.cursor/skills/ai-planning-mode/SKILL.md) en orden **22a → 22b → 22c**.
