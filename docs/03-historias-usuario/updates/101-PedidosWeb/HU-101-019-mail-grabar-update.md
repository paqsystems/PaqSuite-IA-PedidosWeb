# HU-101-019 — Mail al grabar (update — cantidad según parámetro)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-019-mail-grabar](../../101-PedidosWeb/HU-101-019-mail-grabar.md) |
| **SPEC update** | [SPEC-101-13-mails-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-13-mails-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **destinatario del mail de comprobante**, quiero **ver la misma cantidad que cargó el usuario** según `CargaUnidadesVenta`, para **no confundir unidades de venta con unidades de stock**.

## Criterios de aceptación

- [ ] **CA-CC10-M01:** Con `DetallePorMail` activo y parámetro `false`, columna cantidad = `cantidad`.
- [ ] **CA-CC10-M02:** Con parámetro `true`, columna cantidad = `cantidad_venta`.
- [ ] **CA-CC10-M03:** No se muestran ambas cantidades en el mail.
