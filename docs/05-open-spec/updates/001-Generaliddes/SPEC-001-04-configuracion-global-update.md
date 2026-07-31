# SPEC-001-04 — Configuración global (update — CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-001-04-configuracion-global-update |
| **SPEC base** | [SPEC-001-04-configuracion-global](../../001-Generaliddes/SPEC-001-04-configuracion-global.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | Seed `PQ_PARAMETROS_GRAL.PedidosWeb`; `PedidosWebParameterService` |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Incorporar el parámetro general booleano **`CargaUnidadesVenta`** para que la carga de renglones (pantalla, Excel, asistente IA y mail) interprete el dato «cantidad» como unidades de stock/precio o como unidades de venta.

## In scope (delta)

| Campo | Valor |
|-------|--------|
| **Clave** | `CargaUnidadesVenta` |
| **tipo_valor** | `B` (booleano) |
| **Default** | `false` — el usuario ingresa unidades de stock/precio (`cantidad`) |
| **Programa** | `PedidosWeb` |
| **Administración** | ERP / herramientas internas (sin ABM web en MVP) |
| **Seed deploy** | Incluir en `PQ_PARAMETROS_GRAL.PedidosWeb.seed.json` y seeder de actualización de versión (`paqsuite:seed-deploy` / INSERT idempotente) |
| **CAPTION sugerido** | Carga de pedidos por unidades de venta |
| **TOOLTIP sugerido** | Si está activo, la cantidad ingresada en renglón / Excel / asistente se interpreta como unidades de venta (`cantidad_venta`); si no, como unidades de stock/precio (`cantidad`). |

**Semántica runtime (consumo):**

- `false`: el valor editable «cantidad» mapea a `pq_pedidosweb_pedidosdetalle.cantidad`; se deriva `cantidad_venta`.
- `true`: el valor editable «cantidad» mapea a `cantidad_venta`; se deriva `cantidad`.
- Los **importes** se calculan **siempre** a partir de `cantidad` (ver SPEC-101-10-update).

Documentar en producto §10.6 y `consulta-parametros.md`.

## Fuera de scope

- ABM web del parámetro.
- Cambio de otros parámetros §10.6.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-GEN-04 | [HU-GEN-04-consulta-parametros-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-04-consulta-parametros-update.md) |
| TR-GEN-04 | [TR-GEN-04-consulta-parametros-update](../../../04-tareas/updates/001-Generaliddes/TR-GEN-04-consulta-parametros-update.md) |

## Definición de listo (update)

- [ ] Clave en seed JSON + INSERT idempotente documentado.
- [ ] Visible en consulta parámetros (Sí/No) sin edición web.
- [ ] Lectura runtime vía `PedidosWebParameterService` (o equivalente).

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Parámetro `CargaUnidadesVenta` |
| 30/07/2026 | Parte G | Volcado SPEC-update |
