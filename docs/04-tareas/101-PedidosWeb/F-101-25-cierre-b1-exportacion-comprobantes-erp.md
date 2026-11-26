# Cierre B1 — SPEC-101-25 — API exportación comprobantes (PedidosWeb → ERP)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-11-26 |
| **Épica** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) |
| **SPEC** | [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **A1** | [F-101-25-cierre-a1](F-101-25-cierre-a1-exportacion-comprobantes-erp.md) — Apto con observaciones |
| **Veredicto B1** | **Cerrado** — 2 HU enriquecidas; listas para Parte C (TR) |

## HU enriquecidas

| HU | Título | Jira | Lista para TR |
|----|--------|------|---------------|
| [HU-101-057](../../03-historias-usuario/101-PedidosWeb/HU-101-057-integracion-export-comprobantes-plataforma.md) | Plataforma GET integración export | [PIAPEDWEB-56](https://paqsystems.atlassian.net/browse/PIAPEDWEB-56) | **Sí** |
| [HU-101-058](../../03-historias-usuario/101-PedidosWeb/HU-101-058-integracion-export-comprobantes-datos.md) | SP, mapeo cabecera/renglones/cierre | [PIAPEDWEB-57](https://paqsystems.atlassian.net/browse/PIAPEDWEB-57) | **Sí con observaciones** |

## Cobertura SPEC CA → HU

| SPEC CA | HU-057 | HU-058 |
|---------|--------|--------|
| CA-01 | HTTP, envelope, auth | Forma `items[].cabecera/renglones/cierre` |
| CA-02 | Test E2E filtro 0:0 | Datos fixture estado 0 |
| CA-03 | Test OR multi-rango | Dedup en SP/page |
| CA-04 | Query paginación | Consistencia `total` entre páginas |
| CA-05 | Query extremos | Fixture -1/99 |
| CA-06 | Validación 422 | — |
| CA-07 | Validación `1:0` | — |
| CA-08 | 403 portal | Sin flags UI en payload |
| CA-09 | OpenAPI | Esquemas datos en OpenAPI (TR conjunta) |
| CA-10 | Test HTTP dedup | Lógica SP dedup |
| CA-11 | Alias `page_size` | — |

## Revisión B1

- HU-057: 6 escenarios Gherkin (auth, 422, 403, vacío, CSV, paginación); CA alineados a CA-01…CA-12 del SPEC; riesgos y preguntas acotadas a TR plataforma.
- HU-058: 6 escenarios Gherkin (renglones, cierre 98, vacío, OR, filtros AND, campos MUST); referencias a superset cabecera/detalle **solo** como contrato derivado del SPEC (no producto como SoT B1).
- No se agregó alcance fuera de SPEC-101-25 (sin POST descarga, sin GEN-36, sin cambios `/consultas/*`).
- Ambigüedad residual: middleware exacto, shape log JSON, 1 vs 2 SP — delegado a TR.

## Veredicto

**Apto con observaciones para cerrar B1. Autoriza Parte C (TR).**

## Siguiente paso

Generar TR en este orden:

1. [TR-SPEC-101-25-integracion-export-plataforma](TR-SPEC-101-25-integracion-export-plataforma.md) — HU-101-057.
2. [TR-SPEC-101-25-integracion-export-datos](TR-SPEC-101-25-integracion-export-datos.md) — HU-101-058 (depende de contrato HTTP de 25a).