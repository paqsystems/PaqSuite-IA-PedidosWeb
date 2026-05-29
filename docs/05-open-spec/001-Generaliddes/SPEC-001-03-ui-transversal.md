# SPEC-001-03 - UI transversal

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | `HU-GEN-03-*` (a generar tras este SPEC) |
| **Estado** | Pendiente |
| **Revisión A1** | Apto con observaciones (2026-05-28) |

## Objetivo

Definir patrones transversales de interfaz reutilizables en todo el producto (grillas, ABM, exportaciones, plantillas y pivots base).

## Estado de ejecución

Implementable en MVP para estándares de UI compartidos.

## Decisiones humanas

| Tema | Decisión |
|------|----------|
| Checklist UI transversal | Vive en **contexto** `grillas.md` + `patrones-abm.md`; este SPEC referencia checklist mínimo abajo |
| Pivots en MVP PedidosWeb | **Solo referencia documental** en release MVP; implementación avanzada → SPEC-001-08 (sin HU MVP) |

## Fuente de verdad de producto

- `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` — grillas/consultas de negocio
- `docs/05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md` — DevExtreme, consultas Must

## Fuentes (contexto MONO)

`docs/00-contexto/_mono/03-ui-transversal/` — `grillas.md`, `patrones-abm.md`, `exportaciones.md`, `plantillas.md`, `pivots.md`

## Alcance

- Estándares de grillas y listados (filtros, búsqueda, orden, paginación, exportación).
- Patrón ABM por defecto para mantenimientos.
- Reglas de exportación inicial.
- Criterios de plantillas reutilizables.
- Referencias de pivots (sin implementación pivot avanzada en MVP).

## Fuera de alcance

- Component library completa de diseño.
- Implementación total de pivots avanzados en portal MVP.
- Pantallas de negocio PedidosWeb (SPEC-101).

## Checklist UI transversal mínimo (reutilizable en HU)

- [ ] Grilla DevExtreme con paginación y orden por columna.
- [ ] Filtros y búsqueda acordes a `grillas.md`.
- [ ] Estados loading / empty / error en listados.
- [ ] Exportación según `exportaciones.md` cuando el proceso lo permita.
- [ ] Acciones ABM alineadas a permisos (`Permiso_Alta/Modi/Baja/Repo`).
- [ ] `data-testid` en acciones principales de grilla.
- [ ] Textos vía i18n (SPEC-001-01).
- [ ] Consistencia con tema activo (SPEC-001-01).

## Entregables verificables

- Guía operativa: contexto `grillas.md` + este checklist.
- Patrón ABM: `patrones-abm.md`.
- Consistencia: sin contradicción entre grilla y ABM en mismos procesos MVP.

## Criterios de aceptación medibles

- [ ] Checklist anterior citado en al menos una HU de PedidosWeb con grilla.
- [ ] Grillas y ABM sin reglas contradictorias entre sí (revisión cruzada contexto).

## Trazabilidad HU

| HU | Tema SPEC (a generar) |
|----|------------------------|
| HU-GEN-03-grillas-listados | Grillas, filtros, estados |
| HU-GEN-03-patron-abm | ABM transversal |
| HU-GEN-03-exportaciones | Exportación |
