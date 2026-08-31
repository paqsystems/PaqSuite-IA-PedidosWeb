# Cierre I — CC PQ #12 (30/08/2026) — Unificación documental

## Alcance

Parte **I** del dispatcher: fusión de updates **Finalizado** (autorizado por QA manual PQ) en documentos base (SPEC, HU, TR), actualización de manual de usuario y cierre formal del **Control de Calidad #12** tras Partes **D + E + F**.

**Fecha unificación:** 30/08/2026  
**Partes previas:** [E-CC-PQ-12-tests.md](E-CC-PQ-12-tests.md) · [F-CC-PQ-12-cierre-formal.md](F-CC-PQ-12-cierre-formal.md)

---

## Updates fusionados y eliminados

### SPEC (punto 11, primero)

| Origen update | Destino unificado |
|---------------|-------------------|
| `SPEC-001-04-configuracion-global-update-01` | [SPEC-001-04-configuracion-global.md](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) |
| `SPEC-101-02-modelos-update-02` | [SPEC-101-02-modelos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md) |
| `SPEC-101-04-services-pedidos-update-01` | [SPEC-101-04-services-pedidos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| `SPEC-101-07-consultas-api-update-01` | [SPEC-101-07-consultas-api.md](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| `SPEC-101-10-pantalla-carga-update-01` | [SPEC-101-10-pantalla-carga.md](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| `SPEC-101-11-consultas-ui-update-01` | [SPEC-101-11-consultas-ui.md](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| `SPEC-101-13-mails-update-01` | [SPEC-101-13-mails.md](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md) |

**Estado SPEC base:** permanecen **En revisión** — quedan hermanos abiertos (p. ej. `SPEC-001-04-…-update.md`, `SPEC-101-10-…-update.md` de CC #10).

### HU

| Origen update | Destino | Estado HU base |
|---------------|---------|----------------|
| `HU-GEN-04-…-update-01` | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) | **En Control Calidad** (hermano `update.md`) |
| `HU-101-004-…-update-01` | [HU-101-004-seleccion-cliente](../../03-historias-usuario/101-PedidosWeb/HU-101-004-seleccion-cliente.md) | **En Control Calidad** |
| `HU-101-005-…-update` | [HU-101-005-inicializacion-cabecera](../../03-historias-usuario/101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-006-…-update-01` | [HU-101-006-carga-renglones](../../03-historias-usuario/101-PedidosWeb/HU-101-006-carga-renglones.md) | **En Control Calidad** |
| `HU-101-008-…-update` | [HU-101-008-precio-importes](../../03-historias-usuario/101-PedidosWeb/HU-101-008-precio-importes.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-009-…-update` | [HU-101-009-grabar-pedido](../../03-historias-usuario/101-PedidosWeb/HU-101-009-grabar-pedido.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-010-…-update` | [HU-101-010-grabar-presupuesto](../../03-historias-usuario/101-PedidosWeb/HU-101-010-grabar-presupuesto.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-011-…-update` | [HU-101-011-editar-pedido](../../03-historias-usuario/101-PedidosWeb/HU-101-011-editar-pedido.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-018-…-update` | [HU-101-018-consulta-stock](../../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-019-…-update-01` | [HU-101-019-mail-grabar](../../03-historias-usuario/101-PedidosWeb/HU-101-019-mail-grabar.md) | **En Control Calidad** |
| `HU-101-021-…-update` | [HU-101-021-consulta-deuda](../../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-023-…-update` | [HU-101-023-historial-ventas](../../03-historias-usuario/101-PedidosWeb/HU-101-023-historial-ventas.md) | **Finalizado (Parte I CC PQ #12)** |

### TR

| Origen update | Destino unificado |
|---------------|-------------------|
| `TR-GEN-04-consulta-parametros-update-01` | [TR-GEN-04-consulta-parametros.md](../001-Generaliddes/TR-GEN-04-consulta-parametros.md) |
| `TR-SPEC-101-02-modelos-update-02` | [TR-SPEC-101-02-modelos.md](TR-SPEC-101-02-modelos.md) |
| `TR-SPEC-101-04-services-pedidos-update-01` | [TR-SPEC-101-04-services-pedidos.md](TR-SPEC-101-04-services-pedidos.md) |
| `TR-SPEC-101-07-consultas-api-update-01` | [TR-SPEC-101-07-consultas-api.md](TR-SPEC-101-07-consultas-api.md) |
| `TR-SPEC-101-10-pantalla-carga-update-01` | [TR-SPEC-101-10-pantalla-carga.md](TR-SPEC-101-10-pantalla-carga.md) |
| `TR-SPEC-101-11-consultas-ui-update-01` | [TR-SPEC-101-11-consultas-ui.md](TR-SPEC-101-11-consultas-ui.md) |
| `TR-SPEC-101-13-mails-update-01` | [TR-SPEC-101-13-mails.md](TR-SPEC-101-13-mails.md) |

**Estado TR base:** **En Control Calidad** — hermanos `*-update.md` (CC #10 u otros) siguen abiertos.

---

## Manual y producto

| Documento | Cambio |
|-----------|--------|
| `docs/99-manual-usuario/PedidosWeb.md` | §6.2 saldo deuda; §6.7 no stockeables; §6.13 sync leyendas; §6.15 equivalencia y precio neto; §6.16 parámetros; mail Bultos/Unidades; §9.1 colores; §9.3 fechas; §9.4 stock. Versión 2026-08-30 |
| `docs/02-producto/PedidosWeb/consulta-parametros.md` | Entrada `IncluyeArticulosNoStockeables` (informativo) |

---

## Observaciones no bloqueantes (heredadas de F)

| ID | Tema | Destino |
|----|------|---------|
| OBS-F-01 | Script smoke HTTP no re-ejecutado en F1 | Opcional ops |
| OBS-F-03 | Historial fechas DateBox en web | Aceptado |
| OBS-F-04 | `migrate` / `alter-pq-pedidosweb-stockeable.sql` en tenants | Runbook deploy |

---

## Veredicto Parte I

| Familia | Estado documental |
|---------|-------------------|
| SPEC 001-04 / 101-02 / 04 / 07 / 10 / 11 / 13 | Delta CC #12 en base; **En revisión** por updates hermanos |
| HU 005, 008, 009, 010, 011, 018, 021, 023 | **Finalizado (Parte I CC PQ #12)** |
| HU GEN-04, 004, 006, 019 | **En Control Calidad** (updates CC #10) |
| TR GEN-04 y 101-02/04/07/10/11/13 | **En Control Calidad** (updates hermanos) |

**Estado CC #12 en `00-ControlCalidad-PQ.md`:** **Finalizado (Parte I 30/08/2026)**

**Activación deploy (no incluida en Parte I):** `php artisan migrate --force` o SQL `backend/scripts/sql/alter-pq-pedidosweb-stockeable.sql`; seed/INSERT `IncluyeArticulosNoStockeables`.
