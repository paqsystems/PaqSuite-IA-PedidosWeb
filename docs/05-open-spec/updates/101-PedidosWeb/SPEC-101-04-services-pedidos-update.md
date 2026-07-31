# SPEC-101-04 — Services pedidos (update — persistencia cantidad_venta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-04-services-pedidos-update |
| **SPEC base** | [SPEC-101-04-services-pedidos](../../101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | [SPEC-101-02-update](SPEC-101-02-modelos-update.md); [SPEC-101-10-update](SPEC-101-10-pantalla-carga-update.md); [SPEC-001-04-update](../001-Generaliddes/SPEC-001-04-configuracion-global-update.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Asegurar que grabación, edición, copia e hidratación de borradores persistan y recalculen **`cantidad`** + **`cantidad_venta`** según la regla canónica de SPEC-101-10-update, y que los importes usen siempre `cantidad`.

## In scope (delta)

1. Validadores / mappers de renglón: aceptar entrada de usuario según `CargaUnidadesVenta` y materializar ambos campos.
2. Lectura de `equivalencia_ventas` del artículo (fallback 1).
3. Grabar pedido/presupuesto: escribir `cantidad` y `cantidad_venta` en `pq_pedidosweb_pedidosdetalle`.
4. Copia de comprobante (HU-101-026): preservar ambos campos del origen (sin reinterpretar salvo que el producto pida recálculo — **fuera** salvo pedido explícito).
5. Shared helper de conversión reutilizable por Excel y asistente IA.

## Fuera de scope

- Cambiar `ActualizarPrecioCopia` u otras reglas de copia de precios.
- Recalcular `cantidad`/`cantidad_venta` históricos al cambiar el parámetro (solo afecta ingreso nuevo / edición).

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| TR services | [TR-SPEC-101-04-services-pedidos-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update.md) |
| HU renglones | [HU-101-006-carga-renglones-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-006-carga-renglones-update.md) |

## Definición de listo (update)

- [ ] Helper unit-tested de conversión false/true + equiv 0→1.
- [ ] Grabación persiste ambos campos.
- [ ] PHPUnit de grabación / validación mínimo.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Persistencia dual cantidad |
| 30/07/2026 | Parte G | Volcado SPEC-update |
