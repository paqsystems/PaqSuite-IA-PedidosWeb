# TR-SPEC-101-16 — Importación Excel (update — cantidad CC #10)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-16-proceso-excel-pedido-individual](../../101-PedidosWeb/TR-SPEC-101-16-proceso-excel-pedido-individual.md) / [TR-SPEC-101-16-importacion-excel-pantalla-carga](../../101-PedidosWeb/TR-SPEC-101-16-importacion-excel-pantalla-carga.md) |
| **HU update** | HU-101-029 / 030 / 043 updates |
| **SPEC update** | [SPEC-101-16-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel-update.md) · [SPEC-101-21-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. En `processRow` (individual y masivo) y pipeline pantalla carga: llamar helper de conversión con `equivalencia_ventas` del artículo y `CargaUnidadesVenta`.
2. Tests: fila Excel false/true; masivo smoke.

## AC técnicos

- [ ] **AC-CC10-T-X1:** Individual convierte.
- [ ] **AC-CC10-T-X2:** Pantalla carga convierte.
- [ ] **AC-CC10-T-X3:** Masivo convierte.
