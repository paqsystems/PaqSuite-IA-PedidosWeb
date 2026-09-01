# HU-101-009-update — Grabar pedido: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-009-grabar-pedido-update |
| **HU base** | [HU-101-009-grabar-pedido](../../101-PedidosWeb/HU-101-009-grabar-pedido.md) |
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
quiero **que al grabar un pedido las leyendas se recorten a 60 caracteres si vienen más largas**,  
para **guardar el comprobante sin error y alineado al modelo**.

## Reglas de negocio

1. **RN-CC13-G01:** `leyenda_1` … `leyenda_5`: nulo/vacío permitido; si hay texto, persistir los **primeros 60** caracteres Unicode. **No** fallar la grabación por longitud.
2. **RN-CC13-G02:** Si la leyenda dirty actualiza el maestro (CC PQ #12), el valor escrito también va recortado.

## Criterios de aceptación

- [ ] **CA-CC13-G01:** POST grabar pedido con `leyenda_1` de 61 caracteres → éxito (si el resto es válido); en BD quedan 60.
- [ ] **CA-CC13-G02:** POST con 60 caracteres → se persiste el texto completo.
