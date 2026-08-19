# HU-101-004 — Selección de cliente (update — no usar contactos)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-004-seleccion-cliente](../../101-PedidosWeb/HU-101-004-seleccion-cliente.md) |
| **SPEC update** | [SPEC-001-02-acceso-y-seguridad-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-02-acceso-y-seguridad-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #11, 18/08/2026 |
| **Última actualización** | 2026-08-18 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario de carga de pedidos/presupuestos**,  
quiero **seguir eligiendo el cliente como hasta ahora**,  
para **que el nodo `contactos` de la API no cambie el selector ni el flujo de cabecera**.

## Criterios de aceptación

- [ ] **CA-CC11-S01:** SelectBox / cliente fijo usa los mismos campos de display/orden que HU-101-004 vigente (`codCliente`, razón social, fantasía). **No** muestra contactos.
- [ ] **CA-CC11-S02:** Un `contactos` extra en `GET /api/v1/clientes` no rompe el parseo FE (campos previos siguen presentes).
- [ ] **CA-CC11-S03:** `GET /clientes/{cod}/cabecera-inicial` no se reemplaza por la ficha unitaria de contactos.

## Fuera de alcance

- Diseñar UI de contactos en el portal.
