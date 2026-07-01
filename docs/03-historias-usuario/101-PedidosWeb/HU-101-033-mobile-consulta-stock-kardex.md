# HU-101-033 — Mobile consulta stock kardex

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-033-mobile-consulta-stock-kardex |
| **SPEC origen** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Producto** | [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Must |
| **Release** | `v1.2.0-mobile` |
| **Estado** | **Especificado** — smoke Android emulador OK (F v1 2026-06-30) |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | HU-101-032; [HU-101-018](HU-101-018-consulta-stock.md) (API); HU-GEN-11-mobile-shell-exclusiones |

## Narrativa

Como **usuario comercial en app mobile**,  
quiero **consultar stock en una lista de tarjetas (kardex)**,  
para **ver disponibilidad sin una grilla desktop**.

## Alcance incluido

- Ruta `/consultas/stock` en native con vista **kardex** (no DataGrid web).
- API: `GET /api/v1/consultas/stock` sin cambios backend.
- Tarjeta: `codArticulo`, `descripcion`, `disponibleNeto`, `stock`.
- Tap → popup detalle solo lectura (resto campos API §6 producto).
- Filtro `q` (aplicar con **Enter**); paginación; botón Actualizar (`grid.refresh` i18n).
- Resumen «Mostrando X de Y» (`consultas.resultSummary`).
- Carátula `fecha_proceso` bajo título (paridad web).
- Permiso `pw_consultastock`; 403 mensaje i18n.
- Menú v1: único ítem operativo consulta stock.
- Componente reutilizable `ConsultaKardexList` (base v2).

## Fuera de alcance

- Pivot stock.
- Export Excel.
- DataGrid desktop en native.

## Reglas de negocio

1. Fórmulas disponible neto: [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md).
2. Sin filtro por cliente visible.
3. Formato numérico `#,##0.00` en tarjetas.

## Criterios de aceptación

- [x] **CA-01:** Lista kardex con ≥1 artículo tras login con permiso (Android emulador).
- [x] **CA-02:** Tap abre detalle read-only.
- [x] **CA-03:** Filtro `q` reduce resultados (Enter).
- [x] **CA-04:** Actualizar re-fetch servidor.
- [ ] **CA-05:** Usuario sin permiso → 403 UI clara (no probado en smoke).
- [x] **CA-06:** `data-testid` página `page-consulta-stock-mobile`.
- [x] **CA-07:** Sin toggle grilla/pivot.

## Veredicto B1

**Lista para TR** (`TR-SPEC-101-17-mobile-v1-stock-kardex`).
