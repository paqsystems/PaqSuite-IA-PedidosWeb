# Cierre F — CC PQ #7 (15/06/2026) y #8 (19/06/2026)

## Alcance

Verificación **F1 + F** (openspec-05) sobre correcciones derivadas de los controles de calidad #7 y #8. Implementación en **originales** HU/TR/SPEC (sin archivos `*-update*` en `updates/`).

**Fecha verificación:** 19/06/2026  
**Parte E:** [E-CC-PQ-7-8-tests.md](E-CC-PQ-7-8-tests.md)  
**Rama / build:** `v1.1.0-paq` @ `df424e8`

---

## CC #7 — Familias verificadas

| Ítem CC | HU / TR / SPEC | Evidencia código | Estado F1 |
|---------|----------------|------------------|-----------|
| i18n Consulta parámetros | HU-GEN-04, TR-GEN-04, SPEC-001-04 | `resolveParametroConsultaTexts.ts`, `ParametrosConsultaPage.tsx`, claves `parametros.pedidosWeb.*` en 5 locales | OK |
| i18n captions pivot | HU-GEN-08, TR-GEN-08 | `resolveConsultaColumnCaption.ts`, `mapMetadataToPivotFields.ts`, `usePivotDataSource.ts` | OK |
| Perfil inicial `CodPerfilPedidos` | HU-101-005, TR-SPEC-101-10 | `CabeceraInicialService::resolveCodPerfilInicial`, `PedidosWebParameterService::getCodPerfilPedidos` | OK |
| Rediseño pantalla carga | HU-101-005, TR-SPEC-101-10 | `PedidosCargaPage.tsx` + `.css`, `ComprobanteCabeceraForm.tsx` (layout 3 columnas / toolbar / leyendas) | OK |
| Validaciones grabación | HU-101-009/010, TR-SPEC-101-04 | `ComprobanteGrabacionValidator.php` + tests unitarios | OK |

### CC #7 — Detalle validaciones grabación

| Regla CC | Evidencia | Estado |
|----------|-----------|--------|
| Cliente, vendedor, perfil, condición, transporte, DE, lista | `assertComprobanteGrabable` + lookups tablas | OK |
| Al menos un renglón | `assertRenglonesGrabables` | OK |
| `NivelExtremo` → nivel 0 o 100 | `assertNivelValido` | OK |
| Precio cero según parámetros | `assertPreciosRenglonesValidos` | OK |
| Cliente `inhabilitado = false` | `assertClienteHabilitado` | OK |

---

## CC #8 — Familias verificadas

| Ítem CC | HU / TR / SPEC | Evidencia código | Estado F1 |
|---------|----------------|------------------|-----------|
| Vendedor al importar Excel | HU-101-029/030, TR-SPEC-101-16 | `handleExcelImportComplete` → `fetchCabeceraInicial` + `mapExcelRowToCabecera` conserva `codVended`/`vendedorNombre` del cliente | OK |
| Precarga catálogo artículos una vez | HU-101-005, TR-SPEC-101-10 | `useEffect` montaje → `loadArticulosCatalogo()`; búsqueda local en `SelectBoxDx` | OK |
| Mensaje cargando artículos | HU-101-005 | `articulosCargando` i18n + `data-testid="articulos-cargando"` | OK |
| Icono Actualizar artículos | HU-101-005 | `Button` icon `refresh`, `data-testid="articulosRefresh"` | OK |

---

## F — Verificación documental (TR ↔ SPEC ↔ HU ↔ producto)

**Resultado F:** **Aprobado con observaciones**

| Documento | Alineado | Nota |
|-----------|----------|------|
| HU/TR base (#7) | Sí | Metadatos **Finalizado** |
| HU/TR base (#8) | Sí | Metadatos **Finalizado** |
| `pantalla-carga-comprobante-ui.md` | Parcial | Actualizado en Parte I (precarga al ingresar + refresh) |
| `docs/99-manual-usuario/PedidosWeb.md` | Parcial | Actualizado en Parte I (§6.7, §6.12, import Excel) |
| `00-ControlCalidad-PQ.md` #7 / #8 | Sí | Cierre Parte I 19/06/2026 |

### Observaciones no bloqueantes

| ID | Tema | Destino |
|----|------|---------|
| OBS-01 | E2E dedicado import Excel + vendedor cliente | Opcional; cubierto por unit `mapExcelImportToCarga` |
| OBS-02 | QA manual PQ layout vs imagen diseño | Recomendado post-deploy |
| OBS-03 | Precarga artículos sin lista de precios en cabecera | Combobox deshabilitado hasta lista válida (comportamiento producto) |

---

## Veredicto final

| Control | F1 | F |
|---------|----|---|
| CC #7 (15/06/2026) | Aprobado | Aprobado con observaciones |
| CC #8 (19/06/2026) | Aprobado | Aprobado con observaciones |

**Estado CC #7 y #8:** listos para **Parte I** — [I-CC-PQ-7-8-cierre-formal.md](I-CC-PQ-7-8-cierre-formal.md)
