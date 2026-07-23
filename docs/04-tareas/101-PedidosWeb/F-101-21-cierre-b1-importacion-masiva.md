# Cierre B / B1 — SPEC-101-21 — Importación masiva

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-19 |
| **Épica** | Importación masiva de pedidos / presupuestos |
| **A1** | [F-101-21-cierre-a1](F-101-21-cierre-a1-importacion-masiva.md) — Apto con observaciones |
| **Veredicto B1** | **Cerrado** — 3 HU enriquecidas; listas para Parte C (TR) |

## HU enriquecidas

| HU | Título | B1 | Lista para TR |
|----|--------|----|---------------|
| [HU-101-043](../../03-historias-usuario/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo.md) | Proceso Excel `PEDIDO_MASIVO` | Sí | **Sí** |
| [HU-101-044](../../03-historias-usuario/101-PedidosWeb/HU-101-044-pantalla-importacion-masiva.md) | Pantalla grilla + grabación FE | Sí | **Sí** |
| [HU-101-045](../../03-historias-usuario/101-PedidosWeb/HU-101-045-consultar-borrador-importacion-masiva.md) | Consultar readonly | Sí | **Sí** |

## Cobertura SPEC CA → HU

| SPEC CA | HU |
|---------|-----|
| CA-01 | 044 |
| CA-02 | 043 (+ plantilla en 044 toolbar) |
| CA-03 | 043 + 044 |
| CA-04 | 043 + 044 |
| CA-05 | 043 |
| CA-06 | 044 |
| CA-07 | 045 (+ acción en 044) |
| CA-08 | 044 |
| CA-09 | 044 |
| CA-10 | 044 |
| CA-11 | 044 |
| CA-12 | 044 |
| CA-13 | 044 |
| CA-14 | 044 |

## Orden sugerido Parte C (TR)

1. **TR-SPEC-101-21a** — HU-101-043 (catálogo, handler, agrupación)  
2. **TR-SPEC-101-21b** — HU-101-044 (menú, grilla, import UI, grabación FE, modales)  
3. **TR-SPEC-101-21c** — HU-101-045 (Consultar readonly)

## Observaciones B1

- Contrato exacto del payload de grupos (043) y `data-testid` / ruta (044/045) quedan para TR — no bloquean C.
- Evidencia leída: SPEC-101-21 completo + HU-043/044/045 Parte B.

## Siguiente paso

Parte **C** — generar TR en orden 21a → 21b → 21c; luego C1.
