# HU-101-010-update — Grabar presupuesto: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-010-grabar-presupuesto-update |
| **HU base** | [HU-101-010-grabar-presupuesto](../../101-PedidosWeb/HU-101-010-grabar-presupuesto.md) |
| **SPEC origen** | [SPEC-101-04-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **TR** | [TR-SPEC-101-04-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update.md) |
| **Última actualización** | 2026-09-01 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario autorizado**,  
quiero **el mismo recorte a 60 caracteres en leyendas al grabar presupuesto**,  
para **no divergir del pedido**.

## Reglas de negocio

1. **RN-CC13-GP01:** Mismo recorte a 60 que HU-101-009-update; **no** error 4xx por longitud.

## Criterios de aceptación

- [ ] **CA-CC13-GP01:** Grabar presupuesto con leyenda de 61 caracteres → persiste recortada a 60.
