# Cierre I — CC PQ #4 (10/06/2026) — Unificación documental pivot informes

## Alcance

Parte **I** del dispatcher: fusión de updates en documentos base (SPEC, HU, TR), actualización de producto y manual de usuario, y cierre formal del **Control de Calidad #4** tras Partes **D + E + F** (11/06/2026).

**Fecha unificación:** 16/06/2026  
**Partes previas:** [E-CC-PQ-4-tests.md](E-CC-PQ-4-tests.md) · [F-CC-PQ-4-pivot-informes.md](F-CC-PQ-4-pivot-informes.md)

---

## Updates fusionados y eliminados

| Origen update | Destino unificado |
|---------------|-------------------|
| `SPEC-101-11-consultas-ui-update` | [SPEC-101-11-consultas-ui.md](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| `HU-101-028-consulta-detalle-pedidos-update` | [HU-101-028-consulta-detalle-pedidos.md](../../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) |
| `HU-101-021-consulta-deuda-update` | [HU-101-021-consulta-deuda.md](../../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md) |
| `HU-101-022-consulta-cheques-update` | [HU-101-022-consulta-cheques.md](../../03-historias-usuario/101-PedidosWeb/HU-101-022-consulta-cheques.md) |
| `HU-101-018-consulta-stock-update` | [HU-101-018-consulta-stock.md](../../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md) |
| `TR-SPEC-101-11-consultas-ui-update` | [TR-SPEC-101-11-consultas-ui.md](TR-SPEC-101-11-consultas-ui.md) |

**Estado metadatos:** HU/TR base → **Finalizado (Parte I CC PQ #4)**; SPEC base → **Especificado**.

---

## Manual y producto

| Documento | Cambio |
|-----------|--------|
| `docs/99-manual-usuario/Generalidades.md` | §19 vista pivot (GEN-08) |
| `docs/99-manual-usuario/PedidosWeb.md` | §8–9 pivot en informes; versión 2026-06-16 |
| `docs/02-producto/PedidosWeb/consulta-detalle-pedidos.md` | §7 UI pivot |
| `docs/02-producto/PedidosWeb/consulta-deuda.md` | §9 UI pivot |
| `docs/02-producto/PedidosWeb/consulta-cheques.md` | §9 UI pivot |
| `docs/02-producto/PedidosWeb/consulta-stock.md` | §9 UI pivot |

---

## Observaciones no bloqueantes (heredadas de F)

| ID | Tema | Destino |
|----|------|---------|
| OBS-01 | Catálogo detalle pedidos — no todas las columnas cabecera en seeder | Fallback FE; ampliar seeder en iteración futura |
| OBS-02 | E2E cheques/stock | Opcional en suite agrupada |
| OBS-03 | PHPUnit pivot con tenant SQL Server | CI tenant `desarrollo` |
| OBS-04 | QA manual PQ en tenant con flags activos | Checklist post-deploy |

---

## Veredicto Parte I

| CC #4 | Estado |
|-------|--------|
| SPEC-101-11 consultas UI + pivot informes | **Finalizado (Parte I)** |
| HU-101-028 detalle pedidos | **Finalizado (Parte I)** |
| HU-101-021 deuda | **Finalizado (Parte I)** |
| HU-101-022 cheques | **Finalizado (Parte I)** |
| HU-101-018 stock | **Finalizado (Parte I)** |
| TR-SPEC-101-11 consultas UI | **Finalizado (Parte I)** |

**Estado CC #4 en `00-ControlCalidad-PQ.md`:** **Finalizado (Parte I 16/06/2026)**

**Activación deploy (no incluida en Parte I):** migraciones pivots + seed catálogo + `PIVOTS_ENABLED=true` en tenant objetivo.
