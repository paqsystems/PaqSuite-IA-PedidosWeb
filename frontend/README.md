# Frontend — PaqSuite-IA-PedidosWeb (React + Vite)

SPA en **React 18**, **TypeScript** y **Vite 5**. Stack transversal documentado en [`docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md`](../docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md) §4.

## Instalación

```powershell
cd frontend
npm install
```

### Dependencias transversales MVP (si faltan en `package.json`)

```powershell
npm install react-router-dom i18next react-i18next devextreme devextreme-react
npm install -D @playwright/test vitest
```

## Desarrollo

```powershell
npm run dev
```

URL: `http://localhost:3010` — proxy `/api` → backend `:8000`.

## Tests

```powershell
npm run test
npm run test:e2e
npm run test:all
```

## Variables de entorno

En raíz del repo o `frontend/.env` (ver `frontend/.env.example`):

- `VITE_API_BASE_URL=/api/v1` (dev con proxy Vite; producción: URL absoluta del backend)
- `VITE_TENANT_DEFAULT_CLIENT=desarrollo`
- `VITE_APP_VERSION` (desde `VERSION`)
- `VITE_DEVEXTREME_LICENSE`

## Build

```powershell
npm run build
```

Requisito obligatorio antes de merge/deploy (ver regla `22-frontend-build-typescript.md`).

## Estructura objetivo

```text
src/app/          # App, router
src/features/     # dominios (auth, i18n, …)
src/shared/http/  # client + X-Paq-Cliente + Bearer
src/shared/ui/    # wrappers DevExtreme
src/layouts/     # shell post-login
tests/e2e/        # Playwright
```

## Cliente HTTP

`src/shared/http/client.ts` — header `X-Paq-Cliente` (MONO). Ampliar con interceptor Bearer en slice auth.
