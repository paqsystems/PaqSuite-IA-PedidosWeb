# HU-101-028 — Consulta detalle de pedidos

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-028-consulta-detalle-pedidos |
| **SPEC origen** | [SPEC-101-07-consultas-api](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md), [SPEC-101-11-consultas-ui](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Producto** | [consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md), §17.3.1 |
| **Prioridad** | Must |
| **Estado** | En Control Calidad |
| **B1** | Enriquecida (2026-06-03) |
| **C** | Generada (2026-06-03) |
| **C1** | Cerrada — apto para D1 (2026-06-03) |
| **D1** | Implementado (2026-06-03) |
| **TR relacionada** | [TR-SPEC-101-07-consultas-api](../../04-tareas/101-PedidosWeb/TR-SPEC-101-07-consultas-api.md) (Bloque 3), [TR-SPEC-101-11-consultas-ui](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md) (Bloque 3) |
| **Dependencias** | HU-GEN-03-grillas-listados; HU-GEN-03-exportaciones; HU-GEN-02-visibilidad-datos-pedidosweb; columnas cabecera ([consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md)) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Consultas con paginación y `fecha_proceso` | RN-04, CA-03 |
| Visibilidad por perfil (GEN-02) | RN-01, CA-05 |
| Grilla `DataGridDx` + export Excel GEN-03 | CA-02, CA-04 |
| Permiso `Permiso_Repo` en consultas | RN-02 |

## Narrativa

Como **usuario comercial**,  
quiero **consultar pedidos y presupuestos con sus renglones en una sola grilla**,  
para **analizar líneas, importes y estados sin entrar a la pantalla de carga**.

## Contexto funcional

Complementa las consultas de **cabecera** (§17.1–17.3): aquí cada fila es un **renglón** con datos de cabecera repetidos. Incluye **todos los estados** del comprobante; no reemplaza acciones de edición en pedidos ingresados ni presupuestos activos.

## Alcance incluido

- Ítem de menú **Pedidos → Detalle de pedidos** (`procedimiento`: `pw_detallepedidos`, ruta `/pedidos/detalle`).
- API `GET /api/v1/consultas/detalle-pedidos` paginada con join cabecera + detalle + maestros.
- Grilla solo lectura: columnas cabecera (reutilizar contrato existente) + columnas detalle documentadas en producto.
- Columna **estado** como **texto** (i18n), no número.
- Carátula `metadata.fecha_proceso`.
- Export Excel GEN-03; layouts persistentes si `gridLayoutsEnabled`.
- Filtros opcionales: `cod_cliente`, `cod_pedido`, `estado`, `q` (código/descripción artículo).

## Fuera de alcance

- Acciones ver / editar / eliminar / copiar (pertenecen a HU-101-015, 016, 017).
- Modal de detalle (patrón historial ventas); la grilla **es** el detalle.
- PDF; mutaciones de comprobantes.

## Reglas de negocio

Fuente de verdad columnas, joins y contrato: **[consulta-detalle-pedidos.md](../../02-producto/PedidosWeb/consulta-detalle-pedidos.md)**.

1. **Estados:** incluir `-1`, `0`, `1`, `2`, `98`, `99` sin filtro por defecto.
2. **Visibilidad:** solo comprobantes cuyo `cod_cliente` pertenece a `visibleClientsForUser`; `cod_cliente` en query opcional → **404** si ajeno.
3. **Permiso:** `Permiso_Repo` sobre `pw_detallepedidos`.
4. **Una fila por renglón;** orden `fecha` cabecera DESC, `cod_pedido`, `renglon`.
5. **Descuento renglón:** columna `porc_bonif` (API `porcBonif` / alias `descuento` según TR).
6. **Descripción artículo:** preferir `descripcion_articulo` del detalle; fallback `articulos.descripcion`.
7. **Estado en UI:** `consultas.comprobanteEstado.*` (mismas claves que consulta cabecera).

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Menú / ruta | Grupo `grp_pedidos`; ruta `/pedidos/detalle`; `procedimiento` `pw_detallepedidos` |
| Filtro estados por defecto | **Ninguno** — todos los estados en un solo listado |
| Estado en grilla | **Texto** i18n; columna numérica `estado` oculta por defecto |
| Acciones fila | **Ninguna** |
| `fecha_proceso` | `now()` en metadata (no hay tabla ERP de proceso dedicada) |
| Paginación | Estándar consultas: `page`, `page_size` máx. 100 |
| Componente columnas | Extender `ComprobanteConsultaColumns` + `DetallePedidosConsultaColumns` |

## Criterios de aceptación

- [ ] **CA-01:** Menú Pedidos muestra «Detalle de pedidos» solo con `Permiso_Repo`.
- [ ] **CA-02:** Grilla `DataGridDx` con filtros, agrupación y columnas cabecera + detalle según producto.
- [ ] **CA-03:** API devuelve paginación envelope + `metadata.fecha_proceso`.
- [ ] **CA-04:** Export Excel habilitado con datos; deshabilitado si grilla vacía (GEN-03).
- [ ] **CA-05:** Usuario ve solo renglones de clientes visibles; `cod_cliente` ajeno → 404.
- [ ] **CA-06:** Columna estado muestra descripción (ej. «Ingresado»), no `0`.
- [ ] **CA-07:** No existen acciones de edición/eliminación en la grilla.
- [ ] **CA-08:** Layouts por `proceso`=`pw_detallepedidos`, `grid_id`=`pw_detallepedidos` si flag habilitado.
- [ ] **CA-09:** ≥ 1 E2E feliz (carga grilla con filas) + 1 caso visibilidad o vacío.
- [x] **CA-CC-01:** Columna **nombre comercial** del cliente.
- [x] **CA-CC-02:** Carátula fecha último proceso `dd/MM/yyyy HH:mm` (i18n).
- [x] **CA-CC-03:** Ícono **Actualizar** recarga grilla.
- [x] **CA-CC-04:** Columna **Precio neto unitario** en detalle (SPEC-101-10).

## Escenarios Gherkin

```gherkin
Feature: Consulta detalle de pedidos

  Scenario: Listado plano cabecera y renglones
    Given un supervisor con Permiso_Repo y pedidos visibles con detalle
    When abre Detalle de pedidos
    Then la grilla muestra una fila por renglón
    And columnas de cabecera y detalle según producto

  Scenario: Estado como texto
    Given un comprobante en estado 0 visible
    When se lista en detalle de pedidos
    Then la columna estado muestra la descripción i18n "Ingresado"
    And no muestra solo el número 0

  Scenario: Sin edición
    When el usuario abre Detalle de pedidos
    Then no existen acciones editar ni eliminar en la grilla

  Scenario: Visibilidad cliente ajeno
    Given un vendedor autenticado
    When GET detalle-pedidos con cod_cliente fuera de su universo
    Then HTTP 404
```

## Supuestos explícitos

- Tabla `pq_pedidosweb_pedidosdetalle` con columnas del modelo actual (`porc_bonif`, `importe_*`, etc.).
- Servicio dedicado `DetallePedidosConsultaService` o extensión de `ConsultaListadoService` (detalle TR).
- Seed menú y permisos MVP alineados a `paqsuite_mvp.php` + `paqsuite_visibility.php`.

## Preguntas abiertas

(Ninguna — cerradas en B1.)

## Veredicto B1

**Lista para TR** — ampliación TR-SPEC-101-07 y TR-SPEC-101-11.

## Veredicto C

**TR ampliadas** — Bloque 3 en [TR-SPEC-101-07-consultas-api](../../04-tareas/101-PedidosWeb/TR-SPEC-101-07-consultas-api.md) y [TR-SPEC-101-11-consultas-ui](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md).

## Veredicto C1

**Apto con observaciones para D1** — C1 Bloque 3 en ambas TR (101-07 API primero; 101-11 UI después).

## Veredicto D1

**Implementado** — Bloque 3 en TR-101-07 + TR-101-11.
