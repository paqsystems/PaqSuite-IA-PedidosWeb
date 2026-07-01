# TR-SPEC-101-17-mobile-v1-scaffold — Scaffold PedidosWeb mobile

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-031-mobile-v1-scaffold](../../03-historias-usuario/101-PedidosWeb/HU-101-031-mobile-v1-scaffold.md) |
| **SPEC relacionada** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Must |
| **Release** | `v1.2.0-mobile` |
| **Dependencias** | [TR-GEN-11-mobile-capacitor-scaffold](../001-Generaliddes/TR-GEN-11-mobile-capacitor-scaffold.md) |
| **Estado** | **D1 implementado** — **F formal 2026-06-30** (smoke Android emulador validado) |
| **Última actualización** | 2026-06-30 (post-D + smoke QA) |

**Origen:** [HU-101-031](../../03-historias-usuario/101-PedidosWeb/HU-101-031-mobile-v1-scaffold.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Branding e identidad PedidosWeb en Capacitor: appId, URL API default, assets splash/icon.

### Narrativa
Como equipo PedidosWeb, quiero la app instalable con identidad del producto y URL backend correcta.

### In scope / Out of scope
- **In scope:** `com.paqsystems.pedidosweb`, splash/icon placeholder, `.env.mobile`, URL prod default, smoke doc.
- **Out of scope:** login, stock kardex, tiendas.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| CA-01 | `cap sync` con build PedidosWeb OK |
| CA-02 | Branding PedidosWeb al abrir app |
| CA-03 | APK debug instala en Android |
| CA-04 | iOS debug documentado/ejecutado Mac/CI |

---

## 3) Reglas de negocio

1. **RN-01:** `appId` = `com.paqsystems.pedidosweb`; `appName` = `PedidosWeb`.
2. **RN-02:** URL API producción default: `https://backend.pedidosweb.paqsystems.com/api/v1`.
3. **RN-03:** Dev local: `VITE_API_BASE_URL` en `.env.mobile` apuntando a LAN/emulador (`10.0.2.2` Android emulator doc).
4. **RN-04:** `VITE_DEVEXTREME_LICENSE` obligatorio en build mobile (mismo que web).

---

## 3.1) Informe C1 (2026-06-30)

**Apto** — implementar junto TR-GEN-11 scaffold en mismo PR o inmediatamente después.

---

## 3.2) Plan D1

| Artefacto | Valor |
|-----------|--------|
| `capacitor.config.ts` | `appId`, `appName` PedidosWeb |
| `.env.mobile` | `VITE_API_BASE_URL`, `VITE_DEVEXTREME_LICENSE` |
| `resources/` o assets Capacitor | icon + splash placeholder v1 |
| `docs/_base/01-mobile/03-comandos-generacion-aplicaciones.md` | Sección smoke PedidosWeb |

### Android emulator API

Documentar: host `10.0.2.2` para backend en `localhost:8088` (o puerto actual).

### Android dev — mixed content y cleartext (post-smoke)

| Artefacto | Propósito |
|-----------|-----------|
| `capacitor.config.ts` → `android.allowMixedContent: true` | WebView `https://localhost` → API `http://10.0.2.2` en smoke local |
| `capacitor.config.ts` → `plugins.StatusBar.overlaysWebView: false` | Contenido no debajo de barra de estado |
| `android/app/src/main/res/xml/network_security_config.xml` | Cleartext `10.0.2.2`, `localhost` |
| `android/app/src/debug/AndroidManifest.xml` | `usesCleartextTraffic` solo debug |
| `index.html` | `viewport-fit=cover` |

Runbooks: [`05-runbook-primera-prueba-android-emulador.md`](../../_base/01-mobile/05-runbook-primera-prueba-android-emulador.md), [`06-instalacion-emulador-android-studio.md`](../../_base/01-mobile/06-instalacion-emulador-android-studio.md).

## 4) Impacto en datos

**N/A**

---

## 5) Contratos de API

**N/A** — sin backend.

---

## 6) Cambios frontend

- Ajustar `capacitor.config.ts` producto sobre base GEN-11.
- Modo Vite `mobile` en `vite.config.ts` si se usa env separado.
- Splash screen texto/logo PedidosWeb.

---

## 7) Plan de tareas

| ID | Descripción | DoD |
|----|-------------|-----|
| T1 | appId/appName + env mobile | Config OK |
| T2 | Assets splash/icon mínimos | App identifiable |
| T3 | Smoke Android dispositivo | CA-03 |
| T4 | Doc iOS smoke | CA-04 |

---

## 8) Tests

Manual smoke pre-tag `v1.2.0-mobile`. CI web sin regresión.

---

## 10) Checklist

- [x] CA-01 … CA-04 (Android emulador smoke; iOS documentado Mac/CI)
- [x] Coordinado con TR-GEN-11-mobile-capacitor-scaffold

---

## Archivos (post-D)

- `frontend/capacitor.config.ts` — `allowMixedContent`, `StatusBar` plugin
- `frontend/.env.mobile.example`
- `frontend/index.html` — `viewport-fit=cover`
- `frontend/android/app/src/main/res/xml/network_security_config.xml`
- `frontend/android/app/src/debug/AndroidManifest.xml`
- `docs/_base/01-mobile/03-comandos-generacion-aplicaciones.md`
- `docs/_base/01-mobile/05-runbook-primera-prueba-android-emulador.md`
- `docs/_base/01-mobile/06-instalacion-emulador-android-studio.md`
