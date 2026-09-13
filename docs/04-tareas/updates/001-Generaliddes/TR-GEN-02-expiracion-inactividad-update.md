# TR-GEN-02-expiracion-inactividad-update — Suspender logout por inactividad

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-GEN-02-expiracion-inactividad](../../001-Generaliddes/TR-GEN-02-expiracion-inactividad.md) |
| **SPEC relacionada** | [SPEC-001-02-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-02-acceso-y-seguridad-update.md) |
| **HU relacionada** | [HU-GEN-02-expiracion-inactividad-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-02-expiracion-inactividad-update.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #16 · **12/09/2026** |
| **Última actualización** | 2026-09-12 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Desactivar en runtime el timer de inactividad del shell autenticado, dejando el código listo para reactivar al adoptar SDK Framework.

| Pieza | Acción |
|-------|--------|
| Constante `inactivityLogoutEnabled` | `false` (D1-31); comentario con CC #16 y criterio de restablecimiento |
| `SessionLifecycleManager` | Pasar `enabled: inactivityLogoutEnabled && isAuthenticated && sessionContext !== null` |
| E2E `smoke.spec.ts` | Sustituir assert de expiración por assert de **no** expiración mientras el flag esté off |
| Unit `sessionInactivity*` | Sin cambio obligatorio (prueban el motor, no el gate) |

## 2) Criterios de aceptación

- **AC-CC16-T-C1:** Con flag off, `useInactivityTimeout` no arma el controlador (o no dispara `onExpire`).
- **AC-CC16-T-C2:** E2E: timeout corto (`0.01` min) **no** redirige a login por inactividad.
- **AC-CC16-T-C3:** Logout manual / 401 de token siguen OK (smoke existente o regresión manual).

## 3) Implementación

1. Agregar `inactivityLogoutEnabled` en `frontend/src/features/auth/` (p. ej. `sessionInactivity.ts` o archivo hermano).
2. Cablear en `SessionLifecycleManager.tsx`.
3. Ajustar E2E de inactividad en `frontend/tests/e2e/smoke.spec.ts`.

## 4) Tests

- E2E: inactividad suspendida no expira sesión.
- Unit existentes del controlador: mantienen cobertura del motor.

## 5) Fuera de alcance

- Backend / OpenAPI / `MinutosWeb`.
- Adopción real del SDK Framework.
