# Pull Request Report

**Base:** `develop`  
**Compare:** `v1.2.0`  
**Modo SDK:** deploy (Cloudsmith: `laravel-core` 1.3.18-beta.28 / `react-core` 2.4.30-beta.7)

## Resumen

El diseñador GEN-15 en Preview/productivos devolvía «No se pudo completar la solicitud» y el combo de procesos quedaba vacío: `GET /api/v1/emissions/processes` ejecutaba `pq_sp_emission_process_get` y el SP no existía porque Forge no corre `paqsuite:installation:update`.

Este PR despliega el SQL GEN-15 del SDK en el host (CREATE/ALTER, sin DROP), siembra el catálogo de 8 informes y cablea el hook GEN-34 de catálogo.

## SPEC / HU / TR relacionadas

- SPEC: SPEC-001-06-emision-update
- HU: HU-GEN-15-diseniador-reportes
- TR: TR-GEN-15-diseniador-reportes
- Plan: D1-PLAN-CC-PQ-19 (R4/R5 mitigados en host)

## Cambios realizados

### Backend
- `paqsuite:deploy-sdk-sql` + `PedidosWebSdkSqlDeployer` (SP `pq_sp_emission_*`, tabla/schemas diseñador, seed catálogo)
- `paqsuite:seed-deploy` invoca el comando GEN-15
- Migración `2026_11_06_100016_deploy_gen15_sdk_sql`
- Bind `InstallationCatalogSyncContract` (host, no Null del SDK)
- Envelope 4713 si el SQL GEN-15 sigue ausente (en lugar de 500/transport)

### Frontend
- i18n `emission.designer.schemaMissing` en 5 locales

### Base de datos
- CREATE/ALTER PROCEDURE GEN-15 (idempotente)
- CREATE TABLE IF `pq_emission_designer_schemas`
- Seed `pq_emission_processes` / `pq_emission_reports` (8 informes)

### Tests
- Unit: splitter GO + detección SP ausente (OK)
- Feature: `SeedDeployCommandTest` (OK)

## Observaciones deploy

| Ítem | Requerido en Preview |
|------|----------------------|
| migrate | Sí — `2026_11_06_100016_deploy_gen15_sdk_sql` |
| seed / SQL | Sí — `paqsuite:deploy-sdk-sql` (también vía `seed-deploy`) |
| .env nuevo | No |
| Solo código | No |

Demo Forge (`demopedidosweb`, rama `develop`) ya tiene `migrate --force` + `paqsuite:seed-deploy`.

### Smoke post-deploy

- [ ] GET `/api/v1/health`
- [ ] Login Preview + Diseñador de reportes: combo con 8 procesos, sin «No se pudo completar la solicitud»
