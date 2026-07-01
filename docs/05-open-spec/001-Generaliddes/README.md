# SPEC 001 - Generaliddes

> **Nombre canónico:** carpeta `001-Generaliddes` (typo histórico conservado para trazabilidad de rutas).

Este bloque define las **especificaciones de configuración inicial** para PedidosWeb, derivadas de `docs/00-contexto/_mono`, con el criterio **un SPEC por subcarpeta**.

## Revisión A1 (2026-05-28)

| SPEC | Estado A1 | HU MVP | Notas |
|------|-----------|--------|--------|
| 001-01 … 001-05 | Apto con observaciones | Sí (01–02 generadas; 03–05 pendientes) | Parches A1 + decisiones en producto §8.1, §7, §5 |
| 001-02-admin | **D1 + F + E cerrado** (2026-06-19) | **No** en MVP portal | 4 HU + 4 TR; activación vía `ADMIN_SECURITY_UI_ENABLED` |
| 001-06, 001-09 | Documental | **No** en MVP portal | Preparación; sin HU en primer release |
| 001-07 | **C1 cerrado** (2026-06-16) | **No** en MVP portal | 4 HU + 4 TR; epic importar Excel posterior |
| 001-08 | **D1 + F cerrado** (2026-06-11) | **No** en MVP portal | 4 HU + 4 TR implementadas; activación vía flags |
| 001-10 | **C1 cerrado** (2026-06-21) | **No** en MVP portal | 5 HU + 5 TR; epic Chat Asistente IA posterior — [F-GEN-10-cierre-c1](../../04-tareas/001-Generaliddes/F-GEN-10-cierre-c1.md) |
| 001-11 | **A1 + B1 + C1 cerrados** (2026-06-30) | **No** en MVP portal web | Mobile Capacitor — v1 `v1.2.0-mobile` — **autorizada Parte D** — [F-GEN-11-cierre-c1](../../04-tareas/001-Generaliddes/F-GEN-11-cierre-c1.md) |

**Fuente de producto compartida:** `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md`

**Contrato API (transversal):** [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md) — envelope `error` / `respuesta` / `resultado` en todos los endpoints `/api/v1/*`.

## Orden Fase 0 (implementación)

1. `SPEC-001-05` — tenancy MONO  
2. `SPEC-001-02` — acceso y seguridad  
3. `SPEC-001-01` — experiencia base  
4. `SPEC-001-03` — UI transversal  
5. `SPEC-001-04` — configuración global  

## Índice de SPEC

1. `SPEC-001-01-experiencia-base.md`
2. `SPEC-001-02-acceso-y-seguridad.md`
2b. `SPEC-001-02-admin-mantenimiento-roles-permisos.md` — post-MVP; ABM roles/permisos/atributos
3. `SPEC-001-03-ui-transversal.md`
4. `SPEC-001-04-configuracion-global.md`
5. `SPEC-001-05-variantes-y-alcance.md`
6. `SPEC-001-06-emision.md` — documental
7. `SPEC-001-07-importar-excel.md` — documental (fuera MVP)
8. `SPEC-001-08-pivots.md` — D1 implementado (fuera MVP portal)
9. `SPEC-001-09-tareas-programadas.md` — documental
10. `SPEC-001-10-chat-asistente-ia.md`
11. [`SPEC-001-11-mobile-capacitor.md`](SPEC-001-11-mobile-capacitor.md) — Capacitor MONO; release `v1.2.0-mobile`; login tenant-first

## Regla de alcance

- Estos SPEC cubren **configuración inicial y lineamientos base**.
- No reemplazan los SPEC funcionales de módulos core (`SPEC-101-xx` en `101-PedidosWeb`).
- Las HU/TR de ejecución deben referenciar el SPEC correspondiente de este índice.

## HU relacionadas

Índice: `docs/03-historias-usuario/001-Generaliddes/README.md`
