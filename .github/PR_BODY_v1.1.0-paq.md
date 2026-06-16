## Summary

Entrega **Fase 1 MVP** del portal **MONO PedidosWeb** en la rama **`v1.1.0-paq`**, integrando scaffold fullstack, épica **101 PedidosWeb**, cierres de **Control de Calidad PQ #1–#5**, saneamiento documental y la épica **GEN-08 Pivots (D1)**.

1. **Scaffold fullstack** (Laravel 10 + React/Vite/DevExtreme) con **GEN-01 / GEN-02 / GEN-03** cerrados en documentación e implementación.
2. **Épica 101 — PedidosWeb**: Parte D completa + cierre formal **F** (TR 101-02 … 101-15 + **TR-GEN-04** consulta parámetros).
3. **CC PQ #1**: mejoras en carga, consultas, dashboard, mail, inactividad y manual de usuario.
4. **CC PQ #2**: layouts propios `(*)`, plantilla sistema, export Excel formateado (fechas, booleanos, totales pie).
5. **CC PQ #3**: `SelectBoxDx` con loading, auto-match único, carga artículos optimizada, totalizadores pie en layouts.
6. **CC PQ #4**: pivots en informes (detalle pedidos, deuda, cheques, stock) + formato decimal unificado `#,##0.00`.
7. **CC PQ #5**: listbox artículos en carga — disponible neto `stock − comprometido − comprometido_web`; display `codigo - descripcion — Disp. X (Y)`.
8. **Fix post-QA**: evita loop infinito en búsqueda de artículos (deduplicación, manejo de errores, sin doble `load()` en auto-match).
9. **GEN-08 Pivots (SPEC-001-08)**: motor metadata/API, PivotGrid, diseños persistentes, export Excel client-side — **flags default `false`** hasta activación en deploy.

**Compare:** `main` ← **`v1.1.0-paq`**  
**Tip:** `a87f2a2` — `fix(pedidosweb): evitar loop infinito en busqueda de articulos en carga.`

**Commits clave (cronología reciente):**

| Commit | Resumen |
|--------|---------|
| `a87f2a2` | **Fix** — loop infinito búsqueda artículos en carga (dedupe + errores) |
| `4702e06` | **CC PQ #5** — restaurar disponible neto con `comprometido_web` en listbox carga |
| `4c3dc02` | **CC PQ #4** — pivots en informes + formato decimal unificado |
| `7244247` | CC PQ #5 — separación lookup carga vs consulta stock (docs) |
| `2735155` | **GEN-08** epic Pivots D1 — motor, PivotGrid, layouts, export |
| `5eefea3` | Fix `onOpened` DevExtreme en SelectBox artículos |
| `e387e58` | **CC PQ #3** — listas DX, layouts pie, carga artículos |
| `514ea48` | **CC PQ #1** — dashboard, consultas, fechas, manual |
| `ad30265` | Ruteo SPA Vercel (`frontend/vercel.json`) |

Informes de cierre: [`F-101-PedidosWeb-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md) · [`F-GEN-04-consulta-parametros-cierre.md`](docs/04-tareas/001-Generaliddes/F-GEN-04-consulta-parametros-cierre.md) · [`F-CC-PQ-02-GEN-03-cierre-formal.md`](docs/04-tareas/001-Generaliddes/F-CC-PQ-02-GEN-03-cierre-formal.md) · [`F-CC-PQ-03-cierre-formal.md`](docs/04-tareas/001-Generaliddes/F-CC-PQ-03-cierre-formal.md) · [`F-CC-PQ-4-pivot-informes.md`](docs/04-tareas/101-PedidosWeb/F-CC-PQ-4-pivot-informes.md) · [`F-GEN-08-cierre-formal.md`](docs/04-tareas/001-Generaliddes/F-GEN-08-cierre-formal.md) · [`00-ControlCalidad-PQ.md`](docs/00-ControlCalidad/00-ControlCalidad-PQ.md)

---

## Bloque Generalidades

| Área | Estado |
|------|--------|
| Shell, menú sidebar, avatar, idioma (5 locales), temas | Finalizado |
| Login, sesión, recuperación/cambio contraseña, seed seguridad | Finalizado |
| Expiración por inactividad (última acción usuario) | Finalizado — CC PQ #1 |
| `DataGridDx`, layouts, ABM modal, export Excel | Finalizado + CC PQ #2/#3 |
| `SelectBoxDx` (loading, auto-match) | Finalizado — CC PQ #3 |
| Visibilidad comercial (cliente / vendedor / supervisor) | Finalizado |
| Consulta de parámetros (TR-GEN-04) | Finalizado — CC PQ #3 alineación Valor |
| **Pivots (SPEC-001-08)** | **Finalizado (D1)** — flags `pivotsEnabled` / `pivotLayoutsEnabled` default **false** |
| CI GitHub Actions (smoke backend + build frontend) | `.github/workflows/ci.yml` |

---

## Bloque GEN-08 — Pivots (SPEC-001-08)

| TR | Entregable | Estado |
|----|------------|--------|
| TR-GEN-08-motor-metadata-pivots | Catálogo `pq_pivots_*`, API metadata/data/validate | Finalizado |
| TR-GEN-08-pivotgrid-visualizacion | `ConsultaGrillaPivotShell`, toggle grilla/pivot | Finalizado |
| TR-GEN-08-layouts-pivot | `pq_pivots_config` + API CRUD, toolbar diseños | D1 — Aprobado |
| TR-GEN-08-exportacion-pivot | Export client-side Excel básico/tabla dinámica | D1 — Aprobado |

**CC PQ #4:** pivots extendidos a detalle pedidos, deuda, cheques y stock (`ConsultaInformePivotPage`, seeder informes, agregaciones y `#,##0.00` unificado).

**Activación en tenant (post-merge, no incluida en deploy MVP):**

1. Migraciones `2026_06_11_100000_*`, `2026_06_11_110000_*`.
2. Seeders `PivotCatalogPilotSeeder` + `PivotCatalogInformesSeeder` (o `backend/scripts/sql/seed-pivot-catalog.sql`).
3. `.env`: `PIVOTS_ENABLED=true`, `PIVOT_LAYOUTS_ENABLED=true` (opcional layouts).

---

## Bloque PedidosWeb (101) — D + F + CC #1–#5

| TR | Entregable | Estado |
|----|------------|--------|
| 101-01 | Tenancy multi-empresa | **Diferida** (`EMPRESAS_CONEXION`) |
| 101-02 … 101-15 | Backend + frontend MVP | Finalizado |
| TR-GEN-04 | Consulta parámetros | Finalizado + CC #3 |

### Highlights CC PQ #3 / fix búsqueda artículos

| Tema | Cambio |
|------|--------|
| `SelectBoxDx` | Loading indicator, `onOpened`, auto-match único |
| Carga artículos | Política carga diferida (≥4 chars, sin consulta al foco) |
| **Loop infinito** | Deduplica peticiones, absorbe errores 504/CORS, evita segundo `load()` en auto-match |
| Layouts pie | Totalizadores persisten en guardar/cargar diseño |

### Highlights CC PQ #5

| Tema | Cambio |
|------|--------|
| Listbox artículos (browse) | Disponible neto: `stock − comprometido − comprometido_web` (pedidos `estado = 0`) |
| Consulta stock | Misma fórmula de disponible neto (sin regresión) |
| Display ítem | `codigo - descripcion — Disp. X (Y)` con base opcional entre paréntesis |

---

## API (referencia)

Rutas bajo `/api/v1/` (autenticadas + tenant `X-Paq-Cliente`):

- Comprobantes, pedidos, presupuestos, consultas, dashboard, integración (MVP existente)
- **Pivots:** `GET/POST /pivots/consultas/{id}/metadata|data|validate-structure`
- **Pivot layouts:** `GET/POST/PUT/DELETE /pivot-configs*`
- **Config pública:** `pivotsEnabled`, `pivotLayoutsEnabled` en `GET /config/public`

OpenAPI: `backend/storage/api-docs/api-docs.json`

---

## Validaciones ejecutadas

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | 75+ passed (skips sin SQL Server) |
| `php artisan test --filter=Pivot` | Feature + unit (requieren tenant SQL Server) |
| `npm run build` (frontend) | OK |
| `npm run test` (Vitest) | Unit pivot + grid export + SelectBox + `articulosCargaRemoteLoad` |
| E2E pivot | `pivot-historial`, `pivot-informes`, `pivot-layout-persistencia`, `pivot-export` |
| E2E MVP | `consultas-d1`, `mvp-section9`, `grid-layouts`, `grid-export` |

---

## Observaciones (no bloquean merge)

- Tests integración pivot y PedidosWeb requieren **SQL Server tenant** en CI (skipped local sin BD).
- **GEN-08** fuera del MVP release hasta flags + migraciones en tenant objetivo.
- Advertencia Vite: chunk DevExtreme > 500 kB (preexistente).
- **SPEC-001-07** (importar Excel): documentación A1; implementación pendiente de D1.
- TR-101-01 permanece diferida hasta `EMPRESAS_CONEXION`.

---

## Test plan

### MVP base (F)

- [ ] Login con usuario ERP o seed MVP + header `X-Paq-Cliente`
- [ ] Carga: grabar pedido y presupuesto con cabecera completa y renglones
- [ ] Consultas: ingresados, pendientes, presupuestos, detalle, stock, deuda, cheques, historial
- [ ] General → Consulta de parámetros (sin columna clave, Valor centrado)
- [ ] Dashboard operativo: KPIs visibles

### CC PQ #3 / fix artículos

- [ ] SelectBox cliente/artículo: indicador loading; auto-match si único resultado
- [ ] Carga artículos: búsqueda diferida sin bloquear UI
- [ ] **Búsqueda artículos:** una sola petición por término; sin loop ante timeout backend
- [ ] Desplegable vacío (flecha): carga primer lote sin reintentos infinitos

### CC PQ #4 / #5

- [ ] Pivots en detalle pedidos, deuda, cheques, stock (con flags activos)
- [ ] Valores numéricos pivot con formato `#,##0.00`
- [ ] Listbox artículos en carga: disponible neto con pedidos web ingresados
- [ ] Artículo con base: paréntesis muestra disponible del código base

### GEN-08 Pivots (con flags activos en tenant piloto)

- [ ] Migraciones + seeders pivot en tenant `desarrollo`
- [ ] `.env`: `PIVOTS_ENABLED=true`, `PIVOT_LAYOUTS_ENABLED=true`
- [ ] Historial ventas e informes CC #4: toggle Grilla / Pivot
- [ ] Guardar/cargar diseño pivot; export Excel pivot
- [ ] Sin flags: solo grilla (comportamiento MVP)

### CI / despliegue

- [ ] Workflow `.github/workflows/ci.yml` en verde
- [ ] Deploy frontend Vercel con `frontend/vercel.json`
- [ ] `php artisan test` con tenant SQL Server + seeds (tanda integración opcional)
