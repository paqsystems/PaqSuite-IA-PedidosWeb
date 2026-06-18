# F-101-16 — Cierre revisión C1 (importación Excel pedido individual)

| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-101-16-importacion-pedido-individual-excel](../../05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| **Fecha** | 2026-06-17 |
| **Alcance** | Revisión C1 de TR-16a y TR-16b contra SPEC, HU, GEN-07 implementado y código actual |
| **Veredicto** | **Apto** — **autorizada Parte D1** en orden 16a → 16b |

## Resultado por TR

| TR | Estado C1 | Bloqueantes D1 |
|----|-----------|----------------|
| [TR-SPEC-101-16-proceso-excel-pedido-individual](TR-SPEC-101-16-proceso-excel-pedido-individual.md) | **Apto** | Ninguno |
| [TR-SPEC-101-16-importacion-excel-pantalla-carga](TR-SPEC-101-16-importacion-excel-pantalla-carga.md) | **Apto** | Depende implementación TR-16a (`validRows` enriquecidos) |

## Checklist C1 transversal

| Área | Estado | Notas |
|------|--------|-------|
| Cobertura CA HU-029 (16) | OK | Matriz §2 TR-16a + tests T9 |
| Cobertura CA HU-030 (13) | OK | Disabled + onComplete + E2E T7 |
| Alineación GEN-07 código | OK | Hook lot-aware; extensión i18n sin romper `ARTICULOS_ALTA` |
| Contrato `ExcelImportHostResult` | OK | Sin cambios; `validRows` snake_case |
| Normas transversales API | OK | Permisos vía `procedimiento_host`; OpenAPI `Accept-Language` en plantilla |
| Decisiones B1 preservadas | OK | Perfil C, orden hidratación, copia deshabilitada |
| CC PQ #6 en import | OK | RN-14 TR-16a; grabar CA-11 TR-16b |

## Decisiones cerradas en C1

| ID | Tema | Decisión |
|----|------|----------|
| AMB-C1-16a-01 | Validación lote cross-fila | Interfaz `ExcelImportLotAwareHandler` + hook en `ExcelImportLotService` post-parse |
| AMB-C1-16a-02 | i18n columnas | `nombre_campo_interno` snake; clave i18n camelCase |
| AMB-C1-16a-03 | Comentario obligatorio | Reemplazar `OBLIGATORIO` fijo en `ExcelImportHeaderCommentBuilder` |
| AMB-C1-16a-04 | Descuento por cantidad | `processRow` + `findDescuentoCantidad` → `porc_bonif` en payload |
| AMB-C1-16a-05 | Payload precio | `precio_lista` → `precio` en JSON |
| AMB-C1-16a-06 | Matriz permisos | Sin fila nueva; `pw_cargapedidos` existente |
| AMB-C1-16b-01 | `excelImportDisabled` | `modo === 'nuevo'`, sin `comprobanteId`, `readOnly`, `renglonesValidosParaGrabar` |
| AMB-C1-16b-02 | Copia | Deshabilitado por URL + renglones precargados |
| AMB-C1-16b-03 | Descuento cantidad UI | Solo mapeo; sin API nueva en v1 |
| AMB-C1-16b-04 | `excelImportEnabled` | `fetchPublicConfig()` en `PedidosCargaPage` |
| AMB-C1-16b-05 | Constante proceso | `EXCEL_PROCESO_PEDIDO_INDIVIDUAL` |

## Hallazgos código vs TR (incorporados en C1)

| Hallazgo | Resolución C1 |
|----------|----------------|
| `ExcelTemplateService` usa `nombre_columna_excel` fijo | T2 TR-16a — `ExcelColumnI18nResolver` |
| `ExcelImportHeaderCommentBuilder` literal `OBLIGATORIO` | T2 — i18n por locale |
| `buildColumnIndexMap` solo match literal BD | T2 — mapa multilenguaje |
| No existe hook validación lote | T0 — `ExcelImportLotAwareHandler` |
| `findDescuentoCantidad` solo en backend | Mover a TR-16a `processRow` (no endpoint FE) |
| `PedidosCargaPage` sin toolbar Excel | TR-16b T2 |
| Copia usa `modo=copia` + `comprobanteId` | Incluido en fórmula `disabled` |

## Observaciones no bloqueantes (D1)

1. Regresión obligatoria: plantilla/parser `ARTICULOS_ALTA` sin i18n sigue igual.
2. Tests feature Excel: skip sin SQL tenant (patrón GEN-07).
3. E2E import: mocks API preferidos en CI.
4. Manual usuario (`PedidosWeb.md`) — Parte Q opcional post-D1.
5. Responsive toolbar: verificar en QA manual que Grabar/Cancelar siguen visibles.

## Orden D1 recomendado

```text
1. TR-SPEC-101-16-proceso-excel-pedido-individual   (T0…T10)
2. TR-SPEC-101-16-importacion-excel-pantalla-carga (T1…T8)
```

## Fuera de alcance confirmado

- Importación masiva / edición comprobante / grabación automática.
- Migrar `ARTICULOS_ALTA` a i18n.
- ABM web `PQ_EXCEL_PROCESOS*`.
- Endpoint nuevo descuento cantidad en frontend.

## Próximo paso

**Parte D1:** implementación código según TR en orden 16a → 16b.

**Deploy tenant:** `PedidosWebExcelImportCatalogSeeder` + `EXCEL_IMPORT_ENABLED=true` (runbook §10.1). **No** bootstrap destructivo en `Ankas_del_sur` sin consentimiento explícito.
