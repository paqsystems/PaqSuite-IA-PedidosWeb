# Verificación del agente — SPEC-101-25 Exportación comprobantes ERP

| Campo | Valor |
|-------|--------|
| **Alcance** | SPEC-101-25 (TR [25a](TR-SPEC-101-25-integracion-export-plataforma.md), [25b](TR-SPEC-101-25-integracion-export-datos.md)) |
| **Fecha** | 2026-11-26 |
| **Parte** | **F1** |
| **Jira** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) · [58](https://paqsystems.atlassian.net/browse/PIAPEDWEB-58) · [59](https://paqsystems.atlassian.net/browse/PIAPEDWEB-59) |

## Resultado

**Aprobado con observaciones** — apto cierre **F** e **I**.

## Evidencia revisada

| Eje | Evidencia |
|-----|-----------|
| Alcance | `GET /api/v1/integracion/comprobantes`; auth integración; envelope GEN-20; paginación; filtros estado/cliente/pedido/fechas; sin UI |
| Backend | Controller, Request, Service, SP `pq_sp_integracion_export_comprobantes_list`, mapper standalone, log writer, throttle `integracion-export` |
| Datos | Deploy `backend/scripts/deploy-integracion-export-sqlserver.php`; SP dos result sets + `@total` OUTPUT |
| OpenAPI | `OpenApiIntegracionExportPaths`, schemas cabecera/renglón/cierre; tag **Integración exportación** |
| Tests auto | `php artisan test --filter=IntegracionExport` → **14 passed** (2026-11-26) |
| QA manual | Postman collection **PedidosWeb - Export comprobantes ERP** — usuario confirma **OK** (2026-11-26) |

## Tests

```text
php artisan test --filter=IntegracionExport
→ 14 passed (61 assertions)
```

OpenAPI: tag alfabético en `OpenApiTags.php`; `php artisan test tests/Feature/OpenApiDocumentationTest.php` → **7 passed** (2026-11-26).

## Hallazgos críticos

Ninguno bloqueante post Fase 2 y smoke Postman.

## Advertencias (no bloquean)

| ID | Tema |
|----|------|
| OBS-F1-25-01 | Usuario técnico: `POST /auth/login` exige perfil comercial; usar `paqsuite:provision-integracion-user` (vínculo `INTEGRERP01`) o token CLI — documentado en README Postman. |
| OBS-F1-25-02 | `paqsuite:seed-seguridad-mvp` puede fallar por drift `locale` en RDS; no requerido para export si existe provision integración. |
| OBS-F1-25-03 | Deploy SP obligatorio por tenant/BD (`deploy-integracion-export-sqlserver.php`). |
| OBS-F1-25-04 | OpenAPI cabecera: schemas ampliados (MUST); campos SHOULD del checklist consulta cabecera no todos expuestos en schema (aceptable MVP export). |

## Pendientes operativos

- PR a `develop` (Parte **N**) cuando el usuario autorice commit/push.
- Forge/prod: aplicar SP en BD empresa por instalación.

## Recomendación final

Marcar SPEC-101-25 / TR 25a-25b **Finalizado** (Parte **I**). Sin bloqueantes para uso ERP vía HTTP.