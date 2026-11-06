# Cierre F — CC PQ #18 (30/10/2026) — Delta + `eliminarSiAusente` transacciones

## Alcance

| # | Tema | Evidencia |
|---|------|-----------|
| 1 | Delta operativo + baja por ítem (stock/deuda/cheques) | SP `00-deploy-all.sql` · `TransaccionJsonHandler` |
| 2 | OpenAPI snapshot vs delta | `OpenApiGetsSchemas.php` · `api-docs.json` |
| 3 | Historial ventas sin baja | Decisión documentada en SPEC-101-23 |

**Fecha F/F1:** 02/11/2026  
**Parte E:** [E-CC-PQ-18-tests.md](E-CC-PQ-18-tests.md)

## F1 — Verificación agente

**Resultado:** **Aprobado con observaciones**

- Código y SP alineados a HU/TR CC #18.
- Feature tests de integración async: observación entorno local (500 / falta `ImportationDispatcher` en generate); unit normalizer OK.
- **Deploy producción:** ejecutar `php scripts/deploy-integracion-transacciones-sqlserver.php` por tenant antes de usar bajas delta.

## Parte I

Ver [I-CC-PQ-18-cierre-formal.md](I-CC-PQ-18-cierre-formal.md).
