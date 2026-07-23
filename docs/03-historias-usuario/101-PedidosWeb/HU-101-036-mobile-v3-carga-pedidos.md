# HU-101-036 — Mobile v3 carga pedidos

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-036-mobile-v3-carga-pedidos |
| **SPEC origen** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Must (release `v1.2.2-mobile`) |
| **Release** | `v1.2.2-mobile` |
| **Estado** | **Implementado (F v3)** |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | HU-101-035; HU-101-004…010 (carga web); [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |

## Narrativa

Como **vendedor o cliente en app mobile**,  
quiero **cargar pedidos y presupuestos con UX mobile dedicada**,  
para **operar en campo sin la pantalla desktop de 1300+ líneas**.

## Alcance incluido

- Ruta `/pedidos/carga` mobile: UX **wizard/cards** (no paridad DataGrid desktop).
- Grabar pedido / presupuesto (estados 0 / 99) vía API existente.
- Selección cliente, renglones simplificados, totales.
- Integración menú y permisos carga.
- i18n + testids estables.

## Fuera de alcance

- Importación Excel (HU-101-030).
- Paridad total SPEC-101-10 desktop.
- Pivot, admin.

## Reglas de negocio

1. Validaciones grabado iguales backend (HU-101-009/010).
2. CC PQ validaciones aplican según TR.
3. Rediseño UX obligatorio; no wrapper directo `PedidosCargaPage` desktop.

## Criterios de aceptación

- [x] **CA-01:** Crear pedido nuevo mobile smoke E2E.
- [ ] **CA-02:** Crear presupuesto mobile smoke (pendiente formal).
- [x] **CA-03:** Perfiles C/V/S según reglas web.
- [ ] **CA-04:** Tag release `v1.2.2-mobile` (tras smoke iOS + físico).

## Veredicto B1

**Lista para TR** (`TR-SPEC-101-17-mobile-v3-carga`) — tras v1.2.1-mobile.
