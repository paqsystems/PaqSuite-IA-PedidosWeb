# SPEC-101-16 — Importación Excel individual (update — cantidad / CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-16-importacion-pedido-individual-excel-update |
| **SPEC base** | [SPEC-101-16-importacion-pedido-individual-excel](../../101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | [SPEC-101-10-update](SPEC-101-10-pantalla-carga-update.md); [SPEC-101-04-update](SPEC-101-04-services-pedidos-update.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

La columna Excel **`cantidad`** (plantilla `PEDIDO_INDIVIDUAL`) recibe el **mismo tratamiento** que el campo «cantidad» del modal de renglón según `CargaUnidadesVenta`.

## In scope (delta)

- No cambiar nombre de columna Excel ni i18n de plantilla (sigue llamándose cantidad).
- Al `processRow` / hidratar renglones: aplicar helper de conversión canónico (SPEC-101-10 / 101-04).
- Resultado: renglones con `cantidad` + `cantidad_venta` coherentes; importes desde `cantidad`.
- Aplica también a importación desde pantalla de carga (HU-101-030).

## Fuera de scope

- Nueva columna Excel `cantidad_venta` o `equivalencia_ventas`.
- Cambiar obligatorios de plantilla.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-029 | [HU-101-029-proceso-excel-pedido-individual-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual-update.md) |
| HU-101-030 | [HU-101-030-importacion-excel-pantalla-carga-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-030-importacion-excel-pantalla-carga-update.md) |
| TR | [TR-SPEC-101-16-importacion-excel-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-16-importacion-excel-update.md) |

## Definición de listo (update)

- [ ] Import individual + desde carga aplican conversión.
- [ ] Test de fila Excel false/true.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Excel cantidad = modal renglón |
| 30/07/2026 | Parte G | Volcado SPEC-update |
