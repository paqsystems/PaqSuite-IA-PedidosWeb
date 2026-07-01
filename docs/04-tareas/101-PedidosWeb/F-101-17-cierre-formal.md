# Cierre F Formal — SPEC-101-17 Mobile Capacitor PedidosWeb v1

## Alcance del cierre

Release **`v1.2.0-mobile`**: scaffold + login tenant + consulta stock kardex.

| TR | HU |
|----|-----|
| [TR-SPEC-101-17-mobile-v1-scaffold](TR-SPEC-101-17-mobile-v1-scaffold.md) | [HU-101-031](../../03-historias-usuario/101-PedidosWeb/HU-101-031-mobile-v1-scaffold.md) |
| [TR-SPEC-101-17-mobile-v1-login-tenant](TR-SPEC-101-17-mobile-v1-login-tenant.md) | [HU-101-032](../../03-historias-usuario/101-PedidosWeb/HU-101-032-mobile-login-tenant.md) |
| [TR-SPEC-101-17-mobile-v1-stock-kardex](TR-SPEC-101-17-mobile-v1-stock-kardex.md) | [HU-101-033](../../03-historias-usuario/101-PedidosWeb/HU-101-033-mobile-consulta-stock-kardex.md) |

**SPEC:** [SPEC-101-17-mobile-capacitor-pedidosweb.md](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md)

**Transversal:** [F-GEN-11-cierre-formal](../001-Generaliddes/F-GEN-11-cierre-formal.md)  
**Verificación D / F1:** [D-VERIFICACION-101-17-mobile-v1](D-VERIFICACION-101-17-mobile-v1.md)

## Resultado global

- **Aprobado con observaciones**

Implementación D1 completa. **Smoke Android emulador validado** (login, stock kardex, scroll, menú). Tag Git `v1.2.0-mobile` pendiente de smoke **iOS**.

## Resumen por slice

| Slice | Resultado | Evidencia |
|-------|-----------|-----------|
| Scaffold PedidosWeb | Aprobado | Capacitor, cleartext/mixed content, runbooks 05/06 |
| Login tenant PedidosWeb | Aprobado | Landing `/consultas/stock`, config API, tenant `desarrollo` |
| Stock kardex | Aprobado | `StockMobileView`, paginación, filtro Enter, `pageScroll`, detalle popup |
| Shell mobile | Aprobado | Drawer, safe area, menú v1 Stock, exclusiones |

## Verificación automatizada (2026-06-30)

| Comando | Resultado |
|---------|-----------|
| `npm run build` / `build:mobile` | OK |
| `npm test -- --run` | OK |
| `npx cap sync` | OK |

## Smoke manual

| Plataforma | Estado | Notas |
|------------|--------|-------|
| Android emulador (Pixel 6 API 34) | **OK** | Ver [D-VERIFICACION](D-VERIFICACION-101-17-mobile-v1.md) § smoke |
| iOS simulador/dispositivo | **Pendiente** | Mac/CI antes del tag |
| Web regresión login | **Pendiente** | Checklist runbook 05 §8.2 |

## Activación / smoke en entorno dev

1. **Backend:** `php artisan serve --host=0.0.0.0 --port=8088` (obligatorio tras reinicio PC).
2. **VPN** si SQL Server en red privada (login; health no usa SQL).
3. Engranaje: `http://10.0.2.2:8088/api/v1` + Probar conexión.
4. `cd frontend && npm run build:mobile && npx cap sync android` → Run ▶ Android Studio.
5. Login: tenant `desarrollo`, `supervisor.mvp`, `ChangeMeInLocalEnv`.
6. Checklist rápido: [`00-Inicio-de-ejecuciones`](../../_base/01-mobile/00-Inicio-de-ejecuciones.md).

## Fuera de alcance v1 (confirmado)

- HU-101-034…036 (consultas/listados v2, carga v3).
- Publicación Play Store / TestFlight.
- E2E Playwright Capacitor (opcional TR).
- Controles expandir/contraer árbol menú en native v1.

## Observaciones

| ID | Tema | Notas |
|----|------|-------|
| OBS-01 | Tag release | Crear `v1.2.0-mobile` tras smoke **iOS** |
| OBS-02 | iOS | Requiere Mac/CI para verificación final |
| OBS-03 | Backend | Sin endpoints nuevos; reutiliza API web |
| OBS-04 | TR post-smoke | Actualizados shell, stock kardex, scaffold (2026-06-30) |

## Veredicto

**F formal v2 cerrado** — SPEC-101-17 v1 implementado y verificado en Android emulador. Siguiente hito: smoke iOS → tags `v1.2.0-mobile` / `v1.2.1-mobile` → [F-101-17-cierre-formal-v2](F-101-17-cierre-formal-v2.md) v3 (HU-101-036).
