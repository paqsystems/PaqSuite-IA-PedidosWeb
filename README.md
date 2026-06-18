# PaqSuite-IA-PedidosWeb

Portal **MONO** — Laravel API + React/Vite.

## Estado actual

- Herencia IA: symlinks `base`, `mono`, `docs/_base`, `docs/00-contexto/_mono`.
- **Backend:** Laravel 10 + `ApiResponse` (envelope) + `GET /api/v1/health`.
- **Frontend:** scaffold React/Vite (ampliar deps según guía §4).
- Documentación scaffold: [`docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md`](docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md).

## Arranque local

### Backend

```powershell
cd backend
copy .env.example .env
composer install
php artisan key:generate
php artisan serve --port=8000
```

Verificar: `GET http://localhost:8000/api/v1/health` → envelope `{ "error": 0, "respuesta": "ok", "resultado": { ... } }`.

### Frontend

```powershell
cd frontend
npm install
npm run dev
```

## Instalación inicial (nuevo clon o producto)

Seguir en orden:

1. [`docs/_base/symlinks_paqsuite_ia.md`](docs/_base/symlinks_paqsuite_ia.md) — herencia IA  
2. [`docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md`](docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md) — Laravel + envelope + React/DevExtreme  
3. [`docs/_base/00-inicio-arquitectura.md`](docs/_base/00-inicio-arquitectura.md) — checklist §7  

## Patrón MONO

- Header tenant: `X-Paq-Cliente` (`desarrollo` / `demo` en local).
- Sin selector de empresa ni `X-Company-Id`.

## Checklist desarrollo

- [x] Laravel 10 en `backend/`
- [x] Envelope `ApiResponse` + tests
- [x] Health `/api/v1/health`
- [ ] Dependencias frontend transversales (router, i18n, DevExtreme) según guía
- [ ] OpenAPI `/api/documentation`
- [ ] Auth login (TR-GEN-02-login-sesion)

## OpenSpec

HU/TR: `docs/03-historias-usuario/`, `docs/04-tareas/`, `docs/05-open-spec/`.

## CI

GitHub Actions en `.github/workflows/ci.yml` (plantilla: [`docs/_base/00-github-actions-ci-scaffold.md`](docs/_base/00-github-actions-ci-scaffold.md)). Requiere secret **`VITE_DEVEXTREME_LICENSE`** en el repositorio.

## Operación / releases

- Runbook actualización versión (BASE, Forge + Vercel): [`docs/_base/00-runbook-actualizacion-version.md`](docs/_base/00-runbook-actualizacion-version.md) — anexo PedidosWeb §10 (tablas `pq_pivots_*`, `pq_excel_*`, `pq_grid_layouts*`)
- Aviso commit/push (migrate, seeds, datos fijos): [`docs/_base/00-commit-push-revision-version-deploy.md`](docs/_base/00-commit-push-revision-version-deploy.md)

**No commit/push** sin autorización explícita.
