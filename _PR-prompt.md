# Pull Request Report

**Base:** `main`  
**Compare:** `v1.1.0` (release MVP integrado desde `v1.1.0-paq`)  
**Último commit en rama:** `f2c69ab` — merge `v1.1.0-paq` (listbox catálogo provisional + planes NOLOCK)  
**Tip funcional:** `893319a` — listbox carga provisional solo catálogo `codigo - descripcion`

**Crear PR:** [Compare main...v1.1.0](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/main...v1.1.0)

```powershell
gh pr create --base main --head v1.1.0 --title "release(v1.1.0): MVP PedidosWeb + CC PQ #1–#5 + GEN-08 Pivots D1" --body-file .github/PR_BODY_v1.1.0.md
```

**PR incremental paq → release:** [Compare v1.1.0...v1.1.0-paq](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/v1.1.0...v1.1.0-paq) — ver `.github/PR_BODY_v1.1.0-paq-merge.md`

---

## Resumen

Release **v1.1.0** del portal **MONO PedidosWeb**: scaffold fullstack ejecutable (Laravel 10 + React/Vite/DevExtreme), épica **101 PedidosWeb** (D + F), cierres **Control de Calidad PQ #1–#5**, épica **GEN-08 Pivots (D1)** y planes técnicos de concurrencia SQL.

Incluye oleadas **GEN-01 / GEN-02 / GEN-03** (experiencia base, seguridad, UI transversal) y procesos de negocio MVP: carga de pedidos/presupuestos, consultas, dashboard, integración y pivots en informes (con flags default `false`).

## Contexto funcional

PedidosWeb MONO necesita un portal autenticado con envelope API homogéneo, menú por permisos, preferencias de usuario, flujos de contraseña DevExtreme, componentes transversales de listado y procesos comerciales MVP antes de tenancy multi-empresa (TR-101-01 diferida).

## SPEC / HU / TR relacionadas

### SPEC-001-01 — Experiencia base

Shell, menú sidebar, avatar, idioma (5 locales), temas — **Finalizado** (F-GEN-01-02).

### SPEC-001-02 — Acceso y seguridad

Login, sesión, recuperación/cambio contraseña, seed seguridad, menú API, visibilidad comercial, inactividad — **Finalizado**.

### SPEC-001-03 — UI transversal

`DataGridDx`, layouts, ABM modal, export Excel — **Finalizado** + CC PQ #2/#3.

### SPEC-001-08 — Pivots

Motor metadata/API, PivotGrid, layouts pivot, export Excel — **Finalizado (D1)**. CC PQ #4 extiende a informes. Flags default **false**.

### SPEC-101 — PedidosWeb

TR 101-02 … 101-15 + TR-GEN-04 — **Finalizado** (F-101). TR-101-01 **diferida**.

### Control de Calidad PQ

| # | Tema | Estado |
|---|------|--------|
| #1 | Dashboard, consultas, inactividad, manual | Cerrado |
| #2 | Layouts `(*)`, export Excel formateado | Cerrado |
| #3 | SelectBox loading, layouts pie, parámetros | Cerrado |
| #4 | Pivots informes + `#,##0.00` | Cerrado |
| #5 | Listbox artículos carga | **Provisional** — solo catálogo `codigo - descripcion` |

Informes: [`00-ControlCalidad-PQ.md`](docs/00-ControlCalidad/00-ControlCalidad-PQ.md)

---

## Historial de commits recientes

| Commit | Descripción |
|--------|-------------|
| `f2c69ab` | Merge `v1.1.0-paq` — listbox catálogo provisional + planes NOLOCK |
| `893319a` | Listbox carga provisional (`solo_catalogo`) |
| `6c8c916` | Planes NOLOCK y framework compartido |
| `a87f2a2` | Fix loop infinito búsqueda artículos |
| `4c3dc02` | CC PQ #4 — pivots informes |
| `2735155` | GEN-08 Pivots D1 |
| `514ea48` | CC PQ #1 |

---

## Cambios realizados por capa

### Backend (Laravel 10)

- Scaffold envelope `ApiResponse`, tenant `X-Paq-Cliente`, Sanctum, health
- Auth, preferencias, menú, visibilidad comercial
- Comprobantes, pedidos, presupuestos, consultas, dashboard
- Pivots: catálogo `pq_pivots_*`, API metadata/data, layouts `pq_pivots_config`
- Artículos carga: `solo_catalogo` omite stock en lookup browse
- Seeds MVP, tests Feature/Unit

### Frontend (React + Vite + DevExtreme)

- Shell, auth DevExtreme, menú, i18n 5 locales, temas
- `DataGridDx`, layouts, ABM, export Excel
- Carga pedidos: precarga catálogo artículos, display `codigo - descripcion`
- Consultas + pivots informes (`ConsultaGrillaPivotShell`)
- E2E Playwright MVP + pivot

### Base de datos

- Migraciones seguridad, menús, preferencias, grid layouts
- Migraciones pivots (`2026_06_11_100000_*`, `2026_06_11_110000_*`)
- Seed SQL opcional: `backend/scripts/sql/seed-pivot-catalog.sql`

### Documentación / planes

- OpenSpec, HU/TR 001 y 101, cierres F formales
- `.cursor/plans/nolock-concurrencia-sql.plan.md`
- `.cursor/plans/paqsuite-framework-compartido.plan.md`

---

## Revisión de versión / deploy

**Conviene:** tag `v1.1.0` en merge a `main` (release MVP).

**En Forge (por tenant):**

1. `git pull` / deploy del tag o rama `v1.1.0`
2. `composer install --no-dev` + `php artisan config:cache`
3. **Migraciones** (si no aplicadas): `php artisan migrate --force` — incluye `pq_pivots_*`
4. **Pivots (opcional):** `PivotCatalogPilotSeeder` + `PivotCatalogInformesSeeder` o `backend/scripts/sql/seed-pivot-catalog.sql`
5. **`.env`:** `PIVOTS_ENABLED=true`, `PIVOT_LAYOUTS_ENABLED=true` (solo si se activan informes pivot)

**Vercel:** redeploy frontend (`frontend/vercel.json`).

**Este delta reciente (893319a):** solo código — **sin** migrate ni seed adicional.

**Smoke test post-deploy:**

- Login `cliente.mvp` → carga pedido → listbox artículos muestra `codigo - descripcion`
- Consulta stock con disponible neto
- Con flags pivot: toggle grilla/pivot en informe deuda

---

## Validaciones y tests

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | 75+ passed (skips sin SQL Server) |
| `php artisan test --filter=Pivot` | Requiere tenant SQL Server |
| `npm run build` | OK |
| `npm run test` | Vitest unit |
| E2E | `mvp-section9`, `consultas-d1`, `pivot-*`, `grid-*` |

---

## Riesgos

- Tests integración requieren SQL Server tenant en CI
- GEN-08 inactivo hasta flags + migraciones + seed
- CC PQ #5 listbox: implementación provisional sin disponible
- Chunk DevExtreme > 500 kB en build Vite
- TR-101-01 tenancy diferida

---

## Checklist para reviewer

- [ ] `GET /api/v1/health` devuelve envelope MONO
- [ ] Login seed MVP → dashboard con menú
- [ ] Carga pedido: listbox artículos precarga catálogo; display `codigo - descripcion`
- [ ] Consultas MVP operativas
- [ ] Con flags pivot: informes CC #4 con toggle grilla/pivot
- [ ] CI `.github/workflows/ci.yml` en verde
- [ ] Sin secretos en diff

---

## Título sugerido para el PR

`release(v1.1.0): MVP PedidosWeb MONO — CC PQ #1–#5 + GEN-08 Pivots D1`

## Cuerpo sugerido (resumen corto para GitHub)

Release MVP del portal PedidosWeb: backend Laravel + frontend React/DevExtreme con login, menú por permisos, carga/consultas/dashboard, pivots en informes (flags default false) y cierres CC PQ #1–#5. Listbox artículos en carga: modo provisional solo catálogo. Requiere SQL Server para seed completo y activación pivots opcional post-deploy.
