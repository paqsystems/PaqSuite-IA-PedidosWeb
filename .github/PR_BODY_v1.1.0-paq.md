## Summary

Entrega **Fase 1 MVP** del portal **MONO PedidosWeb** integrada en la rama `v1.1.0` (merge fast-forward desde `v1.1.0-paq`).

1. **Scaffold fullstack** (Laravel 10 + React/Vite/DevExtreme) con **GEN-01 / GEN-02 / GEN-03** cerrados en documentación e implementación.
2. **Épica 101 — PedidosWeb**: Parte D completa + cierre formal **F** (TR 101-02 … 101-15 + **TR-GEN-04** consulta parámetros).
3. **Manuales de usuario** base para soporte y chatbot: `Generalidades.md` §18, `PedidosWeb.md`.

**Compare:** `main` ← **`v1.1.0`**  
**Último commit:** `c986e47` — `docs(pedidosweb): cierre formal F MVP, manuales y TR/HU finalizados`  
**Commits clave:** `db041e9` (consultas D1, carga comprobante, parámetros) · `087230b` (D1 núcleo API/frontend)

Informes de cierre: [`F-101-PedidosWeb-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md) · [`F-GEN-04-consulta-parametros-cierre.md`](docs/04-tareas/001-Generaliddes/F-GEN-04-consulta-parametros-cierre.md)

---

## Bloque Generalidades

| Área | Estado |
|------|--------|
| Shell, menú sidebar, avatar, idioma (5 locales), temas | Finalizado |
| Login, sesión, recuperación/cambio contraseña, seed seguridad | Finalizado |
| `DataGridDx`, layouts, ABM modal, export Excel | Finalizado |
| Visibilidad comercial (cliente / vendedor / supervisor) | Finalizado |
| **Consulta de parámetros** (TR-GEN-04) | Finalizado — solo lectura, sin columna clave, orden por descripción |

Cierres formales previos: `F-GEN-01-02-cierre-formal.md`, `F-GEN-03-cierre-formal.md`, `F-GEN-04-consulta-parametros-cierre.md`.

---

## Bloque PedidosWeb (101) — D + F

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
| 101-14 | Dashboard operativo (8 KPIs) | Finalizado |
| 101-15 | Tests unit/feature/E2E hardening | Finalizado |

**Highlights post-D1 (`db041e9`):**

- Carga comprobante: cabecera completa en grabación/mail, hidratación edición (DevExtreme), limpieza post-grabación.
- Consulta **Detalle de pedidos** (grilla plana cabecera + renglones).
- Consulta **Parámetros** (General) alineada a producto.

---

## API (referencia)

Rutas bajo `/api/v1/` (autenticadas + tenant `X-Paq-Cliente`):

- `POST comprobantes/grabar`, `POST comprobantes/copiar`
- Pedidos: CRUD + edición (`iniciar` / `touch` / `cancelar`)
- Presupuestos: CRUD (sin DELETE) + `POST presupuestos/{cod}/cerrar`
- `GET motivos-cierre`, `GET/POST presupuestos/{cod}/tratativas`
- Consultas: `pedidos-ingresados`, `pedidos-pendientes`, `presupuestos`, `detalle-pedidos`, `stock`, `deuda`, `cheques`, `historial-ventas`, `parametros`
- `GET dashboard/operativo`, `GET integracion/logs`

---

## Validaciones ejecutadas (F1 — 2026-06-03)

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | **75 passed**, 51 skipped (integración/403 sin SQL Server) |
| `php artisan test --filter=ParametrosConsulta` | **3 passed** |
| `npm run build` (frontend) | **OK** |
| `npx playwright test consultas-d1.spec.ts mvp-section9.spec.ts` | **7/7 OK** |
| QA manual usuario | OK (carga/edición, consultas, dashboard, parámetros) |

---

## Observaciones (no bloquean merge)

- Tests integración repositories y feature 403/200 requieren **SQL Server tenant** en CI (skipped en PHPUnit local).
- **Descuento por cantidad** en popup renglón: documentado; wiring UI pendiente.
- **Tratativas presupuesto**: alcance Should parcial (cierre operativo sí).
- **TR-101-01** permanece diferida hasta `EMPRESAS_CONEXION`.
- Advertencia Vite: chunk DevExtreme > 500 kB (preexistente).

---

## Test plan

- [ ] Login con usuario ERP o seed MVP + header `X-Paq-Cliente`
- [ ] Carga: grabar pedido y presupuesto con cabecera completa (lookups obligatorios) y renglones
- [ ] Edición comprobante: cabecera + renglones persisten al abrir desde consulta
- [ ] Consultas: ingresados, pendientes, presupuestos, **detalle pedidos**, stock, deuda, cheques, historial
- [ ] **General → Consulta de parámetros** (sin columna clave, orden descripción)
- [ ] Dashboard operativo: 8 KPIs visibles
- [ ] Toast mail si `mailEnviado === false` tras grabación OK
- [ ] `npm run test:e2e` suite completa en CI
- [ ] `php artisan test` con tenant SQL Server + seeds (tanda integración)
