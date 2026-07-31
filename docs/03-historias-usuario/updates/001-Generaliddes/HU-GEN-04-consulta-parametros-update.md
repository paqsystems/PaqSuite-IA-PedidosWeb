# HU-GEN-04 — Consulta de parámetros (update — CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-GEN-04-consulta-parametros](../../001-Generaliddes/HU-GEN-04-consulta-parametros.md) |
| **SPEC update** | [SPEC-001-04-configuracion-global-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-04-configuracion-global-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario autenticado con permiso de consulta**, quiero **ver el parámetro `CargaUnidadesVenta`** en la consulta de parámetros, para **saber si la carga opera en unidades de venta o de stock/precio**.

## Criterios de aceptación

- [ ] **CA-CC10-P01:** Parámetro `CargaUnidadesVenta` (`tipo_valor = B`) visible con caption/tooltip; valor Sí/No; sin edición web.
- [ ] **CA-CC10-P02:** Incluido en seed PedidosWeb y seeder de actualización de versión.

## Reglas (delta)

- **RN-P02 (CC PQ #10):** `CargaUnidadesVenta` default `false`; administración solo ERP.
