# Verificación Parte D — SPEC-101-17 mobile v1 (`v1.2.0-mobile`)

| Campo | Valor |
|-------|--------|
| **Fecha D** | 2026-06-30 |
| **Fecha smoke Android** | 2026-06-30 (emulador Pixel 6 API 34) |
| **Release** | Tag Git **`v1.2.0-mobile`** (pendiente solo smoke iOS) |
| **SPEC** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Transversal** | [SPEC-001-11](../../05-open-spec/001-Generaliddes/SPEC-001-11-mobile-capacitor.md) |
| **Cierre C1** | [F-101-17-cierre-c1](F-101-17-cierre-c1.md), [F-GEN-11-cierre-c1](../001-Generaliddes/F-GEN-11-cierre-c1.md) |
| **Cierre F** | [F-101-17-cierre-formal](F-101-17-cierre-formal.md), [F-GEN-11-cierre-formal](../001-Generaliddes/F-GEN-11-cierre-formal.md) |

## Leyenda

| Estado | Significado |
|--------|-------------|
| **OK** | AC cubierto en código y/o smoke manual |
| **PARCIAL** | Implementado; falta smoke en otra plataforma |
| **N/A** | Fuera de alcance v1 |

## Verificación automatizada (2026-06-30)

| Comando | Resultado |
|---------|-----------|
| `npm run build` / `npm run build:mobile` | **OK** |
| `npm test -- --run` | **OK** (incl. `mobileMenuPolicy`, `normalizeApiBaseUrl`) |
| `npx cap sync` | **OK** (android + ios, plugins) |

## Resumen por TR v1

| TR | HU / GEN | Estado D | Evidencia principal |
|----|----------|----------|---------------------|
| TR-GEN-11-mobile-capacitor-scaffold | HU-GEN-11-scaffold | **OK** | Capacitor 8, `base: './'`, `allowMixedContent`, StatusBar, cleartext Android |
| TR-SPEC-101-17-mobile-v1-scaffold | HU-101-031 | **OK** | App id `com.paqsystems.pedidosweb`, runbooks 05/06 |
| TR-GEN-11-mobile-login-tenant | HU-GEN-11-login + config | **OK** | Preferences, `MobileConfigPopup`, `normalizeApiBaseUrl` |
| TR-SPEC-101-17-mobile-v1-login-tenant | HU-101-032 | **OK** | Landing `/consultas/stock`, tenant `desarrollo`, forgot ocultos |
| TR-GEN-11-mobile-shell | HU-GEN-11-shell | **OK** | Drawer, backdrop, menú derecha native, safe area, `operationalOnly` |
| TR-SPEC-101-17-mobile-v1-stock-kardex | HU-101-033 | **OK** | `StockMobileView`, lista HTML `pageScroll`, paginación, detalle |

## Matriz AC transversal (muestra)

| AC | Estado | Nota |
|----|--------|------|
| Tenant solo en native | OK | `isNativeApp()` en `LoginPage` |
| Web sin regresión login | OK | Tests automatizados; smoke web manual pendiente checklist |
| `X-Paq-Cliente` dinámico | OK | `getActiveTenantSync()` en `client.ts` |
| Menú sin pivot/excel/admin | OK | `pedidosWebMobilePolicy` + `mobileMenuPolicy` |
| v1 solo ruta stock | OK | `MobileRouteGuard` |
| Kardex stock (no DataGrid) | OK | Branch en `StockPage.tsx` |
| Scroll lista stock en Capacitor | OK | `ConsultaKardexList` `pageScroll` + `shellMain` overflow |
| Header no tapado por status bar | OK | `StatusBar` + inset 28px + menú a la derecha |
| i18n mobile + stock | OK | `consultas.resultSummary`, `shell.menu.closeBackdrop` |
| `data-testid` estables | OK | Ver TR stock-kardex §6 |

## Smoke manual

| # | Caso | Plataforma | Estado |
|---|------|------------|--------|
| 1 | Config API `http://10.0.2.2:8088/api/v1` + health OK | Android emulador | **OK** |
| 2 | Login `desarrollo` / `supervisor.mvp` → stock kardex | Android emulador | **OK** (requiere VPN si SQL remoto + `artisan serve`) |
| 3 | Filtro `q` (Enter) + contador «Mostrando X de Y» | Android emulador | **OK** |
| 4 | Scroll lista (arrastre / gesto; no rueda mouse) | Android emulador | **OK** |
| 5 | Menú ☰ derecha → Stock; backdrop cierra | Android emulador | **OK** |
| 6 | Detalle popup al tap tarjeta | Android emulador | **OK** (validar en sesión si no marcado) |
| 7 | Mismo flujo | iOS simulador/dispositivo | **PENDIENTE** |
| 8 | Web: login sin tenant → dashboard | Browser | **PENDIENTE** (regresión manual) |

**Prerrequisitos operativos (no olvidar tras reinicio PC):**

1. `php artisan serve --host=0.0.0.0 --port=8088` (health **no** requiere SQL; login **sí**).
2. VPN si la base ERP/SQL está en red privada.
3. `npm run build:mobile && npx cap sync android` tras cambios frontend.

**Runbooks:** [`05-runbook`](../../_base/01-mobile/05-runbook-primera-prueba-android-emulador.md) · [`06-instalacion`](../../_base/01-mobile/06-instalacion-emulador-android-studio.md) · [`00-Inicio-de-ejecuciones`](../../_base/01-mobile/00-Inicio-de-ejecuciones.md)

## Ajustes post-smoke documentados en TR

| Tema | TR actualizado |
|------|----------------|
| Mixed content HTTP local | TR-GEN-11-scaffold, TR-101-17-scaffold |
| Lista kardex HTML + scroll página | TR-101-17-stock-kardex |
| Menú compacto / safe area | TR-GEN-11-shell |
| Filtro Enter + resultSummary | TR-101-17-stock-kardex |

## Observaciones (no bloqueantes tag v1)

| ID | Tema | Notas |
|----|------|-------|
| OBS-D-01 | E2E Playwright Capacitor | Opcional; no añadido en D1 |
| OBS-D-02 | Tag `v1.2.0-mobile` | Tras smoke **iOS** (Android emulador OK) |
| OBS-D-03 | Publicación tiendas | Fuera v1 |
| OBS-D-04 | TR v2/v3 | HU-101-034…036 — Parte C futura |
| OBS-D-05 | Controles árbol menú web | Ocultos en native v1 por diseño; recuperables en v2 si hace falta |

## Veredicto D / F1

**D1 + verificación smoke Android: OK** — código, tests y QA manual emulador alineados con TR v1.

**Pendiente pre-tag:** smoke iOS + regresión web manual (checklist §8.2 runbook 05).

Informes F: [F-101-17-cierre-formal](F-101-17-cierre-formal.md), [F-GEN-11-cierre-formal](../001-Generaliddes/F-GEN-11-cierre-formal.md).
