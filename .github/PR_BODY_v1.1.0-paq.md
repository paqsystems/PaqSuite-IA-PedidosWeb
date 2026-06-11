## Summary

Entrega **Fase 1 MVP** del portal **MONO PedidosWeb** en la rama **`v1.1.0`**, integrando el trabajo de **`v1.1.0-paq`** (cierre F), correcciones de **Control de Calidad PQ #1** (Parte G → D → I) y saneamiento documental.

1. **Scaffold fullstack** (Laravel 10 + React/Vite/DevExtreme) con **GEN-01 / GEN-02 / GEN-03** cerrados en documentación e implementación.
2. **Épica 101 — PedidosWeb**: Parte D completa + cierre formal **F** (TR 101-02 … 101-15 + **TR-GEN-04** consulta parámetros).
3. **CC PQ #1 (04/06 → unificado 09/06)**: mejoras en carga, consultas, dashboard, mail, inactividad y manual de usuario.
4. **Manuales de usuario** para soporte: `PedidosWeb.md` actualizado; circuito Open-Spec / CC en `docs/00-ControlCalidad/`.

**Compare:** `main` ← **`v1.1.0`**  
**Tip:** `77a9826` — `merge: integrar origin/v1.1.0 (vercel/database) con v1.1.0-paq`

**Commits clave (cronología reciente):**

| Commit | Resumen |
|--------|---------|
| `db041e9` | Consultas D1, carga comprobante, parámetros |
| `c986e47` | Cierre formal F MVP, manuales y TR/HU finalizados |
| `514ea48` | CC PQ #1 — dashboard, consultas, fechas, manual |
| `1f1076e` | Saneamiento estados HU/TR/SPEC; regla refresh grillas Informes |
| `75e7a25` | Ampliación hallazgos CC PQ #2 (solo documental) |
| `777c4e9` | Merge `v1.1.0-paq` → `v1.1.0` |
| `ad30265` | Ruteo SPA Vercel (`frontend/vercel.json`) |

Informes de cierre: [`F-101-PedidosWeb-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md) · [`F-GEN-04-consulta-parametros-cierre.md`](docs/04-tareas/001-Generaliddes/F-GEN-04-consulta-parametros-cierre.md) · [`00-ControlCalidad-PQ.md`](docs/00-ControlCalidad/00-ControlCalidad-PQ.md) (CC #1 Finalizado)

---

## Bloque Generalidades

| Área | Estado |
|------|--------|
| Shell, menú sidebar, avatar, idioma (5 locales), temas | Finalizado |
| Login, sesión, recuperación/cambio contraseña, seed seguridad | Finalizado |
| **Expiración por inactividad** (última acción usuario) | Finalizado — CC PQ #1 |
| `DataGridDx`, layouts, ABM modal, export Excel | Finalizado |
| Visibilidad comercial (cliente / vendedor / supervisor) | Finalizado |
| **Consulta de parámetros** (TR-GEN-04) | Finalizado — solo lectura, sin columna clave, orden por descripción |
| **CI GitHub Actions** (smoke backend + build frontend) | `.github/workflows/ci.yml` |

Cierres formales: `F-GEN-01-02-cierre-formal.md`, `F-GEN-03-cierre-formal.md`, `F-GEN-04-consulta-parametros-cierre.md`.

---

## Bloque PedidosWeb (101) — D + F + CC #1

| TR | Entregable | Estado |
|----|------------|--------|
| 101-01 | Tenancy multi-empresa | **Diferida** (`EMPRESAS_CONEXION`) |
| 101-02 | Modelos Eloquent `PqPedidosweb*` | Finalizado |
| 101-03 | Repositories + contratos | Finalizado |
| 101-04 | Services pedidos, totales, copia, cierre | Finalizado |
| 101-05 | Controllers REST + OpenAPI | Finalizado |
| 101-06 | Seguridad y visibilidad | Finalizado |
| 101-07 | Consultas API (+ detalle pedidos B3) | Finalizado |
| 101-08 | Logs integración | Finalizado |
| 101-09 | Frontend base y rutas lazy | Finalizado |
| 101-10 | Pantalla carga (alta/edición/copia/convertir) | Finalizado |
| 101-11 | Consultas UI + detalle pedidos | Finalizado |
| 101-12 | Cierre presupuesto (+ tratativas Should parcial) | Finalizado |
| 101-13 | Mail al grabar + toast fallo envío | Finalizado |
| 101-14 | Dashboard operativo (KPIs + unidades + mes en curso) | Finalizado |
| 101-15 | Tests unit/feature/E2E hardening | Finalizado |

### Highlights CC PQ #1 (código + docs unificados)

| Tema | Cambio |
|------|--------|
| **Selección cliente** | Display `(código) razón social - nombre fantasía`; orden por código / razón / fantasía |
| **Cabecera / renglones** | Bonif. 3 admite negativos; **precio neto unitario** en grilla; recálculo al cambiar lista o bonificaciones |
| **Artículos** | Excluye `usa_esc = 'B'` (BASE) en búsqueda de carga |
| **Consultas** | Columna nombre comercial; carátula fecha `dd/MM/yyyy HH:mm` (i18n); ícono **Actualizar**; **Copiar** en pendientes |
| **Detalle pedidos** | Columna precio neto unitario (`precio_neto`) |
| **Mail grabar** | Renglones con precio neto; importes neto/bruto con descuentos correctos |
| **Dashboard** | KPIs con **unidades**; bloque **mes en curso** (estados 0, 1, 2, 3, 98, 99) |
| **Inactividad** | Timeout desde última interacción (`useInactivityTimeout`) |

### Regla transversal (agente / futuros Informes)

- Ícono Actualizar en grillas tipo Informe: `devextreme-frontend.mdc` → `.cursor/rules/mono/08-devextreme-grid-standards.md` §1.12
- Componente: `GridRefreshButton.tsx` (`grid.refresh`, `data-testid="gridRefresh"`)

---

## API (referencia)

Rutas bajo `/api/v1/` (autenticadas + tenant `X-Paq-Cliente`):

- `POST comprobantes/grabar`, `POST comprobantes/copiar`
- Pedidos: CRUD + edición (`iniciar` / `touch` / `cancelar`)
- Presupuestos: CRUD (sin DELETE) + `POST presupuestos/{cod}/cerrar`
- `GET motivos-cierre`, `GET/POST presupuestos/{cod}/tratativas`
- Consultas: `pedidos-ingresados`, `pedidos-pendientes`, `presupuestos`, `detalle-pedidos`, `stock`, `deuda`, `cheques`, `historial-ventas`, `parametros`
- `GET dashboard/operativo`, `GET integracion/logs`

OpenAPI: `backend/storage/api-docs/api-docs.json`

---

## Validaciones ejecutadas

| Comando | Resultado (referencia F1 / post-CC) |
|---------|-------------------------------------|
| `php artisan test --filter=PedidosWeb` | **75 passed**, skips integración sin SQL Server |
| `php artisan test --filter=ParametrosConsulta` | **3 passed** |
| `npm run build` (frontend) | **OK** |
| `npx playwright test consultas-d1.spec.ts mvp-section9.spec.ts` | **7/7 OK** (F1) |
| Tests nuevos CC #1 | Unit/feature: dashboard, mail, artículos BASE, fechas consulta, precios renglones |
| QA manual usuario (PQ) | CC #1 cerrado Parte I 09/06/2026 |

---

## Observaciones (no bloquean merge)

- Tests integración repositories y feature 403/200 requieren **SQL Server tenant** en CI (skipped en PHPUnit local sin BD).
- **Descuento por cantidad** en popup renglón: documentado; wiring UI pendiente.
- **Tratativas presupuesto**: alcance Should parcial (cierre operativo sí).
- **TR-101-01** permanece diferida hasta `EMPRESAS_CONEXION`.
- Advertencia Vite: chunk DevExtreme > 500 kB (preexistente).
- **CC PQ #2** (pendiente): layouts propios `(*)`, plantilla sistema, export Excel formateado.
- **CC PQ #3** (pendiente): cartel cargando / lentitud artículos y precios.

---

## Test plan

### MVP base (F)

- [ ] Login con usuario ERP o seed MVP + header `X-Paq-Cliente`
- [ ] Carga: grabar pedido y presupuesto con cabecera completa y renglones
- [ ] Edición comprobante: cabecera + renglones persisten al abrir desde consulta
- [ ] Consultas: ingresados, pendientes, presupuestos, detalle, stock, deuda, cheques, historial
- [ ] **General → Consulta de parámetros** (sin columna clave, orden descripción)
- [ ] Dashboard operativo: KPIs visibles
- [ ] Toast mail si `mailEnviado === false` tras grabación OK

### CC PQ #1 (regresión recomendada)

- [ ] Cliente: formato `(cod) razón - fantasía` y orden en selector
- [ ] Carga: bonif. 3 negativa; precio neto en grilla; recálculo al cambiar lista/bonif.
- [ ] Búsqueda artículos sin registros BASE (`usa_esc = 'B'`)
- [ ] Consultas: nombre comercial, fecha proceso sin segundos, **Actualizar**, copiar en pendientes
- [ ] Detalle pedidos: columna precio neto unitario
- [ ] Mail: precio neto en renglones e importes con descuentos
- [ ] Dashboard: unidades por KPI + sección mes en curso
- [ ] Sesión: inactividad reinicia con interacción del usuario

### CI / despliegue

- [ ] Workflow `.github/workflows/ci.yml` en verde en el PR
- [ ] Deploy frontend Vercel con `frontend/vercel.json` (SPA rewrite)
- [ ] `php artisan test` con tenant SQL Server + seeds (tanda integración opcional)
