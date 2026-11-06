# Cierre B / B1 — SPEC-101-22 — APIs importación maestros ERP

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-21 |
| **Épica** | APIs importación maestros (ERP → PedidosWeb) |
| **A1** | [F-101-22-cierre-a1](F-101-22-cierre-a1-importacion-maestros-erp.md) — Apto con observaciones |
| **Veredicto B1** | **Cerrado** — 3 HU enriquecidas; listas para Parte C (TR) |

## HU generadas y enriquecidas

| HU | Título | B1 | Lista para TR |
|----|--------|----|---------------|
| [HU-101-046](../../03-historias-usuario/101-PedidosWeb/HU-101-046-integracion-maestros-plataforma.md) | Plataforma API (auth, envelope, logs, OpenAPI común) | Sí | **Sí** |
| [HU-101-047](../../03-historias-usuario/101-PedidosWeb/HU-101-047-integracion-maestros-catalogo-articulos.md) | Catálogo, artículos, listas y precios (10 recursos) | Sí | **Sí** |
| [HU-101-048](../../03-historias-usuario/101-PedidosWeb/HU-101-048-integracion-maestros-clientes-parametros.md) | Clientes, DE, contactos, parámetros (4 recursos) | Sí | **Sí** |

## Cobertura SPEC CA producto → HU

| SPEC CA | HU |
|---------|-----|
| CA-1 (token integración vs vendedor) | 046, 048 |
| CA-2 (idempotencia PK) | 047, 048 |
| CA-3 (delta parcial) | 046, 047, 048 |
| CA-4 (OpenAPI todos recursos) | 046 + 047 + 048 |
| CA-5 (pedidos intactos) | 047, 048 |
| CA-6 (sin transacciones) | Todas (fuera de alcance) |
| CA-7 (mobile/excel sin tocar) | Todas (fuera de alcance) |

## Orden sugerido Parte C (TR)

1. **TR-SPEC-101-22-integracion-maestros-plataforma** — HU-101-046  
2. **TR-SPEC-101-22-integracion-maestros-catalogo-articulos** — HU-101-047  
3. **TR-SPEC-101-22-integracion-maestros-clientes-parametros** — HU-101-048  

## Observaciones B1

- Scripts SP y nombres `pq_sp_integracion_maestros_*` se detallan en TR (no bloquean C).
- Consumidor ERP (Tango) queda fuera del repo PedidosWeb; contrato HTTP es el entregable.

## Siguiente paso

Parte **C** — generar TR en orden 22a → 22b → 22c; luego **C1**.
