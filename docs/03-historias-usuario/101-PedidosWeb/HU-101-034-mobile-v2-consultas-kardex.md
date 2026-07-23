# HU-101-034 — Mobile v2 consultas kardex

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-034-mobile-v2-consultas-kardex |
| **SPEC origen** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Should → Must (release `v1.2.1-mobile`) |
| **Release** | `v1.2.1-mobile` |
| **Estado** | **Especificado** — smoke Android emulador OK (F v2 2026-06-30) |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | HU-101-033; HU-101-015…018, 021…023, 028 (API web) |

## Narrativa

Como **usuario mobile**,  
quiero **todas las consultas MVP en formato kardex**,  
para **consultar información comercial sin grilla desktop**.

## Alcance incluido

Consultas en kardex (reutilizar `ConsultaKardexList` + mappers):

- Deuda (`/consultas/deuda`)
- Cheques (`/consultas/cheques`)
- Historial ventas (`/consultas/historial`)
- Detalle pedidos (`/pedidos/detalle`)
- Parámetros consulta (`/general/parametros`) — solo lectura
- Logs integración (`/integracion/logs`) — si permiso

Misma API SPEC-101-07; filtros colapsables; detalle al tap; sin pivot.

## Fuera de alcance

- Listados pedidos/presupuestos (HU-101-035).
- Export Excel mobile (evaluar TR; default fuera).
- Carga comprobantes.

## Criterios de aceptación

- [x] **CA-01:** Cada consulta listada accesible desde menú mobile v2 si permiso (Android emulador).
- [x] **CA-02:** Todas usan kardex, no DataGrid.
- [x] **CA-03:** Sin rutas pivot en native.
- [x] **CA-04:** Smoke por consulta crítica documentado ([D-VERIFICACION v2](../../04-tareas/101-PedidosWeb/D-VERIFICACION-101-17-mobile-v2.md)).

## Veredicto B1

**Lista para TR** (`TR-SPEC-101-17-mobile-v2-consultas`) — implementar tras cierre v1 (`v1.2.0-mobile`).
