# HU-101-047 — Importación maestros catálogo, artículos y precios

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-047-integracion-maestros-catalogo-articulos |
| **SPEC origen** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Épica** | APIs importación maestros ERP → PedidosWeb |
| **Prioridad** | **Must** |
| **Estado** | Especificado |
| **B1** | Cerrado (2026-10-21) |
| **TR** | [TR-SPEC-101-22-integracion-maestros-catalogo-articulos](../../04-tareas/101-PedidosWeb/TR-SPEC-101-22-integracion-maestros-catalogo-articulos.md) |
| **Dependencias** | [HU-101-046](HU-101-046-integracion-maestros-plataforma.md) |

## Trazabilidad SPEC

| Recursos SPEC §1 | Esta HU |
|------------------|---------|
| `provincias`, `condventa`, `transportes`, `perfiles`, `vendedores` | CA-01…CA-03 |
| `listaprecios`, `escalas` | CA-04, CA-05 |
| `articulos` | CA-06, CA-07 |
| `listaprecios-articulos`, `descuentocantidad` | CA-08, CA-09 |
| Políticas snapshot catálogo chico (A1-C02) | RN-03, CA-03 |
| Snapshot clientes/artículos/vendedores (A1-C03) en `articulos`/`vendedores` | RN-04, CA-06 |
| Solo delta en `listaprecios-articulos`, `descuentocantidad` (A1-C04) | RN-05, CA-08 |
| SP `pq_sp_integracion_maestros_*` | RN-01 |
| OpenAPI por path | CA-10 |
| CA producto §7 (2)(3)(4) | CA-06, CA-08 |

## Narrativa

Como **job ERP de sincronización**,  
quiero **empujar catálogos comerciales, artículos, listas y precios por lote**,  
para **que carga y consultas del portal usen datos actualizados sin ABM web**.

## Contexto funcional

Implementación de handlers y stored procedures para diez recursos de maestros «de catálogo y precios», respetando orden de carga §7 del SPEC (responsabilidad del origen; API rechaza FK faltante por ítem). `vendedores` no crea `users` ni asigna `cod_login`.

## Alcance incluido

Recursos y tablas:

| Recurso | Tabla(s) |
|---------|----------|
| `provincias` | `pq_pedidosweb_provincias` |
| `condventa` | `pq_pedidosweb_condventa` |
| `transportes` | `pq_pedidosweb_transportes` |
| `perfiles` | `pq_pedidosweb_perfil` |
| `vendedores` | `pq_pedidosweb_vendedores` |
| `listaprecios` | `pq_pedidosweb_listaprecios` |
| `escalas` | cabecera + detalle (lote anidado `valores[]`) |
| `articulos` | `pq_pedidosweb_articulos` |
| `listaprecios-articulos` | `pq_pedidosweb_listaprecios_articulos` |
| `descuentocantidad` | `pq_pedidosweb_descuentocantidad` |

- Un SP (o par SP escalas) por recurso; acceso solo vía SP desde PHP.
- **Delta:** merge PATCH (campos omitidos no pisan).
- **Snapshot catálogo chico:** universo del lote; DELETE ausentes sin referencia; referenciado → `omitidos`.
- **Snapshot `articulos` / `vendedores`:** replace por ítem; sin borrar claves ausentes del lote; `eliminarSiAusente` opcional por ítem.
- **`listaprecios-articulos` / `descuentocantidad`:** solo `delta`; `snapshot` → 422 `integracion.maestro.modoNoSoportado`.
- `escalas`: 1 ítem = 1 cabecera; `valores[]` = detalle; snapshot reemplaza detalle de cabeceras enviadas.
- `articulos`: tipos canónicos (`usa_esc`, `equivalencia_ventas`, `stockeable`, `especial` boolean).
- `perfiles`: no alterar default de pedidos vía este recurso (solo catálogo perfil).
- Ignorar `cod_login` en `vendedores`.
- OpenAPI: un path POST por recurso de esta HU.
- Feature tests representativos: p. ej. `condventa` snapshot delete, `articulos` delta parcial, `listaprecios-articulos` snapshot rechazado.

## Fuera de alcance

- Clientes, direcciones, contactos, parámetros → [HU-101-048](HU-101-048-integracion-maestros-clientes-parametros.md).
- Plataforma común → HU-101-046.

## Reglas de negocio

1. **RN-01:** Escritura de negocio exclusivamente por SP desplegados en install/update.
2. **RN-02:** FK lógica faltante (ej. lista inexistente en `listaprecios-articulos`) → ítem `rechazado`, resto del lote continúa en delta.
3. **RN-03:** Snapshot catálogo chico sin soft-delete inventado.
4. **RN-04:** Reenvío del mismo lote no duplica PK (upsert idempotente).
5. **RN-05:** `modo=snapshot` en recursos solo-delta → 422 a nivel lote.

## Criterios de aceptación

- [ ] **CA-01:** POST `provincias` delta upsert persiste y reenvío no duplica.
- [ ] **CA-02:** POST `condventa` snapshot elimina código ausente no referenciado; referenciado incrementa `omitidos`.
- [ ] **CA-03:** POST `vendedores` ignora `cod_login` en JSON.
- [ ] **CA-04:** POST `escalas` con cabecera y `valores[]` persiste cabecera y detalle.
- [ ] **CA-05:** Snapshot `escalas` elimina `cod_valor` hijo ausente del `valores[]` de esa cabecera.
- [ ] **CA-06:** POST `articulos` delta con un ítem inválido aplica los demás (`aplicados` + `rechazados`).
- [ ] **CA-07:** POST `articulos` con `eliminarSiAusente` borra clave sin referencias; con referencias → `rechazado` en ese ítem.
- [ ] **CA-08:** POST `listaprecios-articulos` con `modo=snapshot` → 422.
- [ ] **CA-09:** POST `descuentocantidad` delta upsert por `(cod_articu, cantidad)`.
- [ ] **CA-10:** OpenAPI documenta los diez paths de esta HU.

## Escenarios Gherkin

```gherkin
Feature: Importación maestros catálogo y artículos

  Scenario: Delta artículos con error parcial
    Given maestros de integración habilitados
    And lote delta con 2 artículos válidos y 1 código inválido
    When POST "/api/v1/integracion/maestros/articulos"
    Then HTTP 200 y error 0
    And resultado.aplicados es 2
    And resultado.rechazados es 1

  Scenario: Snapshot condventa sin referencias
    Given catálogo con código "Z" sin uso en pedidos
    And snapshot sin código "Z"
    When POST "/api/v1/integracion/maestros/condventa"
    Then código "Z" ya no existe en tabla
```

## Supuestos explícitos

- El origen respeta orden §7 antes de llamar recursos dependientes.
- Pedidos/presupuestos existentes no se modifican al importar maestros.

## Preguntas abiertas (TR)

- Mapeo camelCase campo a campo en OpenAPI (tabla por recurso en TR).

## Veredicto B1

**Lista para TR** — depende de HU-101-046 para rutas y envelope.
