# TR-SPEC-101-16 — Proceso Excel `PEDIDO_INDIVIDUAL` (catálogo, i18n, handler)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-029-proceso-excel-pedido-individual](../../03-historias-usuario/101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual.md) |
| **SPEC relacionada** | [SPEC-101-16-importacion-pedido-individual-excel](../../05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | **Should** |
| **Dependencias** | TR-GEN-07-plantilla-excel; TR-GEN-07-carga-staging-excel; TR-GEN-07-grilla-procesamiento-excel; TR-GEN-07-ui-embebida-host; TR-SPEC-101-04 (servicios pedido); TR-SPEC-101-06 (visibilidad); SPEC-001-04 (parámetros) |
| **Estado** | **C1 cerrado** — apto D1 |
| **Última actualización** | 2026-06-17 (Parte C1) |

**Origen:** [HU-101-029](../../03-historias-usuario/101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual.md)  
**Producto:** [Importación Pedido Individual desde Excel.md](../../02-producto/PedidosWeb/Importación%20Pedido%20Individual%20desde%20Excel.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Proceso de importación Excel `PEDIDO_INDIVIDUAL` con validación de negocio PedidosWeb e i18n de columnas.

### Narrativa
Como usuario autorizado en carga de pedidos, quiero descargar plantilla e importar filas validadas con defaults de cabecera/renglón, para recibir un payload listo al host sin errores parciales.

### In scope / Out of scope
- **In scope:** seeder catálogo; extensión i18n GEN-07 (plantilla, parser, export errores, captions grilla); handler negocio; validación lote (mismo cliente, coherencia cabecera); tests unit/feature.
- **Out of scope:** integración `PedidosCargaPage` → [TR-SPEC-101-16-importacion-excel-pantalla-carga](TR-SPEC-101-16-importacion-excel-pantalla-carga.md); importación masiva; migrar `ARTICULOS_ALTA` a i18n.

---

## 2) Criterios de aceptación (AC)

Heredados de HU-101-029 (CA-01 … CA-16). Resumen ejecutable:

| AC | Verificación |
|----|----------------|
| CA-01 | Seeder idempotente; 23 campos activos orden 1..23 |
| CA-02 | Plantilla `Accept-Language: en` → encabezados inglés |
| CA-03 | Comentario obligatorio i18n `excelImport.columnComment.required` |
| CA-04 | Archivo EN importable con UI en ES |
| CA-05 … CA-11 | Feature + unit handler (errores negocio) |
| CA-12 | `GET .../filas/validas` con payload enriquecido |
| CA-13 | Fila en `PQ_EXCEL_IMPORTACIONES` |
| CA-14 | 403 sin `pw_cargapedidos` |
| CA-15 | Unit handler ≥3 defaults + ≥3 errores |
| CA-16 | Feature lote feliz + error validación |

### Escenarios Gherkin

(Heredados de HU-101-029.)

---

## 3) Reglas de negocio (implementación)

| RN | Implementación |
|----|----------------|
| RN-01 | `permite_procesamiento_parcial=false`; `permite_solo_validar=false`; `procedimiento_host=pw_cargapedidos` |
| RN-02 … RN-03 | `validateBusinessRow` + resolución en `processRow` vía `CabeceraInicialService` / artículos |
| RN-04 | `ExcelColumnI18nResolver` — claves `excelImport.column.{codigoProceso}.{campo}` |
| RN-05 | `PedidoIndividualLotValidator` post-parse: mismo `cod_cliente`; cabecera coherente (excl. renglón) |
| RN-06 | Parcial false ya enforced por GEN-07; host recibe `validRows:[]` si errores |
| RN-07 | Leer `ParametrosCarga` / `PedidosWebParameterService` en contexto lote |
| RN-08 | `PedidosWebVisibilityGuard::ensureCodClienteVisible` |
| RN-09 … RN-12 | Repositorios artículo/cliente/catálogos |
| RN-13 | `processRow` persiste JSON enriquecido en `PQ_EXCEL_IMPORTACIONES_FILAS` |
| RN-14 | Validar cabecera resuelta completa antes de marcar fila válida |

### Campos cabecera — coherencia entre filas

Excluir de comparación: `cod_articulo`, `cantidad`, `precio_lista`, `bonif_renglon`.

Incluir: resto de §2 SPEC-101-16.

### Matriz `Modifica*` (RN-07)

| Columna Excel | Parámetro V | Parámetro S | Perfil C |
|---------------|-------------|-------------|----------|
| `cod_lista`, `precio_lista` | `ModificaListaPrecV`, `ModificaPrecioV` | `ModificaListaPrecS`, `ModificaPrecioS` | Siempre vacío en Excel |
| `bonif_renglon` | `ModificaBonArtV` | `ModificaBonArtS` | Siempre vacío |
| `bonif1`, `bonif2`, `bonif3` | `ModificaBonCliV` | `ModificaBonCliS` | Aplica si editable en pantalla |

Valor no vacío cuando el parámetro correspondiente = `0` → error fila con clave i18n `excelImport.pedidoIndividual.campoNoEditable`.

---

## 3.1) Catálogo `PEDIDO_INDIVIDUAL`

### Seeder

- Archivo: `backend/database/seeders/ExcelImport/PedidosWebExcelImportCatalogSeeder.php`
- Invocar desde runbook §10.1 (junto a `ExcelImportCatalogPilotSeeder` o documentado aparte).
- `nombre_columna_excel` en BD = **fallback español** (sin tildes) para compatibilidad legado / `ARTICULOS_ALTA`.

### Handler registry

```php
// config/excel_import.php
'Importacion.Pedidos.IndividualHandler' => \App\Services\ExcelImport\Handlers\PedidoIndividualExcelImportHandler::class,
```

### Clases nuevas (backend)

| Clase | Rol |
|-------|-----|
| `PedidoIndividualExcelImportHandler` | `validateBusinessRow`, `processRow` |
| `PedidoIndividualLotValidator` | Validación cross-fila tras parse |
| `PedidoIndividualRowResolver` | Defaults §2 SPEC (delega `CabeceraInicialService`, precios, artículo) |
| `ExcelColumnI18nResolver` | Título columna + comentarios por locale/proceso |
| `PedidosWebExcelImportCatalogSeeder` | Catálogo proceso + 23 campos |
| `ExcelImportLotAwareHandler` (interfaz opcional) | Hook validación cross-fila post-parse |

### Seeder — atributos proceso

| Campo | Valor |
|-------|--------|
| `codigo_proceso` | `PEDIDO_INDIVIDUAL` |
| `handler_backend` | `Importacion.Pedidos.IndividualHandler` |
| `permite_procesamiento_parcial` | `false` |
| `permite_solo_validar` | `false` |
| `genera_plantilla` | `true` |
| `procedimiento_host` | `pw_cargapedidos` (config `paqsuite_visibility.procedimientos.cargaComprobantes`) |
| `formato_booleano_plantilla` | `0_1` (sin columnas booleano en v1) |

Lista completa de 23 campos (orden, `nombre_campo_interno`, `tipo_dato`, obligatoriedad): **SPEC-101-16 §2** — no duplicar en TR; el seeder itera esa tabla.

### Hook validación lote (C1)

Hoy `ExcelImportLotService::processLotFile` persiste filas justo después de `parseSheet` (validación fila a fila). Para RN-05 (mismo cliente / cabecera coherente):

1. Nueva interfaz opcional `ExcelImportLotAwareHandler extends ExcelImportHandlerInterface` con método `validateBusinessLot(array $parsedRows, ExcelImportLotContext $ctx): array` (misma forma que filas parseadas).
2. `ExcelImportLotService` invoca el método **después** de `parseSheet` y **antes** del `DB::transaction` de persistencia; el validator marca `tieneError`, `errores` y ajusta contadores.
3. `PedidoIndividualExcelImportHandler` implementa la interfaz y delega en `PedidoIndividualLotValidator`.

Sin extender el contrato para procesos que no lo necesitan (`ARTICULOS_ALTA` sin cambios).

---

## 3.2) Extensión i18n GEN-07

### Convención claves

| Tipo | Patrón |
|------|--------|
| Encabezado | `excelImport.column.PEDIDO_INDIVIDUAL.{campoCamel}` — sufijo **camelCase**; `nombre_campo_interno` en BD permanece **snake_case** (`cod_cliente` → clave `codCliente`) |
| Obligatorio (comentario) | `excelImport.columnComment.required` |
| Ayuda (opcional) | `excelImport.columnComment.PEDIDO_INDIVIDUAL.{campo}` |

**Frontend:** `frontend/src/locales/{es,en,fr,pt,it}.json`  
**Backend:** `backend/lang/{locale}/excel_import.php` (espejo para PhpSpreadsheet)

### Cambios en servicios existentes

| Servicio | Cambio |
|----------|--------|
| `ExcelImportProcessController::plantilla` | Leer `Accept-Language` / `?locale=`; normalizar a `es\|en\|fr\|pt\|it`; fallback `es` |
| `ExcelTemplateService::generateSpreadsheet` | Firma `generateSpreadsheet(PqExcelProceso $proceso, string $locale = 'es')`; encabezado = `ExcelColumnI18nResolver::headerLabel($proceso, $campoInterno, $locale)` |
| `ExcelImportHeaderCommentBuilder` | Inyectar `ExcelColumnI18nResolver`; reemplazar literal `OBLIGATORIO` por `__('excel_import.columnComment.required', [], $locale)` |
| `ExcelImportParserService` | `buildColumnIndexMap`: para cada campo, match encabezado contra **todos** los títulos i18n del proceso + fallback `nombre_columna_excel` |
| `ExcelStagingQueryService` | `caption` i18n (locale request o default `es`) |
| `ExcelImportErrorsExportService` | Encabezados export i18n |

### Compatibilidad `ARTICULOS_ALTA`

Sin cambio de comportamiento: si proceso no define claves i18n, usar `nombre_columna_excel` de BD.

---

## 3.3) Payload fila válida (`validRows`)

Tras `processRow`, `datos_normalizados_json` mínimo:

```json
{
  "cod_cliente": "CLI001",
  "cod_articulo": "ART01",
  "cantidad": 2,
  "precio": 150.5,
  "porc_bonif": 5,
  "porc_iva": 21,
  "descripcion_articulo": "Producto demo",
  "cod_condvta": 1,
  "cod_transpor": "T01",
  "id_de": 3,
  "cod_lista": 1,
  "nivel": 0,
  "bonif1": 0,
  "bonif2": 0,
  "bonif3": 0,
  "expreso": null,
  "expreso_dire": null,
  "fecha_entrega": null,
  "observaciones": "",
  "cod_perfil": "1",
  "leyenda1": null,
  "leyenda2": null,
  "leyenda3": null,
  "leyenda4": null,
  "leyenda5": null
}
```

Claves en **snake** alineadas a API cabecera / handler; el host (TR-16b) mapea a `ComprobanteCabecera` / `ComprobanteRenglon`.

| Campo Excel / interno | Clave payload | Notas |
|-----------------------|---------------|--------|
| `precio_lista` | `precio` | Precio lista resuelto |
| `bonif_renglon` | `porc_bonif` | Tras `findDescuentoCantidad` si aplica (C1 §3.4) |

### Descuento por cantidad (C1 — cerrado)

En `processRow`, tras resolver cantidad y lista: `ArticuloRepository::findDescuentoCantidad($codArticulo, $cantidad)`; si hay fila, `porc_bonif` = bonificación del descuento; si no, default artículo (`bonif_renglon` resuelto). **No** delegar a TR-16b ni exponer endpoint nuevo en v1.

---

## 4) Impacto en datos

### Tablas

| Tabla | Acción |
|-------|--------|
| `PQ_EXCEL_PROCESOS` | INSERT/UPDATE `PEDIDO_INDIVIDUAL` |
| `PQ_EXCEL_PROCESOS_CAMPOS` | 23 filas |
| `PQ_EXCEL_IMPORTACIONES*` | Uso existente GEN-07 |

Sin migración de esquema si tablas GEN-07 ya aplicadas.

### Seed mínimo tests

- Cliente visible con condición, transporte, lista, dirección habitual.
- Artículos con precio en lista; uno `usa_esc = 'B'` para CA-10.
- Parámetros `ModificaListaPrecV=0` escenario permisos.
- `EXCEL_IMPORT_ENABLED=true` en `.env` test.

---

## 5) Contratos API

Reutiliza APIs GEN-07 (`TR-GEN-07-*`). Cambios:

| Método | Path | Cambio |
|--------|------|--------|
| GET | `/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL/plantilla` | Query `locale` o header `Accept-Language`; OpenAPI documentar ambos; 401/403 heredados GEN-07 |
| POST | `/api/v1/excel-import/procesos/PEDIDO_INDIVIDUAL/lotes` | Handler + validación lote |
| GET | `/api/v1/excel-import/lotes/{guid}/filas/validas` | Payload enriquecido |
| GET | `/api/v1/excel-import/lotes/{guid}/export-errores` | Encabezados i18n |

**Permiso:** `Permiso_Alta` en `pw_cargapedidos` (`ProcedimientoHost`).

### Matriz permisos

No requiere filas nuevas en matriz: `procedimiento_host = pw_cargapedidos` reutiliza permisos GEN-07 existentes por `{codigoProceso}`. Documentar en runbook que `PEDIDO_INDIVIDUAL` comparte gate con carga de comprobantes.

---

## 6) Cambios frontend (esta TR)

| Archivo | Cambio |
|---------|--------|
| `frontend/src/locales/*.json` | 23×5 claves `excelImport.column.PEDIDO_INDIVIDUAL.*` + comentarios |
| `excelImportApi.ts` | `downloadExcelTemplate`: header `Accept-Language: i18n.language` |
| `ExcelStagingQueryService` captions | Consumidos por modal GEN-07 (backend i18n) |

Integración toolbar carga → TR-030.

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T0 | Backend | `ExcelImportLotAwareHandler` + hook en `ExcelImportLotService` | Unit lot validator |
| T1 | Backend | `ExcelColumnI18nResolver` + lang files | Unit: 5 idiomas, sin tildes |
| T2 | Backend | Patch `ExcelTemplateService`, `ExcelImportParserService`, comentarios, export | Feature plantilla EN + parse EN |
| T3 | Backend | `PedidosWebExcelImportCatalogSeeder` | CA-01 |
| T4 | Backend | `PedidoIndividualExcelImportHandler` + `PedidoIndividualRowResolver` | Unit defaults/errores |
| T5 | Backend | `PedidoIndividualLotValidator` | Unit coherencia cabecera/cliente |
| T6 | Backend | Registry + `processRow` persist JSON | CA-12 |
| T7 | Frontend | Claves i18n 23 columnas × 5 idiomas | CA-02, CA-03 |
| T8 | Frontend | `Accept-Language` en descarga | CA-02 |
| T9 | Tests | Feature `ExcelImportPedidoIndividualFeatureTest` | CA-16; skip sin SQL tenant |
| T10 | Docs | Runbook §10.1: seeder + `EXCEL_IMPORT_ENABLED` | Deploy |

**Orden ejecución:** T0 → T1 → T2 → T3 → T4 → T5 → T6 → T7 → T8 → T9 → T10.

---

## 10.1) Revisión C1 (2026-06-17)

| ID | Tema | Decisión D1 |
|----|------|-------------|
| AMB-C1-16a-01 | Hook validación lote | `ExcelImportLotAwareHandler` en `ExcelImportLotService` post-parse |
| AMB-C1-16a-02 | i18n BD vs clave | `nombre_campo_interno` snake; sufijo i18n camelCase |
| AMB-C1-16a-03 | Comentario obligatorio | Sustituir `OBLIGATORIO` fijo en `ExcelImportHeaderCommentBuilder` |
| AMB-C1-16a-04 | Descuento cantidad | `processRow` + `findDescuentoCantidad` → `porc_bonif` en payload |
| AMB-C1-16a-05 | Payload precio | `precio_lista` Excel → `precio` en JSON |
| AMB-C1-16a-06 | Matriz permisos | Sin fila nueva; `pw_cargapedidos` vía `procedimiento_host` |
| AMB-C1-16a-07 | Seeder campos | Tabla canónica SPEC §2; seeder por convención piloto |

**Veredicto C1:** **Apto** — sin bloqueantes D1.

**Observaciones no bloqueantes:** sincronizar traducciones FE/BE en mismo PR; tests feature con `BuildsExcelImportWorkbooks`; extensión i18n afecta procesos futuros — regresión `ARTICULOS_ALTA` obligatoria en T2.

---

## 8) Estrategia de tests

- **Unit:** `ExcelColumnI18nResolverTest`; `PedidoIndividualExcelImportHandlerTest` (defaults, permisos, BASE, inhabilitado); `PedidoIndividualLotValidatorTest`.
- **Feature:** `ExcelImportPedidoIndividualFeatureTest` — plantilla locale, lote feliz 2 filas, error `cod_cliente` distinto, error columna obligatoria vacía.
- **Herramienta:** `BuildsExcelImportWorkbooks` trait — workbook con encabezados i18n.

---

## 9) Riesgos y edge cases

| ID | Riesgo | Mitigación |
|----|--------|------------|
| R1 | Divergencia FE/BE traducciones | Tests feature; fuente única documentada (espejo lang PHP) |
| R2 | Validación lote vs fila | `PedidoIndividualLotValidator` después de parse, antes de procesar |
| R3 | `processRow` no persiste JSON | Test integración lee `filas/validas` |
| R4 | Tenant sin GEN-07 migrado | Runbook migrate + seed |
| R5 | Performance N filas × resolver defaults | Cache cabecera por `cod_cliente` dentro del lote |

---

## 10) Checklist final

- [ ] Seeder `PEDIDO_INDIVIDUAL` idempotente
- [ ] i18n plantilla + parser multilenguaje
- [ ] Handler registrado y con tests
- [ ] Matriz permisos actualizada
- [ ] Runbook §10.1 documentado
- [ ] Sin ampliación fuera SPEC/HU/TR
- [ ] TR-030 puede consumir `validRows` (stub o implementación real)

---

## Archivos previstos

### Backend
- `app/Services/ExcelImport/ExcelColumnI18nResolver.php`
- `app/Services/ExcelImport/Handlers/PedidoIndividualExcelImportHandler.php`
- `app/Services/ExcelImport/PedidoIndividual/PedidoIndividualRowResolver.php`
- `app/Services/ExcelImport/PedidoIndividual/PedidoIndividualLotValidator.php`
- `app/Services/ExcelImport/Contracts/ExcelImportLotAwareHandler.php`
- `app/Services/ExcelImport/ExcelImportLotService.php` (hook lot-aware)
- `database/seeders/ExcelImport/PedidosWebExcelImportCatalogSeeder.php`
- `lang/{es,en,fr,pt,it}/excel_import.php`
- `tests/Unit/Services/ExcelImport/PedidoIndividual*Test.php`
- `tests/Feature/Api/ExcelImport/ExcelImportPedidoIndividualFeatureTest.php`

### Frontend
- `frontend/src/locales/*.json` (claves columna)
- `frontend/src/features/excelImport/api/excelImportApi.ts` (Accept-Language)
