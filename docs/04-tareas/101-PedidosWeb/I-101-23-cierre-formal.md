# Cierre I — SPEC-101-23 Importación transacciones ERP

| Campo | Valor |
|-------|-------|
| Fecha | 2026-10-29 |
| Veredicto | **Finalizado** (documental; deploy productivo vía PR / Parte N) |
| Jira épica | [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35) |

## Artefactos

- F1: [D-VERIFICACION-101-23-importacion-transacciones-erp.md](D-VERIFICACION-101-23-importacion-transacciones-erp.md)
- E: [E-101-23-importacion-transacciones-erp.md](E-101-23-importacion-transacciones-erp.md)
- F: [F-101-23-cierre-formal.md](F-101-23-cierre-formal.md)
- D: [F-101-23-cierre-d-importacion-transacciones-erp.md](F-101-23-cierre-d-importacion-transacciones-erp.md)
- TR: 23a, 23b, 23c · HU 049–051

## Documentos base actualizados (Parte I)

| Documento | Cambio |
|-----------|--------|
| [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) | Estado **Finalizado**; checklist D/E/F/I |
| [APIs-Importacion-transacciones.md](../../02-producto/PedidosWeb/APIs-Importacion-transacciones.md) | Estado producto; async 202 + GET |
| [runbook-usuario-integracion-transacciones.md](../../06-operacion/runbook-usuario-integracion-transacciones.md) | Worker por tenant |
| HU-101-049 / 050 / 051 | Estado **Finalizado** |
| TR 23a / 23b / 23c | Estado **Finalizado** |

## Decisiones incorporadas

| Tema | Decisión |
|------|----------|
| Motor | Mismo **GEN-36** que maestros (`ProcessImportationMaestroJob`, GET por `importacionId`) |
| Historial | Solo **delta**; persistencia en `pq_pedidosweb_ventadetallada`; alias ERP vía normalizer |
| Stock | Rechazo campos `comprometidoWeb` / `*Base` / `disponibleNeto*` en handler |
| Snapshot saldos | Staging + finalize SP (stock/deuda/cheques); no TRUNCATE desde PHP |

## Condición de cierre

SQL transacciones aplicado y worker activo en todos los tenants (usuario). QA ERP con lotes nominales: seguimiento **OBS-F-23-01**.

## Fuera de Parte I

- **Desplegado** Jira — Parte N al merge PR `develop`.

## Veredicto Parte I

**Estado SPEC-101-23:** **Finalizado (Parte I 2026-10-29)**  
**Jira Epic:** [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35) → **Finalizado**
