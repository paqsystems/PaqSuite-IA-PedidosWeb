# Cierre C — SPEC-101-23 — APIs importación transacciones ERP

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-22 |
| **Épica** | [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35) |
| **B1** | [F-101-23-cierre-b1](F-101-23-cierre-b1-importacion-transacciones-erp.md) — Cerrado |
| **Veredicto C** | **Generada** — 3 TR; pendiente C1 |

## TR generadas

| ID | Archivo | HU | Orden D1 |
|----|---------|----|----------|
| 23a | [TR-SPEC-101-23-integracion-transacciones-plataforma](TR-SPEC-101-23-integracion-transacciones-plataforma.md) | 049 / [PIAPEDWEB-43](https://paqsystems.atlassian.net/browse/PIAPEDWEB-43) | 1 |
| 23b | [TR-SPEC-101-23-integracion-transacciones-saldos](TR-SPEC-101-23-integracion-transacciones-saldos.md) | 050 / [PIAPEDWEB-44](https://paqsystems.atlassian.net/browse/PIAPEDWEB-44) | 2 |
| 23c | [TR-SPEC-101-23-integracion-transacciones-historial-ventas](TR-SPEC-101-23-integracion-transacciones-historial-ventas.md) | 051 / [PIAPEDWEB-45](https://paqsystems.atlassian.net/browse/PIAPEDWEB-45) | 3 |

## Cobertura

| Alcance | TR |
|---------|----|
| Auth, tenant, envelope, rate limit, logs y OpenAPI | 23a |
| Stock, deuda y cheques; snapshot atómico y delta | 23b |
| Historial; delta exclusivo y upsert `idGva53` | 23c |
| SP y acceso de negocio | 23a, 23b, 23c |

## Temas a cerrar en C1

| Tema | TR | Nota |
|------|----|------|
| Reutilización exacta de clases comunes de 101-22 | 23a | Confirmar adapter/registry sin duplicar contrato |
| Mecanismo técnico de snapshot atómico | 23b | Staging/TVP/contrato SP, sin cambiar comportamiento |
| Columnas físicas canónicas de stock y cheques | 23b | Cerrar contra modelo vigente |
| Precedencia fecha de fila vs carátula | 23c | Decisión necesaria antes de D1 |
| Lista completa de campos opcionales de historial | 23c | Tabla campo a campo para OpenAPI |

Estas observaciones no bloquean la generación de las TR, pero C1 debe resolverlas antes de autorizar D1.

## Siguiente paso

**C1** — revisar ambigüedad y consistencia de las tres TR; luego generar plan D1.
