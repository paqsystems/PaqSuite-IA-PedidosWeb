# HU-101-004 — Selección de cliente en carga

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-004-seleccion-cliente |
| **SPEC origen** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **Última actualización** | 2026-08-31 |
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
6. **CC PQ #12:** Tras elegir cliente, se muestra saldo de deuda con colores (verde ≤0; negro >0 sin vencidos; rojo con vencidos). Si saldo ≠ 0, ícono abre modal con grilla de comprobantes + total, sin export/layouts/pivot. Respeta visibilidad GEN-02 del cliente.
7. **CC PQ #11:** El nodo `contactos` de la API de clientes **no** altera el selector ni el flujo de cabecera; SelectBox / cliente fijo conserva display y orden vigentes.

## Fuera de alcance

- Diseñar UI de contactos en el portal.

## Criterios de aceptación

- [ ] **CA-01:** Perfil cliente no muestra selector de cliente ajeno.
- [ ] **CA-02:** Vendedor ve solo clientes asignados (SelectBox DevExtreme).
- [ ] **CA-03:** Supervisor puede buscar/seleccionar cualquier cliente autorizado.
- [ ] **CA-04:** Sin cliente seleccionado no se habilita carga de renglones.
- [ ] **CA-05:** `data-testid` estable en selector (`cliente-select` o equivalente).
- [x] **CA-CC-01:** SelectBox muestra `(codigo) {razonSocial} - {nombreFantasia}`.
- [x] **CA-CC-02:** Ordenamiento habilitado por código, razón social o nombre fantasía.
- [x] **CA-CC-03:** Textos y tooltips vía i18n; `data-testid` estable preservado.
- [x] **CA-CC12-D01:** Tras elegir cliente, se muestra el saldo de deuda con colores (verde ≤0; negro >0 sin vencidos; rojo con vencidos).
- [x] **CA-CC12-D02:** Si saldo ≠ 0, ícono abre modal con grilla de comprobantes + total, sin export/layouts/pivot.
- [x] **CA-CC12-D03:** Respeta visibilidad GEN-02 del cliente.
- [ ] **CA-CC11-S01:** SelectBox / cliente fijo usa los mismos campos de display/orden que HU-101-004 vigente (`codCliente`, razón social, fantasía). **No** muestra contactos.
- [ ] **CA-CC11-S02:** Un `contactos` extra en `GET /api/v1/clientes` no rompe el parseo FE (campos previos siguen presentes).
- [ ] **CA-CC11-S03:** `GET /clientes/{cod}/cabecera-inicial` no se reemplaza por la ficha unitaria de contactos.

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

## Historial CC PQ #12 (28/08/2026) — Parte I 30/08/2026

Saldo de deuda con colores y modal de comprobantes tras selección de cliente (RN-6, CA-CC12-D01…D03). Unificación delta `HU-101-004-seleccion-cliente-update-01` (archivo eliminado en Parte I).

## Historial CC PQ #11 (18/08/2026) — Parte I 31/08/2026

Selector sin UI de contactos; tolerancia nodo `contactos` en API (RN-7, CA-CC11-S01…S03). Unificación delta `HU-101-004-seleccion-cliente-update` (archivo eliminado en Parte I).

## Veredicto B1

**Lista para TR** (SPEC-101-10).
