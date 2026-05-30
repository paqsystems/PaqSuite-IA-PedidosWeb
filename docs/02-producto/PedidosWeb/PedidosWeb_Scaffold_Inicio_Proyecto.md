# PedidosWeb - Scaffold inicial (MONO)

Este documento registra el scaffold del producto. La **guía canónica de instalación** para todos los MONO está en:

**[`docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md`](../../00-contexto/_mono/00-instalacion-scaffold-fullstack.md)**

## 1) Modo declarado

- Modo: **MONO**
- Patrón host/tenant: `{cliente}.{proyecto}` -> `frontend.{proyecto}` + `backend.{proyecto}`
- Header API: `X-Paq-Cliente`

## 2) Herencia IA y symlinks

Verificados:

- `.cursor/rules/base` -> `PaqSuite-IA-BASE/.cursor/rules`
- `.cursor/rules/mono` -> `PaqSuite-IA-MONO/.cursor/rules`
- `.cursor/skills` -> `PaqSuite-IA-BASE/.cursor/skills`
- `prompts` -> `PaqSuite-IA-BASE/.cursor/prompts`
- `docs/_base` -> `PaqSuite-IA-BASE/.cursor/docs`
- `docs/_mono` -> `PaqSuite-IA-MONO/.cursor/docs`
- `docs/00-contexto/_mono` -> `PaqSuite-IA-MONO/docs/00-contexto`

## 3) Backend (Laravel 10 + envelope)

| Pieza | Estado |
|-------|--------|
| Laravel 10 completo en `backend/` | Instalado |
| `App\Http\Responses\ApiResponse` | Envelope MONO |
| `GET /api/v1/health` | Operativo |
| `OpenApi.php` + L5-Swagger | Operativo — `/api/documentation` |
| Tests Feature + Unit envelope | `php artisan test` verde |
| Sanctum | Incluido en skeleton |

Detalle: [`backend/README.md`](../../../backend/README.md).

## 4) Frontend (React + Vite)

| Pieza | Estado |
|-------|--------|
| Vite + React + TS | Scaffold base |
| `index.html`, `tsconfig*.json`, `vite-env.d.ts` | Completado |
| `@vitejs/plugin-react` | Instalado |
| `playwright.config.ts`, `vitest.config.ts` | Completado |
| Proxy `/api` -> `:8000` | `vite.config.ts` |
| `shared/http/client.ts` | Header `X-Paq-Cliente` + Bearer |
| Auth MVP (`LoginPage`, shell mínimo) | Implementado (TR login) |
| `npm run build` | Verde |
| react-router-dom, i18next, DevExtreme | Documentados en guía §4 — instalar con `npm install` |

Detalle: [`frontend/README.md`](../../../frontend/README.md).

## 5) Operación y configuración base

- Archivo `VERSION`: `1.1.0`
- `.env.example` en raíz; `backend/.env.example` con variables MONO

## 6) Próximas implementaciones (Fase 0)

1. Completar `npm install` de dependencias transversales frontend (guía §4): react-router-dom, i18next, DevExtreme.
2. ~~`TR-GEN-02-modelo-roles-permisos-seed`~~ (implementada)
3. ~~`TR-GEN-02-login-sesion`~~ (implementada — smoke API OK contra SQL Server)
4. ~~`TR-GEN-02-autorizacion-menu-api`~~ (implementada)
5. Shell post-login (`TR-GEN-01-shell-layout`)
