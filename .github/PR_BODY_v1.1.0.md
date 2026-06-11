## Summary

Entrega **Fase 1 MVP** del portal **MONO PedidosWeb** en la rama **`v1.1.0`**, integrando el trabajo de **`v1.1.0-paq`**, cierres de **Control de Calidad PQ #1–#3**, saneamiento documental y la épica **GEN-08 Pivots (D1)**.

1. **Scaffold fullstack** (Laravel 10 + React/Vite/DevExtreme) con **GEN-01 / GEN-02 / GEN-03** cerrados en documentación e implementación.
2. **Épica 101 — PedidosWeb**: Parte D completa + cierre formal **F** (TR 101-02 … 101-15 + **TR-GEN-04** consulta parámetros).
3. **CC PQ #1**: mejoras en carga, consultas, dashboard, mail, inactividad y manual de usuario.
4. **CC PQ #2**: layouts propios `(*)`, plantilla sistema, export Excel formateado (fechas, booleanos, totales pie).
5. **CC PQ #3**: `SelectBoxDx` con loading, auto-match único, carga artículos optimizada, totalizadores pie en layouts.
6. **GEN-08 Pivots (SPEC-001-08)**: motor metadata/API, PivotGrid en historial ventas, diseños persistentes, export Excel client-side — **flags default `false`** hasta activación en deploy.
7. **Documentación** SPEC-001-07 (importar Excel, A1) y SPEC-001-08 (pivots, A1 + F formal).

**Compare:** `main` ← **`v1.1.0`**  
**Tip:** `cc2eb07` — `merge: integrar v1.1.0-paq (GEN-08 Pivots D1, CC PQ #3, docs SPEC-001-07/08)`  
**Crear PR:** [Compare main...v1.1.0](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/main...v1.1.0)

```powershell
gh pr create --base main --head v1.1.0 --title "release(v1.1.0): MVP PedidosWeb + CC PQ #1–#3 + GEN-08 Pivots D1" --body-file .github/PR_BODY_v1.1.0.md
```

**Commits clave (cronología reciente):**

| Commit | Resumen |
|--------|---------|
| `cc2eb07` | Merge `v1.1.0-paq` → `v1.1.0` (GEN-08 + CC #3 + docs) |
| `2735155` | **GEN-08** epic Pivots D1 — motor, PivotGrid, layouts, export |
| `5eefea3` | Fix `onOpened` DevExtreme en SelectBox artículos |
| `0c2c9f2` | Docs A1 SPEC-001-08 pivots + SPEC-001-07 importar Excel |
| `e387e58` | **CC PQ #3** — listas DX, layouts pie, carga artículos |
| `777c4e9` | Merge `v1.1.0-paq` (CC PQ, saneamiento docs, MVP) |
| `514ea48` | **CC PQ #1** — dashboard, consultas, fechas, manual |
| `75e7a25` | Hallazgos CC PQ #2 (export Excel formateado) |
| `ad30265` | Ruteo SPA Vercel (`frontend/vercel.json`) |

Informes de cierre: [`F-101-PedidosWeb-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md) · [`F-GEN-04-consulta-parametros-cierre.md`](docs/04-tareas/001-Generaliddes/F-GEN-04-consulta-parametros-cierre.md) · [`F-CC-PQ-02-GEN-03-cierre-formal.md`](docs/04-tareas/001-Generaliddes/F-CC-PQ-02-GEN-03-cierre-formal.md) · [`F-CC-PQ-03-cierre-formal.md`](docs/04-tareas/001-Generaliddes/F-CC-PQ-03-cierre-formal.md) · [`F-GEN-08-cierre-formal.md`](docs/04-tareas/001-Generaliddes/F-GEN-08-cierre-formal.md) · [`00-ControlCalidad-PQ.md`](docs/00-ControlCalidad/00-ControlCalidad-PQ.md)

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
| **Pivots (SPEC-001-08)** | **D1 implementado** — flags `pivotsEnabled` / `pivotLayoutsEnabled` default **false** |
| CI GitHub Actions (smoke backend + build frontend) | `.github/workflows/ci.yml` |

Cierres formales: `F-GEN-01-02-cierre-formal.md`, `F-GEN-03-cierre-formal.md`, `F-GEN-04-consulta-parametros-cierre.md`, `F-GEN-08-cierre-formal.md`.

---

## Bloque GEN-08 — Pivots (SPEC-001-08)

| TR | Entregable | Estado |
|----|------------|--------|
| TR-GEN-08-motor-metadata-pivots | Catálogo `pq_pivots_*`, API metadata/data/validate | D1 — Aprobado |
| TR-GEN-08-pivotgrid-visualizacion | `ConsultaGrillaPivotShell`, toggle grilla/pivot, piloto historial ventas | D1 — Aprobado |
| TR-GEN-08-layouts-pivot | `pq_pivots_config` + API CRUD, toolbar diseños | D1 — Aprobado |
| TR-GEN-08-exportacion-pivot | Export client-side Excel básico/tabla dinámica | D1 — Aprobado |

**Activación en tenant (post-merge, no incluida en deploy MVP):**

1. Migraciones `2026_06_11_100000_*`, `2026_06_11_110000_*`.
2. Seeder `PivotCatalogPilotSeeder`.
3. `.env`: `PIVOTS_ENABLED=true`, `PIVOT_LAYOUTS_ENABLED=true` (opcional layouts).

**Fuera del release MVP portal** hasta activar flags en deploy.

---

## Bloque PedidosWeb (101) — D + F + CC #1–#3

| TR | Entregable | Estado |
|----|------------|--------|
| 101-01 | Tenancy multi-empresa | **Diferida** (`EMPRESAS_CONEXION`) |
| 101-02 … 101-15 | Backend + frontend MVP | Finalizado |
| TR-GEN-04 | Consulta parámetros | Finalizado + CC #3 |

### Highlights CC PQ #1

| Tema | Cambio |
|------|--------|
| Selección cliente | Display `(código) razón social - nombre fantasía` |
| Cabecera / renglones | Bonif. 3 negativos; precio neto unitario; recálculo lista/bonif. |
| Consultas | Nombre comercial; fecha `dd/MM/yyyy HH:mm`; ícono Actualizar; Copiar en pendientes |
| Dashboard | KPIs con unidades + mes en curso |
| Inactividad | Timeout desde última interacción |

### Highlights CC PQ #2 (GEN-03)

| Tema | Cambio |
|------|--------|
| Layouts propios | Sufijo ` (*)` en diseños del usuario |
| Plantilla sistema | Reset con `state(null)` |
| Export Excel | Fechas locale, enteros/decimales, booleanos i18n, encabezados gris, totales pie |

### Highlights CC PQ #3

| Tema | Cambio |
|------|--------|
| `SelectBoxDx` | Loading indicator, `onOpened`, auto-match único |
| Carga artículos | Política carga diferida, cache clientes sesión, display `codigo - descripcion` |
| Layouts pie | Totalizadores persisten en guardar/cargar diseño |
| Parámetros | Columna Valor centrada |

---

## API (referencia)

Rutas bajo `/api/v1/` (autenticadas + tenant `X-Paq-Cliente`):

- Comprobantes, pedidos, presupuestos, consultas, dashboard, integración (MVP existente)
- **Pivots (nuevo):** `GET/POST /pivots/consultas/{id}/metadata|data|validate-structure`
- **Pivot layouts (nuevo):** `GET/POST/PUT/DELETE /pivot-configs*`
- **Config pública:** `pivotsEnabled`, `pivotLayoutsEnabled` en `GET /config/public`

Matriz permisos: [`matriz-permisos-mvp.md`](docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md) § Pivots.

OpenAPI: `backend/storage/api-docs/api-docs.json`

---

## Validaciones ejecutadas

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | 75+ passed (skips sin SQL Server) |
| `php artisan test --filter=Pivot` | Feature + unit (requieren tenant SQL Server) |
| `npm run build` (frontend) | OK |
| `npm run test` (Vitest) | Unit pivot + grid export + SelectBox |
| E2E pivot | `pivot-historial`, `pivot-layout-persistencia`, `pivot-export` |
| E2E MVP | `consultas-d1`, `mvp-section9`, `grid-layouts`, `grid-export` |

---

## Observaciones (no bloquean merge)

- Tests integración pivot y PedidosWeb requieren **SQL Server tenant** en CI (skipped local sin BD).
- **GEN-08** fuera del MVP release hasta flags + migraciones en tenant objetivo.
- `pq_pivots_aud`, export pivot server-side, PDF pivot: fuera D1 v1.
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

### CC PQ #2 / #3 (regresión)

- [ ] Layouts: sufijo ` (*)` en diseños propios; plantilla sistema resetea grilla
- [ ] Export Excel: fechas, booleanos, totales pie formateados
- [ ] SelectBox cliente/artículo: indicador loading; auto-match si único resultado
- [ ] Carga artículos: búsqueda diferida sin bloquear UI
- [ ] Guardar diseño grilla preserva totalizadores pie

### GEN-08 Pivots (con flags activos en tenant piloto)

- [ ] Migraciones + `PivotCatalogPilotSeeder` en tenant `desarrollo`
- [ ] `.env`: `PIVOTS_ENABLED=true`, `PIVOT_LAYOUTS_ENABLED=true`
- [ ] Historial ventas: toggle Grilla / Pivot; metadata y datos cargan
- [ ] Guardar/cargar diseño pivot; persistencia tras F5
- [ ] Export Excel pivot (básico y tabla dinámica si aplica)
- [ ] Sin flags: historial ventas solo grilla (comportamiento MVP)

### CI / despliegue

- [ ] Workflow `.github/workflows/ci.yml` en verde
- [ ] Deploy frontend Vercel con `frontend/vercel.json`
- [ ] `php artisan test` con tenant SQL Server + seeds (tanda integración opcional)
