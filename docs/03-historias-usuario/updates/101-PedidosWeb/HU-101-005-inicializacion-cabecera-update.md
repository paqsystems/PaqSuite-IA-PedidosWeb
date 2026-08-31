# HU-101-005 — Inicialización cabecera (update — deuda + listbox stockeable + leyendas snapshot)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-005-inicializacion-cabecera](../../101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) |
| **SPEC update** | [SPEC-101-10-pantalla-carga-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Criterios de aceptación

- [ ] **CA-CC12-C01:** Al inicializar cabecera con cliente, queda disponible el saldo/modal de deuda (coherente HU-101-004-update-01).
- [ ] **CA-CC12-C02:** Snapshot de leyendas 1–5 al abrir/inicializar para dirty tracking.
- [ ] **CA-CC12-C03:** En listbox de artículos, ítems `stockeable=false` no muestran stock/disponible.
