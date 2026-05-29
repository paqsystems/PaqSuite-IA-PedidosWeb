# PedidosWeb - Scaffold inicial (MONO)

Este documento registra el scaffold mínimo creado siguiendo `docs/_base/00-inicio-arquitectura.md` (secciones 1-7).

## 1) Modo declarado

- Modo: **MONO**
- Patrón host/tenant: `{cliente}.{proyecto}` -> `frontend.{proyecto}` + `backend.{proyecto}`
- Header API: `X-Paq-Cliente`

## 2) Herencia IA y symlinks

Verificados:

- `.cursor/rules/base` -> `PaqSuite-IA-BASE/.cursor/rules`
- `.cursor/rules/mono` -> `PaqSuite-IA-MONO/.cursor/rules`
- `.cursor/skills` -> `PaqSuite-IA-BASE/.cursor/skills` (OpenSpec: `/spec-ambiguity-review`, `/enrich-user-story`, `/ai-planning-mode`, etc.)
- `prompts` -> `PaqSuite-IA-BASE/.cursor/prompts`
- `docs/_base` -> `PaqSuite-IA-BASE/.cursor/docs`
- `docs/_mono` -> `PaqSuite-IA-MONO/.cursor/docs`
- `docs/00-contexto/_mono` -> `PaqSuite-IA-MONO/docs/00-contexto`

## 3) Backend mínimo creado

- `backend/routes/api.php`: endpoint `GET /api/v1/health`
- `backend/app/Http/Controllers/HealthController.php`: respuesta envelope base
- `backend/OpenApi.php`: raíz de anotaciones OpenAPI
- `backend/tests/Feature/HealthCheckTest.php`: test de healthcheck

## 4) Frontend mínimo creado

- `frontend/vite.config.ts`: servidor en puerto `3000`, proxy `/api` -> `:8000`
- `frontend/src/main.tsx` y `frontend/src/app/App.tsx`: app base
- `frontend/src/shared/http/client.ts`: helper de tenant headers
- `frontend/tests/e2e/smoke.spec.ts`: E2E básico

## 5) Operación y configuración base

- Archivo `VERSION`: `1.1.0`
- Archivo `.env.example` con variables mínimas de API, DB y frontend

## 6) Próximas implementaciones sugeridas

1. Inicializar Laravel 10 y React+TS si aún no están creados por CLI.
2. Implementar autenticación y autorización real.
3. Implementar shell post-login en frontend.
4. Completar flujo E2E principal de negocio.
