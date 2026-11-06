# Cierre I — SPEC-101-22 — Importación maestros ERP

## Alcance

Parte **I** del dispatcher: marcar **Finalizado** la épica SPEC-101-22 y unificar en documentos base las decisiones de implementación (22d, GEN-36 host, aliases ERP, snapshot vendedores). Confirmación explícita PQ **2026-10-29**.

**Fecha unificación:** 2026-10-29  
**F1:** [D-VERIFICACION-101-22-importacion-maestros-erp.md](D-VERIFICACION-101-22-importacion-maestros-erp.md)  
**F:** [F-101-22-cierre-formal.md](F-101-22-cierre-formal.md)

## Documentos base actualizados (Parte I)

| Documento | Cambio |
|-----------|--------|
| [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) | Estado **Finalizado**; adopción GEN-36; aliases payload; checklist D/F/I |
| [APIs-Importacion-datos-maestros.md](../../02-producto/PedidosWeb/APIs-Importacion-datos-maestros.md) | Estado producto alineado; nota async 202 + GET |
| [runbook-usuario-integracion-maestros.md](../../06-operacion/runbook-usuario-integracion-maestros.md) | Worker, recursos implementados, deploy SP |
| HU-101-046 / 047 / 048 | Estado **Finalizado** |
| TR 22a / 22b / 22c | Estado **Finalizado** |

No hubo archivos `*-update` separados que eliminar; el delta 22d se incorporó en SPEC y producto.

## Decisiones incorporadas (post-implementación)

| Tema | Decisión |
|------|----------|
| Motor | Recepción **async GEN-36** en host (`202`, staging, job, GET por `importacionId`) |
| Listas de precios | Normalización ERP: `codigo`→`codLista`; precios en `items[]` o `articulos[]`; hijos `codigo`→`codArticulo` |
| Escalas | Aceptar `descripcion` como alias de `descripEs` en API y SP |
| Vendedores snapshot | `pq_sp_integracion_maestros_vendedores_finalize_snapshot` — mismo patrón que condventa/perfiles; FK clientes → error en contadores finalize |
| Vendedores delta | `eliminarSiAusente: true` para baja explícita puntual |

## Fuera de Parte I

- **SPEC-101-23** (transacciones) — sin cambio de estado.
- **Desplegado** (Jira / Parte N) — cuando exista PR mergeado a `develop`.

## Veredicto Parte I

**Estado SPEC-101-22:** **Finalizado (Parte I 2026-10-29)**  
**Jira Epic:** [PIAPEDWEB-34](https://paqsystems.atlassian.net/browse/PIAPEDWEB-34) → columna **Finalizado** (pedido usuario).
