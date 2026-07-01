# Verificación Parte D / F1 — SPEC-101-17 mobile v2 (`v1.2.1-mobile`)

| Campo | Valor |
|-------|--------|
| **Fecha D** | 2026-06-30 |
| **Fecha smoke Android** | 2026-06-30 (emulador; QA usuario «en principio todo perfecto») |
| **Release** | Tag Git **`v1.2.1-mobile`** (pendiente smoke iOS) |
| **SPEC** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Cierre C1 v2** | [F-101-17-cierre-c1-v2](F-101-17-cierre-c1-v2.md) |
| **Cierre F v2** | [F-101-17-cierre-formal-v2](F-101-17-cierre-formal-v2.md) |

## Verificación automatizada (2026-06-30)

| Comando | Resultado |
|---------|-----------|
| `npm run build` / `build:mobile` | **OK** |
| `npm test -- --run` | **OK** (190 tests; incl. `pedidosWebMobilePolicy.test.ts`) |
| `npx cap sync` | **OK** |

## Resumen por TR v2

| TR | HU | Estado D | Evidencia |
|----|-----|----------|-----------|
| TR-SPEC-101-17-mobile-v2-consultas | HU-101-034 | **OK** | `ConsultaKardexMobileView`, consultas + parámetros + logs |
| TR-SPEC-101-17-mobile-v2-listados | HU-101-035 | **OK** | `ComprobanteListadoMobileView`, pedidos/presupuestos activos |
| TR-GEN-11-mobile-shell (ext.) | — | **OK** | `mobileV2AllowedRoutePrefixes`, `isRouteAllowedOnMobileApp` |

## Smoke manual Android

| # | Caso | Estado |
|---|------|--------|
| 1 | Menú native muestra ítems MVP según permiso (no solo Stock) | **OK** |
| 2 | Consultas: deuda, cheques, historial, detalle pedidos | **OK** |
| 3 | Listados: pedidos ingresados/pendientes, presupuestos ingresados | **OK** |
| 4 | Parámetros consulta (solo lectura) | **OK** |
| 5 | Logs integración + filtros | **OK** |
| 6 | Kardex: filtro Enter, refresh, scroll, detalle popup | **OK** |
| 7 | `/pedidos/carga` bloqueado (redirige a stock) | **OK** (por diseño v2) |
| 8 | Mismo flujo | **iOS pendiente** |
| 9 | Web regresión login | **Pendiente** |

## Fuera de alcance v2 (confirmado en smoke)

| Ítem | Notas |
|------|--------|
| Presupuestos tab **cerrados** | Solo activos en mobile |
| Tratativas | Placeholder |
| Acciones comprobante (editar/copiar/eliminar/convertir) | v3 + carga mobile |
| Carga pedidos `/pedidos/carga` | v3 |

## Observaciones

| ID | Tema | Notas |
|----|------|-------|
| OBS-D2-01 | Tag `v1.2.1-mobile` | Tras smoke iOS |
| OBS-D2-02 | `normalizeApiBaseUrl` | Fix `http:\` → `http://` (Windows paste) |
| OBS-D2-03 | Stock refactor | Usa `ConsultaKardexMobileView` modo servidor |

## Veredicto D / F1

**D2 + verificación smoke Android: OK** — listo para cierre F v2.

Informe F: [F-101-17-cierre-formal-v2](F-101-17-cierre-formal-v2.md).
