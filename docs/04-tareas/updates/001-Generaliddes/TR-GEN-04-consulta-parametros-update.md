# TR-GEN-04 — Consulta parámetros (update — CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-GEN-04-consulta-parametros](../../001-Generaliddes/TR-GEN-04-consulta-parametros.md) |
| **HU update** | [HU-GEN-04-consulta-parametros-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-04-consulta-parametros-update.md) |
| **SPEC update** | [SPEC-001-04-configuracion-global-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-04-configuracion-global-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Agregar clave `CargaUnidadesVenta` (`B`, default `false`) en `PQ_PARAMETROS_GRAL.PedidosWeb.seed.json` + CAPTION/TOOLTIP.
2. INSERT idempotente / seed-deploy para tenants existentes.
3. Exponer en `PedidosWebParameterService` (lectura booleana).
4. Producto: §10.6 + `consulta-parametros.md`.

## AC técnicos

- [ ] **AC-CC10-T-P1:** Seed contiene la clave.
- [ ] **AC-CC10-T-P2:** API consulta parámetros la lista.
- [ ] **AC-CC10-T-P3:** Runtime lee el booleano.
