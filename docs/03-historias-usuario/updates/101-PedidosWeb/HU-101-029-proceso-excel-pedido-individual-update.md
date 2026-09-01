# HU-101-029-update — Excel individual: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-029-proceso-excel-pedido-individual-update |
| **HU base** | [HU-101-029-proceso-excel-pedido-individual](../../101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual.md) |
| **SPEC origen** | [SPEC-101-16-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel-update.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **TR** | [TR-SPEC-101-16-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-16-proceso-excel-pedido-individual-update.md) |
| **Última actualización** | 2026-09-01 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario que importa un pedido individual desde Excel**,  
quiero **que una leyenda de más de 60 caracteres se recorte y el archivo siga siendo válido**,  
para **no perder el lote por un texto largo**.

## Reglas de negocio

1. **RN-CC13-X01:** `leyenda1`…`leyenda5` se recortan a 60 en el resolver PedidosWeb.
2. **RN-CC13-X02:** Superar el largo **no** genera error de fila ni bloquea el lote.
3. **RN-CC13-X03:** El catálogo **no** usa `largo_maximo = 60` (GEN-07 rechazaría).

## Criterios de aceptación

- [ ] **CA-CC13-X01:** Archivo con leyenda 1 de 61 caracteres → lote válido.
- [ ] **CA-CC13-X02:** El valor hidratado en carga tiene 60 caracteres (prefijo).
