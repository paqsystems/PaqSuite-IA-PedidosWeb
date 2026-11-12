## Summary
- **GEN-13:** pin `laravel-core` **1.3.18-beta.32**; comando `pedidosweb:tasks-run-due` (poller RDS manual + due); bind `RdsDeferredBackupExecutionPoller`.
- **Ops:** manifiesto `pedidosweb-1.3.18-beta.32.json`; runbook/SPEC-HU-TR y script `sync-gen13-tasks.ps1` alineados.
- **Tests:** `BackupRdsExecutionPollerTest` (unit).

## Deploy (Preview Forge)
- `composer install` (lock con `1.3.18-beta.32`).
- Post-deploy: `migrate --force`, `paqsuite:installation:update` (manifiesto beta.32), `paqsuite:seed-deploy` (salvo `--skip-tasks`).
- **Environment:** `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true` (RDS), `PAQSUITE_TASKS_BACKUP_RDS_GATEWAY=rds`, S3 prefix según runbook.
- Cron existente `schedule:run` — no cambia; el `Kernel` ya programa `pedidosweb:tasks-run-due`.

## Test plan
- [ ] `php artisan pedidosweb:tasks-run-due --dry-run`
- [ ] Preview → Tareas programadas → backup manual RDS → `EN_EJECUCION` y cierre en historial tras ~1 min
- [ ] `php artisan tasks:validate-catalog`
