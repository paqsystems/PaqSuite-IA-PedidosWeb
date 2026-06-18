## Summary

Integración incremental **`v1.1.0`** ← **`v1.1.0-paq`**: épica **GEN-07 Importar Excel** (motor transversal + UI embebida), **SPEC-101-16** pedido individual desde Excel en pantalla de carga, catálogo de artículos con precarga local y corrección **CC PQ #6** del disponible neto base en listbox.

1. **GEN-07 — Motor Excel:** catálogo `PQ_EXCEL_*`, staging, procesamiento async, plantillas i18n, historial, handlers plug-in, tests feature/unit/E2E.
2. **SPEC-101-16 — Pedido individual Excel:** proceso `PEDIDO_INDIVIDUAL`, validación lote, toolbar en `PedidosCargaPage`, hidratación cabecera/renglones desde filas válidas.
3. **Listbox artículos (carga):** precarga local hasta 10 000 ítems; display `codigo - descripcion — Disp. X (Y)` con fórmulas alineadas a consulta stock §4–§5.
4. **CC PQ #6:** `disponibleNetoBase` = SUM por `articulos.base` (no stock del código base aislado).

**Compare:** [`v1.1.0...v1.1.0-paq`](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/v1.1.0...v1.1.0-paq)

```powershell
gh pr create --base v1.1.0 --head v1.1.0-paq --title "feat(pedidosweb): importacion Excel pedido individual y disponible base listbox" --body-file .github/PR_BODY_v1.1.0-paq-merge.md
```

### Commits incluidos (6)

| Commit | Resumen |
|--------|---------|
| `a9bcb2b` | **CC PQ #6** — disponible neto base SUM por `base` + docs SPEC/HU/TR |
| `ffe3d18` | Disponible neto en listbox; paréntesis con `disponibleNetoBase` |
| `db3b38d` | SPEC-101-16 pedido individual + catálogo artículos precarga local |
| `e061e8d` | **GEN-07** — motor Excel, UI embebida, PhpSpreadsheet, tests E |
| `cc4bddc` | Docs CC PQ #4 pivot informes + manuales |
| `bd005df` | Actualización cuerpos PR (baseline listbox) |

---

## Contexto funcional

### Importación Excel (GEN-07 + SPEC-101-16)

| Aspecto | Detalle |
|---------|---------|
| **Proceso piloto producto** | `PEDIDO_INDIVIDUAL` — importar pedido desde plantilla Excel en `/pedidos/carga` (modo nuevo) |
| **Flujo** | Descargar plantilla → subir Excel → staging → procesar lote → `validRows` → hidratar cabecera/renglones |
| **Permisos** | `ProcedimientoHost` en catálogo Excel; gate vía `ExcelImportAccessService` |
| **Flag** | `EXCEL_IMPORT_ENABLED` (default **false**) expuesto en `GET /config/public` como `excelImportEnabled` |
| **Referencias** | [SPEC-101-16](docs/05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) · [HU-101-029/030](docs/03-historias-usuario/101-PedidosWeb/) · [F-101-16-cierre-c1](docs/04-tareas/101-PedidosWeb/F-101-16-cierre-c1.md) |

### Listbox artículos — CC PQ #5 / #6

| Campo | Fórmula |
|-------|---------|
| `disponibleNeto` | `stock − comprometido − comprometido_web` (pedidos `estado = 0`) |
| `disponibleNetoBase` | `SUM(stock) − SUM(comprometido) − comprometido_base_web` sobre **todas** las presentaciones con la misma `base` |
| **Display** | `ART - desc — Disp. 12,50 (177.100,00)` — paréntesis = disponible neto base, no impegnato web |
| **Carga datos** | `GET /articulos?lista_precios={n}&page_size=10000`; búsqueda **local** DevExtreme |
| **Fuente de verdad** | [pantalla-carga-comprobante-ui.md](docs/02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §3 |

---

## Cambios por área

### Backend

| Área | Archivos / notas |
|------|------------------|
| Excel import | Controllers, services, handlers, jobs, modelos `PqExcel*`, migraciones `2026_06_16_*`, i18n `lang/*/excel_import.php` |
| Pedido individual | `PedidoIndividualExcelImportHandler`, `PedidoIndividualLotValidator`, `PedidoIndividualRowResolver` |
| Artículos carga | `ArticuloCargaLookupService` — subconsulta `[bs]` con `SUM` por `base` |
| Dependencia | `phpoffice/phpspreadsheet` (composer) |
| Seeds | `PedidosWebExcelImportCatalogSeeder`, `ExcelImportCatalogPilotSeeder`, menús/seguridad MVP |

### Frontend

| Área | Archivos / notas |
|------|------------------|
| Excel import | `features/excelImport/*` — host modal, staging, historial, plantilla |
| Carga pedidos | Toolbar Excel en `PedidosCargaPage`, `mapExcelImportToCarga`, precarga catálogo artículos |
| Display artículos | `cargaCatalogos.ts` — `articuloDisplay` / `articuloDisplayConBase` |
| i18n | Claves `excelImport.*` en 5 locales |
| E2E | `pedidos-excel-import.spec.ts` |

### Documentación

- CC PQ #6 en `00-ControlCalidad-PQ.md`
- SPEC/HU/TR 101-10, 101-16, GEN-07 actualizados
- Manuales `PedidosWeb.md`, `Generalidades.md`

---

## Deploy post-merge

| Paso | Comando / acción |
|------|------------------|
| **Migraciones** | `php artisan migrate --force` — tablas `PQ_EXCEL_*` (`2026_06_16_100000`, `2026_06_16_110000`) |
| **Seeds catálogo Excel** | `PedidosWebExcelImportCatalogSeeder` (+ pilot si aplica) |
| **Seeds menú/seguridad** | `paqsuite:seed-menus-mvp` y `paqsuite:seed-seguridad-mvp` si faltan entradas Excel |
| **`.env` backend** | `EXCEL_IMPORT_ENABLED=true` para activar importación en tenant |
| **`.env` opcional** | `EXCEL_IMPORT_ASYNC_MAX_BYTES`, `EXCEL_IMPORT_ASYNC_MAX_ROWS` |
| **Composer** | `composer install` (PhpSpreadsheet) |
| **Frontend** | Redeploy Vercel/Forge estático |
| **Cola** | Worker de cola activo si lotes async (>5 MB / >2000 filas) |

**Sin cambios de esquema PedidosWeb** en los fixes de listbox (solo lógica SQL).

**Smoke mínimo:**

1. `/pedidos/carga` modo nuevo → botón importar Excel visible con flag activo.
2. Descargar plantilla pedido individual → procesar archivo demo → renglones en grilla.
3. Listbox artículos: artículo con base (ej. AC01) muestra paréntesis = disponible neto base de consulta stock.
4. Grabar pedido tras importación parcial o manual.

---

## Test plan

### GEN-07 / SPEC-101-16

- [ ] Con `EXCEL_IMPORT_ENABLED=true`: toolbar Excel visible en carga modo nuevo
- [ ] Toolbar **oculto** en editar / ver / copia / con renglones precargados
- [ ] Descarga plantilla `PEDIDO_INDIVIDUAL` (nombre i18n según locale)
- [ ] Upload + staging + procesar lote feliz → cabecera y renglones hidratados
- [ ] Errores de fila exportables; lote sin filas válidas no habilita grabar
- [ ] Historial importaciones accesible desde menú (permiso repo)

### CC PQ #6 — listbox artículos

- [ ] Tras elegir lista de precios: precarga catálogo (loading + combobox habilitado)
- [ ] Ítem sin base: `codigo - descripcion — Disp. X,XX`
- [ ] Ítem con base: paréntesis = **disponible neto base** (ej. AC01 → 177.100), no impegnato web
- [ ] Consulta stock §5: mismos valores base para mismos artículos
- [ ] Agregar renglón y grabar pedido sin regresión

### Automatizado

- [ ] `php artisan test --filter=ExcelImport`
- [ ] `php artisan test --filter=ArticuloCargaLookupServiceTest`
- [ ] `npm run test` — `cargaCatalogos`, `mapExcelImportToCarga`, `excelImportApi`
- [ ] E2E `pedidos-excel-import.spec.ts`, `mvp-section9`
- [ ] CI GitHub Actions en verde

---

## Observaciones (no bloquean merge)

- Tests feature Excel / lookup artículos pueden **skip** sin SQL Server tenant en CI local.
- `PEDIDO_INDIVIDUAL_plantilla.xlsx` en raíz del repo **no** está versionada (fixture en `frontend/tests/fixtures/`).
- Flag `EXCEL_IMPORT_ENABLED=false` por defecto: comportamiento MVP previo hasta activación explícita en deploy.
- CC PQ #5 original (disponible sin web) quedó **supersedido** — ver CC #6 y producto §3.1 vigente.
