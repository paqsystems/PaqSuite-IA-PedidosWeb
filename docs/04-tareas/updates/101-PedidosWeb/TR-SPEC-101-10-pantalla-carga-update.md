# TR-SPEC-101-10 — Pantalla carga (update CC PQ #5)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-10-pantalla-carga](../../101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md) |
| **HU update** | [HU-101-005-inicializacion-cabecera-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-005-inicializacion-cabecera-update.md) |
| **SPEC update** | [SPEC-101-10-pantalla-carga-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update.md) |
| **Estado** | Finalizado |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #5, 09/06/2026 |
| **Última actualización** | 2026-06-09 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Finalizado |

## Implementación

### Backend

1. `StockConsultaService::lookupDisponibilidadCargaPorCodigos(array $codigos)` — disponible `stock − comprometido`; base `stock_base − comprometido_base`; **sin** joins a pedidos web.
2. `ArticuloController::index` — usar lookup carga cuando **no** hay query `codigos`; mantener `lookupDisponibilidadPorCodigos` en refresh por `codigos`.

### Producto

Actualizar [pantalla-carga-comprobante-ui.md](../../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §3.1: distinguir fórmula carga vs consulta stock.

### Tests

- PHPUnit: `StockConsultaServiceTest::testLookupCargaExcluyeComprometidoWeb`.
- Sin cambio en frontend display (`formatArticuloCargaDisplay` ya usa `disponibleNeto` / `disponibleNetoBase`).

## AC técnicos

- [x] **AC-CC5-T1:** `lookupDisponibilidadCargaPorCodigos` para fixture CC stock: ART-A disponible 90 (no 85).
- [x] **AC-CC5-T2:** ART-P1 `disponibleNetoBase` = 130 (no 122).
- [x] **AC-CC5-T3:** `listar()` sigue devolviendo disponible neto con web.

## Cierre implementación (Parte D — 09/06/2026)

- `StockConsultaService::lookupDisponibilidadCargaPorCodigos` + flag `incluirComprometidoWeb` en `buildStockQuery`.
- `ArticuloController::index` — lookup carga en browse; lookup neto en refresh `codigos`.
- Producto §3.1 actualizado; test `testLookupCargaExcluyeComprometidoWeb` (skip si no hay tablas stock en entorno test).
