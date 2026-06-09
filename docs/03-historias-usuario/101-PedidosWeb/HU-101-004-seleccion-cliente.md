# HU-101-004 — Selección de cliente en carga

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-004-seleccion-cliente |
| **SPEC origen** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-101-001; HU-GEN-02-visibilidad-datos-pedidosweb |

## Narrativa

Como **vendedor o supervisor**,  
quiero **elegir el cliente sobre el que opero**,  
para **cargar pedidos o presupuestos con datos de cabecera correctos**.

Como **cliente**,  
quiero **que el sistema use mi cliente asociado sin selector**,  
para **no elegir entidades ajenas**.

## Reglas de negocio

1. **Cliente:** sin selector; cliente fijo del login.
2. **Vendedor:** solo clientes asignados.
3. **Supervisor:** todos los clientes visibles.
4. Al elegir cliente se dispara inicialización de cabecera (HU-101-005).
5. **CC PQ 04/06/2026:** SelectBox muestra `(codigo) {razonSocial} - {nombreFantasia}`; ordenamiento habilitado por código, razón social o nombre fantasía (`cliente-orden-select`).

## Criterios de aceptación

- [ ] **CA-01:** Perfil cliente no muestra selector de cliente ajeno.
- [ ] **CA-02:** Vendedor ve solo clientes asignados (SelectBox DevExtreme).
- [ ] **CA-03:** Supervisor puede buscar/seleccionar cualquier cliente autorizado.
- [ ] **CA-04:** Sin cliente seleccionado no se habilita carga de renglones.
- [ ] **CA-05:** `data-testid` estable en selector (`cliente-select` o equivalente).
- [x] **CA-CC-01:** SelectBox muestra `(codigo) {razonSocial} - {nombreFantasia}`.
- [x] **CA-CC-02:** Ordenamiento habilitado por código, razón social o nombre fantasía.
- [x] **CA-CC-03:** Textos y tooltips vía i18n; `data-testid` estable preservado.

## Escenarios Gherkin

```gherkin
Feature: Selección de cliente

  Scenario: Cliente autenticado
    Given un usuario tipo cliente
    When abre carga de pedido
    Then ve su cliente fijado sin combo de selección

  Scenario: Vendedor con cartera acotada
    Given un vendedor con 3 clientes asignados
    When abre el selector de cliente
    Then solo ve esos 3 clientes
```

## Veredicto B1

**Lista para TR** (SPEC-101-10).
