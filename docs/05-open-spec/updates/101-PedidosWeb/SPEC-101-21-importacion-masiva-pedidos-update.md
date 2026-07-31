# SPEC-101-21 — Importación masiva (update — cantidad / CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-21-importacion-masiva-pedidos-update |
| **SPEC base** | [SPEC-101-21-importacion-masiva-pedidos](../../101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | [SPEC-101-16-update](SPEC-101-16-importacion-pedido-individual-excel-update.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

En importación **masiva** (`PEDIDO_MASIVO`), la columna `cantidad` usa la misma semántica que la importación individual / modal de renglón (`CargaUnidadesVenta`).

## In scope (delta)

- Reutilizar helper/pipeline de SPEC-101-16-update (sin duplicar reglas).
- Grilla de trabajo y grabación del lote: renglones con ambos campos materializados.

## Fuera de scope

- Cambios de agrupación, permisos o UX de la pantalla masiva.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-043 | [HU-101-043-proceso-excel-pedido-masivo-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo-update.md) |
| TR | Cubierto por [TR-SPEC-101-16-importacion-excel-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-16-importacion-excel-update.md) (+ nota masiva) |

## Definición de listo (update)

- [ ] Masiva aplica misma conversión que individual.
- [ ] Smoke/test mínimo de fila masiva.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Masiva: misma cantidad |
| 30/07/2026 | Parte G | Volcado SPEC-update |
