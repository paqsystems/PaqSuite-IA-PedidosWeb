## Summary

Entrega **Fase 1 MVP** del portal **MONO PedidosWeb** en la rama **`v1.1.0-paq`**, integrando scaffold fullstack, épica **101 PedidosWeb**, cierres **CC PQ #1–#6**, **GEN-08 Pivots**, **GEN-07 Importar Excel** y **SPEC-101-16** pedido individual desde Excel.

1. **Scaffold fullstack** (Laravel 10 + React/Vite/DevExtreme) — GEN-01/02/03.
2. **Épica 101 — PedidosWeb:** carga, consultas, dashboard, mail, conversiones, copia.
3. **CC PQ #1–#4:** mejoras carga, export Excel, SelectBox loading, pivots informes.
4. **GEN-07 + SPEC-101-16:** motor Excel transversal + importación pedido individual en pantalla de carga.
5. **CC PQ #5/#6:** listbox artículos con disponible neto y base (SUM por `articulos.base`).

**Compare:** [`main...v1.1.0-paq`](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/main...v1.1.0-paq)

```powershell
gh pr create --base main --head v1.1.0-paq --title "release(pedidosweb): MVP v1.1.0-paq — PedidosWeb + Excel + CC PQ #1-6" --body-file .github/PR_BODY_v1.1.0-paq.md
```

### Commits recientes (delta vs `v1.1.0`)

| Commit | Resumen |
|--------|---------|
| `a9bcb2b` | CC PQ #6 — disponible neto base SUM por base |
| `ffe3d18` | Disponible neto listbox + paréntesis base |
| `db3b38d` | SPEC-101-16 pedido individual + catálogo local |
| `e061e8d` | GEN-07 motor Excel + UI embebida |
| `cc4bddc` | Docs CC PQ #4 pivot informes |
| `bd005df` | Docs PR baseline |

Informes de cierre: [`F-101-PedidosWeb-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md) · [`F-GEN-07-cierre-c1.md`](docs/04-tareas/001-Generaliddes/F-GEN-07-cierre-c1.md) · [`F-101-16-cierre-c1.md`](docs/04-tareas/101-PedidosWeb/F-101-16-cierre-c1.md) · [`00-ControlCalidad-PQ.md`](docs/00-ControlCalidad/00-ControlCalidad-PQ.md)

---

## Bloque Generalidades

| Área | Estado |
|------|--------|
| Shell, menú, idioma (5 locales), temas | Finalizado |
| Login, sesión, recuperación/cambio contraseña | Finalizado |
| `DataGridDx`, layouts, export Excel formateado | Finalizado — CC PQ #2/#3 |
| `SelectBoxDx` (loading, auto-match) | Finalizado — CC PQ #3 |
| Visibilidad comercial (C/V/S) | Finalizado |
| **Pivots (GEN-08)** | Finalizado D1 — flags default **false** |
| **Importar Excel (GEN-07)** | Finalizado D1 — flag `EXCEL_IMPORT_ENABLED` default **false** |
| CI GitHub Actions | `.github/workflows/ci.yml` |

---

## Bloque GEN-07 — Importar Excel

| Entregable | Detalle |
|------------|---------|
| Motor | Catálogo procesos/campos, staging, jobs async, handlers plug-in |
| UI | Host modal embebible, grilla staging, historial, descarga plantilla |
| Producto PedidosWeb | Proceso `PEDIDO_INDIVIDUAL` (SPEC-101-16) |
| Tablas | `PQ_EXCEL_PROCESOS`, `PQ_EXCEL_IMPORTACION*`, migraciones `2026_06_16_*` |
| Activación | `EXCEL_IMPORT_ENABLED=true` + seeds catálogo |

---

## Bloque PedidosWeb (101)

| TR / tema | Estado |
|-----------|--------|
| 101-02 … 101-15 | Backend + frontend MVP |
| CC PQ #5/#6 listbox | Disponible neto + base entre paréntesis; precarga local 10k |
| SPEC-101-16 | Import Excel en `/pedidos/carga` (modo nuevo) |
| Consulta stock | Fórmulas §4–§5 referencia para listbox carga |

### Listbox artículos (CC PQ #6)

| Campo | Regla |
|-------|-------|
| API | `GET /articulos?lista_precios={n}&page_size=10000` |
| `disponibleNeto` | `stock − comprometido − comprometido_web` |
| `disponibleNetoBase` | SUM por `articulos.base` — [consulta-stock.md](docs/02-producto/PedidosWeb/consulta-stock.md) §5 |
| UI | `codigo - descripcion — Disp. X (Y)` si hay base |

Fuente de verdad: [pantalla-carga-comprobante-ui.md](docs/02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md)

---

## Deploy post-merge (tenant)

| Paso | Acción |
|------|--------|
| Migraciones MVP | `php artisan migrate --force` (PedidosWeb + Excel + Pivots según tenant) |
| Seeds | `paqsuite:seed-seguridad-mvp`, `paqsuite:seed-menus-mvp`, `PedidosWebExcelImportCatalogSeeder`, pivot seeders si aplica |
| `.env` | `EXCEL_IMPORT_ENABLED=true`, `PIVOTS_ENABLED=true` / `PIVOT_LAYOUTS_ENABLED=true` según activación |
| Composer | `composer install` (PhpSpreadsheet) |
| Cola | Worker para importación async |
| Frontend | Redeploy con build Vite |

**Bump de versión:** recomendable tag `v1.1.0` post-merge si este PR consolida release.

---

## Validaciones ejecutadas

| Comando | Resultado esperado |
|---------|---------------------|
| `php artisan test --filter=PedidosWeb` | Passed (skips sin SQL Server) |
| `php artisan test --filter=ExcelImport` | Passed / skipped según tenant |
| `php artisan test --filter=ArticuloCargaLookupServiceTest` | Passed / skipped |
| `npm run build` | OK |
| `npm run test` | Vitest unit |
| E2E | `mvp-section9`, `pedidos-excel-import`, pivot specs |

---

## Test plan

### MVP base

- [ ] Login + `X-Paq-Cliente`
- [ ] Carga pedido/presupuesto; consultas; dashboard
- [ ] Parámetros generales; layouts grilla

### GEN-07 / SPEC-101-16

- [ ] Import Excel pedido individual (flag activo)
- [ ] Toolbar oculto fuera de modo nuevo
- [ ] Plantilla, staging, procesar, hidratar renglones

### CC PQ #4 / #6

- [ ] Pivots informes (flags activos)
- [ ] Listbox artículos: disponible y base correctos vs consulta stock
- [ ] Grabar pedido post-importación

### CI

- [ ] Workflow CI en verde

---

## Observaciones

- Tests integración requieren SQL Server tenant en CI.
- Flags Excel y Pivots default **false** — activación explícita por tenant.
- CC PQ #5 (disponible sin web) supersedido por producto §3.1 + CC #6.
- TR-101-01 (multi-empresa) diferida.
