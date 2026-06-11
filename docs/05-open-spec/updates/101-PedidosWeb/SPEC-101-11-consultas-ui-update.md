# SPEC-101-11 — Consultas UI (update — pivot informes)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-11-consultas-ui-update |
| **SPEC base** | [SPEC-101-11-consultas-ui](../../101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-06-11 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #4 — **10/06/2026** |
| **Dependencias** | [SPEC-001-08-pivots](../../001-Generaliddes/SPEC-001-08-pivots.md) (B1 cerrado); HU-GEN-08-* / TR-GEN-08-* |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Extender las pantallas de consulta **informe** con alternancia **grilla / pivot** (PivotGrid DevExtreme), reutilizando el patrón validado en **Historial ventas** (`ConsultaGrillaPivotShell`, catálogo `pq_pivots_*`, diseños `pq_pivots_config`).

## In scope (delta)

| Informe | Ruta UI | Proceso grilla | `consulta_id` pivot (propuesto) |
|---------|---------|----------------|----------------------------------|
| Detalle de pedidos | `/pedidos/detalle` | `pw_detallepedidos` | `CONSULTA_DETALLE_PEDIDOS` |
| Deudas | `/consultas/deuda` | `pw_deuda` | `CONSULTA_DEUDA` |
| Cheques | `/consultas/cheques` | `pw_cheques` | `CONSULTA_CHEQUES` |
| Stock | `/consultas/stock` | `pw_stock` | `CONSULTA_STOCK` |

Por cada informe:

1. Vista inicial **grilla** (comportamiento operativo actual sin cambios).
2. Toggle **grilla / pivot** cuando `PIVOTS_ENABLED` y metadata indica `pivot_habilitado`.
3. `tipoProceso="informe"` en shell pivot (paridad menú Informes).
4. Paridad GEN-08 en bloque pivot: diseños guardados, plantilla inicial, Actualizar (`pivotRefresh`), export Excel básico y tabla dinámica.
5. `pivotBase` útil por consulta (ver tabla abajo).
6. Catálogo `pq_pivots_campos` alineado a columnas API/grilla de cada informe (paridad Historial ventas).
7. Totalización por tipo de dato (regla transversal SPEC-001-08 / `PivotCampoAggregationPolicy`).

### pivotBase sugerido

| Informe | Filas / dimensiones | Valores / métricas |
|---------|---------------------|--------------------|
| Detalle de pedidos | `codCliente`, `razonSocial`, `codArticulo` | `cantidad`, `precioNeto`, importes |
| Deudas | `codCliente`, `razonSocial`, `tipo` | `saldo` (sum) |
| Cheques | `codCliente`, `banco`, `estado` | `importe` (sum) |
| Stock | `codArticulo`, `descripcion` | `stock`, `comprometido`, `disponibleNeto` |

## Fuera de scope (sin cambio)

- Pedidos ingresados, pendientes, presupuestos (consultas cabecera con acciones).
- Historial ventas (ya adoptado como piloto transversal `CONSULTA_PILOTO_PIVOT`).
- PDF; mutaciones API de consultas (SPEC-101-07 sin cambio de contrato listado).
- Nuevo motor pivot (cubierto por SPEC-001-08 / TR-GEN-08).

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-028 | [HU-101-028-consulta-detalle-pedidos-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos-update.md) |
| HU-101-021 | [HU-101-021-consulta-deuda-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-021-consulta-deuda-update.md) |
| HU-101-022 | [HU-101-022-consulta-cheques-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-022-consulta-cheques-update.md) |
| HU-101-018 | [HU-101-018-consulta-stock-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-018-consulta-stock-update.md) |
| TR UI + catálogo | [TR-SPEC-101-11-consultas-ui-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md) |

## Definición de listo (update)

- [ ] Cuatro pantallas migradas a `ConsultaGrillaPivotShell` con toggle operativo.
- [ ] Cuatro filas en `pq_pivots_consultas` + campos en `pq_pivots_campos` (seeder).
- [ ] E2E mínimo: 1 informe con toggle grilla/pivot + menú agregación (patrón historial).
- [ ] Resto de consultas SPEC-101-11 sin pivot habilitado.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 10/06/2026 | CC PQ #4 | Adopción pivot en Detalle, Deudas, Cheques, Stock |
| 11/06/2026 | Parte G | Volcado SPEC-update |
