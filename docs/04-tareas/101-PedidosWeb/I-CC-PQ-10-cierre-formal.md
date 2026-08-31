# Cierre I — CC PQ #10 (31/08/2026) — `CargaUnidadesVenta`

## Alcance

Parte **I** del dispatcher: fusión de updates **Finalizado** (confirmados por PQ 31/08/2026) en documentos base (SPEC, HU, TR). CC #10 ya tenía D+E+F1; faltaba unificación documental.

**Fecha unificación:** 31/08/2026  
**Partes previas:** [E-CC-PQ-10-tests.md](E-CC-PQ-10-tests.md) · [F-CC-PQ-10-cierre-formal.md](F-CC-PQ-10-cierre-formal.md)

---

## Updates fusionados y eliminados (33 familias CC #10 + #11 en mismo commit doc)

### CC #10 — SPEC

| Origen update (eliminado) | Destino unificado |
|---------------------------|-------------------|
| `SPEC-001-04-configuracion-global-update` | [SPEC-001-04-configuracion-global.md](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) |
| `SPEC-101-02-modelos-update` | [SPEC-101-02-modelos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md) |
| `SPEC-101-04-services-pedidos-update` | [SPEC-101-04-services-pedidos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| `SPEC-101-07-consultas-api-update` | [SPEC-101-07-consultas-api.md](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| `SPEC-101-10-pantalla-carga-update` | [SPEC-101-10-pantalla-carga.md](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| `SPEC-101-11-consultas-ui-update` | [SPEC-101-11-consultas-ui.md](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| `SPEC-101-13-mails-update` | [SPEC-101-13-mails.md](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md) |
| `SPEC-101-16-importacion-pedido-individual-excel-update` | [SPEC-101-16-importacion-pedido-individual-excel.md](../../05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| `SPEC-101-19-asistente-carga-ia-mutaciones-update` | [SPEC-101-19-asistente-carga-ia-mutaciones.md](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| `SPEC-101-21-importacion-masiva-pedidos-update` | [SPEC-101-21-importacion-masiva-pedidos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |

### CC #10 — HU / TR

| Capa | Updates eliminados | Bases **Finalizado** |
|------|-------------------|----------------------|
| HU | GEN-04, 101-006, 019, 028, 029, 030, 040, 043 | Misma ruta en `docs/03-historias-usuario/` |
| TR | GEN-04, 101-02, 04, 07, 10, 11, 13, 101-16 (×2), 101-19 | Misma ruta en `docs/04-tareas/` |

---

## Lista C — originales desbloqueados

Tras Parte I, **ningún ítem C queda En Control Calidad** por CC #10:

| ID | Documento base | Estado |
|----|----------------|--------|
| C1 | SPEC-001-04 | Finalizado (Parte I CC PQ #10/#11) |
| C3 | SPEC-101-02 | Finalizado (Parte I CC PQ #10/#11) |
| C4–C8 | SPEC-101-04/07/10/11/13 | Finalizado (Parte I CC PQ #10/#11) |
| C9 | HU-GEN-04 | Finalizado |
| C12–C13 | HU-101-006, 019 | Finalizado |
| C14–C21 | TR-GEN-04, TR-101-02/04/07/10/11/13 | Finalizado (Parte I CC PQ #10/#11) |

Además: SPEC/HU/TR 101-16, 101-19, 101-21 y HU 028/029/030/040/043.

---

## Conflicto resuelto con CC #12

| Tema | CC #10 | CC #12 | Vigente |
|------|--------|--------|---------|
| Mail detalle | Una columna cantidad según `CargaUnidadesVenta` | **Bultos** + **Unidades** | **CC #12** |
| Modal renglón | Un solo campo cantidad editable | Equivalencia stock solo lectura si param true | **Ambos** integrados |

---

## Manual

`docs/99-manual-usuario/PedidosWeb.md` — §6.16 parámetro *Carga unidades de venta*; §8 detalle pedidos (columna cantidad venta vía producto); mail §6.16 *Incluir detalle en mail*.

---

## Veredicto Parte I

**Estado CC #10 en `00-ControlCalidad-PQ.md`:** **Finalizado (Parte I 31/08/2026)**

**Deploy:** sin pasos adicionales respecto a D (`56271ef`); migración `2026_07_30_100000_add_carga_unidades_venta_columns.php` ya en release.
