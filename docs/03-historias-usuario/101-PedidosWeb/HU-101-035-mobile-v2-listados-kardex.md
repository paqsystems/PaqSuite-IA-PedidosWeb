# HU-101-035 — Mobile v2 listados kardex

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-035-mobile-v2-listados-kardex |
| **SPEC origen** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Should → Must (release `v1.2.1-mobile`) |
| **Release** | `v1.2.1-mobile` |
| **Estado** | **Especificado** — smoke Android emulador OK (F v2 2026-06-30) |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | HU-101-034; HU-101-015, 016, 017, 014 (consultas listado) |

## Narrativa

Como **usuario mobile**,  
quiero **ver listados de pedidos y presupuestos en kardex**,  
para **seguir comprobantes sin la grilla web completa**.

## Alcance incluido

Listados kardex:

- Pedidos ingresados (`/pedidos/ingresados`)
- Pedidos pendientes (`/pedidos/pendientes`)
- Presupuestos ingresados (`/presupuestos/ingresados`)
- Tratativas presupuestos (`/presupuestos/tratativas`) — si Should producto aplica

Acciones reducidas vs web (TR define: ver detalle; copiar/editar solo si UX mobile lo permite).

## Fuera de alcance

- Acciones masivas Excel.
- Eliminación/edición completa si no cabe en UX mobile (TR acota).

## Criterios de aceptación

- [x] **CA-01:** Listados en kardex con paginación (cliente).
- [x] **CA-02:** Permisos y visibilidad por perfil igual web.
- [x] **CA-03:** Tap → detalle popup read-only (acciones comprobante en v3).

## Veredicto B1

**Lista para TR** (`TR-SPEC-101-17-mobile-v2-listados`).
