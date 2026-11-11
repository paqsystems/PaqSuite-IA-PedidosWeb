## Summary
- **GEN-13** tareas programadas (CC PQ #20): rutas UI SDK, backup RDS/Windows (gateways), scheduler `tasks:run-due`, tests E2E + PHPUnit.
- Migraciones host `pq_task_*` + fix columna `process_parameter_id` (guardado definiciones).
- Manifiesto GEN-34, runbook `runbook-gen-13-backup-pedidosweb.md`, pins SDK sin cambio de oleada.

## Deploy (Preview / Forge develop)
- `php artisan migrate --force` (incluye `2026_11_11_100017` y `2026_11_11_100018`).
- `php artisan db:seed --class=Database\\Seeders\\PqTaskProcessFrameworkSeeder` si falta catálogo `BACKUP_DATABASE`.
- `php artisan paqsuite:installation:update --cliente={tenant} --proyecto=pedidosweb --non-interactive` si SP GEN-13 desactualizados.
- Verificar `.env` Forge dev: `PAQSUITE_TASKS_ROUTES_ENABLED`, `PAQSUITE_TASKS_BACKUP_*`, `TASKS_BACKUP_S3_ARN_PREFIX` (RDS real); lab `PAQSUITE_TASKS_BACKUP_RDS_GATEWAY=memory`.
- Vite preview: `VITE_PAQSUITE_TASKS_BACKUP_BACKEND` alineado al backend.

## Test plan
- [ ] GET /api/v1/health (Forge dev)
- [ ] Login preview + menú **General → Tareas programadas**
- [ ] Alta definición **Backup de base de datos** (diaria) + guardar
- [ ] `php artisan tasks:validate-catalog` → 1 proceso
