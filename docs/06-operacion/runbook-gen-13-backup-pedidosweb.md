# GEN-13 — Tareas programadas y backup BD (PedidosWeb)

Runbook operativo CC #20. Cableado alineado a **Partes** (RDS por defecto; Windows parametrizable).

## Variables backend (`.env`)

| Variable | Uso |
|----------|-----|
| `PAQSUITE_TASKS_ROUTES_ENABLED` | `true` para APIs GEN-13 |
| `PAQSUITE_TASKS_SCHEDULE_TIMEZONE` | Zona del scheduler (ej. `America/Argentina/Buenos_Aires`) |
| `PAQSUITE_TASKS_BACKUP_BACKEND` | **`rds`** (Forge/RDS, default) o **`windows`** (SQL Server local TO DISK) |
| `PAQSUITE_TASKS_BACKUP_RDS_GATEWAY` | **`rds`** (msdb `rds_backup_database`) o **`memory`** (solo lab) |
| `PAQSUITE_TASKS_BACKUP_WINDOWS_GATEWAY` | **`sqlserver`** o **`memory`** (lab) |
| `TASKS_BACKUP_S3_ARN_PREFIX` | ARN bucket/prefijo S3 (obligatorio con backend `rds`) |
| `TASKS_BACKUP_WINDOWS_DEFAULT_FOLDER` | Ruta carpeta documentada para operadores (solo Windows; el formulario muestra el campo **carpeta** solo con backend `windows`) |
| `TASKS_BACKUP_POLL_SECONDS` | Polling `rds_task_status` |
| `TASKS_BACKUP_LOCK_LEASE_SECONDS` | Lock global RDS por instalación |

## Variables frontend (Vite)

| Variable | Uso |
|----------|-----|
| `VITE_PAQSUITE_TASKS_BACKUP_BACKEND` | Debe coincidir con `PAQSUITE_TASKS_BACKUP_BACKEND` (`rds` \| `windows`) para ocultar/mostrar parámetros en el formulario SDK |

## Scheduler (Forge)

1. Cron del servidor: `* * * * * php /path/to/artisan schedule:run`
2. `app/Console/Kernel.php` programa `tasks:run-due` cada minuto (`withoutOverlapping(15)`) y `tasks:purge-history` diario.

Smoke CLI (sin ejecutar backup):

```bash
php artisan tasks:validate-catalog
php artisan tasks:run-due --dry-run
```

## Deploy SQL GEN-13

**Sin DROP.** El catálogo y los SP de tareas entran por **manifiesto GEN-34** (`paqsuite:installation:update`), no por `paqsuite:deploy-sdk-sql` (este último es GEN-15 emisiones).

### Prerequisito

Conectividad desde la máquina que ejecuta Artisan hasta `DB_HOST` / `DB_DATABASE` del entorno (RDS: VPN, security group, IP permitida). Si hay `TCP Provider: Tiempo de espera agotado`, no correr seeds ni reintentar en bucle: corregir red y repetir.

### Cliente / proyecto (GEN-34)

Alinear con la instalación en `config/paqsuite.php` → `instalaciones`:

| `DB_DATABASE` (ejemplo) | `PAQSUITE_CLIENTE` sugerido |
|-------------------------|------------------------------|
| `paqsystems_pedidosweb_demo` | `demo` |
| `paqsystems_pedidosweb_*` (Quento) | `quento` |
| LAN / desarrollo | `desarrollo` |

Definir en `backend/.env`:

```env
PAQSUITE_CLIENTE=demo
PAQSUITE_PROYECTO=pedidosweb
```

### Tablas host `pq_task_*` (obligatorio en BD vacía / restore sin Partes)

El manifiesto **Framework** despliega SP GEN-13 y evoluciones, pero **no** crea las tablas base. PedidosWeb las provisiona con la migración host `2026_11_11_100017_create_pq_task_tables.php` (mismo contrato que Partes-Atención).

Aplicar también `2026_11_11_100018_add_process_parameter_id_to_pq_task_definition_parameter.php`: sin la columna `process_parameter_id`, `pq_sp_task_definition_parameter_set` falla y la UI muestra *No se pudo guardar la definición*.

Tras `installation:update` (o `migrate --force` en dev):

```bash
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\PqTaskProcessFrameworkSeeder
```

`tasks:validate-catalog` debe reportar **1 process** (`BACKUP_DATABASE`).

Si `installation:update` falla por **Checksum mismatch** en `deploy_gen15_sdk_sql`, regenerar el manifiesto host:

```bash
php scripts/dev/generate-pedidosweb-host-manifest.php
```

### Dev — base del `.env` local

Desde la raíz del repo (misma secuencia que entorno de desarrollo + verificación GEN-13):

```powershell
$env:PAQSUITE_CLIENTE = 'demo'   # ajustar según tabla anterior
$env:PAQSUITE_PROYECTO = 'pedidosweb'
.\scripts\dev\sync-gen13-tasks.ps1
```

Equivalente manual en `backend/`:

```bash
php artisan migrate --force
php artisan paqsuite:installation:update --cliente=demo --proyecto=pedidosweb --non-interactive
php artisan paqsuite:sql:sync-dev
php artisan tasks:validate-catalog
php artisan tasks:run-due --dry-run
php artisan paqsuite:installation:status --cliente=demo --proyecto=pedidosweb
```

### Bases operativas (Forge / Preview / producción)

Ejecutar **en cada sitio** tras deploy del release que incluye GEN-13 (CC #20), con el `.env` del servidor ya apuntando a la BD **operativa** de ese tenant:

1. Variables GEN-13 / backup (tabla §1) y `VITE_PAQSUITE_TASKS_BACKUP_BACKEND` en build Vercel/Forge según RDS vs Windows.
2. `php artisan migrate --force` (si el pipeline del host lo incluye).
3. `php artisan paqsuite:installation:update --cliente={tenant} --proyecto=pedidosweb --non-interactive`
4. `php artisan tasks:validate-catalog`
5. Cron/scheduler: `schedule:run` → `tasks:run-due`.
6. Smoke UI: definición `BACKUP_DATABASE` + ejecución manual + fila en historial.

**ERP compartidas / Ankas:** mismo comando solo si el release y el manifiesto aplican a esa BD; **nunca** bootstrap destructivo PedidosWeb. Coordinar ventana y `cliente` con operación.

Forge: añadir pasos 3–4 al script post-deploy existente o correr `sync-gen13-tasks.ps1` adaptado en el servidor Linux (`bash` equivalente de los `artisan` anteriores).

## Smoke manual

1. Login admin → **General → Tareas programadas** (landing en **Definiciones**).
2. Alta definición proceso `BACKUP_DATABASE` (MONO sin picker empresas).
3. Ejecutar manualmente → fila en **Historial**.
4. Forge: `PAQSUITE_TASKS_BACKUP_RDS_GATEWAY=rds` (no `memory`).

## Lab local

```env
PAQSUITE_TASKS_BACKUP_RDS_GATEWAY=memory
# o windows lab:
PAQSUITE_TASKS_BACKUP_BACKEND=windows
PAQSUITE_TASKS_BACKUP_WINDOWS_GATEWAY=memory
```
