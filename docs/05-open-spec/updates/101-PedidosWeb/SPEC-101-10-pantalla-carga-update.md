# SPEC-101-10 — Pantalla carga (update — CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-10-pantalla-carga-update |
| **SPEC base** | [SPEC-101-10-pantalla-carga](../../101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | [SPEC-001-04-update](../001-Generaliddes/SPEC-001-04-configuracion-global-update.md); [SPEC-101-02-update](SPEC-101-02-modelos-update.md); [SPEC-101-04-update](SPEC-101-04-services-pedidos-update.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Definir la semántica del campo único «cantidad» en el modal/edición de renglón de pedido/presupuesto según `CargaUnidadesVenta`, manteniendo un solo control visible y calculando siempre los importes sobre `cantidad`.

## Regla de conversión (canónica)

Sea `equiv = equivalencia_ventas` del artículo; si `equiv` es null o `≤ 0` → `equiv = 1`.

| `CargaUnidadesVenta` | Valor que edita el usuario (label «cantidad») | Persistencia | Importes |
|----------------------|-----------------------------------------------|--------------|----------|
| `false` | → `cantidad` | `cantidad_venta = cantidad / equiv` | Desde `cantidad` (sin cambio) |
| `true` | → `cantidad_venta` | `cantidad = cantidad_venta * equiv` | Desde `cantidad` (derivada) |

### UI

- **Un solo** campo «cantidad» en alta/edición de renglón (DevExtreme).
- **Prohibido** mostrar ambos (`cantidad` y `cantidad_venta`) para edición simultánea.
- Al abrir edición: mostrar el valor correspondiente al modo del parámetro (stock o venta).
- Grilla de renglones: mostrar la misma cantidad «de usuario» coherente con el parámetro (no duplicar columnas salvo decisión de producto posterior).

### Mobile

- Misma semántica en rama native de carga (`isNativeApp()` / HU-101-036) si el modal/renglón aplica.

## Fuera de scope

- Segundo campo visible de equivalencia o de la otra cantidad.
- Cambiar fórmulas de precio/IVA/bonificación más allá de la base `cantidad`.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-006 | [HU-101-006-carga-renglones-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-006-carga-renglones-update.md) |
| TR UI | [TR-SPEC-101-10-pantalla-carga-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga-update.md) |

## Definición de listo (update)

- [ ] Modal renglón respeta parámetro + conversión.
- [ ] Persistencia de ambos campos al grabar (vía SPEC-101-04).
- [ ] i18n / `data-testid` estables; tests FE unitarios de conversión.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Cantidad dual según `CargaUnidadesVenta` |
| 30/07/2026 | Parte G | Volcado SPEC-update |
