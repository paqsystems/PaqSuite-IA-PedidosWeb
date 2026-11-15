# TR-GEN-13 — Tareas programadas y BACKUP_DATABASE

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-13-tareas-programadas](../../03-historias-usuario/001-Generaliddes/HU-GEN-13-tareas-programadas.md) |
| **SPEC** | [SPEC-001-09-tareas-programadas](../../05-open-spec/001-Generaliddes/SPEC-001-09-tareas-programadas.md) |
| **Estado** | Finalizado |
| **Jira** | [PIAPEDWEB-52](https://paqsystems.atlassian.net/browse/PIAPEDWEB-52) (CC #22) · [PIAPEDWEB-48](https://paqsystems.atlassian.net/browse/PIAPEDWEB-48) (CC #20) |
| **Unificación I** | 15/11/2026 — [I-CC-PQ-GEN-13](../101-PedidosWeb/I-CC-PQ-GEN-13-cierre-formal.md) |
| **F1** | [F1-CC-PQ-22-verificacion-agente.md](../101-PedidosWeb/F1-CC-PQ-22-verificacion-agente.md) — **Aprobado con observaciones** |
| **Origen** | 00-ControlCalidad-PQ #19 · #20 · #22 |

---

## 1) Pins / Cloudsmith

- `paqsuite/laravel-core` **≥ 1.3.18-beta.38**
- `@paqsuite/react-core` **≥ 2.4.30-beta.19**
- Locks con `CLOUDSMITH_READ_TOKEN` (regla **45**); **sin** `path`/`file:` al monorepo.
- Docs: [integracion-framework-sdk.md](../../01-arquitectura/integracion-framework-sdk.md) · [runbook-gen-13-backup-pedidosweb.md](../../06-operacion/runbook-gen-13-backup-pedidosweb.md)

## 2) Frontend

- Ruta `/general/tareas-programadas` → wrapper delgado `TareasProgramadasPage` → `TareasProgramadasSdkRoutes`.
- Chrome **`ProcessPageLayout`**; solapas NavLink (Procesos / Definiciones / Ejecuciones / Historial) — equivalente a `TaskScheduledLayout` si el pin no lo exporta.
- Export SDK: `TaskProcessesPage`, `TaskDefinitionsPage`, `TaskDefinitionFormPage`, `TaskExecutionsPage`, `TaskAutomationsMonitorPage`.
- `data-testid="page-tareas-programadas"`; título `t('menu.tareasProgramadas')`.
- `registerTareasProgramadasI18nResources(i18n, 'translation')`; **no** duplicar `tareasProgramadas.*` GEN en JSON host.
- Token + `buildPlatformHeaders()`; MONO `single`.
- Mobile: exclusión `pedidosWebMobilePolicy` (+ Vitest).

## 3) Backend

- Rutas package (`PAQSUITE_TASKS_ROUTES_ENABLED`).
- `TaskProcessRegistry` + `BackupDatabaseProcess` del laravel-core. **Prohibido** copiar handler/formulario a `App\`.
- DualPath SP / `installation:update` (CREATE/ALTER, **sin DROP**).
- Gateways DualPath host (`RdsSqlServerBackupGateway` / `WindowsSqlServerMultiDiskBackupGateway`); in-memory **solo** `memory`.
- Scheduler: **`pedidosweb:tasks-run-due`** cada minuto (`Kernel`): poller RDS luego due; `tasks:purge-history` diario.
- Forge/RDS: `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true`.

## 4) Operación

SoT: [runbook-gen-13-backup-pedidosweb](../../06-operacion/runbook-gen-13-backup-pedidosweb.md) — S3, scheduler, defer, seed `pq_task_*`.

Smoke CLI:

```bash
php artisan tasks:validate-catalog
php artisan pedidosweb:tasks-run-due --dry-run
```

## 5) AC técnicos

- [x] **AC-T-01:** Pins y locks ≥ objetivo; docs alineados. (`1.3.18-beta.38` / `2.4.30-beta.19`)
- [x] **AC-T-02:** `TareasProgramadasPage` usa `ProcessPageLayout` + pantallas SDK; sin forks i18n GEN.
- [x] **AC-T-03:** Listar procesos incluye `BACKUP_DATABASE`; definición MONO persiste sin picker.
- [x] **AC-T-04:** Ejecución manual registra historial; host sin segunda API de tareas.
- [x] **AC-T-05:** Cron `pedidosweb:tasks-run-due`, defer RDS y vars S3 en runbook; native no expone la ruta.
- [x] **AC-T-06:** En configuración no-lab el gateway in-memory no es el bind de producción.
- [x] **AC-T-07:** Smoke FW #17–#20 (nombre, rehidratación, EXITOSA/defer, inactiva, baja física) — F1 15/11 **Aprobado con observaciones**.

## 6) Evidencia

| Fecha | Qué |
|-------|-----|
| 14/11/2026 D | Bump Cloudsmith; Vitest policy 7/7; PHPUnit poller; CLI catalog + due dry-run |
| 15/11/2026 E/F1 | PHPUnit 8/8 (nombre `-sNN`/`-mNN`, poller, gateway lab, resolver MONO); Vitest 7/7; usuario aprueba UI; E2E CC #22 en repo |

## Historial de unificación (Parte I 15/11/2026)

Incorpora y elimina `TR-GEN-13-tareas-programadas-update.md` (CC #20) y `TR-GEN-13-tareas-programadas-update-01.md` (CC #22).
