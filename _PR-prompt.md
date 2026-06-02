# Pull Request Report

**Base:** `v1.1.0` (documentación, reglas Cursor, symlinks — sin `backend/` ni `frontend/` en Git)  
**Compare:** `v1.1.0-paq` (scaffold MVP fullstack + oleadas GEN-01 / GEN-02 / GEN-03)  
**Último commit en rama:** `1bb5262` — `feat(gen-03): bloque SPEC-001-03 UI transversal completo`  
**Magnitud:** ~433 archivos, +51 976 líneas (primera entrega ejecutable del portal MONO PedidosWeb)

**Crear PR:** [Compare v1.1.0...v1.1.0-paq](https://github.com/<org>/PaqSuite-IA-PedidosWeb/compare/v1.1.0...v1.1.0-paq) — sustituir `<org>` por el remoto real. `gh` no disponible en el entorno del agente; usar la UI de GitHub o `gh pr create` localmente.

---

## Resumen

Esta rama introduce el **scaffold MVP ejecutable** (Laravel 10 + React/Vite/DevExtreme) y cierra las oleadas de **Generalidades** necesarias para operar el portal: experiencia base (shell, menú, idioma, temas, avatar), acceso y seguridad (login, sesión, contraseñas, roles/seed, menú API, visibilidad de datos demo) y **UI transversal** (grillas `DataGridDx`, layouts persistentes, patrón ABM modal, exportación Excel).

No es un PR acotado solo a GEN-03: es la **primera integración de producto** sobre la línea documental `v1.1.0`.

## Contexto funcional

PedidosWeb MONO necesita un portal autenticado con envelope API homogéneo, menú por permisos, preferencias de usuario (idioma/tema), flujos de contraseña alineados al patrón auth DevExtreme, y componentes transversales de listado reutilizables antes de implementar procesos de negocio (SPEC-101).

## SPEC / HU / TR relacionadas

### SPEC-001-01 — Experiencia base

| TR | HU | Estado en rama |
|----|-----|----------------|
| [TR-GEN-01-shell-layout](docs/04-tareas/001-Generaliddes/TR-GEN-01-shell-layout.md) | [HU-GEN-01-shell-layout](docs/03-historias-usuario/001-Generaliddes/HU-GEN-01-shell-layout.md) | Implementado — F formal en [F-GEN-01-02-cierre-formal](docs/04-tareas/001-Generaliddes/F-GEN-01-02-cierre-formal.md) |
| [TR-GEN-01-menu-general-sidebar](docs/04-tareas/001-Generaliddes/TR-GEN-01-menu-general-sidebar.md) | [HU-GEN-01-menu-general-sidebar](docs/03-historias-usuario/001-Generaliddes/HU-GEN-01-menu-general-sidebar.md) | Implementado (D 2026-05-30) |
| [TR-GEN-01-menu-avatar](docs/04-tareas/001-Generaliddes/TR-GEN-01-menu-avatar.md) | [HU-GEN-01-menu-avatar](docs/03-historias-usuario/001-Generaliddes/HU-GEN-01-menu-avatar.md) | Implementado (D) |
| [TR-GEN-01-idioma](docs/04-tareas/001-Generaliddes/TR-GEN-01-idioma.md) | [HU-GEN-01-idioma](docs/03-historias-usuario/001-Generaliddes/HU-GEN-01-idioma.md) | Implementado — F formal en F-GEN-01-02 |
| [TR-GEN-01-apariencia-temas](docs/04-tareas/001-Generaliddes/TR-GEN-01-apariencia-temas.md) | [HU-GEN-01-apariencia-temas](docs/03-historias-usuario/001-Generaliddes/HU-GEN-01-apariencia-temas.md) | Implementado — F formal en F-GEN-01-02 |
| TR-GEN-01-ayuda-externa | HU-GEN-01-ayuda-externa | **Fuera de alcance** (Should; no implementado) |

**SPEC:** [SPEC-001-01-experiencia-base](docs/05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md)

### SPEC-001-02 — Acceso y seguridad

| TR | HU | Estado en rama |
|----|-----|----------------|
| [TR-GEN-02-modelo-roles-permisos-seed](docs/04-tareas/001-Generaliddes/TR-GEN-02-modelo-roles-permisos-seed.md) | HU-GEN-02-modelo-roles-permisos-seed | Implementado (seed + matriz) |
| [TR-GEN-02-login-sesion](docs/04-tareas/001-Generaliddes/TR-GEN-02-login-sesion.md) | HU-GEN-02-login-sesion | Implementado — F formal en F-GEN-01-02 |
| [TR-GEN-02-recuperacion-contrasena](docs/04-tareas/001-Generaliddes/TR-GEN-02-recuperacion-contrasena.md) | HU-GEN-02-recuperacion-contrasena | Implementado — F formal en F-GEN-01-02 |
| [TR-GEN-02-cambio-contrasena](docs/04-tareas/001-Generaliddes/TR-GEN-02-cambio-contrasena.md) | HU-GEN-02-cambio-contrasena | Implementado — F formal en F-GEN-01-02 |
| [TR-GEN-02-autorizacion-menu-api](docs/04-tareas/001-Generaliddes/TR-GEN-02-autorizacion-menu-api.md) | HU-GEN-02-autorizacion-menu-api | Implementado |
| [TR-GEN-02-visibilidad-datos-pedidosweb](docs/04-tareas/001-Generaliddes/TR-GEN-02-visibilidad-datos-pedidosweb.md) | HU-GEN-02-visibilidad-datos-pedidosweb | Implementado (slice MVP demo) |
| [TR-GEN-02-expiracion-inactividad](docs/04-tareas/001-Generaliddes/TR-GEN-02-expiracion-inactividad.md) | HU-GEN-02-expiracion-inactividad | Implementado en código (`SessionLifecycleManager`); TR con observaciones C1 (multi-tab) |
| [TR-GEN-02-politicas-endpoints](docs/04-tareas/001-Generaliddes/TR-GEN-02-politicas-endpoints.md) | HU-GEN-02-politicas-endpoints | Marco / matriz viva — no slice único |

**SPEC:** [SPEC-001-02-acceso-seguridad](docs/05-open-spec/001-Generaliddes/SPEC-001-02-acceso-seguridad.md)  
**Matriz viva:** [matriz-permisos-mvp.md](docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md)

### SPEC-001-03 — UI transversal

| TR | HU | Estado en rama |
|----|-----|----------------|
| [TR-GEN-03-grillas-listados](docs/04-tareas/001-Generaliddes/TR-GEN-03-grillas-listados.md) | HU-GEN-03-grillas-listados | **Finalizado** |
| [TR-GEN-03-layouts-grilla](docs/04-tareas/001-Generaliddes/TR-GEN-03-layouts-grilla.md) | HU-GEN-03-layouts-grilla | **Finalizado** |
| [TR-GEN-03-patron-abm](docs/04-tareas/001-Generaliddes/TR-GEN-03-patron-abm.md) | HU-GEN-03-patron-abm | **Finalizado** |
| [TR-GEN-03-exportaciones](docs/04-tareas/001-Generaliddes/TR-GEN-03-exportaciones.md) | HU-GEN-03-exportaciones | **Finalizado** |

**SPEC:** [SPEC-001-03-ui-transversal](docs/05-open-spec/001-Generaliddes/SPEC-001-03-ui-transversal.md)  
**Cierre F:** [F-GEN-03-cierre-formal](docs/04-tareas/001-Generaliddes/F-GEN-03-cierre-formal.md) — Aprobado con observaciones

### Documentación sin implementación en esta rama

- **SPEC-001-10** (chat asistente IA): solo definición OpenSpec/HU/TR (`5db6571`); sin código de producto.

---

## Historial de commits (orden cronológico)

| Commit | Descripción |
|--------|-------------|
| `c82f586` | Docs OpenSpec Fase 0, HU B1 y plan scaffold MVP |
| `bdb4186` | Cambio de contraseña autenticado + gate `firstLogin` |
| `5f5c485` | `.gitignore`, plantillas `.env`, README arranque |
| `6d7e6f2` | OpenSpec, HU, TR Generalidades y producto |
| `0c93ecb` | Backend Laravel: seed, login, menú, health |
| `6553cc4` | Frontend React: shell, sidebar, preferencias |
| `17345a5` | Reglas Cursor tablas seguridad SQL |
| `90f6c2d` | Idioma (D1), Swagger L5, schemas OpenAPI tipados |
| `a51b518` | Menú avatar, `openInNewTab` |
| `f9b995e` | Apariencia/temas, PATCH theme |
| `5db6571` | Docs SPEC-001-10 chat IA |
| `444d630` | Cierre C1/D1 pendientes, baseline OpenAPI |
| `69d04d3` | Slices MVP seguridad y visibilidad |
| `390cd79` | Auth UI y preferencias alineadas MONO |
| `3bdff13` | Tokens CSS variables auth PaqSuite |
| `1bb5262` | Bloque GEN-03 completo (grillas, layouts, ABM, export) |

---

## Cambios realizados por capa

### Repositorio / DevOps

- `.gitignore` raíz, `backend/.env.example`, `frontend/.env.example`
- [README.md](README.md) con arranque local backend/frontend
- Symlinks herencia IA (`base`, `mono`, `docs/_base`, `docs/00-contexto/_mono`) — sin duplicar MONO en Git de PedidosWeb

### Backend (Laravel 10)

- **Scaffold:** envelope `ApiResponse`, middleware tenant `X-Paq-Cliente`, Sanctum, `GET /api/v1/health`
- **Auth:** login/logout/me, forgot/reset/change password, bootstrap sesión en login
- **Preferencias:** `GET/PATCH /users/me/preferences`, locale, theme
- **Menú:** `GET /user/menu` filtrado por permisos
- **Visibilidad demo:** clientes, comprobante, resumen dashboard
- **Grid layouts (GEN-03):** CRUD + layout activo, `PublicConfigController` (`gridLayoutsEnabled`)
- **Seed MVP:** `paqsuite:seed-seguridad-mvp`, menús MVP, usuarios QA (ver matriz)
- **OpenAPI:** L5-Swagger + schemas tipados (commit `90f6c2d`)
- **Tests Feature:** `AuthLoginTest`, `ChangePasswordTest`, `PasswordRecoveryTest`, `SeedSeguridadMvpTest`, `SeedMenusMvpTest`, `UserMenuTest`, `UserPreferencesTest`, `VisibilityDataTest`, `GridLayoutTest`, `HealthCheckTest`, `OpenApiDocumentationTest`

### Frontend (React + Vite + DevExtreme)

- **Shell:** layout autenticado, rutas protegidas, dashboard
- **Auth (DevExtreme):** login, forgot/reset/change password, gate `firstLogin`, inactividad (`SessionLifecycleManager`)
- **Menú:** sidebar TreeView, tres controles header, persistencia user/terminal
- **Avatar:** menú usuario, cambio contraseña, logout, `openInNewTab`
- **i18n:** `LocaleProvider`, 5 locales, selector, sync DevExtreme + claves `grid.dx.*`
- **Temas:** selector apariencia, paleta en shell, PATCH theme
- **GEN-03:** `DataGridDx`, `gridLayouts`, `gridExport`, `abm`, demos `/demo/abm`, `/demo/export-empty`
- **Estilos auth:** variables CSS centralizadas (`3bdff13`)

### Base de datos

- Migraciones seguridad MVP (`Pq_*`), menús, preferencias usuario
- Migración `pq_grid_layouts` + `pq_grid_layout_last_used` (GEN-03)

### Documentación

- OpenSpec Fase 0, HU/TR Generalidades (001-Generaliddes)
- [F-GEN-01-02-cierre-formal.md](docs/04-tareas/001-Generaliddes/F-GEN-01-02-cierre-formal.md) — oleada shell/idioma/temas/login/contraseñas
- [F-GEN-03-cierre-formal.md](docs/04-tareas/001-Generaliddes/F-GEN-03-cierre-formal.md) — bloque UI transversal
- [README 001-Generaliddes](docs/04-tareas/001-Generaliddes/README.md) — orden TR y estado GEN-03
- Reglas: `devextreme-frontend.mdc`, `41-i18n-and-testid.md` (sub-checklist `DataGridDx`)
- Patrón MONO (symlink): `patron-i18n-grilla-devextreme` — versionado en repo **PaqSuite-IA-MONO**

### Repos hermanos (fuera del diff de este PR)

- Commits documentados en **PaqSuite-IA-BASE** (`49fa43f`) y **PaqSuite-IA-MONO** (`4793b50`) para reglas/patrones i18n grilla

---

## API expuesta (referencia rápida)

Ver [matriz-permisos-mvp.md](docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md) y `backend/routes/api.php`.

Públicas: `health`, `auth/login`, `auth/password/forgot|reset`.  
Autenticadas: `auth/logout|me|password/change`, `config/public`, `user/menu`, preferencias, `grid-layouts/*`, visibilidad `clientes`, `comprobantes/{id}`, `dashboard/resumen`.

---

## Validaciones y tests

### Ejecutados y documentados en cierres F

| Ámbito | Comando / spec | Resultado documentado |
|--------|----------------|------------------------|
| Frontend unit | `npm run test` (Vitest) | **60 passed** (incl. post-F `DataGridDx.test.tsx`) |
| Frontend build | `npm run build` | **OK** (warning chunk DX > 500 kB) |
| Backend layouts | `php artisan test --filter=GridLayout` | **6 passed** |
| E2E GEN-03 | `grid-transversal`, `grid-layouts`, `grid-export`, `abm-transversal` | **9 passed** |
| E2E F-GEN-01-02 | `password-recovery.spec.ts`, `theme.spec.ts` | **OK** |
| QA manual GEN-03 | Dashboard + `/demo/abm` (es) | **OK** (usuario) |

### Suite E2E completa en rama (ejecutar en CI / pre-merge)

| Spec | TR / área |
|------|-----------|
| `smoke.spec.ts` | Arranque portal |
| `change-password.spec.ts` | TR-GEN-02-cambio-contrasena |
| `password-recovery.spec.ts` | TR-GEN-02-recuperacion-contrasena |
| `locale.spec.ts` | TR-GEN-01-idioma |
| `theme.spec.ts` | TR-GEN-01-apariencia-temas |
| `menu-sidebar.spec.ts` | TR-GEN-01-menu-general-sidebar |
| `avatar-menu.spec.ts` | TR-GEN-01-menu-avatar |
| `grid-transversal.spec.ts` | TR-GEN-03-grillas-listados |
| `grid-layouts.spec.ts` | TR-GEN-03-layouts-grilla |
| `grid-export.spec.ts` | TR-GEN-03-exportaciones |
| `abm-transversal.spec.ts` | TR-GEN-03-patron-abm |

```powershell
cd frontend
npm run test:e2e
```

### Backend Feature (requiere SQL Server + seed estable)

```powershell
cd backend
php artisan test
```

**Observación F-GEN-01-02:** `PasswordRecoveryTest` puede fallar si `paqsuite:seed-seguridad-mvp` no completa en el entorno local.

---

## Evidencia de cierre

| Documento | Veredicto |
|-----------|-----------|
| [F-GEN-01-02-cierre-formal](docs/04-tareas/001-Generaliddes/F-GEN-01-02-cierre-formal.md) | Aprobado con observaciones (oleada parcial GEN-01/02; no todos los TR de carpeta) |
| [F-GEN-03-cierre-formal](docs/04-tareas/001-Generaliddes/F-GEN-03-cierre-formal.md) | Aprobado con observaciones (4 TR GEN-03) |

---

## Riesgos

- **Primera carga del repo:** clon requiere symlinks BASE/MONO y SQL Server para seed completo
- **Chunk DevExtreme** grande en build Vite — evaluar code-split en release
- **Patrón i18n grilla** en symlink MONO — equipos deben actualizar MONO/BASE además de PedidosWeb
- **Demos** `/demo/*` — no sustituyen procesos SPEC-101
- **PasswordRecoveryTest** dependiente de entorno seed
- **Inactividad multi-tab** — TR-GEN-02-expiracion-inactividad con ambigüedades C1 abiertas en documento

---

## Pendientes / follow-ups (post-merge)

- Integrar `DataGridDx` en pantallas de negocio (SPEC-101)
- CI: suite E2E completa + `php artisan test` en pipeline
- Re-ejecutar `PasswordRecoveryTest` con seed estable
- TR-GEN-01-ayuda-externa (Should)
- SPEC-001-10 chat IA (futuro)
- OpenAPI detallado endpoints `grid-layouts` si falta en Swagger

---

## Checklist para reviewer

### Scaffold y arquitectura

- [ ] `GET /api/v1/health` devuelve envelope MONO
- [ ] Header `X-Paq-Cliente` requerido en rutas tenant
- [ ] README y `.env.example` permiten arranque sin secretos en repo

### GEN-01 / GEN-02 (experiencia y seguridad)

- [ ] Login `cliente.mvp` / `secret` → dashboard con menú
- [ ] `primerIngreso.mvp` fuerza cambio de contraseña
- [ ] Recuperación y reset con UI DevExtreme + i18n
- [ ] Selector idioma y tema persisten (PATCH) y shell refleja paleta
- [ ] Sidebar: tres controles, menú acotado con `vendedor.acotado.mvp`
- [ ] Avatar: logout, cambio clave, enlaces externos si aplica
- [ ] `usuario.sinPermiso.mvp` → 403 coherente con envelope

### GEN-03 (UI transversal)

- [ ] Dashboard `DataGridDx`: filtros, agrupación, column chooser en idioma activo
- [ ] Menú contextual encabezado (ordenar/agrupar) traducido
- [ ] Totalizadores por columna (pie; dos columnas distintas)
- [ ] Toolbar layouts: guardar como, cargar, persistencia
- [ ] Export Excel con datos; deshabilitado en `/demo/export-empty`
- [ ] `/demo/abm`: alta/edición/baja con confirmación
- [ ] Dashboard **consulta** sin botón + ABM; hint `grid.consulta.noAbmHint` si aplica

### Calidad

- [ ] Matriz permisos alineada con rutas nuevas
- [ ] Sin secretos ni `.env` reales en diff
- [ ] `data-testid` estables en controles DX

---

## Notas para QA

| Usuario seed | Uso |
|--------------|-----|
| `cliente.mvp` / `secret` | Flujo feliz cliente + dashboard |
| `vendedor.acotado.mvp` | Menú parcial |
| `supervisor.mvp` | Acceso total |
| `primerIngreso.mvp` | Gate first login |
| `usuario.sinPermiso.mvp` | 403 permisos |

- Cambiar idioma **sin F5** y validar textos de grilla (ítems 21–28 en [TR-GEN-01-idioma](docs/04-tareas/001-Generaliddes/TR-GEN-01-idioma.md) §4)
- Tenant local: `desarrollo` o `demo` vía `X-Paq-Cliente`

---

## Título sugerido para el PR

`feat(mvp): scaffold PedidosWeb MONO — GEN-01/02 experiencia y seguridad + GEN-03 UI transversal`

## Cuerpo sugerido (resumen corto para GitHub)

Integración inicial del portal PedidosWeb sobre `v1.1.0`: backend Laravel y frontend React/DevExtreme con login, menú por permisos, preferencias (idioma/tema), visibilidad demo, y bloque GEN-03 (`DataGridDx`, layouts, ABM, export Excel). Incluye seeds QA, tests automatizados y cierres F documentados. Requiere symlinks BASE/MONO y SQL Server para seed completo.
