# TR-SPEC-101-13 — Mails (update — cantidad mail)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-13-mails](../../101-PedidosWeb/TR-SPEC-101-13-mails.md) |
| **HU update** | [HU-101-019-mail-grabar-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-019-mail-grabar-update.md) |
| **SPEC update** | [SPEC-101-13-mails-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-13-mails-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. En builder de filas de detalle del mail: elegir `cantidad` o `cantidad_venta` según parámetro.
2. Test unitario del selector.

## AC técnicos

- [ ] **AC-CC10-T-MAIL1:** false → cantidad; true → cantidad_venta.
