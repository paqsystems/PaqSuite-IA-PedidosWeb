# Cierre F Formal — SPEC-101-25 (Exportación comprobantes ERP)

## Alcance

`GET /api/v1/integracion/comprobantes` — portal → ERP, lectura paginada por cabecera, payload anidado `cabecera` + `renglones[]` + `cierre`.

| Artefacto | Ruta |
|-----------|------|
| **SPEC** | [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Producto** | [APIs-Exportacion-comprobantes-erp.md](../../02-producto/PedidosWeb/APIs-Exportacion-comprobantes-erp.md) |
| **F1** | [D-VERIFICACION-101-25-exportacion-comprobantes-erp.md](D-VERIFICACION-101-25-exportacion-comprobantes-erp.md) |
| **Postman** | [README-export-comprobantes.md](../../06-operacion/postman/README-export-comprobantes.md) |

## Resultado global

**Aprobado con observaciones** (F1 2026-11-26).

## Entregables D

| Capa | Entregable |
|------|------------|
| HTTP | Ruta, middleware `integracion.importacion`, throttle dedicado, validación rangos estado |
| Persistencia | SP `pq_sp_integracion_export_comprobantes_list` + script deploy |
| Dominio | `StoredProcedureExportComprobantesRepository`, `IntegracionExportComprobanteMapper` |
| Contrato | OpenAPI + i18n `integracion.export.*` |
| Pruebas | Platform + Data + Mapper unit; Postman reproducible |

## Smoke / QA

| Escenario | Estado |
|-----------|--------|
| Login integración + GET export (Postman) | **OK** (usuario 2026-11-26) |
| PHPUnit `IntegracionExport` | **OK** (14 tests) |
| SP desplegado RDS demo | **OK** |

## Observaciones heredadas (F1)

Ver OBS-F1-25-01…04 en [D-VERIFICACION-101-25](D-VERIFICACION-101-25-exportacion-comprobantes-erp.md).

## Pendiente operativo

- Merge PR → `develop` (Parte **N** / Jira Desplegado) cuando corresponda.
- SQL SP en cada BD empresa en deploy Forge.

## I — Finalizado

SPEC-101-25 y TR 25a/25b quedan **Finalizado** en markdown con este cierre, sujeto a despliegue por entorno.