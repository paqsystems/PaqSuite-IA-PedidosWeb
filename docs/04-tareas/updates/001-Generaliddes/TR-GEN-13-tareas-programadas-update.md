# TR-GEN-13 — Tareas programadas y BACKUP_DATABASE (update CC PQ #20)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-13-tareas-programadas-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-13-tareas-programadas-update.md) |
| **SPEC** | [SPEC-001-09-tareas-programadas-update-01](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update-01.md) |
| **TR base** | [TR-GEN-13-tareas-programadas](../../001-Generaliddes/TR-GEN-13-tareas-programadas.md) |
| **Estado** | Pendiente |
| **Jira** | [PIAPEDWEB-48](https://paqsystems.atlassian.net/browse/PIAPEDWEB-48) |
| **Origen** | 00-ControlCalidad-PQ #20 — 11/11/2026 |

---

## 1) Frontend

- Envolver `TaskProcessesPage` con **`ProcessPageLayout`** (ficha GEN-13 / GEN-01). Título vía `t` / `resolveMenuCaption`; **prohibido** chrome host con literales ES.
- Conservar `data-testid="page-tareas-programadas"` y ruta `/general/tareas-programadas`.
- Token + `buildPlatformHeaders()` (`X-Paq-Cliente`); `tenancyMode` / platform `single`.
- i18n: namespace del bootstrap; `register*` del SDK si el pin lo exporta; **no** duplicar `tasks.*` / `menu.*` GEN en JSON host.
- Mobile: mantener exclusión en `pedidosWebMobilePolicy` (+ test de policy).

## 2) Backend

- Rutas GEN-13 del package (`PAQSUITE_TASKS_ROUTES_ENABLED`).
- `TaskProcessRegistry` + `BackupDatabaseProcess` del laravel-core. **Prohibido** copiar handler/formulario a `App\`.
- DualPath SP / `installation:update` / `paqsuite:deploy-sdk-sql` (CREATE/ALTER, sin DROP).
- Gateway: `InMemoryMonoRdsBackupGateway` **solo lab**; bind del gateway real SDK en Forge/RDS (Windows `INIT`/`NOINIT`; RDS `rds_task_status` + lock global).
- Scheduler: `tasks:run-due` en Laravel scheduler / cron Forge.

## 3) Operación / runbook

- Documentar `TASKS_BACKUP_S3_ARN_PREFIX` y credenciales en runbook deploy / GEN-34.
- Seed o alta operativa de al menos una definición `BACKUP_DATABASE`.
- Smoke: ejecución manual + fila en historial de ejecuciones.

## 4) AC técnicos

- **AC-T-20-01:** `TareasProgramadasPage` usa `ProcessPageLayout`; tests/UI no dependen del DOM interno DX.
- **AC-T-20-02:** Listar procesos incluye `BACKUP_DATABASE`; definición MONO persiste sin picker multi-empresa.
- **AC-T-20-03:** Ejecución manual registra historial; host sin segunda API de tareas.
- **AC-T-20-04:** Cron `tasks:run-due` y vars S3 documentados; native no expone la ruta.
- **AC-T-20-05:** En configuración no-lab, el gateway in-memory no es el bind de producción.

## 5) Orden sugerido (D1)

1. Diff pin SDK vs ficha GEN-13 (`ProcessPageLayout`, registry, backup).
2. Wrapper UI + i18n + policy mobile.
3. Bind gateway real vs lab; DualPath SP.
4. Scheduler + runbook S3.
5. Smoke definición + ejecución manual (dev; sin DROP).
