## Summary

Primera entrega ejecutable del portal **MONO PedidosWeb** sobre la línea documental `v1.1.0`:

1. **Scaffold MVP fullstack** (Laravel 10 + React/Vite/DevExtreme) con oleadas **GEN-01 / GEN-02 / GEN-03** ya cerradas en documentación.
2. **Épica 101 — PedidosWeb D1**: modelos, repositories, services de negocio, API REST, mails, dashboard operativo, rutas/páginas frontend y tests mínimos.

**Base:** `v1.1.0` (solo docs/reglas) → **Compare:** `v1.1.0-paq`  
**Último commit:** `087230b` — `feat(101): D1 PedidosWeb — repos, services, API, frontend y tests`

---

## Bloque Generalidades (GEN-01 / GEN-02 / GEN-03)

| Área | Estado |
|------|--------|
| Shell, menú sidebar, avatar, idioma (5 locales), temas | Implementado |
| Login, sesión, recuperación/cambio contraseña, seed seguridad | Implementado |
| `DataGridDx`, layouts, ABM modal, export Excel | Implementado |
| Visibilidad demo (clientes, comprobante, resumen) | Implementado |

Ver cierres formales: `F-GEN-01-02-cierre-formal.md`, `F-GEN-03-cierre-formal.md`.

---

## Bloque PedidosWeb (101) — D1

| TR | Entregable D1 |
|----|----------------|
| 101-01 | **Omitida** (tenancy multi-empresa diferida) |
| 101-02 | Modelos Eloquent `PqPedidosweb*` + PK compuesta + tests unitarios |
| 101-03 | Repositories + contratos + `PedidosWebRepositoryServiceProvider` |
| 101-04 | Services: `PedidoService`, totales, copia, cierre, parámetros ERP |
| 101-05 | 8 controllers REST + rutas bajo `auth:sanctum` + `paq.tenant` |
| 101-06 | `ensurePermission(alta\|modi\|baja\|repo)` + procedimientos en visibilidad |
| 101-07 | 7 endpoints consultas paginados |
| 101-08 | `GET /integracion/logs` (Should) |
| 101-09 | `pedidosWebRoutes.tsx` lazy — sin placeholders |
| 101-10 | `PedidosCargaPage` (toolbar, cabecera, grilla, toast mail) |
| 101-11 | 7 páginas consulta con `DataGridDx` |
| 101-12 | Motivos cierre, cerrar presupuesto, tratativas básicas |
| 101-13 | `ComprobanteNotificationMail` + hook `mailEnviado` + Blade i18n |
| 101-14 | `GET /dashboard/operativo` (8 KPIs) + UI dashboard |
| 101-15 | Tests unit/feature + E2E smoke `mvp-section9.spec.ts` |

**Decisiones D1 cerradas:** D1-01..D1-06 (canal grabar, destinatarios mail, copia borrador, dashboard desempate, mail i18n, toast mail fallido).

---

## API nueva (referencia)

Rutas bajo `/api/v1/` (autenticadas + tenant):

- `POST comprobantes/grabar`, `POST comprobantes/copiar`
- CRUD pedidos + edición (`iniciar` / `touch` / `cancelar`)
- CRUD presupuestos (sin DELETE) + `POST presupuestos/{cod}/cerrar`
- `GET motivos-cierre`, `GET/POST presupuestos/{cod}/tratativas`
- Consultas: `pedidos-ingresados`, `pedidos-pendientes`, `presupuestos`, `stock`, `deuda`, `cheques`, `historial-ventas`
- `GET dashboard/operativo`, `GET integracion/logs`

---

## Validaciones ejecutadas

| Comando | Resultado |
|---------|-----------|
| `npm run build` (frontend) | OK |
| `php artisan test --filter=PedidoServiceTest\|ComprobanteGrabarTest\|PedidosWebRepositoryBindingTest` | 5 passed |
| `npx playwright test tests/e2e/pedidosweb/mvp-section9.spec.ts` | OK (agente) |

---

## Alcance MVP / observaciones para revisión

- **D1 = primera implementación por slice**, no cierre formal F ni cumplimiento total de todos los AC de cada TR.
- Pantalla de carga y consultas son **esqueleto funcional**: faltan flujos completos (edición concurrente -1, validaciones ERP exhaustivas, acciones de fila E2E, OpenAPI anotado por endpoint 101, matriz permisos actualizada por ruta).
- Tests integración repositories requieren SQL Server tenant + seed (`skipped` sin BD local).
- TR-101-01 permanece diferida hasta etapa `EMPRESAS_CONEXION`.

---

## Test plan

- [ ] Login con usuario seed MVP + header `X-Paq-Cliente`
- [ ] Navegar menú → `/pedidos/carga` (`data-testid=page-pedidos-carga`)
- [ ] Grabar pedido/presupuesto (API + UI básica)
- [ ] Consultas: pedidos ingresados, presupuestos (tabs 99/98), stock, deuda
- [ ] Dashboard operativo: 8 KPIs visibles
- [ ] Toast mail si `mailEnviado === false` tras grabación OK
- [ ] `npm run test:e2e` suite completa en CI
- [ ] `php artisan test` con tenant SQL Server + seeds
