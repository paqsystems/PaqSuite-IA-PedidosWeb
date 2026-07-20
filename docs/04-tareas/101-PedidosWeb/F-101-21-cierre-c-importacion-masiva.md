# Cierre C — SPEC-101-21 — Importación masiva

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-19 |
| **Épica** | Importación masiva de pedidos / presupuestos |
| **B1** | [F-101-21-cierre-b1](F-101-21-cierre-b1-importacion-masiva.md) — Cerrado |
| **Veredicto C** | **Generada** — 3 TR; **C1 cerrada** (Apto) |

## TR generadas

| ID | Archivo | HU | Orden D1 |
|----|---------|----|----------|
| 21a | [TR-SPEC-101-21-proceso-excel-pedido-masivo](TR-SPEC-101-21-proceso-excel-pedido-masivo.md) | 043 | 1 |
| 21b | [TR-SPEC-101-21-pantalla-importacion-masiva](TR-SPEC-101-21-pantalla-importacion-masiva.md) | 044 | 2 |
| 21c | [TR-SPEC-101-21-consultar-borrador-importacion-masiva](TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) | 045 | 3 |

## Temas a cerrar en C1 (no bloquean generación C)

| Tema | TR | Nota |
|------|----|------|
| Contrato JSON de `grupos` vs extensión GEN-07 | 21a | Opción A recomendada |
| Auth store pedido/presupuesto con solo `pw_importacionmasiva` | 21b | AMB-C-03 |
| Persistencia borrador al desmontar (context vs sessionStorage) | 21c | Según shell de pestañas |

## Siguiente paso

**C1** — **cerrada** ([F-101-21-cierre-c1](F-101-21-cierre-c1-importacion-masiva.md) — **Apto**).  
Siguiente: **D1** (plan de implementación) en orden **21a → 21b → 21c**.
