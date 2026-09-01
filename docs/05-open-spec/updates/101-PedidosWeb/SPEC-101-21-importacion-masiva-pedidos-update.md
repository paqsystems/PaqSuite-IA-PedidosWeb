# SPEC-101-21-update — Excel masivo: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-21-importacion-masiva-pedidos-update |
| **SPEC base** | [SPEC-101-21-importacion-masiva-pedidos](../../101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **HU relacionadas** | [HU-101-043-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo-update.md) |
| **TR relacionadas** | [TR-SPEC-101-21-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo-update.md) |
| **Última actualización** | 2026-09-01 |

## Objetivo

`PEDIDO_MASIVO`: mismas leyendas que individual. Texto > 60 → **recortar**; el lote **sí** se aplica (CC PQ #13).

## In scope

- Recorte en el armado de cabecera masiva (mismo helper SPEC-101-04 / 101-16).
- Catálogo: **no** `largo_maximo = 60` (evitar rechazo GEN-07).

## Fuera de scope

- Grabación del lote (SPEC-101-04-update recorta de nuevo al persistir).
- Pantalla grilla masiva / consultar borrador.

## Definición de listo

- [ ] Excel masivo con leyenda > 60 → grupos válidos; leyenda persistible de 60 caracteres
