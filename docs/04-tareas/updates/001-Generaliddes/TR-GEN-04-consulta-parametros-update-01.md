# TR-GEN-04 — Consulta parámetros (update-01 — IncluyeArticulosNoStockeables)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-GEN-04-consulta-parametros](../../001-Generaliddes/TR-GEN-04-consulta-parametros.md) |
| **HU update** | [HU-GEN-04-consulta-parametros-update-01](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-04-consulta-parametros-update-01.md) |
| **SPEC update** | [SPEC-001-04-configuracion-global-update-01](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-04-configuracion-global-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Seed `IncluyeArticulosNoStockeables` tipo B default false + caption/tooltip i18n.
2. INSERT idempotente en seed-deploy.
3. Visible en consulta parámetros; sin PATCH.

## AC técnicos

- [ ] **AC-CC12-T-P1:** Seed + lectura en consulta.
- [ ] **AC-CC12-T-P2:** No ABM web.
