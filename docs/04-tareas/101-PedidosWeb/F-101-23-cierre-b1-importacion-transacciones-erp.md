# Cierre B1 — SPEC-101-23 — APIs importación transacciones ERP

| Campo | Valor |
|-------|-------|
| **Fecha** | 2026-10-22 |
| **Épica** | [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35) |
| **SPEC** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **A1** | [F-101-23-cierre-a1](F-101-23-cierre-a1-importacion-transacciones-erp.md) — Apto con observaciones |
| **Veredicto B1** | **Cerrado** — 3 HU enriquecidas; listas para Parte C (TR) |

## HU generadas y enriquecidas

| HU | Título | B1 | Lista para TR |
|----|--------|----|---------------|
| [HU-101-049](../../03-historias-usuario/101-PedidosWeb/HU-101-049-integracion-transacciones-plataforma.md) | Plataforma API: auth, tenant, envelope, logs y OpenAPI | Sí | **Sí** |
| [HU-101-050](../../03-historias-usuario/101-PedidosWeb/HU-101-050-integracion-transacciones-saldos.md) | Importación de stock, deuda y cheques | Sí | **Sí** |
| [HU-101-051](../../03-historias-usuario/101-PedidosWeb/HU-101-051-integracion-transacciones-historial-ventas.md) | Importación delta de historial de ventas | Sí | **Sí** |

## Cobertura del SPEC → HU

| Criterio / entregable SPEC | HU |
|-----------------------------|----|
| Auth, tenant, envelope, 401/403/404/422 y rate limit | HU-101-049 |
| Cuatro rutas y OpenAPI transversal | HU-101-049 |
| Logs `transaccion_import` | HU-101-049 |
| Snapshot atómico de stock, deuda y cheques | HU-101-050 |
| Delta merge/upsert y FKs | HU-101-050 |
| Exclusión de `comprometidoWeb` y campos `*Base` | HU-101-050 |
| Historial solo delta y upsert `idGva53` | HU-101-051 |
| Preservación de ausentes y `fecha_proceso` | HU-101-051 |
| Persistencia por SP | HU-101-050 / HU-101-051 |
| Sin maestros, UI, cron ni idempotencia | Todas, fuera de alcance |

## Revisión B1

- Las tres HU contienen narrativa, contexto, alcance, exclusiones, reglas de negocio, criterios verificables, escenarios Gherkin, supuestos y preguntas para TR.
- La separación plataforma / saldos / historial evita mezclar el alcance de SPEC-101-22.
- Las ambigüedades remanentes son de detalle técnico de TR: nombres finales de SP, mapeo físico, rate limit y precedencia de fechas.
- No se agregaron reglas fuera del SPEC ni se generaron TR o código.

## Veredicto

**Apto con observaciones para cerrar B1. Autoriza Parte C (TR).**

## Siguiente paso

Generar las TR en este orden:

1. Plataforma transversal — HU-101-049.
2. Stock, deuda y cheques — HU-101-050.
3. Historial de ventas — HU-101-051.
