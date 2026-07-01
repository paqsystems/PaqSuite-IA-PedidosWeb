# TR-SPEC-101-17-mobile-v1-login-tenant — Login PedidosWeb mobile

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-032-mobile-login-tenant](../../03-historias-usuario/101-PedidosWeb/HU-101-032-mobile-login-tenant.md) |
| **SPEC relacionada** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Must |
| **Release** | `v1.2.0-mobile` |
| **Dependencias** | TR-GEN-11-mobile-login-tenant; TR-SPEC-101-17-mobile-v1-scaffold; TR-GEN-02-login-sesion |
| **Estado** | **D1 implementado** — **F formal 2026-06-30** (smoke Android emulador validado) |
| **Última actualización** | 2026-06-30 (post-D + smoke QA) |

**Origen:** [HU-101-032](../../03-historias-usuario/101-PedidosWeb/HU-101-032-mobile-login-tenant.md)  
**Patrón:** [`04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md)

---

## 1) HU refinada (resumen)

### Título
Login PedidosWeb native con tenant, landing post-login en stock, config API y sin recuperación v1.

### Narrativa
Como usuario PedidosWeb mobile, quiero elegir empresa y autenticarme para acceder al tenant correcto.

### In scope / Out of scope
- **In scope:** placeholders `desarrollo`/`demo`/`ankasdelsur`/`quento`, redirect `/consultas/stock`, ocultar forgot/reset, config engranaje.
- **Out of scope:** dashboard landing; recuperación contraseña v1.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| CA-01 | Login native tenant `desarrollo` + seed OK |
| CA-02 | Post-login → `/consultas/stock` (no `/dashboard`) |
| CA-03 | Web login sin regresión |
| CA-04 | Enlaces recuperación ocultos native v1 |
| CA-05 | Config API operativa |

### Escenarios Gherkin

(Heredados HU-101-032.)

---

## 3) Reglas de negocio

1. **RN-01:** Smoke QA tenant **`desarrollo`** + usuarios seed MVP.
2. **RN-02:** Mismo contrato `POST /api/v1/auth/login` que web.
3. **RN-03:** 403 `auth.noCommercialProfile` sin perfil comercial.
4. **RN-04:** Landing v1 mobile **solo** stock (D1-7); web mantiene `/dashboard` salvo decisión futura.

### Redirect post-login

```typescript
// LoginPage.tsx — pseudocódigo D
const landingPath = isNativeApp() ? '/consultas/stock' : '/dashboard';
navigate(landingPath, { replace: true });
```

`firstLogin` prevalece → `/change-password` antes de landing.

---

## 3.1) Informe C1 (2026-06-30)

**Apto** — landing stock cerrada A1 D1-7; forgot/reset fuera D1-12.

---

## 3.2) Plan D1

Implementación **extiende** [TR-GEN-11-mobile-login-tenant](../001-Generaliddes/TR-GEN-11-mobile-login-tenant.md):

| Delta producto | Detalle |
|----------------|---------|
| Placeholders i18n | Ejemplos tenant PedidosWeb |
| Landing | `/consultas/stock` en native |
| Links forgot | `{!isNativeApp() && <Link forgot...>}` |
| Config default URL | `backend.pedidosweb.paqsystems.com` |

No duplicar lógica HTTP — un solo `LoginPage`.

---

## 4) Impacto en datos

**N/A** — seed `desarrollo` existente.

---

## 5) Contratos de API

Sin cambios. Ver TR-GEN-11-mobile-login-tenant §5.

| Método | Path | Permiso |
|--------|------|---------|
| POST | `/api/v1/auth/login` | Público + `X-Paq-Cliente` |

---

## 6) Cambios frontend

| Archivo | Cambio |
|---------|--------|
| `LoginPage.tsx` | Landing native + ocultar links |
| `pedidosWebRoutes.tsx` | Verificar ruta stock accesible post-login |
| i18n | Placeholders tenant producto |

### data-testid

Heredados GEN-11: `loginTenant`, etc.

---

## 7) Plan de tareas

| ID | Descripción | DoD |
|----|-------------|-----|
| T1 | Landing `/consultas/stock` native | CA-02 |
| T2 | Ocultar forgot/reset native | CA-04 |
| T3 | Smoke login desarrollo + vendedor seed | CA-01 |
| T4 | Regresión web login → dashboard | CA-03 |
| T5 | E2E o manual config API | CA-05 |

---

## 8) Tests

- **Manual:** tenant `desarrollo`, usuario `supervisor.mvp` → stock visible (smoke Android emulador OK).
- **Operativo:** `php artisan serve --host=0.0.0.0 --port=8088` obligatorio tras reinicio PC; VPN si SQL en red privada.
- **URL API:** `normalizeApiBaseUrl()` convierte `\` → `/` en override pegado desde Windows.
- **E2E:** extender suite auth con flag native (fase D — opcional).

---

## 10) Checklist

- [x] CA-01 … CA-05 (smoke Android emulador)
- [x] TR-GEN-11-mobile-login-tenant implementado en mismo release

---

## Archivos (post-D)

- `frontend/src/features/auth/LoginPage.tsx`
- `frontend/src/features/mobile/MobileConfigPopup.tsx`
- `frontend/src/shared/mobile/mobileRuntime.ts` — `normalizeApiBaseUrl`, `StatusBar`
- `frontend/src/locales/es.json` (y en/pt/fr/it)
