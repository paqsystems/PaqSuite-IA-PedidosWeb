# HU-101-043 — Proceso Excel `PEDIDO_MASIVO` (catálogo, handler y agrupación)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-043-proceso-excel-pedido-masivo |
| **SPEC origen** | [SPEC-101-21-importacion-masiva-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Épica** | 101 — PedidosWeb / Importación masiva |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | **Cerrado** (2026-07-19) |
| **TR** | [TR-SPEC-101-21-proceso-excel-pedido-masivo](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo.md) |
| **Dependencias** | HU-GEN-07-* (motor Excel); [HU-101-029](HU-101-029-proceso-excel-pedido-individual.md) (columnas / i18n `PEDIDO_INDIVIDUAL`); SPEC-001-04; SPEC-101-06 |
| **HUs relacionadas** | [HU-101-044](HU-101-044-pantalla-importacion-masiva.md); [HU-101-045](HU-101-045-consultar-borrador-importacion-masiva.md) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| §2 proceso `PEDIDO_MASIVO`, `genera_plantilla`, parcial=false, host | RN-01, RN-14, CA-01, CA-15 |
| Columnas = 101-16 §2; i18n AMB-01; sin vendedor | RN-02, CA-02 |
| SPEC CA-02 (plantilla) | CA-02 |
| SPEC CA-03 (multi-grupo + Pedido) | CA-03, CA-16 (tipo vía host 044) |
| SPEC CA-04 (vendedor maestro) | RN-07, CA-09 |
| SPEC CA-05 (perfil C) | RN-04, CA-06 |
| Validaciones §2 + herencia 101-16 | RN-10, CA-05, CA-08, CA-11, CA-17 |
| Agrupación §3 + AMB-M-12 | RN-08 … RN-12, CA-03, CA-04, CA-18 |
| AMB-C-02 coherencia cruda | RN-06, CA-08 |
| AMB-M-09 sin vendedor | RN-07, CA-10 |
| AMB-M-10 / permiso `pw_importacionmasiva` | RN-01, RN-13, CA-12, CA-13 |
| Entregable seeder + tests unit/feature | CA-01, CA-19 |

## Narrativa

Como **usuario autorizado de importación masiva**,  
quiero **que el sistema valide mi Excel multi-comprobante, resuelva cabeceras/renglones y agrupe por cliente+vendedor+nivel**,  
para **obtener un conjunto de borradores listos para la grilla sin ingreso parcial erróneo**.

## Contexto funcional

SPEC-101-21 §2–§3 define el proceso GEN-07 **`PEDIDO_MASIVO`**: mismas columnas e i18n que `PEDIDO_INDIVIDUAL`, pero admite múltiples cabeceras/clientes. El handler valida el lote **sin parcial**, resuelve defaults (espíritu `PedidoIndividual` + `CabeceraInicialService`), asigna vendedor del maestro cliente, agrupa por `(cod_cliente, cod_vended, nivel)` y entrega **comprobantes armados** al host (AMB-M-12).

## Actores

| Actor | Regla |
|-------|--------|
| **Vendedor / Supervisor** | Cada `cod_cliente` del archivo en cartera visible (SPEC-101-06) |
| **Cliente (C)** | Todo `cod_cliente` = cliente de sesión |
| Sin `pw_importacionmasiva` | 403 en APIs del proceso |

## Alcance incluido

- Seeder idempotente `PQ_EXCEL_PROCESOS` + campos: `PEDIDO_MASIVO`, mismos `nombre_campo_interno` que 101-16 §2.
- `genera_plantilla = true`; `permite_procesamiento_parcial = false`; `procedimiento_host = pw_importacionmasiva`.
- Handler registrado: validate + process/agrupar; payload de grupos con cabecera resuelta, renglones, vendedor y datos para totales.
- Plantilla: títulos/comentarios vía `excelImport.column.PEDIDO_INDIVIDUAL.*` (y comentarios asociados); parser multilenguaje.
- Obligatorios estructurales: `cod_cliente`, `cod_articulo`, `cantidad`.
- Historial de lote GEN-07.
- Tests unit de agrupación/vendedor/errores + feature API del proceso.

## Fuera de alcance

- Pantalla grilla, toggles, Grabar FE, modales, menú UI → [HU-101-044](HU-101-044-pantalla-importacion-masiva.md).
- Consultar readonly → [HU-101-045](HU-101-045-consultar-borrador-importacion-masiva.md).
- Cambiar columnas de la plantilla individual.
- Endpoint de grabación en lote.
- Persistencia de borrador en servidor.
- Tipo pedido/presupuesto en Excel (siempre Pedido en host).

## Datos involucrados

| Objeto | Rol |
|--------|-----|
| `PQ_EXCEL_*` | Catálogo, staging, historial |
| `pq_pedidosweb_clientes` (+ vendedor) | Defaults, `cod_vended`, razón social |
| Catálogos / artículos / parámetros | Validación y defaults (herencia 101-16) |

## Reglas de negocio

1. **RN-01:** Código `PEDIDO_MASIVO`; usable solo con catálogo activo y `EXCEL_IMPORT_ENABLED`.
2. **RN-02:** 23 campos = 101-16; **sin** columna vendedor; i18n reutilizado `PEDIDO_INDIVIDUAL.*`.
3. **RN-03:** ≥1 error estructura/validación del archivo → **no** entregar grupos al host (sin parcial de importación).
4. **RN-04:** Perfil C: cualquier `cod_cliente` ≠ sesión → error de lote.
5. **RN-05:** Perfil V/S: cliente fuera de cartera → error.
6. **RN-06:** Dentro del grupo, campos de cabecera 101-16 (excl. renglón) idénticos en **valores crudos**; vacío = vacío (AMB-C-02).
7. **RN-07:** `cod_vended` + nombre desde maestro cliente; cliente sin vendedor → error (AMB-M-09).
8. **RN-08:** Clave `(cod_cliente, cod_vended_resuelto, nivel_resuelto)`; nivel vacío → `0` tras resolución para la clave.
9. **RN-09:** Orden de grupos = primera aparición del grupo en el Excel (AMB-05).
10. **RN-10:** Defaults y validaciones artículo/cantidad/`NivelExtremo`/precio cero/inhabilitado/`Modifica*` alineados a 101-16 donde apliquen.
11. **RN-11:** Múltiples clientes/claves de cabecera **permitidos** (a diferencia del individual).
12. **RN-12:** Payload por grupo incluye cabecera resuelta + renglones suficientes para totales (mismas funciones que carga/import individual en el host o backend según TR).
13. **RN-13:** APIs del proceso exigen `pw_importacionmasiva`.
14. **RN-14:** No se graba comprobante en BD en esta HU; solo validación/enriquecimiento/agrupación.

## Criterios de aceptación

- [ ] **CA-01:** Existe catálogo `PEDIDO_MASIVO` con mismos campos internos que individual, `genera_plantilla=true`, parcial=false, host=`pw_importacionmasiva`.
- [ ] **CA-02:** Plantilla descargable sin columna vendedor; títulos i18n `PEDIDO_INDIVIDUAL.*` (SPEC CA-02).
- [ ] **CA-03:** Archivo con ≥2 claves distintas `(cliente, vendedor-resuelto, nivel)` → ≥2 grupos en payload (SPEC CA-03 backend).
- [ ] **CA-04:** Orden de grupos = primera aparición en Excel.
- [ ] **CA-05:** Una fila inválida (p. ej. sin cantidad) → sin grupos al host (sin parcial).
- [ ] **CA-06:** Perfil C con otro `cod_cliente` → error de lote (SPEC CA-05).
- [ ] **CA-07:** V/S con cliente fuera de cartera → error.
- [ ] **CA-08:** Cabecera cruda distinta dentro del mismo grupo → error de lote.
- [ ] **CA-09:** Vendedor de cada grupo = maestro del cliente (SPEC CA-04 origen datos).
- [ ] **CA-10:** Cliente sin `cod_vended` → error de importación.
- [ ] **CA-11:** Con `NivelExtremo`, nivel distinto de 0/100 → error.
- [ ] **CA-12:** Sin `pw_importacionmasiva` → 403 en plantilla/carga del proceso.
- [ ] **CA-13:** `EXCEL_IMPORT_ENABLED=false` → proceso no usable (GEN-07).
- [ ] **CA-14:** Lote queda registrado en historial Excel GEN-07.
- [ ] **CA-15:** Parser acepta encabezados en cualquiera de los 5 idiomas del portal (herencia 101-16).
- [ ] **CA-16:** Columna no editable por `Modifica*` con valor informado → error (herencia 101-16).
- [ ] **CA-17:** Artículo inválido / cantidad ≤ 0 / cliente inhabilitado / precio 0 prohibido → error según mismas reglas 101-16.
- [ ] **CA-18:** Payload de grupo listo para que el host arme fila de grilla (cabecera + renglones + vendedor).
- [ ] **CA-19:** Tests unit handler (agrupación feliz + ≥3 errores) y feature API lote.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Dos `cod_cliente` con cabeceras coherentes | ≥2 grupos (éxito) |
| Mismo cliente, distinto nivel | Grupos distintos |
| Mismo cliente+nivel, `bonif1` crudo distinto entre filas del grupo | Error de lote |
| Proceso inactivo en catálogo | Error/404 según GEN-07 |

## Escenarios Gherkin

```gherkin
Feature: Proceso Excel PEDIDO_MASIVO
  Scenario: Agrupa dos clientes en un archivo
    Given un usuario con permiso pw_importacionmasiva
    And un Excel válido con dos cod_cliente distintos y renglones coherentes
    When procesa el lote PEDIDO_MASIVO
    Then el host recibe dos grupos ordenados por primera aparición
    And cada grupo tiene cod_vended del maestro cliente

  Scenario: Error de fila inhibe todo el lote de importación
    Given un Excel con una fila sin cantidad
    When procesa el lote PEDIDO_MASIVO
    Then no se entregan grupos al host

  Scenario: Perfil cliente con otro código
    Given un usuario perfil C de cliente "001"
    And un Excel con cod_cliente "002"
    When procesa el lote
    Then la importación falla sin parcial

  Scenario: Sin permiso
    Given un usuario autenticado sin pw_importacionmasiva
    When solicita plantilla o carga del proceso PEDIDO_MASIVO
    Then recibe 403
```

## Supuestos explícitos

- El contrato JSON exacto del payload de grupos (agrupado en backend vs filas + post-proceso) se fija en TR respetando AMB-M-12.
- “23 campos” = mismo set `nombre_campo_interno` documentado en SPEC-101-16 §2 (referenciado por SPEC-101-21).
- Totales definitivos en UI pueden recalcularse en el host con las mismas funciones que carga (SPEC §3 paso 7).

## Preguntas abiertas

Ninguna bloqueante. Detalle de API/registry en TR.

## Riesgos de ambigüedad

Bajo. Residual: forma exacta del enriquecimiento staging vs respuesta agrupada (TR).

## Veredicto B1

**Lista para TR: Sí** — cobertura SPEC CA-02…05 (lado proceso) + reglas §2–§3.
