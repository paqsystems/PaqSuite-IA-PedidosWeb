# SPEC-001-09 — Tareas programadas (update CC PQ #19)

| Campo | Valor |
|-------|--------|
| **SPEC base** | [SPEC-001-09-tareas-programadas](../../001-Generaliddes/SPEC-001-09-tareas-programadas.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #19, 03/11/2026 |
| **GEN Framework** | 13 — Tareas programadas + proceso `BACKUP_DATABASE` |
| **Última actualización** | 03/11/2026 (Parte G) |

## Delta de alcance

Implementar en PedidosWeb el módulo **Tareas programadas** del SDK y el proceso registry **`BACKUP_DATABASE`** para backups de BD programables, sin duplicar formularios ni handlers de backup en el host.

### In scope

1. **UI GEN-13** — `TaskProcessesPage` (definiciones, ejecuciones); `ProcessPageLayout`; i18n obligatorio.
2. **Menú** — ítem bajo **General** (`grp_general`), `procedimiento` **`gen_tareasprogramadas`**, ruta **`/general/tareas-programadas`**, orden **64**; permiso administrativo (no exponer a perfil Cliente MVP salvo decisión explícita en TR).
3. **Backend** — registrar `TaskProcessRegistry` desde laravel-core; migraciones/SP GEN-13 vía pipeline [GEN-18](../../../06-operacion/plan-adopcion-sdk-framework.md) / `installation:update`.
4. **Proceso `BACKUP_DATABASE`** — único proceso de backup; reglas MONO (PedidosWeb): tenant fijo, destino S3 compartido según runbook Forge; Windows `INIT`/`NOINIT`; RDS polling `rds_task_status`; lock RDS global por instalación — **UI genérica de definiciones**, no pantalla custom en host.
5. **Operación** — variables entorno backup documentadas en runbook deploy; smoke post-definición (ejecución manual o cron de prueba).

### Fuera de scope (CC #19)

- Scheduler premium / orquestación ERP externa ([SPEC-101-22](../../101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md): cron maestros en ERP, no replicar en PedidosWeb).
- Jobs de dominio PedidosWeb no listados en Parte G (registrar en entregas posteriores vía `TaskProcessRegistry` host).
- Mobile: módulo tareas **no** en Capacitor MVP.

### Fuentes normativas

- `docs/00-contexto/_mono/Tareas-programadas/` (modelo, playbook, `backups-programables-mono-multi.md`).
- Cableado GEN-13: regla **44** § GEN-13.

### Criterios de aceptación medibles (SPEC)

- [ ] **CA-SPEC-09-19-01:** Usuario admin accede a Tareas programadas desde General y lista definiciones.
- [ ] **CA-SPEC-09-19-02:** Definición de tarea con proceso `BACKUP_DATABASE` se guarda y puede ejecutarse (manual o según cron).
- [ ] **CA-SPEC-09-19-03:** Host **no** contiene copia del formulario/handler de backup (solo registry + UI SDK).
- [ ] **CA-SPEC-09-19-04:** Runbook operación lista variables S3/credenciales requeridas.

### HU / TR derivadas

- [HU-GEN-13-tareas-programadas](../../../03-historias-usuario/001-Generaliddes/HU-GEN-13-tareas-programadas.md)
- [TR-GEN-13-tareas-programadas](../../../04-tareas/001-Generaliddes/TR-GEN-13-tareas-programadas.md)
