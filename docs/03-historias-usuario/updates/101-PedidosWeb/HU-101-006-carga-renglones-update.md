# HU-101-006 — Carga de renglones (update — CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-006-carga-renglones](../../101-PedidosWeb/HU-101-006-carga-renglones.md) |
| **SPEC update** | [SPEC-101-10-pantalla-carga-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario que carga un comprobante**, quiero **ingresar una sola cantidad** en el renglón interpretada según `CargaUnidadesVenta`, para **trabajar en unidades de venta o de stock sin ver dos campos**.

## Criterios de aceptación

- [ ] **CA-CC10-R01:** Modal/alta renglón muestra un solo campo «cantidad».
- [ ] **CA-CC10-R02:** Con parámetro `false`: persiste `cantidad` ingresada y `cantidad_venta = cantidad / equiv` (`equiv`≤0 → 1).
- [ ] **CA-CC10-R03:** Con parámetro `true`: persiste `cantidad_venta` ingresada y `cantidad = cantidad_venta * equiv`; importes desde `cantidad`.
- [ ] **CA-CC10-R04:** Al editar, el valor mostrado corresponde al modo del parámetro.

## Reglas (delta)

1. Importes siempre desde `cantidad`.
2. `equivalencia_ventas` del artículo; 0/null → 1.
