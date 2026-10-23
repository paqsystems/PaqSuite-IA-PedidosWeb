# Cierre F — CC PQ #17 (20/09/2026) — Atributo `especial` en artículos

## Alcance

Verificación **F1 + F** sobre el atributo booleano `especial` en artículos (modelo, consulta detallada, lookup carga):

| # | Tema | Updates |
|---|------|---------|
| 1 | Columna `especial` en `pq_pedidosweb_articulos` (D1-32) | SPEC/TR-101-02-update |
| 2 | Columna **Especial** en informe Detalle de pedidos: `""` / `"*"` (D1-33) | SPEC/HU/TR-101-07-update · SPEC/TR-101-11-update · HU-101-028-update |
| 3 | Sufijo ` (*)` en listbox browse de carga (D1-34) | SPEC/HU/TR-101-10-update · HU-101-006-update |

**Fecha verificación F1/F:** 21/09/2026  
**Parte E:** [E-CC-PQ-17-tests.md](E-CC-PQ-17-tests.md)  
**TR:** [TR-SPEC-101-02-modelos.md](TR-SPEC-101-02-modelos.md) · [TR-SPEC-101-07-consultas-api.md](TR-SPEC-101-07-consultas-api.md) · [TR-SPEC-101-10-pantalla-carga.md](TR-SPEC-101-10-pantalla-carga.md) · [TR-SPEC-101-11-consultas-ui.md](TR-SPEC-101-11-consultas-ui.md)

---

## F1 — Verificación agente (8 ejes)

**Resultado F1:** **Aprobado con observaciones**

### 1. Alcance

| Pedido CC #17 | Implementado | Fuera de alcance respetado |
|---------------|--------------|----------------------------|
| Atributo `especial` en tabla artículos | DDL idempotente + modelo + bootstrap | Sin ABM artículos ni UI de edición |
| Columna **Especial** en consulta detallada | API `"*"` / `""` + grilla + kardex mobile | Otros informes de consulta sin cambio |
| Sufijo ` (*)` en lookup carga | `formatArticuloCargaDisplay` + API browse `especial: bool` | `GET /articulos?codigos=...` sin sufijo (TR) |
| Excel / asistente IA | No tocados | Correcto: no estaban en el hallazgo |

### 2. Código

| Pieza | Evidencia |
|-------|-----------|
| DDL | `backend/scripts/sql/alter-pq-pedidosweb-articulos-especial.sql` |
| CREATE canónico | `backend/scripts/sql/create-pq-pedidosweb-articulos.sql` |
| Bootstrap | `PedidosWebSchemaBootstrap::addColumnIfMissing('especial', …)` |
| Modelo | `PqPedidoswebArticulo` — `$fillable` + `$casts['especial']` |
| API detalle | `DetallePedidosConsultaService::resolveEspecialArticulo` → `*` / `""` |
| API browse | `ArticuloCargaLookupService` — SELECT + payload `especial: bool` |
| FE consulta | `DetallePedidosConsultaColumns.tsx` + `consultaMobileRenderers.tsx` |
| FE carga | `cargaCatalogos.ts` — sufijo literal ` (*)` |
| OpenAPI | `OpenApiInformesSchemas` — `ConsultaDetallePedidoItem.especial` |

### 3. Base de datos

| Ítem | Estado |
|------|--------|
| `ALTER` idempotente `especial bit NOT NULL DEFAULT 0` | OK — script + bootstrap |
| Default `0` en filas existentes | OK — constraint en ALTER |
| Cast boolean en modelo | OK |

**Deploy:** ejecutar `alter-pq-pedidosweb-articulos-especial.sql` o `PedidosWebSchemaBootstrap::ensureMvpSchema()` en cada tenant antes de usar la columna en producción.

### 4. Backend / API

| RN | Evidencia | Estado |
|----|-----------|--------|
| Detalle pedidos expone `especial` string | `mapDetalleItem` + OpenAPI | OK |
| Browse carga expone `especial` boolean | `ArticuloCargaLookupService` + guard `SqlSchemaPresence` | OK |
| Sin columna en BD legacy | Lookup degrada a `0`; feature test hace skip | OK |
| Permisos / envelope | Sin cambio de contrato HTTP | N/A |

### 5. Frontend

| Superficie | Evidencia | Estado |
|------------|-----------|--------|
| Grilla Detalle de pedidos | Columna i18n `consultas.detalle.column.especial` (5 idiomas) | OK |
| Mobile kardex | `consultaMobileRenderers` — campo `especial` | OK |
| Listbox artículos carga | Sufijo ` (*)` tras disponible/base | OK |
| `data-testid` / DevExtreme | Sin regresión en tests E2E existentes | OK |

### 6. Tests

Ver [E-CC-PQ-17-tests.md](E-CC-PQ-17-tests.md). Re-ejecutados en **F 21/09/2026**:

| Suite | Comando | Resultado |
|-------|---------|-----------|
| Vitest | `cargaCatalogos.test.ts` + `consultaColumns.test.tsx` | **12 passed** |
| PHPUnit unit | `DetallePedidosConsultaServiceTest` (3) + `ArticuloCargaLookupServiceTest` (1) | **4 passed** |
| PHPUnit feature | `--filter=consultaDetallePedidosIncluyeEspecialArticulo` | **Skipped** (tenant SQL Server no disponible) |
| Playwright E2E | Parte E (2 specs) | **2 passed** (Parte E; no re-ejecutado en F) |

**Corrección F:** `DetallePedidosConsultaServiceTest` dejó de mockear clases `final`; usa `$this->app->make()`.

### 7. Documentación

| Doc | Alineado |
|-----|----------|
| Updates SPEC/HU/TR en `docs/.../updates/` | Sí — D1-32…D1-34 |
| `consulta-detalle-pedidos.md` / `pantalla-carga-comprobante-ui.md` | Sí (Parte D) |
| OpenAPI `ConsultaDetallePedidoItem.especial` | Sí |
| CC #17 + E + F | Sí |

### 8. Trazabilidad

CC #17 → SPEC/HU/TR base (101-02/07/10/11 + HU-006/028) → D (código) → E (tests) → F → **I** (este documento).

---

## F — Smoke / checklist QA

| # | Escenario | Resultado |
|---|-----------|-----------|
| 1 | Renglón detalle con artículo `especial=1` → API `"*"` | **OK** (PHPUnit unit) |
| 2 | Renglón detalle con `especial=0` o sin artículo → `""` | **OK** (PHPUnit unit) |
| 3 | Browse carga incluye clave `especial` boolean | **OK** (PHPUnit unit) |
| 4 | Grilla `/pedidos/detalle` — columnheader **Especial** + celda `*` | **OK** (E2E Parte E) |
| 5 | Listbox carga — ítem especial muestra `(*)` | **OK** (Vitest + E2E Parte E) |
| 6 | Feature HTTP 200 con tenant real | **Skipped** — re-ejecutar cuando SQL Server activo |
| 7 | Smoke manual PQ con artículo marcado en tenant | **Opcional** |

---

## Observaciones no bloqueantes

| ID | Tema | Destino |
|----|------|---------|
| OBS-F-01 | Feature `consultaDetallePedidosIncluyeEspecialArticulo` skipped sin BD tenant | Re-ejecutar pre-deploy o en CI con SQL Server |
| OBS-F-02 | Deploy requiere `ALTER` en cada BD empresa | Runbook / checklist deploy |
| OBS-F-03 | ~~Updates en `docs/.../updates/`~~ | Cerrado — Parte I 21/09/2026 |

---

## Veredicto final

| Control | F1 (agente) | F (tests auto) | F (manual PQ extra) |
|---------|-------------|----------------|---------------------|
| CC #17 (20/09/2026) | **Aprobado con observaciones** | **Aprobado con observaciones** | Opcional OBS-F-07 |

**Estado CC #17:** **G+D+E+F+I 21/09/2026 — Finalizado.**

**Recomendación:** En deploy aplicar script SQL `especial` antes de smoke en producción.
