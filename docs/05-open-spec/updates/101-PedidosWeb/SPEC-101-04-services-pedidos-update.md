# SPEC-101-04-update — Recortar leyendas 1–5 a 60 al grabar / hidratar

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-04-services-pedidos-update |
| **SPEC base** | [SPEC-101-04-services-pedidos](../../101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **HU relacionadas** | [HU-101-009-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-009-grabar-pedido-update.md) · [HU-101-010-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-010-grabar-presupuesto-update.md) |
| **TR relacionadas** | [TR-SPEC-101-04-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update.md) |
| **Última actualización** | 2026-09-01 |

## Objetivo

Al grabar pedido o presupuesto, persistir `leyenda_1` … `leyenda_5` con **como máximo 60** caracteres Unicode (CC PQ #13). Si el cliente envía más, **recortar**; **no** devolver error 4xx por longitud.

## In scope

- Helper canónico (p. ej. `recortarLeyendaCabecera`) usado al armar cabecera a persistir: `mb_substr($texto, 0, 60)`; nulo/vacío se conserva.
- Aplica a **Grabar pedido** y **Grabar presupuesto** (alta, edición, conversión que persiste cabecera). **Prohibido** `max:60` que falle la request.
- Sync a maestro cliente (CC PQ #12): el valor escrito en `clientes.leyenda_N` ya va recortado.
- `CabeceraInicialService`: recortar legado del maestro si superara 60.
- OpenAPI: documentar longitud persistida 60 (`maxLength: 60` = tamaño almacenado). El servidor acepta strings más largos y recorta.

## Fuera de scope

- Recorte en Excel / asistente (mismos 60, mismos helper; ver SPEC-101-16 / SPEC-101-19).
- Cambiar la regla dirty de sesión (CC PQ #12).

## Definición de listo

- [ ] Grabar con leyenda de 61 caracteres → 2xx (si el resto es válido); en BD quedan 60
- [ ] Grabar con 60 caracteres → sin cambio de texto
- [ ] OpenAPI documenta `maxLength: 60` como longitud persistida
