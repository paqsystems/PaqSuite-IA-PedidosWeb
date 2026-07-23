# Cierre I — CC PQ #9 (02/07/2026) — Unificación documental `ActualizarPrecioCopia`

## Alcance

Parte **I** del dispatcher: fusión de updates en documentos base (SPEC, HU, TR), actualización de manual de usuario y cierre formal del **Control de Calidad #9** tras Partes **D + E + F** (02/07/2026).

**Fecha unificación:** 02/07/2026  
**Partes previas:** [E-CC-PQ-9-tests.md](E-CC-PQ-9-tests.md) · [F-CC-PQ-9-cierre-formal.md](F-CC-PQ-9-cierre-formal.md)

---

## Updates fusionados y eliminados

| Origen update | Destino unificado |
|---------------|-------------------|
| `SPEC-001-04-configuracion-global-update` | [SPEC-001-04-configuracion-global.md](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) |
| `SPEC-101-04-services-pedidos-update` | [SPEC-101-04-services-pedidos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| `HU-GEN-04-consulta-parametros-update` | [HU-GEN-04-consulta-parametros.md](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) |
| `HU-101-026-copiar-comprobante-update` | [HU-101-026-copiar-comprobante.md](../../03-historias-usuario/101-PedidosWeb/HU-101-026-copiar-comprobante.md) |
| `TR-GEN-04-consulta-parametros-update` | [TR-GEN-04-consulta-parametros.md](../001-Generaliddes/TR-GEN-04-consulta-parametros.md) |
| `TR-SPEC-101-04-services-pedidos-update` | [TR-SPEC-101-04-services-pedidos.md](TR-SPEC-101-04-services-pedidos.md) |

**Estado metadatos:** HU/TR base → **Finalizado (Parte I CC PQ #9)**; SPEC base → **Especificado** (referencia vigente).

---

## Manual y producto

| Documento | Cambio |
|-----------|--------|
| `docs/99-manual-usuario/PedidosWeb.md` | §6.9 copia paramétrica (`ActualizarPrecioCopia`, validación precios, modal error); versión 2026-07-02 |
| `docs/02-producto/PedidosWeb/consulta-parametros.md` | Entrada `ActualizarPrecioCopia` (ya en D) |
| `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` | §10.6 `ActualizarPrecioCopia` (ya en D) |
| `docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json` | 58 claves incl. `ActualizarPrecioCopia` |

---

## Observaciones no bloqueantes (heredadas de F)

| ID | Tema | Destino |
|----|------|---------|
| OBS-01 | E2E Playwright copia `false` vs `true` | Opcional; smoke manual PQ OK |
| OBS-02 | INSERT parámetro en tenants productivos | Runbook deploy: seed JSON + script INSERT idempotente |
| OBS-03 | Fila legacy `Articulossinprecio` duplicada en ERP | Operación cliente; runtime prioriza clave canónica |
| OBS-04 | E2E modal copia rechazada | Cubierto por QA manual PQ |

---

## Veredicto Parte I

| CC #9 | Estado |
|-------|--------|
| SPEC-001-04 parámetro `ActualizarPrecioCopia` | **Finalizado (Parte I)** |
| SPEC-101-04 copia paramétrica | **Finalizado (Parte I)** |
| HU-GEN-04 consulta parámetro | **Finalizado (Parte I)** |
| HU-101-026 copiar comprobante | **Finalizado (Parte I)** |
| TR-GEN-04 consulta parámetros | **Finalizado (Parte I)** |
| TR-SPEC-101-04 services copia | **Finalizado (Parte I)** |

**Estado CC #9 en `00-ControlCalidad-PQ.md`:** **Finalizado (Parte I 02/07/2026)**

**Activación deploy (no incluida en Parte I):** INSERT fila `ActualizarPrecioCopia` en `PQ_parametros_gral` por tenant (`docs/backend/seed/PQ_PARAMETROS_GRAL/`); opcional UPDATE caption/tooltip vía SQL idempotente.
