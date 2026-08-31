# HU-101-004 — Selección cliente (update-01 — saldo deuda)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-004-seleccion-cliente](../../101-PedidosWeb/HU-101-004-seleccion-cliente.md) |
| **SPEC update** | [SPEC-101-10-pantalla-carga-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Criterios de aceptación

- [ ] **CA-CC12-D01:** Tras elegir cliente, se muestra el saldo de deuda con colores (verde ≤0; negro >0 sin vencidos; rojo con vencidos).
- [ ] **CA-CC12-D02:** Si saldo ≠ 0, ícono abre modal con grilla de comprobantes + total, sin export/layouts/pivot.
- [ ] **CA-CC12-D03:** Respeta visibilidad GEN-02 del cliente.
