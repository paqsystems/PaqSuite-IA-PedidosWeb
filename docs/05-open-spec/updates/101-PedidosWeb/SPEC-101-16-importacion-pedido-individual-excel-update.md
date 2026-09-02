# SPEC-101-16-update — Excel individual: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-16-importacion-pedido-individual-excel-update |
| **SPEC base** | [SPEC-101-16-importacion-pedido-individual-excel](../../101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **HU relacionadas** | [HU-101-029-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual-update.md) |
| **TR relacionadas** | [TR-SPEC-101-16-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-16-proceso-excel-pedido-individual-update.md) |
| **Última actualización** | 2026-09-01 |

## Objetivo

En `PEDIDO_INDIVIDUAL`, si `leyenda1` … `leyenda5` superan 60 caracteres, **recortar** a 60 y seguir el lote. **No** error de fila por longitud (CC PQ #13).

## In scope

- Recorte en el resolver/handler PedidosWeb (mismo helper que SPEC-101-04), **después** del parseo GEN-07.
- **No** bajar `largo_maximo` del catálogo a 60: GEN-07 rechazaría la celda (`castTexto`) y rompería esta regla. Dejar `largo_maximo` en **255** (o null) para que el parser acepte el texto; el recorte es de negocio PedidosWeb.
- Hidratación en pantalla de carga (HU-101-030): recibe ya el valor recortado.

## Fuera de scope

- Cambiar títulos i18n de columnas.
- Importación masiva (SPEC-101-21-update; mismo helper).

## Definición de listo

- [ ] Celda de 61 caracteres → fila válida; valor hidratado con 60 caracteres
- [ ] El lote **no** falla por largo de leyenda
