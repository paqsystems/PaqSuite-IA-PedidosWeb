## Summary

Entrega **Fase 1 MVP** del portal **MONO PedidosWeb** en la rama **`v1.1.0`**, integrando **`v1.1.0-paq`** (dos merges), cierres **Control de Calidad PQ #1–#8**, épica **GEN-07 Importar Excel**, **SPEC-101-16** pedido individual y **GEN-08 Pivots (D1)**.

1. **Scaffold fullstack** (Laravel 10 + React/Vite/DevExtreme) — **GEN-01 / GEN-02 / GEN-03** cerrados.
2. **Épica 101 — PedidosWeb**: Parte D + cierre formal **F** (TR 101-02 … 101-16 + **TR-GEN-04**).
3. **CC PQ #1–#4**: dashboard, consultas, layouts, export Excel, pivots en informes.
4. **CC PQ #5–#6**: listbox artículos con **disponible neto** y **disponible neto base** (SUM por `base`).
5. **GEN-07 + SPEC-101-16**: motor Excel transversal + importación pedido individual en pantalla de carga.
6. **CC PQ #7**: i18n parámetros/pivot, layout carga 3 columnas, `CodPerfilPedidos`, validaciones grabación.
7. **CC PQ #8**: precarga catálogo artículos al ingresar pantalla, refresh, vendedor cliente al importar Excel.
8. **GEN-08 Pivots (SPEC-001-08)**: motor metadata/API, PivotGrid, diseños — flags default **`false`**.

**Compare:** [`main...v1.1.0`](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/main...v1.1.0)  
**Tip:** `6628475` — merge `v1.1.0-paq` (CC PQ #7/#8 + fixes Excel/disponible)

```powershell
gh pr create --base main --head v1.1.0 --title "release(v1.1.0): MVP PedidosWeb + Excel import + CC PQ #1–#8 + GEN-08" --body-file .github/PR_BODY_v1.1.0.md
```

### Merges desde `v1.1.0-paq`

| Commit | Resumen |
|--------|---------|
| `6628475` | **Merge 2** — CC PQ #7/#8, bonif Excel, errores UI import, disponible en precarga, manuales |
| `c86d42d` | Cierre formal CC #7/#8 (E/F/I), precarga artículos, Excel conserva vendedor |
| `c5962e0` | CC PQ #7 — i18n parámetros, layout carga, `ComprobanteGrabacionValidator` |
| `a56d902` | Disponible listbox, bonif renglon Excel, errores reales importación |
| `4e04e05` | **Merge 1** — GEN-07 Excel, SPEC-101-16, CC PQ #6 disponible base |
| `db3b38d` | SPEC-101-16 pedido individual + precarga catálogo artículos |
| `e061e8d` | **GEN-07** — motor Excel, UI embebida, PhpSpreadsheet |
| `2735155` | **GEN-08** — epic Pivots D1 |

Informes de cierre: [`F-101-PedidosWeb-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md) · [`F-CC-PQ-7-8-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/F-CC-PQ-7-8-cierre-formal.md) · [`I-CC-PQ-7-8-cierre-formal.md`](docs/04-tareas/101-PedidosWeb/I-CC-PQ-7-8-cierre-formal.md) · [`F-GEN-08-cierre-formal.md`](docs/04-tareas/001-Generaliddes/F-GEN-08-cierre-formal.md) · [`00-ControlCalidad-PQ.md`](docs/00-ControlCalidad/00-ControlCalidad-PQ.md)

---

## Bloque Generalidades

| Área | Estado |
|------|--------|
| Shell, menú, avatar, idioma (5 locales), temas | Finalizado |
| Login, sesión, recuperación/cambio contraseña | Finalizado |
| Expiración por inactividad | Finalizado — CC PQ #1 |
| `DataGridDx`, layouts, ABM modal, export Excel | Finalizado + CC PQ #2/#3 |
| `SelectBoxDx` (loading, auto-match) | Finalizado — CC PQ #3 |
| Consulta parámetros i18n | Finalizado — CC PQ #7 |
| Pivots i18n captions | Finalizado — CC PQ #7 |
| **Pivots (SPEC-001-08)** | Finalizado (D1) — flags default **false** |
| **Importar Excel (GEN-07)** | Finalizado — flag `EXCEL_IMPORT_ENABLED` default **false** |

---

## Bloque GEN-07 — Importar Excel

| Aspecto | Detalle |
|---------|---------|
| Motor | Catálogo `PQ_EXCEL_*`, staging, procesamiento async/sync, plantillas i18n, historial |
| Producto piloto | `PEDIDO_INDIVIDUAL` en `/pedidos/carga` (modo nuevo) |
| Flujo | Plantilla → upload → validación lote → hidratar cabecera/renglones |
| Flag | `EXCEL_IMPORT_ENABLED` en `GET /config/public` |
| Referencias | [SPEC-101-16](docs/05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) · [HU-101-029/030](docs/03-historias-usuario/101-PedidosWeb/) |

---

## Bloque PedidosWeb — CC PQ #5–#8 (carga)

| CC | Entregable clave |
|----|------------------|
| **#5 / #6** | Listbox: `codigo - descripcion — Disp. X (Y)`; base = SUM por `articulos.base` |
| **#7** | Layout 3 columnas, leyendas pie, perfil inicial, validaciones grabación server-side |
| **#8** | Precarga catálogo al montar pantalla; botón refresh; import Excel conserva vendedor cliente |

Fórmulas disponible: [pantalla-carga-comprobante-ui.md](docs/02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §3 · [consulta-stock.md](docs/02-producto/PedidosWeb/consulta-stock.md) §4–§5

---

## Bloque GEN-08 — Pivots (SPEC-001-08)

| TR | Entregable | Estado |
|----|------------|--------|
| TR-GEN-08-motor-metadata-pivots | Catálogo `pq_pivots_*`, API metadata/data/validate | Finalizado |
| TR-GEN-08-pivotgrid-visualizacion | `ConsultaGrillaPivotShell`, toggle grilla/pivot | Finalizado |
| TR-GEN-08-layouts-pivot | `pq_pivots_config` + API CRUD | D1 — Aprobado |
| TR-GEN-08-exportacion-pivot | Export client-side Excel | D1 — Aprobado |

**CC PQ #4:** pivots en detalle pedidos, deuda, cheques y stock.

---

## Deploy post-merge

| Paso | Comando / acción |
|------|------------------|
| **Backend** | `git pull` + redeploy Forge |
| **Composer** | `composer install` (PhpSpreadsheet) |
| **Migraciones Excel** | `php artisan migrate --force` — `2026_06_16_100000`, `2026_06_16_110000` |
| **Seeds Excel** | `PedidosWebExcelImportCatalogSeeder` (+ pilot si aplica) |
| **Seeds menú/seguridad** | `paqsuite:seed-menus-mvp` + `paqsuite:seed-seguridad-mvp` si faltan permisos Excel |
| **Migraciones pivots** | `2026_06_11_*` + `PivotCatalogPilotSeeder` / `PivotCatalogInformesSeeder` |
| **`.env` backend** | `EXCEL_IMPORT_ENABLED=true` (importación); `PIVOTS_ENABLED=true` (pivots) — según tenant |
| **Frontend** | Redeploy Vercel (`frontend/vercel.json`) |
| **Cola** | Worker activo si lotes Excel async (>5 MB / >2000 filas) |

**Smoke mínimo:**

1. Login MVP + carga pedido manual (cabecera + renglones + grabar).
2. Listbox artículos: `Disp.` y paréntesis base coherentes con consulta stock.
3. Import Excel pedido individual (flag activo): plantilla → renglones + bonif renglón.
4. Consulta parámetros y pivot (flags activos): textos i18n según locale.

---

## Validaciones ejecutadas

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | Passed (skips sin SQL Server) |
| `php artisan test --filter=ExcelImport` | Feature + unit |
| `php artisan test --filter=ComprobanteGrabacionValidator` | CC PQ #7 |
| `npm run build` | OK |
| `npm run test` | 156+ tests Vitest (incl. CC #7/#8) |
| E2E | `pedidos-excel-import`, `mvp-section9`, `pivot-*` |

---

## Test plan

### MVP base

- [ ] Login seed MVP + tenant `X-Paq-Cliente`
- [ ] Carga: pedido y presupuesto con cabecera completa
- [ ] Consultas: ingresados, stock, deuda, cheques, historial
- [ ] Dashboard operativo

### CC PQ #5–#6 — artículos

- [ ] Precarga catálogo al elegir lista / al ingresar pantalla (CC #8)
- [ ] Display `codigo - descripcion — Disp. X (Y)`; paréntesis = disponible neto base
- [ ] Botón refresh recarga catálogo (`data-testid="articulosRefresh"`)
- [ ] Consulta stock: mismos valores para mismos artículos

### GEN-07 / SPEC-101-16 — Excel

- [ ] Con `EXCEL_IMPORT_ENABLED=true`: toolbar visible en carga modo nuevo
- [ ] Descarga plantilla → upload → cabecera/renglones hidratados
- [ ] Bonif renglón importada correctamente; vendedor cliente conservado (CC #8)
- [ ] Sin permiso `pw_cargapedidos` alta: mensaje claro (no “formato inválido”)

### CC PQ #7

- [ ] Consulta parámetros: captions/tooltips i18n
- [ ] Pivot: captions i18n en historial/informes
- [ ] Layout carga 3 columnas + leyendas pie
- [ ] Validaciones grabación (cliente inhabilitado, sin renglones, nivel extremo)

### GEN-08 / CC PQ #4 (flags activos)

- [ ] Toggle Grilla / Pivot en informes
- [ ] Diseños pivot persistentes
- [ ] Export Excel pivot

### CI / deploy

- [ ] `.github/workflows/ci.yml` en verde
- [ ] Deploy Vercel + Forge post-merge

---

## Observaciones (no bloquean merge)

- Tests integración requieren **SQL Server tenant** en CI (skipped local sin BD).
- Flags `EXCEL_IMPORT_ENABLED` y `PIVOTS_ENABLED` default **false** — activación explícita por tenant.
- CC PQ #5 ítem base aislado **supersedido** por CC #6 (SUM por `base`).
- Advertencia Vite: chunk DevExtreme > 500 kB (preexistente).
- TR-101-01 (multi-empresa) permanece diferida.
