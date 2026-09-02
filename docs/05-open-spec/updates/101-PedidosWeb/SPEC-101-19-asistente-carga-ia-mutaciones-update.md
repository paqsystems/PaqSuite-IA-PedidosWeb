# SPEC-101-19-update — Asistente IA: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-19-asistente-carga-ia-mutaciones-update |
| **SPEC base** | [SPEC-101-19-asistente-carga-ia-mutaciones](../../101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **HU relacionadas** | [HU-101-039-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera-update.md) · [HU-101-040-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar-update.md) |
| **TR relacionadas** | [TR-SPEC-101-19-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update.md) |
| **Última actualización** | 2026-09-01 |

## Objetivo

Cuando el asistente de carga asigna una leyenda 1–5 (texto, audio o extracto de imagen), persistir en el borrador **como máximo 60** caracteres. Si el valor es más largo, **recortar**; **no** `validationError` por longitud (CC PQ #13).

## In scope

- Capacidad **C** (`setCampoLibre` / patch de `leyenda1`…`leyenda5`): aplicar `recortarLeyendaCabecera` y mutar el campo.
- Capacidad **K** (extracto imagen): candidato de leyenda > 60 **sí se aplica**, recortado (sigue siendo válido).
- Pedido compuesto multilínea (D1-25): mismo recorte por leyenda.
- Sin mensaje de error por largo (no hace falta clave i18n de rechazo).

## Fuera de scope

- Observaciones (sin tope 60 en este CC).
- Grabar vía IA (J): SPEC-101-04-update recorta de nuevo al persistir.

## Definición de listo

- [ ] «leyenda 1» con 61 caracteres → se asignan los primeros 60; sin error
- [ ] Extracto imagen con leyenda > 60 → el campo se hidrata recortado
