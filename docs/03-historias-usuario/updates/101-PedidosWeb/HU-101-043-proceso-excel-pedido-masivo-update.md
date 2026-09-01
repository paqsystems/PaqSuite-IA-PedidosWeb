# HU-101-043-update — Excel masivo: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-043-proceso-excel-pedido-masivo-update |
| **HU base** | [HU-101-043-proceso-excel-pedido-masivo](../../101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo.md) |
| **SPEC origen** | [SPEC-101-21-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos-update.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **TR** | [TR-SPEC-101-21-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo-update.md) |
| **Última actualización** | 2026-09-01 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario de importación masiva**,  
quiero **el mismo recorte a 60 caracteres en leyendas que en el Excel individual**,  
para **aplicar el lote aunque alguna celda venga larga**.

## Reglas de negocio

1. **RN-CC13-M01:** Recortar `leyenda1`…`leyenda5` a 60 al armar cada grupo.
2. **RN-CC13-M02:** Leyenda > 60 **no** impide aplicar el lote a la grilla.

## Criterios de aceptación

- [ ] **CA-CC13-M01:** Excel masivo con una leyenda > 60 → la grilla de trabajo se carga.
- [ ] **CA-CC13-M02:** La cabecera del grupo tiene esa leyenda en 60 caracteres.
