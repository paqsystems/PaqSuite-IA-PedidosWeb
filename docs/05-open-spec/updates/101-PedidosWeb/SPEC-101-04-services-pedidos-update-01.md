# SPEC-101-04 — Services pedidos (update-01 — sync leyendas cliente)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-04-services-pedidos-update-01 |
| **SPEC base** | [SPEC-101-04-services-pedidos](../../101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-28 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12 — **28/08/2026** |
| **Dependencias** | [SPEC-101-10-pantalla-carga-update-01](SPEC-101-10-pantalla-carga-update-01.md); parámetros `ClienteLeyenda1`…`5` |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Al **grabar** un pedido/presupuesto, si el usuario **modificó en esa sesión** una leyenda `N` cuya inicialización viene del cliente (`ClienteLeyendaN = true`), actualizar `pq_pedidosweb_clientes.leyenda_N` con el valor grabado. Si la leyenda **no** se modificó en la sesión, **no** tocar el maestro (aunque el valor del comprobante difiera del maestro actual).

## In scope (delta)

1. Solo leyendas con `ClienteLeyendaN = true` en `PQ_parametros_gral`.
2. Detectar dirty por sesión de edición (alta o edición): comparar valor actual vs valor al abrir/inicializar cabecera (no vs maestro al grabar).
3. Ejemplo canónico CC #12:
   - Pedido 100 grabado sin tocar leyenda1.
   - Pedido 200 modifica leyenda1 → actualiza maestro.
   - Editar pedido 100 sin tocar leyenda1 → **no** reescribe el maestro con el texto viejo del 100.
4. Aplica a grabar pedido y grabar presupuesto (mismos campos de cabecera).

## Fuera de scope

- Sincronizar leyendas si `ClienteLeyendaN = false`.
- ABM de parámetros.
- Cambiar inicialización al seleccionar cliente (sigue igual).

## HU / TR derivadas

| Artefacto | Ruta |
|-----------|------|
| HU-101-009 | [HU-101-009-grabar-pedido-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-009-grabar-pedido-update.md) |
| HU-101-010 | [HU-101-010-grabar-presupuesto-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-010-grabar-presupuesto-update.md) |
| HU-101-011 | [HU-101-011-editar-pedido-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-011-editar-pedido-update.md) |
| TR-101-04 | [TR-SPEC-101-04-services-pedidos-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update-01.md) |

## Definición de listo (update)

- [ ] Sync solo si dirty de sesión + `ClienteLeyendaN`.
- [ ] Tests del escenario a–d del CC.
- [ ] Sin regresión de grabación sin dirty.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Sync leyendas cliente al grabar |
| 28/08/2026 | Parte G | Volcado SPEC-update-01 |
