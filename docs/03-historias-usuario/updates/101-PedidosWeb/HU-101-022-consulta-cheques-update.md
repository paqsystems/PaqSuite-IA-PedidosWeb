# HU-101-022 — Consulta de cheques (update — pivot)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-022-consulta-cheques-update |
| **HU base** | [HU-101-022-consulta-cheques](../../101-PedidosWeb/HU-101-022-consulta-cheques.md) |
| **SPEC update** | [SPEC-101-11-consultas-ui-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-11-consultas-ui-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #4 — **10/06/2026** |
| **TR update** | [TR-SPEC-101-11-consultas-ui-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md) (Bloque pivot — cheques) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario comercial**, quiero **analizar cheques en cartera con pivot**, para **ver importes por cliente, banco y estado**.

## Alcance incluido (delta)

- Migrar `ChequesPage` a `ConsultaGrillaPivotShell`.
- `consultaId`: `CONSULTA_CHEQUES`; `proceso`/`gridId`: `pw_cheques`.
- Columnas pivotables según [consulta-cheques.md](../../../02-producto/PedidosWeb/consulta-cheques.md).
- Filtro negocio `fecha >= hoy` se mantiene en API (dataset pivot = mismo listado).
- `pivotBase`: filas cliente + banco + estado; valor `importe` sum.

## Criterios de aceptación (delta)

- [ ] **CA-PVT-01:** Toggle grilla/pivot en `/consultas/cheques`.
- [ ] **CA-PVT-02:** Filtro cartera/futuro intacto (solo cheques vigentes según producto).
- [ ] **CA-PVT-03:** Campos string (banco, estado) totalizables con count/min/max.
- [ ] **CA-PVT-04:** Export grilla y pivot según GEN-03 / GEN-08.
