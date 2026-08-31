# SPEC-001-04 — Configuración global (update-01 — IncluyeArticulosNoStockeables)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-001-04-configuracion-global-update-01 |
| **SPEC base** | [SPEC-001-04-configuracion-global](../../001-Generaliddes/SPEC-001-04-configuracion-global.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-28 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12 — **28/08/2026** |
| **Dependencias** | Seed `PQ_PARAMETROS_GRAL.PedidosWeb`; [SPEC-101-02-modelos-update-02](../101-PedidosWeb/SPEC-101-02-modelos-update-02.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Incorporar el parámetro booleano **`IncluyeArticulosNoStockeables`** en `PQ_parametros_gral` (programa PedidosWeb). En PedidosWeb es **solo informativo** (consulta de parámetros / seed); su uso operativo está en la aplicación que alimenta el archivo de artículos.

## In scope (delta)

| Campo | Valor |
|-------|--------|
| **Clave** | `IncluyeArticulosNoStockeables` |
| **tipo_valor** | `B` |
| **Default** | `false` |
| **Programa** | `PedidosWeb` |
| **Administración** | ERP / herramientas internas (sin ABM web) |
| **Seed deploy** | JSON + INSERT idempotente en `paqsuite:seed-deploy` |
| **CAPTION sugerido** | Incluye artículos no stockeables |
| **TOOLTIP sugerido** | Informativo para la integración que alimenta artículos. PedidosWeb no cambia su lógica de carga según este flag; la exclusión de stock se basa en el atributo del artículo (SPEC-101-02-update-02). |

## Fuera de scope

- ABM web del parámetro.
- Usar este flag como filtro runtime en PedidosWeb (el filtro de stock/listbox usa el atributo del artículo).

## HU / TR derivadas

| Artefacto | Ruta |
|-----------|------|
| HU-GEN-04 | [HU-GEN-04-consulta-parametros-update-01](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-04-consulta-parametros-update-01.md) |
| TR-GEN-04 | [TR-GEN-04-consulta-parametros-update-01](../../../04-tareas/updates/001-Generaliddes/TR-GEN-04-consulta-parametros-update-01.md) |

## Definición de listo (update)

- [ ] Clave en seed + INSERT idempotente.
- [ ] Visible en consulta parámetros (Sí/No).
- [ ] Lectura runtime opcional documentada (informativo).

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Parámetro `IncluyeArticulosNoStockeables` |
| 28/08/2026 | Parte G | Volcado SPEC-update-01 |
