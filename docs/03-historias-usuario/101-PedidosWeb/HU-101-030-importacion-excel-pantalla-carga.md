# HU-101-030 — Importación Excel en pantalla de carga

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-030-importacion-excel-pantalla-carga |
| **SPEC origen** | [SPEC-101-16-importacion-pedido-individual-excel](../../05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| **Épica** | 101 — PedidosWeb / Carga comprobantes |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | **Cerrado** (2026-06-17) |
| **C1** | **Apto** (2026-06-17) — [F-101-16-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-16-cierre-c1.md) |
| **TR** | [TR-SPEC-101-16-importacion-excel-pantalla-carga](../../04-tareas/101-PedidosWeb/TR-SPEC-101-16-importacion-excel-pantalla-carga.md) |
| **Dependencias** | [HU-101-029](HU-101-029-proceso-excel-pedido-individual.md); HU-GEN-07-ui-embebida-host; HU-101-005; HU-101-006; HU-101-007; HU-101-008; HU-101-009; HU-101-010 |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Toolbar embebido en carga | CA-01, CA-02 |
| Reglas `disabled` por perfil y modo | CA-03, CA-04, CA-05 |
| SPEC CA-01, CA-02 (habilitación) | CA-01 … CA-05 |
| `onComplete` → hidratar cabecera y renglones | CA-06, CA-07, CA-08 |
| Cálculos `renglonesCarga.ts` | CA-09, CA-10 |
| SPEC CA-07, CA-08 (éxito + grabar) | CA-06, CA-09, CA-10, CA-11 |
| Grabación manual + CC PQ #6 | CA-11 |
| i18n UI + Accept-Language plantilla | CA-12 |
| E2E / historial indirecto | CA-13 |

## Narrativa

Como **usuario que carga un pedido o presupuesto nuevo**,  
quiero **importar una planilla Excel desde la pantalla de carga y ver el comprobante precargado**,  
para **ingresar muchos renglones sin tipear uno por uno**.

## Contexto funcional

La pantalla `/pedidos/carga` incorpora `ExcelImportHostToolbar` con `codigoProceso="PEDIDO_INDIVIDUAL"`. Tras importación exitosa, el callback `onComplete` vuelca datos al formulario existente (cabecera + grilla renglones) y recalcula bonificación neta, importes y totales con las mismas utilidades que la carga manual. **No graba** el comprobante: el usuario confirma con **Grabar pedido** o **Grabar presupuesto**.

## Actores

| Actor | Comportamiento import |
|-------|----------------------|
| **Vendedor / Supervisor** | Import solo con formulario vacío (sin cliente en combobox, sin renglones). |
| **Cliente** | Import en comprobante nuevo con cliente fijo de sesión. |

## Datos involucrados

| Objeto | Rol |
|--------|-----|
| `PedidosCargaPage` / `ComprobanteCabecera` / `ComprobanteRenglon` | Estado UI post-`onComplete` |
| `ExcelImportHostResult.validRows` | Payload del proceso `PEDIDO_INDIVIDUAL` |
| `renglonesCarga.ts` | Bonificación neta, importes, totales |
| `pantalla-carga-comprobante-ui.md` §12 | Descuento por cantidad al hidratar |

## Alcance incluido

- `ExcelImportHostToolbar` en toolbar de `PedidosCargaPage` (`data-testid` GEN-07).
- Lógica `disabled` / visibilidad:
  - Oculto o deshabilitado si `excelImportEnabled === false`.
  - Deshabilitado en modo **edición** (`codPedido` existente).
  - Perfil **vendedor/supervisor:** deshabilitado si `selectedCliente` no vacío **o** hay renglones cargados.
  - Perfil **cliente:** habilitado en comprobante **nuevo** (cliente fijo de sesión).
- Handler `onComplete` con `validRows` no vacío:
  1. Fijar cliente y `fetchCabeceraInicial`.
  2. Sobrescribir cabecera con valores importados (primera fila / campos resueltos).
  3. Mapear filas a `ComprobanteRenglon[]` (reemplazar grilla).
  4. Aplicar descuento por cantidad si aplica (§12 `pantalla-carga-comprobante-ui`).
  5. Recalcular con `calcularBonificacionNeta`, importes por renglón y `calcularTotalesComprobante`.
- `onComplete` vacío: **no** modificar formulario.
- i18n: `pedidos.carga.excelImport.*` (mensajes locales al host si aplica).
- Envío `Accept-Language` en descarga plantilla (`ExcelTemplateDownloadButton`).

## Fuera de alcance

- Definición catálogo / handler backend → [HU-101-029](HU-101-029-proceso-excel-pedido-individual.md).
- Import en edición de comprobante.
- Grabación automática post-import.
- Cambios al motor modal GEN-07 salvo props/callback.

## Reglas de negocio

1. Import solo en **alta** de comprobante nuevo.
2. Formulario debe estar **vacío** de renglones; V/S sin cliente en combobox (producto + A1).
3. Perfil **C:** import habilitado con cliente precargado; Excel debe coincidir con sesión (validado en HU-101-029).
4. Tras import exitoso, controles de cabecera siguen reglas `Modifica*` (deshabilitados si ERP no permite).
5. Totales en UI = mismas fórmulas que HU-101-007/008 (`renglonesCarga.ts`).
6. Grabar aplica validaciones HU-101-009/010 y CC PQ #6 sin excepción por origen Excel.
7. Respetar `isDevExtremeUserChange` al hidratar programáticamente (§14 `pantalla-carga-comprobante-ui`).
8. **Copia de comprobante:** si la pantalla abre con renglones precargados, import deshabilitado (equivalente a «formulario no vacío»).
9. **Descuento por cantidad:** tras mapear renglones, aplicar `findDescuentoCantidad` por cantidad importada (§12 producto).

## Criterios de aceptación

- [ ] **CA-01:** Comprobante nuevo: visible toolbar con **Descargar plantilla** e **Importar** si `excelImportEnabled`.
- [ ] **CA-02:** Modo edición: toolbar import oculto o deshabilitado.
- [ ] **CA-03:** V/S: con cliente en combobox, **Importar** deshabilitado.
- [ ] **CA-04:** V/S: con renglones cargados, **Importar** deshabilitado aunque no haya cliente.
- [ ] **CA-05:** C: comprobante nuevo con cliente fijo → **Importar** habilitado.
- [ ] **CA-06:** Import exitoso: combobox cliente y cabecera coherentes con Excel; N renglones en grilla.
- [ ] **CA-07:** Import con errores: formulario sin cambios; modal muestra grilla solo errores (GEN-07).
- [ ] **CA-08:** Tras import, usuario puede editar renglones manualmente antes de grabar.
- [ ] **CA-09:** Bonificación neta e importes por renglón = mismos valores que carga manual equivalente.
- [ ] **CA-10:** Totales cabecera (subtotal, IVA, total) actualizados tras import.
- [ ] **CA-11:** Grabar pedido/presupuesto post-import: validaciones HU-101-009/010 y CC PQ #6 (cabecera completa, ≥1 renglón, perfil, nivel extremo, precio cero, cliente habilitado).
- [ ] **CA-12:** Textos visibles del host vía i18n; `data-testid` estables.
- [ ] **CA-13:** E2E: nuevo comprobante → import mock/archivo test → renglones visibles en grilla.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Usuario cierra modal con errores | Formulario intacto |
| `validRows` vacío tras parcial false | Sin cambios en cabecera/renglones |
| Import OK pero grabar sin vendedor/perfil válido | Rechazo grabación (CC PQ #6) |
| Cambio idioma tras descargar plantilla | Import sigue funcionando (parser multilenguaje, HU-101-029) |

## Escenarios Gherkin

```gherkin
Feature: Importacion Excel en pantalla de carga

  Background:
    Given EXCEL_IMPORT_ENABLED activo
    And un vendedor en comprobante nuevo sin cliente seleccionado

  Scenario: Toolbar habilitado en carga nueva
  When abre la pantalla de carga
  Then ve el toolbar de importacion Excel
  And el boton Importar esta habilitado

  Scenario: Import deshabilitado tras elegir cliente
  When selecciona un cliente en el combobox
  Then el boton Importar queda deshabilitado

  Scenario: Volcado exitoso al formulario
  Given un archivo valido para PEDIDO_INDIVIDUAL
  When completa la importacion sin errores
  Then el cliente del comprobante coincide con el Excel
  And la grilla muestra los renglones importados
  And los totales reflejan bonificacion neta e IVA calculados

  Scenario: Sin cambios si hay errores
  Given un archivo con fila invalida
  When cierra el modal tras ver errores
  Then el formulario permanece sin cliente ni renglones cargados por import

  Scenario: Perfil cliente
  Given un usuario cliente en comprobante nuevo
  Then el boton Importar esta habilitado
  When importa un archivo con su codigo de cliente
  Then los renglones se cargan en la grilla
```

## Supuestos explícitos

- Proceso `PEDIDO_INDIVIDUAL` operativo (HU-101-029).
- Componente `ExcelImportHostToolbar` sin cambios de contrato público.
- `fetchCabeceraInicial` y catálogos disponibles tras fijar cliente.

| Copia comprobante con renglones | Import deshabilitado |

## Revisión B1 — cierre (2026-06-17)

### Resultado

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto** |
| **Lista para TR** | **Sí** (orden 2; depende HU-101-029) |
| **Bloqueantes** | Ninguno |

### Checklist B1

| Área | Estado | Notas |
|------|--------|-------|
| Narrativa / actores V-S-C | OK | Excepción perfil C alineada A1 |
| Trazabilidad SPEC CA UI | OK | CA-01 … CA-13 ↔ SPEC §CA |
| Integración GEN-07 embebido | OK | Sin cambiar contrato `onComplete` |
| Cálculos HU-007/008 | OK | `renglonesCarga.ts` |
| CC PQ #6 en grabar | OK | CA-11 explícito |
| Gherkin | OK | 5 escenarios |
| Copia comprobante | OK | RN-08 cerrado en B1 |

### Decisiones cerradas en B1

| ID | Tema | Decisión |
|----|------|----------|
| AMB-B1-030-01 | Ubicación toolbar | Zona superior de `PedidosCargaPage`, sin desplazar acciones Grabar/Cancelar |
| AMB-B1-030-02 | Orden hidratación | Cliente → cabecera inicial API → overlay Excel → renglones → totales |
| AMB-M-101-16-03 | Descuento cantidad | En `onComplete` host, no en handler |

## Veredicto B1

**Cerrado — lista para TR** (`TR-SPEC-101-16-importacion-excel-pantalla-carga`, orden 2). **Depende** de TR-029 implementada o stub de `validRows` en tests.
