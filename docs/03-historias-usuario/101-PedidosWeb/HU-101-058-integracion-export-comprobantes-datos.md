# HU-101-058 — Contrato datos exportación comprobantes (PedidosWeb → ERP)

Jira: [PIAPEDWEB-57](https://paqsystems.atlassian.net/browse/PIAPEDWEB-57)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-058-integracion-export-comprobantes-datos |
| **SPEC origen** | [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Épica** | APIs exportación comprobantes PedidosWeb → ERP |
| **Prioridad** | **Must** |
| **Estado** | **Finalizado** (2026-11-26) |
| **B1** | Cerrado (2026-11-26) — [F-101-25-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-25-cierre-b1-exportacion-comprobantes-erp.md) |
| **TR** | [TR-SPEC-101-25-integracion-export-datos](../../04-tareas/101-PedidosWeb/TR-SPEC-101-25-integracion-export-datos.md) |
| **Depende de** | [HU-101-057](HU-101-057-integracion-export-comprobantes-plataforma.md) |
| **Dependencias** | Regla 74 SP · SPEC-101-25 § Cabecera / Renglón / Cierre · Tablas §2.1–2.2 modelo (vía SPEC) |

## Trazabilidad SPEC

| ID SPEC | Criterio / entregable | Cobertura en esta HU |
|---------|----------------------|----------------------|
| RN-01…05 SPEC | Dedup, total cabeceras, renglones vacíos, estado -1, solo SP | RN-01…RN-05, CA-03…CA-05, CA-11 |
| § Cabecera | Superset consulta + MUST integración | RN-06…RN-08, CA-01, CA-08, CA-12 |
| § Renglón | Lista §4 detalle sin cabecera | RN-09, CA-09 |
| § Cierre | `cierre` objeto/null | RN-10, CA-10 |
| CA-02…CA-05 SPEC | Filtros estado y extremos | CA-02, CA-05, CA-13 |
| CA-04 SPEC | Paginación por cabecera | CA-04, CA-06 |
| CA-10 SPEC | Sin duplicados OR | CA-03 |
| AMB-C-101-25-03 | Sin `tipoComprobante` | RN-11 |
| AMB-C-101-25-04 | AND filtros opcionales | CA-06, CA-07 |
| AMB-M-101-25-09 | Referencia `mapComprobanteItem` | RN-06, RN-07 |
| Plan TR 25b | SP `pq_sp_integracion_export_comprobantes_list` | CA-11, CA-14 |
| Prohibido § | Sin `puede*` ni `guidSufijo` | CA-08 |
| Gateway § | SP conocido en modo agente | Supuesto |

## Narrativa

Como **job ERP que materializa pedidos y presupuestos en Tango**,  
quiero **que cada página del GET traiga cabeceras en camelCase con joins comerciales y todos los renglones asociados**,  
para **sincronizar sin SQL ad-hoc al host y sin las restricciones de visibilidad del operador web**.

## Contexto funcional

Segunda capa de **SPEC-101-25**: implementa lectura por **stored procedure(s)** sobre `pq_pedidosweb_pedidoscabecera` y `pq_pedidosweb_pedidosdetalle`, con predicado de estado (OR de intervalos), filtros AND, paginación por cabecera (`OFFSET`/`FETCH` o equivalente), armado de DTO anidado y despliegue SQL en pipeline install/update. El shape de joins y de `cierre` debe ser **equivalente** al de `ConsultaListadoService::mapComprobanteItem` y `mapPresupuestoCierre`, pero la ruta integración **no** debe invocar ese servicio (evitar visibilidad UI).

## Alcance incluido

### Stored procedure(s)

- SP listado: `pq_sp_integracion_export_comprobantes_list` (nombre final en TR).
- Entrada: parámetros normalizados desde HU-057 (rangos estado, paginación, filtros opcionales).
- Salida: conjunto de cabeceras del page + `total` global de cabeceras que cumplen filtro.
- Estrategia renglones: en la misma transacción de lectura, sin N+1 PHP (join, subconsulta o SP auxiliar documentado en TR).

### Predicados y orden

- Estado: `estado IN (⋃ [desde, hasta])` inclusivo; deduplicar por `cod_pedido` en el resultado paginado.
- `cod_cliente`, `cod_pedido`, rango `fecha` cabecera: **AND** con predicado de estado (ART según A1).
- Orden estable: `fecha` **DESC**, `cod_pedido` **ASC**.

### Mapeo JSON (camelCase)

**Cabecera** — MUST incluir:

- Superset de campos definidos en SPEC-101-25 § Cabecera (tabla origen/joins descritos en SPEC vía consulta cabecera).
- **Obligatorios integración:** `codPedido`, `talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb`, `estado`, `fecha`, `total` (y demás campos MUST del § Cabecera del SPEC).
- **SHOULD** si existen en BD: `codPedidoOrigen`, `codPresupuestoOrigen`, `origenComprobante`.
- **Prohibido:** `puedeEditar`, `puedeEliminar`, `puedeCopiar`, `puedeConvertir`, `puedeCerrar`, `guidSufijo`.

**Renglón** — MUST (mínimo verificable en tests):  
`renglon`, `codArticulo`, `descripcionArticulo`, `especial`, `cantidad`, `cantidadVenta`, `porcBonif`, `precioLista`, `precioNeto`, `importeBruto`, `importeNeto`, `ivaNeto`, `importeNetoConIva` — sin repetir campos de cabecera.

**Cierre** — en cada ítem:

- Clave `cierre` **siempre** presente.
- Objeto solo si `estado === 98` y hay fila de cierre; si no, `null` (incluye estado 99 y 98 sin registro).

### Otros

- Estado **-1** se exporta tal cual en BD (sin reglas de bloqueo ERP en lectura).
- Cabecera sin filas de detalle: `renglones: []`; la cabecera cuenta en `total`.
- Scripts SQL idempotentes en `backend/scripts/sql/` (ruta final en TR) + registro en pipeline update.
- Tests PHPUnit/feature con fixtures: estados -1, 0, 1, 98, 99; paginación; OR multi-rango; AND `codCliente`; cabecera sin detalle; presupuesto cerrado con/sin cierre.

## Fuera de alcance

- Ruta HTTP, validación query, rate limit, OpenAPI scaffold → HU-101-057.
- Escritura, confirmación descarga, cambio de estado ERP.
- `VisibleClientsResolver`.
- Refactor de `ConsultaListadoService` o consultas portal (salvo extracción pura de mapeo sin cambio de comportamiento UI, solo si TR lo documenta).

## Actores

| Actor | Uso |
|-------|-----|
| **ExportComprobantesService** (host) | Invoca SP y mapea a DTO. |
| **Job ERP** | Consume JSON; no accede al SP directamente. |

## Reglas de negocio

1. **RN-01:** Lectura de negocio **solo** vía SP; prohibido Query Builder/Eloquent en controller/servicio para tablas `pq_pedidosweb_*`.
2. **RN-02:** `total` / `totalPages` refieren a **cabeceras**, nunca a renglones.
3. **RN-03:** Cada ítem del page incluye **todos** los renglones de esa cabecera, orden `renglon` ascendente.
4. **RN-04:** Listado ordenado `fecha` desc, `codPedido` asc.
5. **RN-05:** Un comprobante en varios rangos de estado aparece **una vez** por página (dedup en consulta).
6. **RN-06:** Joins de descripción (cliente, vendedor, condición venta, etc.) equivalentes a `mapComprobanteItem`.
7. **RN-07:** Mapeo implementado en capa dedicada (mapper/adapter), no llamada a `ConsultaListadoService` desde la ruta integración.
8. **RN-08:** Sin flags `puede*` ni `guidSufijo` en `cabecera`.
9. **RN-09:** Renglones sin duplicar atributos de cabecera.
10. **RN-10:** Política `cierre` según SPEC (clave siempre; objeto condicionado a 98 + registro).
11. **RN-11:** Pedidos y presupuestos comparten tabla; **sin** discriminador `tipoComprobante` en API.

## Criterios de aceptación

- [ ] **CA-01:** Fixture multi-estado devuelve cabeceras con joins (`razonSocial`, `vendedorDescripcion`, etc.) y MUST `talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb`.
- [ ] **CA-02:** Tras HU-057 validando query, solo filas con `estado` en el rango solicitado aparecen en `items[].cabecera.estado`.
- [ ] **CA-03:** `estadoRangos=0:0,1:1` sobre la misma cabecera que cae en ambos intervalos → un solo ítem por `codPedido`.
- [ ] **CA-04:** `total` coincide al pedir `page=1` y `page=2` con mismo filtro; ningún `codPedido` repetido entre páginas.
- [ ] **CA-05:** `estadoRangos=99:99` y `-1:-1` devuelven filas cuando existen en fixture.
- [ ] **CA-06:** `codPedido` exacto devuelve ≤1 ítem; `codCliente` acota conjunto (AND).
- [ ] **CA-07:** Dos cabeceras con misma `fecha` se ordenan por `codPedido` asc en el JSON.
- [ ] **CA-08:** Ningún ítem incluye `puedeEditar`, `puedeCopiar` ni `guidSufijo`.
- [ ] **CA-09:** Renglones cumplen lista mínima del SPEC § Renglón y no contienen `codCliente` ni `fecha` de cabecera.
- [ ] **CA-10:** `estado=98` con fila cierre → objeto `cierre`; `estado=99` o 98 sin fila → `cierre: null`.
- [ ] **CA-11:** SP ejecutable en SQL Server local vía test o script deploy.
- [ ] **CA-12:** Cabecera MUST usa `codPedido` también para presupuestos (no solo `codPresupuesto` en integración).
- [ ] **CA-13:** Comprobante en `estado=-1` exportado sin transformación de bloqueo ERP.
- [ ] **CA-14:** Script SP versionado e incluido en runbook install/update documentado del producto.

## Escenarios Gherkin

```gherkin
Feature: Datos exportación comprobantes integración

  Scenario: Ítem anidado completo
    Given cabecera "PW-001" en estado 0 con dos renglones
    When el servicio exporta con filtros válidos de HU-057
    Then el ítem tiene "cabecera.codPedido" "PW-001"
    And "renglones" tiene longitud 2
    And "cierre" es null

  Scenario: Presupuesto cerrado con cierre
    Given cabecera "PR-98" en estado 98 con registro de cierre
    When exporta con "estadoRangos=98:98"
    Then "cierre" no es null

  Scenario: Cabecera sin detalle
    Given cabecera "PW-EMPTY" estado 0 sin renglones
    When exporta con "estadoRangos=0:0"
    Then "renglones" es vacío
    And "total" cuenta esa cabecera

  Scenario: Unión OR de rangos
    Given cabecera "A" estado 0 y cabecera "B" estado 1
    When exporta con "estadoRangos=0:0,1:1"
    Then hay exactamente 2 ítems
    And los "codPedido" son distintos

  Scenario: Filtro por cliente
    Given cabeceras de clientes "C1" y "C2" en estado 0
    When exporta con "estadoRangos=0:0" y "codCliente=C1"
    Then todos los ítems tienen "cabecera.codCliente" "C1"

  Scenario: Campos de integración obligatorios
    Given cabecera estado 0 con talonario y usuario web en BD
    When exporta la cabecera
    Then "cabecera" incluye "talPedidoTango" y "nroPedidoTango" y "codUsuarioWeb"
```

## Supuestos explícitos

- Modelo de tablas según SPEC-101-25 y modelo de datos referenciado en el SPEC.
- Modo gateway: el producto solo publica SP; el agente no recibe SQL libre.
- `totalPages` en respuesta HTTP lo calcula capa plataforma a partir de `total` devuelto por SP.

## Preguntas abiertas (resolver en TR)

| # | Tema | Notas |
|---|------|--------|
| Q1 | 1 SP vs 2 | Listado + detalle en un procedimiento o `*_detalle` por lote de PKs del page. |
| Q2 | OpenAPI datos | ¿Schema completo de cabecera en TR datos o referencia a componente consulta? |
| Q3 | Cierre | Tabla/fuente exacta de cierre presupuesto (nombre físico en DDL). |
| Q4 | Reutilización mapper | Extraer función pura desde `ConsultaListadoService` vs duplicar mapper en adapter export. |

## Riesgos de ambigüedad

- Drift entre joins de consulta UI y export si no se comparte lógica de mapeo (riesgo de campos descripcion faltantes).
- SP pesado si se traen todos los renglones del tenant antes de paginar — la paginación **debe** aplicarse en cabecera en SQL.
- Lista exhaustiva de columnas cabecera puede diferir de consulta si el SPEC superset no se checklistea contra DDL en TR.

## Plantilla SDD

```text
exportación comprobantes ERP: adoptar GEN-19/GEN-20. Motor datos = SP + mapper (HU-101-058).
SoT: SPEC-101-25. Host: pq_sp_integracion_export_comprobantes_*.
```