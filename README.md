# PaqSuite-IA-PedidosWeb

Scaffold inicial del proyecto en modo **MONO**, alineado a `docs/_base/00-inicio-arquitectura.md`.

## Estado actual

- Herencia IA y symlinks verificados (`base`, `mono`, `prompts`, `docs/_base`, `docs/_mono`, `docs/00-contexto/_mono`).
- Estructura inicial de `backend/` y `frontend/` creada.
- Documentación de scaffold: `docs/02-producto/PedidosWeb/PedidosWeb_Scaffold_Inicio_Proyecto.md`.

## Patrón MONO vigente

- Entrada tenant: `https://{cliente}.pedidosweb.paqsystems.com`
- Frontend: `https://frontend.pedidosweb.paqsystems.com`
- Backend: `https://backend.pedidosweb.paqsystems.com`
- Header tenant: `X-Paq-Cliente`
- Base por tenant: `pq_pedidosweb_{cliente}`

## Arranque local (siguiente paso)

### 1) Backend Laravel

Desde `backend/`:

```powershell
composer create-project laravel/laravel .
php artisan key:generate
php artisan serve --port=8000
```

Luego integrar los archivos ya scaffolded (`routes/api.php`, controller de health, OpenAPI y tests).

### 2) Frontend React + Vite

Desde `frontend/`:

```powershell
npm install
npm run dev
```

Servidor esperado: `http://localhost:3000`.

### 3) Variables de entorno

- Copiar `.env.example` a `.env`.
- Ajustar DB local (`pq_pedidosweb_demo` u otra definida por entorno).
- Confirmar `TENANT_HEADER_NAME=X-Paq-Cliente`.

## Checklist para comenzar desarrollo funcional

- [ ] Laravel inicializado en `backend/`.
- [ ] Dependencias frontend instaladas y app levantando.
- [ ] Endpoint `GET /api/v1/health` respondiendo.
- [ ] Test de feature backend ejecutando.
- [ ] Smoke E2E frontend ejecutando.
- [ ] OpenAPI base accesible en `/api/documentation` (una vez instalado L5-Swagger).

## Flujo OpenSpec (skills y reglas)

Herencia vía symlinks a **PaqSuite-IA-BASE**:

| Paso | Skill (slash) | Regla |
|------|----------------|--------|
| A1 | `/spec-ambiguity-review` | `12` → `11-spec-ambiguity-review.md` |
| B1 | `/enrich-user-story` | `12-enrich-user-story-desde-spec.md` |
| D1 | `/ai-planning-mode` | `13-ai-planning-mode.md` |
| F1 | `/agent-verification-guide` | `14-agent-verification-guide.md` |
| PR | `/write-pr-report` | `15-write-pr-report.md` |

Rutas efectivas: `.cursor/skills/<nombre>/SKILL.md` y `.cursor/rules/base/00-arquitectura/`. Orquestación: `01-prompts-programados-dispatcher.md`.

HU/TR del MVP: `docs/03-historias-usuario/`, `docs/05-open-spec/`.

## Regla de trabajo

No realizar `commit` ni `push` sin autorización explícita.
