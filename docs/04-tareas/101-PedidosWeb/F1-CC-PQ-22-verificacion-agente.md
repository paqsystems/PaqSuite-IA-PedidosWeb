# F1 — Verificación agente — GEN-13 / Backup (CC PQ #20–#22)

| Campo | Valor |
|-------|--------|
| **Fecha** | 15/11/2026 |
| **Parte** | F1 (`agent-verification-guide`) |
| **Alcance** | PedidosWeb host — tareas programadas + `BACKUP_DATABASE` (GEN-13). **No** GEN-15. |
| **TR primaria** | [TR-GEN-13-tareas-programadas](../001-Generaliddes/TR-GEN-13-tareas-programadas.md) (unificada en Parte I) |
| **HU** | [HU-GEN-13-tareas-programadas](../../03-historias-usuario/001-Generaliddes/HU-GEN-13-tareas-programadas.md) |
| **SPEC** | [SPEC-001-09-tareas-programadas](../../05-open-spec/001-Generaliddes/SPEC-001-09-tareas-programadas.md) |
| **CC** | [00-ControlCalidad-PQ](../../00-ControlCalidad/00-ControlCalidad-PQ.md) **#22** (adopción pin) · **#21** (evidencia → Framework) · **#20** (cableado) · **#19** GEN-13 (cerrado; residual GEN-15) |
| **Jira** | [PIAPEDWEB-50](https://paqsystems.atlassian.net/browse/PIAPEDWEB-50) · [PIAPEDWEB-46](https://paqsystems.atlassian.net/browse/PIAPEDWEB-46) · [PIAPEDWEB-49](https://paqsystems.atlassian.net/browse/PIAPEDWEB-49) · SPEC [PIAPEDWEB-10](https://paqsystems.atlassian.net/browse/PIAPEDWEB-10) |
| **Consentimiento Finalizado** | Usuario 15/11/2026: «en principio aprobado todo en Pedidos Web» + cierre F1/F/I de GEN/CC pendientes de tareas programadas y backups |

## Veredicto

**Aprobado con observaciones**

El host PedidosWeb adopta GEN-13 + `BACKUP_DATABASE` con pin Cloudsmith vigente, DualPath SP, scheduler `pedidosweb:tasks-run-due` y UI SDK. Los CC **#20**, **#21** (host) y **#22** quedan listos para F/I. GEN-15 permanece fuera de este cierre.

## Ocho ejes

| Eje | Resultado | Evidencia |
|-----|-----------|-----------|
| 1. Alcance | OK | Solo GEN-13/backup. GEN-15 (CC #19 residual) no se unifica. |
| 2. Código | OK | Wrapper delgado `TareasProgramadasPage` → `TareasProgramadasSdkRoutes`; `BackupDatabaseProcess` del laravel-core; gateways DualPath. |
| 3. BD | OK | Tablas `pq_task_*` + SP DualPath vía GEN-34 / migrate host; **sin DROP** en este cierre. |
| 4. Backend | OK | Rutas package; `TaskProcessRegistry`; `RdsDeferredBackupExecutionPoller`; in-memory solo lab. |
| 5. Frontend | OK | `ProcessPageLayout` + NavLink Procesos/Definiciones/Ejecuciones/Historial; `registerTareasProgramadasI18nResources`; native excluido. |
| 6. Tests | OK con obs. | PHPUnit 8/8 (15/11); Vitest policy 7/7; E2E Playwright CC #22 existe (mocks API); Playwright no re-ejecutado en esta sesión F1. |
| 7. Docs | OK | Runbook pin ≥ beta.38 / beta.19; SPEC/HU/TR unificados en I. |
| 8. Trazabilidad | OK | CC #19 GEN-13 → #20 → #21 (FW) → #22; Jira PIAPEDWEB-10/46/49/50. |

## Evidencia de código (host)

| Superficie | Archivo |
|------------|---------|
| Entrada menú | `frontend/src/features/gen/pages/TareasProgramadasPage.tsx` |
| Chrome + solapas SDK | `frontend/src/features/gen/tareasProgramadas/TareasProgramadasSdkRoutes.tsx` (`ProcessPageLayout`, `TaskProcessesPage`, `TaskDefinitionsPage`, `TaskExecutionsPage`, `TaskAutomationsMonitorPage`) |
| i18n GEN | `frontend/src/i18n/i18n.ts` — `registerTareasProgramadasI18nResources(i18n, 'translation')` |
| Label menú producto | `menu.tareasProgramadas` en 5 locales (dominio host; no fork `tareasProgramadas.*` GEN) |
| Registry + DualPath | `backend/app/Providers/PedidosWebGenCapabilitiesServiceProvider.php` |
| Scheduler | `backend/app/Console/Kernel.php` — `pedidosweb:tasks-run-due` cada minuto |
| Native | `pedidosWebMobilePolicy` excluye `/general/tareas-programadas` |

## Tests (15/11/2026)

```text
cd backend
php artisan test --filter=Gen13Cc22BackupObjectNameTest|BackupRdsExecutionPollerTest|InMemoryMonoRdsBackupGatewayTest|PedidosWebMonoBackupCompanyTargetResolverTest
# 8 tests, 15 assertions — OK (1 deprecation PHPUnit)

cd frontend
npx vitest run src/shared/platform/pedidosWebMobilePolicy.test.ts
# 7/7 OK
```

E2E: `frontend/tests/e2e/gen/tareas-programadas-cc22.spec.ts` (checkboxes, rehidratación, baja+historial, `EN_EJECUCION`, run deshabilitado si inactiva). No re-corrido Playwright en esta sesión; el usuario aprobó el smoke UI en PedidosWeb.

## Criterios SPEC (todos cubiertos)

| CA | Resultado |
|----|-----------|
| CA-SPEC-09-19-01…04 | Cubiertos por cableado #20 + runbook |
| CA-SPEC-09-20-01…04 | `ProcessPageLayout`; DualPath; scheduler; native excluido |
| CA-SPEC-09-22-01 | Pins `laravel-core` **1.3.18-beta.38** / `react-core` **2.4.30-beta.19** |
| CA-SPEC-09-22-02 | Chrome SDK equivalente (ver OBS-01) |
| CA-SPEC-09-22-03 | Smoke #17–#20: usuario aprobado + E2E mock + PHPUnit nombre `-sNN`/`-mNN` |
| CA-SPEC-09-22-04 | Scheduler + defer RDS en runbook; sin handler `BackupDatabaseProcess` en `App\` |

## Observaciones (no bloqueantes)

| ID | Tema | Notas |
|----|------|-------|
| OBS-01 | `TaskScheduledLayout` | El pin `react-core` **2.4.30-beta.19** no exporta el layout de solapas; el host usa `ProcessPageLayout` + `NavLink` (Procesos → Definiciones → Ejecuciones → Historial). Equivalente aceptado en D 14/11. |
| OBS-02 | Playwright | Spec CC #22 presente; no re-ejecutado en F1 15/11. Aprobación humana de UI. |
| OBS-03 | Gateways host | `RdsSqlServerBackupGateway` / `WindowsSqlServerMultiDiskBackupGateway` son adaptadores DualPath; no son un fork de `BackupDatabaseProcess`. In-memory solo si `rdsGateway`/`windowsGateway` = `memory`. |
| OBS-04 | Backup RDS live | No se disparó un backup real sobre ERP compartido en esta sesión. EXITOSA / defer cubiertos por pin SDK + poller + mock E2E `EN_EJECUCION`. |
| OBS-05 | GEN-15 | CC **#19** sigue abierto para diseñador/emisión. Fuera de este F1. |

## CC #21 (host)

Los defectos (falso FALLIDA + checkboxes) son competencia **Framework** ([PIAFRAME-145](https://paqsystems.atlassian.net/browse/PIAFRAME-145)). PedidosWeb los consume vía pin **beta.38 / beta.19** (CC #22). No hay SPEC/HU/TR-update en este repo para #21. Cierre host: **Finalizado** junto con #22.
