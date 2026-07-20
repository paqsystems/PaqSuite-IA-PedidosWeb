# Plan de implementación D1 — Importación masiva (TR-21a / 21b / 21c)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-20 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-21-cierre-c1](F-101-21-cierre-c1-importacion-masiva.md) — **Apto** |
| **TRs** | [21a](TR-SPEC-101-21-proceso-excel-pedido-masivo.md) · [21b](TR-SPEC-101-21-pantalla-importacion-masiva.md) · [21c](TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) |
| **Estado plan** | **Listo para Parte D** |

---

## Alcance entendido

Implementar importación masiva de pedidos/presupuestos en tres olas:

1. **TR-21a:** proceso Excel `PEDIDO_MASIVO` (catálogo, handler, agrupación `(cod_cliente, cod_vended, nivel)` → `resultado.grupos[]`).
2. **TR-21b:** pantalla `/pedidos/importacion-masiva` (grilla, import GEN-07, toggles, grabación FE secuencial x/N, guardas de salida).
3. **TR-21c:** acción Consultar → `/pedidos/carga` readonly + Volver con rehidratación desde `sessionStorage`.

**Grabación (decisión C1 confirmada por producto):** por cada comprobante del lote, el FE arma el **mismo JSON** que la carga individual (`cabecera` + `renglones` mapeados con `mapCabeceraToApi` / `mapRenglonToApi`) e invoca el **mismo servicio backend** (`PedidoService::grabarComprobante`). No hay endpoint de lote ni DTO paralelo.

**Fuera de alcance D:** mobile Capacitor, endpoint batch backend, borrador en servidor, cambio de columnas Excel 101-16, bootstrap destructivo.

---

## Fuentes leídas

| Tipo | Documento / código |
|------|-------------------|
| SPEC | [SPEC-101-21](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| HU | HU-101-043, 044, 045 |
| TR | TR-SPEC-101-21a/b/c + [C1](F-101-21-cierre-c1-importacion-masiva.md) |
| Producto | [importacion-masiva-pedidos.md](../../02-producto/PedidosWeb/importacion-masiva-pedidos.md) |
| Excel base | `PedidoIndividualExcelImportHandler`, `PedidoIndividualRowResolver`, `PedidoIndividualLotValidator`, `PedidosWebExcelImportCatalogSeeder` |
| Grabación | `ComprobanteController@grabar`, `PedidoController@store`, `PresupuestoController@store`, `ComprobanteGrabacionPayload`, `frontend/.../comprobanteApi.ts` (`grabarComprobante`) |
| UI Excel host | `frontend/src/features/excelImport/*`, `ExcelImportHostModal`, `ExcelStagingGridPage` |
| Carga individual | `PedidosCargaPage.tsx` (payload grabación), `renglonesCarga.ts` |
| Mobile policy | `pedidosWebMobilePolicy.ts` |
| Config | `backend/config/paqsuite_visibility.php`, `backend/config/excel_import.php` |

---

## Impacto esperado

### Base de datos

| Cambio | Detalle |
|--------|---------|
| Catálogo Excel | Filas en `pq_excel_procesos` / campos para `PEDIDO_MASIVO` (copia campos de `PEDIDO_INDIVIDUAL`, `procedimiento_host=pw_importacionmasiva`) |
| Menú / permisos | Seed idempotente `pw_importacionmasiva` en matriz MVP (`paqsuite:seed-menus-mvp` o seeder dedicado) |
| Pedidos reales | Solo INSERT vía APIs store existentes al Grabar |
| **Sin** DROP/migrate destructivo | Solo seeds/SQL idempotente documentado |

### Backend

| Área | TR-21a | TR-21b | TR-21c |
|------|--------|--------|--------|
| Handler Excel | `PedidoMasivoExcelImportHandler` + `PedidoMasivoLotValidator` + agrupador | — | — |
| Config | `excel_import.php` binding; `paqsuite_visibility.importacionMasiva` | — | — |
| Seeder | `PedidoMasivoExcelImportCatalogSeeder` o extensión de `PedidosWebExcelImportCatalogSeeder` | Menú/permiso | — |
| Auth store | — | OR `pw_cargapedidos` \| `pw_importacionmasiva` solo en `store` pedido/presupuesto (+ `/comprobantes/grabar` si se reutiliza) | — |
| API nueva | — | — | — |

**Reuso crítico 21a:** `PedidoIndividualRowResolver` para validar/enriquecer filas; nuevo validator de lote con coherencia **por grupo** y agrupación post-validación.

**Contrato host (`grupos[]`):** cada grupo expone cabecera resuelta, renglones, metadatos vendedor/nivel, listo para mapper FE → payload grabación.

### Frontend

| Nuevo / cambio | TR | Detalle |
|----------------|-----|---------|
| Feature | 21b | `frontend/src/features/pedidos/importacionMasiva/` |
| Página | 21b | `ImportacionMasivaPage` — ruta `/pedidos/importacion-masiva` |
| Grilla | 21b | `ImportacionMasivaGrid` (DataGrid DX): toggle pedido/presupuesto, columna error, acciones fila |
| Toolbar | 21b | Reuso patrón GEN-07 host (`ExcelImportHostToolbar` / modal) con `EXCEL_PROCESO_PEDIDO_MASIVO` |
| Orquestador | 21b | `grabarLoteSecuencial` — loop, progreso «Cargando x de N», best-effort |
| Mapper | 21b | `mapBorradorToGrabarPayload(fila)` → reutiliza tipos/mappers de `comprobanteApi.ts` |
| Grabación | 21b | Preferir **`grabarComprobante()`** (mismo que `PedidosCargaPage`) para paridad 1:1; equivalente a `POST /pedidos` \| `/presupuestos` |
| sessionStorage | 21b/21c | Clave `importacionMasiva.borrador`; snapshot al Consultar; rehidratar al Volver |
| Carga readonly | 21c | Flags `mode=readonly`, `from=importacionMasiva` en `/pedidos/carga`; ocultar grabar/import |
| Router | 21b | Registrar ruta web-only |
| Mobile | 21b | Excluir en `pedidosWebMobilePolicy` + filtro menú |
| i18n | 21b/21c | Prefijo `pedidos.importacionMasiva.*` (es/en/pt/fr/it) |

### Tests

| Capa | TR | Alcance |
|------|-----|---------|
| PHPUnit Unit | 21a | Agrupación, coherencia por grupo, perfil C, vendedor desde maestro |
| PHPUnit Feature | 21a | Lote OK → `grupos[]`; errores sin parcial; 403 sin `pw_importacionmasiva` |
| PHPUnit Feature | 21b | Store con solo `pw_importacionmasiva` → 200; sin permisos → 403 |
| Vitest | 21b | Mapper payload, orquestador secuencial (mock API), sessionStorage |
| Vitest | 21c | Readonly flags, Volver rehidrata |
| E2E Playwright | 21b/21c | Import → grilla → grabar parcial → consultar → volver (opcional wave 2) |

### Documentación

- Manual usuario (post-D): entrada en `PedidosWeb.md` / manual importación masiva.
- OpenAPI: description permiso OR en store.
- Matriz permisos seed documentada en TR.

### DevOps

- Verificar `EXCEL_IMPORT_ENABLED=true` en entornos que usen import.
- Seed menú/permiso en deploy (sin migrate obligatorio salvo catálogo Excel vía seeder/SQL idempotente).
- Smoke: import Excel 2 grupos + 1 grabación OK.

---

## Orden de trabajo

### Ola 1 — TR-21a (backend Excel)

1. Agregar `importacionMasiva => pw_importacionmasiva` en `paqsuite_visibility.php`.
2. Seeder catálogo `PEDIDO_MASIVO` (campos = individual, host distinto, `permite_procesamiento_parcial=false`).
3. Implementar `PedidoMasivoLotValidator` (coherencia cabecera intra-grupo; clave agrupación).
4. Implementar handler + registro en `excel_import.php`.
5. Enganchar respuesta GEN-07 para emitir `resultado.grupos[]` tras lote válido.
6. Tests unit + feature.
7. i18n proceso (reuso claves `PEDIDO_INDIVIDUAL.*` en plantilla).

### Ola 2 — TR-21b (pantalla + grabación)

1. Seed menú `pw_importacionmasiva` + ruta en router web.
2. Exclusión mobile en `pedidosWebMobilePolicy`.
3. Scaffold `ImportacionMasivaPage` + estado borrador (`filas[]`, `idInterno` uuid).
4. Integrar toolbar Excel GEN-07; onComplete mapea `grupos[]` → filas (`esPedido=true` default).
5. Modales reimport (reemplazar / agregar / cancelar).
6. `grabarLoteSecuencial` + mapper reutilizando `grabarComprobante`.
7. Auth OR en `PedidoController@store`, `PresupuestoController@store` y `ComprobanteController@grabar` (alta).
8. Guard salida (`useBlocker`), progreso, toasts resumen.
9. i18n + `data-testid`.
10. Tests Vitest + feature auth.

### Ola 3 — TR-21c (Consultar readonly)

1. Acción Consultar: persistir snapshot en `sessionStorage` + navegar con state.
2. Extender `PedidosCargaPage` (y mobile branch si aplica guard) para `readonly` + `from=importacionMasiva`.
3. Botón Volver → `/pedidos/importacion-masiva` + rehidratar grilla desde sessionStorage.
4. Limpiar sessionStorage en Cancelar / 100% OK / logout (AMB-M-C1-03).
5. Tests Vitest.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Divergencia payload masivo vs individual | Reutilizar `grabarComprobante` + mismos mappers; test comparativo fixture |
| OR permiso abre edición no deseada | Limitar OR **solo** a acción `alta`/`store`; no tocar `modi`/`baja`/`repo` |
| FE reagrupa mal vs backend | C1: consumir `grupos[]` tal cual; no reagrupar en cliente |
| Pérdida borrador al Consultar | sessionStorage snapshot obligatorio antes de navigate |
| Paridad totales renglones | Reusar `renglonesCarga.ts` al hidratar import |
| Excel host vs pantalla propia | Seguir patrón embebido GEN-07 ya usado en carga individual |

---

## Tests a ejecutar

```bash
# Backend 21a + auth 21b
cd backend && php artisan test --filter=PedidoMasivo
cd backend && php artisan test tests/Feature/Api/PedidosWeb/PedidosWebEndpointsAuthTest.php

# Frontend
cd frontend && npm run test -- importacionMasiva
cd frontend && npm run test -- pedidosWebMobilePolicy
```

E2E (opcional): `frontend/tests/e2e/pedidosweb/importacion-masiva.spec.ts`.

---

## Dudas / bloqueos

| ID | Tema | Resolución D1 |
|----|------|----------------|
| D1-01 | ¿`grabarComprobante` vs `POST /pedidos` directo? | **Usar `grabarComprobante`** — mismo body y servicio; TR menciona paths alias por contrato API |
| D1-02 | Enganche exacto GEN-07 para `grupos[]` | Extender respuesta del handler/post-proceso lote existente sin cambiar contrato acordado |
| D1-03 | Helper OR compartido | Método privado en controllers o trait acotado `ensureCargaOrImportacionMasivaStore()` |

**Sin bloqueantes** para iniciar Parte D.

---

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR:** **Sí**
- Mobile excluido: **Sí**
- Sin endpoint lote backend: **Sí**
- Mismo JSON grabación que carga individual: **Sí** (confirmado producto 2026-07-19/20)
- Permiso OR solo en alta/store: **Sí**

---

## Siguiente paso

Iniciar **Parte D — Ola 1 (TR-21a)** salvo indicación contraria del usuario.
