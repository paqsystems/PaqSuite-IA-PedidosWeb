# HU-101-029 — Proceso Excel pedido individual (catálogo y handler)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-029-proceso-excel-pedido-individual |
| **SPEC origen** | [SPEC-101-16-importacion-pedido-individual-excel](../../05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| **Épica** | 101 — PedidosWeb / Carga comprobantes |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | **Cerrado** (2026-06-17) |
| **C1** | **Apto** (2026-06-17) — [F-101-16-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-16-cierre-c1.md) |
| **TR** | [TR-SPEC-101-16-proceso-excel-pedido-individual](../../04-tareas/101-PedidosWeb/TR-SPEC-101-16-proceso-excel-pedido-individual.md) |
| **Dependencias** | HU-GEN-07-plantilla-excel; HU-GEN-07-carga-staging-excel; HU-GEN-07-grilla-procesamiento-excel; HU-GEN-07-ui-embebida-host; HU-101-005; HU-101-006; SPEC-001-04 (parámetros) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Proceso `PEDIDO_INDIVIDUAL` en catálogo | RN-01, CA-01 |
| 23 columnas + obligatoriedad + defaults | RN-02, RN-03, CA-12 |
| i18n encabezados y comentarios (§2.1) | RN-04, CA-02, CA-03, CA-04 |
| Validaciones handler (sin parcial, permisos, CC PQ #6 en import) | RN-05 … RN-12, CA-05 … CA-11 |
| Enriquecimiento `datos_normalizados_json` | RN-13, CA-12 |
| Historial lote | CA-13 |
| Permisos host | CA-14 |
| SPEC CA-03 … CA-09 (backend) | CA-02 … CA-14 |

## Narrativa

Como **usuario autorizado en carga de pedidos/presupuestos**,  
quiero **descargar una plantilla Excel y que el sistema valide mi archivo con las reglas de negocio del comprobante**,  
para **importar renglones y cabecera sin errores antes de volcarlos en pantalla**.

## Contexto funcional

Proceso de importación **`PEDIDO_INDIVIDUAL`** sobre el motor GEN-07 (`PQ_EXCEL_*`). El handler de negocio valida filas, resuelve defaults como `CabeceraInicialService` y artículos por lista, y entrega filas procesadas al componente embebido. **Sin procesamiento parcial:** cualquier error bloquea el payload al host.

Los **títulos de columna** en plantilla y validación estructural usan **i18n** (idioma activo al descargar; parser acepta los 5 idiomas del portal).

## Alcance incluido

- Seeder idempotente: `PQ_EXCEL_PROCESOS` + `PQ_EXCEL_PROCESOS_CAMPOS` para `PEDIDO_INDIVIDUAL` (23 campos, orden SPEC §2).
- Registro handler en `config/excel_import.php` / `ExcelImportHandlerRegistry`.
- Handler `Importacion.Pedidos.IndividualHandler` (nombre acordado en TR):
  - `validateBusinessRow`: reglas §3 SPEC (cliente único, coherencia cabecera, permisos `Modifica*`, visibilidad, artículos, catálogos, nivel, precio cero, cliente inhabilitado).
  - `processRow`: resolver vacíos §2; persistir enriquecimiento en `datos_normalizados_json`.
- Extensión GEN-07 para este proceso:
  - Plantilla: encabezados y comentarios vía claves `excelImport.column.PEDIDO_INDIVIDUAL.*` y `excelImport.columnComment.*` según `Accept-Language`.
  - Parser: mapa inverso multilenguaje → `nombre_campo_interno`.
  - Export errores / captions grilla modal: mismos títulos i18n.
- Claves i18n en `frontend/src/locales/*.json` (es, en, fr, pt, it) para las 23 columnas + comentarios.
- `procedimiento_host = pw_cargapedidos`; `permite_procesamiento_parcial = false`; `permite_solo_validar = false`.

## Fuera de alcance

- Integración `onComplete` en `PedidosCargaPage` → [HU-101-030](HU-101-030-importacion-excel-pantalla-carga.md).
- Importación masiva o múltiples clientes por archivo.
- Grabación del comprobante en BD.
- Migrar proceso piloto `ARTICULOS_ALTA` a i18n (opcional post v1).

## Actores

| Actor | Uso |
|-------|-----|
| **Vendedor (V)** | Descarga plantilla, importa; cliente debe estar en cartera; reglas `Modifica*V`. |
| **Supervisor (S)** | Idem con `Modifica*S`. |
| **Cliente (C)** | Importa con `cod_cliente` = sesión. |

## Datos involucrados

| Objeto | Rol |
|--------|-----|
| `PQ_EXCEL_PROCESOS` / `PQ_EXCEL_PROCESOS_CAMPOS` | Catálogo proceso y columnas |
| `PQ_EXCEL_IMPORTACIONES*` | Lote, filas staging, errores |
| `pq_pedidosweb_clientes`, catálogos cabecera | Defaults y validación |
| `pq_pedidosweb_articulos`, precios por lista | Renglón y `porc_iva` |
| Parámetros ERP (`Modifica*`, `NivelExtremo`, `ClienteLeyenda*`, etc.) | Permisos y reglas CC PQ #6 en import |

## Reglas de negocio

1. **RN-01:** Código proceso fijo `PEDIDO_INDIVIDUAL`; activo solo con `EXCEL_IMPORT_ENABLED=true`.
2. **RN-02:** Obligatorios estructurales: `cod_cliente`, `cod_articulo`, `cantidad` — vacío → error fila.
3. **RN-03:** Vacíos en columnas opcionales → defaults según tabla SPEC §2 (mismas fuentes que cabecera inicial y precios por lista).
4. **RN-04:** Encabezados Excel sin tildes; texto visible = traducción i18n del locale solicitado; import acepta encabezado en cualquiera de los 5 idiomas.
5. **RN-05:** Todas las filas mismo `cod_cliente`; campos cabecera (excl. `cod_articulo`, `cantidad`, `precio_lista`, `bonif_renglon`) idénticos entre filas — validación **a nivel lote** (TR).
6. **RN-06:** Sin parcial: ≥1 fila con error → lote no entrega filas válidas al host (`validRows: []`).
7. **RN-07:** Permisos ERP: columnas no editables en pantalla deben venir vacías en Excel; valor informado → error.
8. **RN-08:** Perfil cliente: `cod_cliente` = sesión. Vendedor/supervisor: cliente en cartera.
9. **RN-09:** Artículo existente; excluir `usa_esc = 'B'`; cantidad > 0.
10. **RN-10:** `NivelExtremo` → nivel solo 0 o 100. `Articulopreciocero` / `Articulossinprecio` → sin precio 0.
11. **RN-11:** Cliente con `inhabilitado = 0`.
12. **RN-12:** Catálogos informados o resueltos deben existir y ser válidos para el cliente.
13. **RN-13:** Payload fila válida incluye cabecera resuelta + `cod_articulo`, `cantidad`, `precio`, `porc_bonif`, `porc_iva`, `descripcion_articulo`.
14. **RN-14 (CC PQ #6 en import):** Tras resolver defaults, cabecera debe tener valores válidos en catálogo (perfil, condición, transporte, dirección, lista, vendedor); al menos un renglón válido en el archivo; cliente no inhabilitado.

## Criterios de aceptación

- [ ] **CA-01:** Existe fila catálogo `PEDIDO_INDIVIDUAL` con 23 campos activos en orden documentado.
- [ ] **CA-02:** `GET .../plantilla` con `Accept-Language: en` devuelve encabezados en inglés (23 columnas).
- [ ] **CA-03:** Comentario obligatorio usa i18n `excelImport.columnComment.required` (no literal fijo `OBLIGATORIO` solo en español).
- [ ] **CA-04:** Archivo generado en `en` se importa con UI en `es` (parser multilenguaje).
- [ ] **CA-05:** Fila sin `cod_cliente`, `cod_articulo` o `cantidad` → error; sin filas válidas para host.
- [ ] **CA-06:** `cod_cliente` distinto entre filas → error en filas afectadas.
- [ ] **CA-07:** `bonif1` distinto entre filas → error; sin volcado parcial.
- [ ] **CA-08:** Vendedor con `ModificaListaPrecV=false` y `cod_lista` informado → error.
- [ ] **CA-09:** Cliente inhabilitado → error en todas las filas del lote.
- [ ] **CA-10:** Artículo BASE (`usa_esc = 'B'`) → error fila.
- [ ] **CA-11:** Precio resuelto = 0 con parámetros que lo prohiben → error.
- [ ] **CA-12:** Importación exitosa: `GET .../filas/validas` devuelve N filas con defaults resueltos y `porc_iva`.
- [ ] **CA-13:** Lote queda en `PQ_EXCEL_IMPORTACIONES` (historial GEN-07).
- [ ] **CA-14:** Sin permiso `pw_cargapedidos` → 403 en plantilla y carga.
- [ ] **CA-15:** Tests unitarios handler: ≥3 combinaciones defaults + ≥3 errores de negocio.
- [ ] **CA-16:** Feature API: lote feliz + error validación para `PEDIDO_INDIVIDUAL`.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| `EXCEL_IMPORT_ENABLED=false` | APIs epic deshabilitadas (GEN-07) |
| Proceso inactivo en catálogo | 404 `excelImport.procesoNotFound` |
| Columna faltante / encabezado en idioma no soportado | Error estructural; sin grilla filas |
| Mezcla filas válidas e inválidas | Parcial false → `validRows` vacío al host |
| Cliente de sesión ≠ Excel (perfil C) | Error en validación negocio |

## Escenarios Gherkin

```gherkin
Feature: Proceso Excel PEDIDO_INDIVIDUAL

  Background:
    Given EXCEL_IMPORT_ENABLED activo
    And un usuario con permiso alta en pw_cargapedidos

  Scenario: Plantilla en idioma activo
  Given el usuario tiene idioma en ingles
  When descarga la plantilla del proceso PEDIDO_INDIVIDUAL
  Then la fila 1 contiene 23 encabezados en ingles sin tildes
  And las columnas obligatorias tienen comentario de requerido traducido

  Scenario: Importar archivo en otro idioma que la UI actual
  Given un archivo descargado con encabezados en ingles
  And la UI del usuario esta en espanol
  When importa y valida el archivo
  Then la validacion estructural reconoce las columnas
  And continua la validacion de negocio

  Scenario: Cliente distinto entre filas
  Given un archivo con dos filas y cod_cliente diferente
  When valida el lote
  Then hay filas con error
  And no hay filas validas para el host

  Scenario: Defaults de cabecera desde cliente
  Given un archivo con solo cod_cliente cod_articulo y cantidad informados
  And el cliente tiene condicion de venta y lista habituales
  When procesa el lote sin errores
  Then cada fila valida incluye cod_condvta y cod_lista resueltos
```

## Supuestos explícitos

- Motor GEN-07 (APIs, staging, modal embebido) ya implementado (D1/D2).
- Parámetros ERP legibles en runtime (`PedidosWebParameterService`).
- `nombre_columna_excel` en BD puede servir como fallback `es` si falta traducción.

## Preguntas abiertas

(Ninguna — cerradas en B1; ver [F-101-16-cierre-b1.md](../../04-tareas/101-PedidosWeb/F-101-16-cierre-b1.md).)

## Revisión B1 — cierre (2026-06-17)

### Resultado

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto** |
| **Lista para TR** | **Sí** (orden 1 en SPEC-101-16) |
| **Bloqueantes** | Ninguno |

### Checklist B1

| Área | Estado | Notas |
|------|--------|-------|
| Narrativa / actor | OK | V, S, C |
| Trazabilidad SPEC CA backend | OK | CA-02 … CA-14 cubren SPEC §CA |
| RN medibles | OK | RN-14 CC PQ #6 en capa import |
| Gherkin | OK | 4 escenarios |
| Casos negativos | OK | Tabla § casos negativos |
| i18n vs GEN-07 legado | OK | Extensión documentada; no rompe `ARTICULOS_ALTA` |
| Coherencia cabecera lote | OK | RN-05 explicita validación cross-fila en TR |
| Dependencias GEN-07 | OK | Motor D1/D2 prerequisito |

### Decisiones cerradas en B1

| ID | Tema | Decisión |
|----|------|----------|
| AMB-M-101-16-03 | Descuento por cantidad | **Host** (HU-101-030) al hidratar; no en handler |
| AMB-M-101-16-04 | Artículo duplicado | Permitir; N renglones |
| AMB-B1-029-01 | Fuente traducciones plantilla | `frontend/src/locales` + espejo `backend/lang/{locale}/excel_import.php` |
| AMB-B1-029-02 | `OBLIGATORIO` en comentario | i18n `excelImport.columnComment.required` (evolución GEN-07) |

## Veredicto B1

**Cerrado — lista para TR** (`TR-SPEC-101-16-proceso-excel-pedido-individual`, orden 1). Ver [HU-101-030](HU-101-030-importacion-excel-pantalla-carga.md) (orden 2).
