# Parte E — Tests CC PQ #16 (12/09/2026)

## Alcance

Suspensión del logout por inactividad (`inactivityLogoutEnabled=false`, D1-31).

## Casos

| Test | Archivo | Qué valida |
|------|---------|------------|
| `mantiene suspendido el logout por inactividad hasta SDK Framework (CC PQ #16)` | `frontend/src/features/auth/sessionInactivity.test.ts` | Flag en `false` |
| `inactividad suspendida no expira la sesion (CC PQ #16)` | `frontend/tests/e2e/smoke.spec.ts` | Con timeout corto la sesión **no** cierra |

## Ejecución

```bash
cd frontend
npx vitest run src/features/auth/sessionInactivity.test.ts
npx playwright test tests/e2e/smoke.spec.ts -g "inactividad suspendida"
```

## Notas

- El E2E anterior (`inactividad expira la sesion…`) se **reemplazó** por el assert de no-expiración mientras dure la suspensión.
- Al reactivar el flag (`true`), restaurar el E2E de expiración positiva.
