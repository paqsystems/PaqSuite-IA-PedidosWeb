# TR-SPEC-101-04 — Services (update-01 — sync leyendas)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-04-services-pedidos](../../101-PedidosWeb/TR-SPEC-101-04-services-pedidos.md) |
| **HU updates** | HU-101-009/010/011-update |
| **SPEC update** | [SPEC-101-04-services-pedidos-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Extender contrato de grabar con flags `leyendaNDirty` (o snapshot comparado en FE/BE).
2. En `PedidoService` / grabar: si dirty && `ClienteLeyendaN`, `UPDATE` cliente.
3. Tests PHPUnit escenario a–d del CC.

## AC técnicos

- [ ] **AC-CC12-T-L1:** Sync solo dirty + parámetro.
- [ ] **AC-CC12-T-L2:** Edición sin dirty no toca maestro.
