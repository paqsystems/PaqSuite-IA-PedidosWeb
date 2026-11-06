# D1 — Plan CC PQ #19 (03/11/2026) — GEN-13 / GEN-15

| Campo | Valor |
|-------|--------|
| **Origen** | 00-ControlCalidad-PQ #19 |
| **Parte G** | 03/11/2026 |
| **Estado plan** | **Fase 1** hecha en código · **Fase 2** hecha en código (04/11/2026) · sin commit/push salvo autorización |

## Pin SDK (acordado — sin bump)

| Paquete | Versión | Notas |
|---------|---------|--------|
| `paqsuite/laravel-core` | **1.3.18-beta.28** | Alineado a monorepo Framework y `docs/01-arquitectura/integracion-framework-sdk.md` |
| `@paqsuite/react-core` | **2.4.30-beta.7** | Exporta `TaskProcessesPage`, `EmissionReportDesignerPage`, `EmissionDialog`, APIs GEN-13/15 |

Desarrollo conjunto opcional: `PAQ_REPO_LAB=1` + `sdk-link.ps1` (no sustituye el pin de release).

## Objetivo

Adoptar **GEN-15** (diseñador + emisión en 8 informes) y **GEN-13** (tareas + `BACKUP_DATABASE`) en PedidosWeb host con SDK pinneado.

## Riesgos / bloqueadores

| # | Riesgo | Mitigación | Estado |
|---|--------|------------|--------|
| R1 | Pin sin exports GEN-13/15 | Pin acordado; exports verificados en oleada | OK |
| R2 | Sidecar DX reporting en dev | `PAQSUITE_EMISSION_DX_MODE=fake` + `.env.example` | OK piloto |
| R3 | Alcance 8 informes + 2 GEN | 3 fases | En curso |
| R4 | `installation:update` no corre en Forge; host no tenía `InstallationCatalogSyncContract` | Bind catalog sync + `paqsuite:deploy-sdk-sql` (CREATE/ALTER, sin DROP) | **Mitigado** |
| R5 | Productivos sin SP `pq_sp_emission_process_get` → diseñador vacío | Migración `2026_11_06_100016` + seed-deploy despliega SQL GEN-15 del SDK | **Mitigado** |
| R6 | `npm run build` TS en node_modules react-core (tasks pages) | Bump SDK cuando salga fix; host sin cambio en páneas piloto | Observación |

## Fase 1 — Infra + menú General

| # | Ítem | Estado |
|---|------|--------|
| 1 | `PedidosWebGenCapabilitiesServiceProvider` — GEN-13 (`BACKUP_DATABASE`, gateway RDS in-memory) + GEN-15 (repo, guard, settings) | Hecho |
| 2 | Menú MVP `pw_diseniadorreportes`, `gen_tareasprogramadas` en `paqsuite_mvp.php` + i18n + rutas FE | Hecho |
| 3 | `DiseniadorReportesPage` / `TareasProgramadasPage` (SDK) | Hecho |
| 4 | API emisiones catálogo + diseño (processes, design layout/principal) | Hecho |
| 5 | `paqsuite:seed-menus-mvp` en deploy (`SeedDeployCommand`) y tests Feature habituales | Hecho (política: no seed manual en chat) |
| 6 | `installation:update` + SP GEN-13/15 en BD | **Mitigado en host:** `paqsuite:deploy-sdk-sql` / migrate `100016`. GEN-34 completo en Forge sigue pendiente (tenants no listados en `paqsuite.instalaciones`). |
| 7 | Sync `label_key` menú demo | **Pendiente** (GEN-34) |

**TR:** TR-GEN-13, TR-GEN-15 — wiring host; cierre D/E tras smoke BD.

## Fase 2 — Diseñador + piloto emisión

| # | Ítem | Estado |
|---|------|--------|
| 1 | `EmissionReportDesignerPage` montada | Hecho (Fase 1) |
| 2 | Seed catálogo piloto `PedidosWebEmissionPilotSeeder` (`pw_pedidosingresados`, `pw_deudaclientes`; menú `pw_deuda` para deuda) | Hecho |
| 3 | Seed en **`paqsuite:seed-deploy`** (`--skip-emission` opcional) | Hecho |
| 4 | Backend emisión: orchestrator, dataset port consultas, jobs/preview/download, PDF fake, artefactos en `storage/app/emissions` | Hecho |
| 5 | Rutas `POST/GET /api/v1/emissions/*` (prefijo `v1` único) | Hecho |
| 6 | FE: `ConsultaEmitReportToolbarItem` + `EmissionDialog`; Pedidos ingresados + Deuda; `gridEmitReport`; i18n `emission.*` (5 locales) | Hecho |
| 7 | `installApiAuthFetch` + `bootstrapPedidosWebApiBase` en `main.tsx` (Bearer/tenant para SDK) | Hecho |
| 8 | Feature test `PedidosWebEmissionPilotApiTest` (PDF magic bytes) | Hecho; **skip** sin tablas GEN-15 |
| 9 | OpenAPI envelopes tipados jobs/preview | **Pendiente** (TR-SPEC-101-07) |
| 10 | Vitest AC-CC19-UI-04 (mock API emitir) | **Pendiente** |
| 11 | Smoke manual: login → ingresados/deuda → Emitir → descarga PDF | **Pendiente** — checklist [`D-SMOKE-CC-PQ-19-emision-piloto.md`](D-SMOKE-CC-PQ-19-emision-piloto.md); intento 04/11 bloqueado en login sin seed MVP |

**TR:** TR-SPEC-101-07/11-update (piloto) — implementación D; falta F1/evidencia.

### Alineación proceso emisión vs menú

| Informe | `procedimiento` grilla | `processCode` emisión | `menu_process_code` (permiso) |
|---------|------------------------|----------------------|-------------------------------|
| Pedidos ingresados | `pw_pedidosingresados` | `pw_pedidosingresados` | `pw_pedidosingresados` |
| Deuda | `pw_deuda` | `pw_deudaclientes` | `pw_deuda` |

## Fase 3 — Rollout 8 informes + backup smoke

| # | Ítem | Estado |
|---|------|--------|
| 1 | Botón emitir en 6 consultas restantes (mismo patrón `emissionProcessCode`) | Pendiente |
| 2 | Registrar dataset ports / SP field-schema por informe (rollout TR-SPEC-101-07) | Pendiente |
| 3 | Definición `BACKUP_DATABASE` en catálogo tareas + smoke manual dev | Pendiente |
| 4 | Runbook S3 / vars Forge (`TASKS_BACKUP_S3_ARN_PREFIX`, etc.) | Pendiente |
| 5 | **E:** `E-CC-PQ-19-tests.md` · **F:** `F-CC-PQ-19-cierre-formal.md` | Pendiente |

## Orden de TR (sin cambio)

1. TR-GEN-15-diseniador-reportes  
2. TR-GEN-13-tareas-programadas  
3. TR-SPEC-101-07-consultas-api-update  
4. TR-SPEC-101-11-consultas-ui-update  

## Verificación reciente

- PHPUnit `PedidosWebEmissionPilotApiTest`: **exit 0** en corrida completa (~9 min con seeds sobre BD real); casos **skipped** si no existen `pq_emission_processes` / `pq_emission_jobs`.
- Corregido ciclo DI en `PedidosWebEmissionInlineTaskDispatcher` (lazy `EmissionBatchTaskHandler`).

## Fuera de esta entrega D1

- Parte I (unificación updates).
- Emisión mobile kardex.

## Próximo paso recomendado (operativo)

1. **Tu ejecución** (cuando toque): BD GEN-15 + `installation:update` / migrate; luego `paqsuite:seed-menus-mvp`, `paqsuite:seed-seguridad-mvp`, `paqsuite:seed-deploy` (emisión piloto incluida).
2. **Smoke manual** siguiendo [`D-SMOKE-CC-PQ-19-emision-piloto.md`](D-SMOKE-CC-PQ-19-emision-piloto.md) — capturas para F1.
3. **Parte E** (OpenAPI jobs/preview, Vitest UI) + **Fase 3** rollout 6 consultas.
4. **Commit/PR** cuando autorices.

Stack dev ya probado levantable con `.\scripts\dev\start-dev.ps1 -All` (Frontend 3010, Backend 8088, OpenAPI `/api/documentation`).
