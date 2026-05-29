# Frontend scaffold (React + Vite + DevExtreme)

Base inicial para `PaqSuite-IA-PedidosWeb` en modo MONO.

## Objetivo del scaffold

- Estructura por `app`, `features`, `shared`, `layouts`, `pages`.
- Cliente HTTP centralizado.
- Base para rutas protegidas y shell post-login.
- Placeholder de test unitario y E2E.

## Estructura mínima creada

- `src/main.tsx`
- `src/app/App.tsx`
- `src/shared/http/client.ts`
- `tests/e2e/smoke.spec.ts`

## Próximos pasos

1. Inicializar React + TS con Vite.
2. Instalar `devextreme` y `devextreme-react`.
3. Implementar shell post-login con layout principal.
4. Implementar login + token + interceptores.
5. Completar tests Vitest y Playwright del flujo E2E principal.
