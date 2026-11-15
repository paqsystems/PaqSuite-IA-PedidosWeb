# SPEC-001-09 — Tareas programadas y backup BD

Jira: PIAPEDWEB-10

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-GEN-13-tareas-programadas](../../03-historias-usuario/001-Generaliddes/HU-GEN-13-tareas-programadas.md) |
| **TR relacionadas** | [TR-GEN-13-tareas-programadas](../../04-tareas/001-Generaliddes/TR-GEN-13-tareas-programadas.md) |
| **Estado** | Finalizado |
| **Revisión A1** | Documental (2026-05-28) |
| **Unificación I** | 15/11/2026 — incorpora CC #19 (GEN-13) · #20 · #22. Cierre F1/F: [F1-CC-PQ-22](../../04-tareas/101-PedidosWeb/F1-CC-PQ-22-verificacion-agente.md) · [F-CC-PQ-22](../../04-tareas/101-PedidosWeb/F-CC-PQ-22-cierre-formal.md) · [I-CC-PQ-GEN-13](../../04-tareas/101-PedidosWeb/I-CC-PQ-GEN-13-cierre-formal.md) |
| **GEN Framework** | 13 — Tareas programadas + proceso `BACKUP_DATABASE` |

## Objetivo

Definir el contrato de PedidosWeb (host MONO) para el módulo de **tareas programadas** como capacidad de soporte operativo, adoptando GEN-13 del SDK (`TaskProcessesPage` / definiciones / ejecuciones) y el proceso registry **`BACKUP_DATABASE`**, sin duplicar formularios ni handlers de backup en el host.

## Fuentes

Subcarpeta: `docs/00-contexto/_mono/Tareas-programadas/`

- `_manual_onboarding_tareas_programadas.md`
- `diagrama_mermaid_tareas_programadas.md`
- `modelo_datos_detallado_tareas_programadas.md`
- `modelo_relacional_sqlserver_tareas_programadas.md`
- `playbook_equipo.md`
- `tareas_programadas_premium.md`
- `tareas_programadas.md`
- `template_proyecto_tasks.md`
- `backups-programables-mono-multi.md` — proceso backup BD (Mono RDS→S3 / Multi tradicional)

Normativa host: ficha GEN-13 en regla **44**; SoT Framework SPEC-001-13 + updates CC FW #17–#20 + defer RDS 11/11.

## Alcance (host vigente)

1. **UI GEN-13** — pantallas SDK (`TaskProcessesPage`, definiciones, ejecuciones, historial) con chrome **`ProcessPageLayout`**; títulos vía i18n (`t` / `resolveMenuCaption`); `registerTareasProgramadasI18nResources`. Si el pin no exporta `TaskScheduledLayout`, el host usa solapas equivalentes (`NavLink` Procesos → Definiciones → Ejecuciones → Historial). **Prohibido** `<h1>` ES o segunda pantalla host.
2. **Menú** — ítem bajo **General** (`grp_general`), `procedimiento` **`gen_tareasprogramadas`**, ruta **`/general/tareas-programadas`**, orden **64**; permiso administrativo MVP (no perfil Cliente).
3. **Contrato host** — token + `X-Paq-Cliente`; `tenancyMode` MONO (`single`); namespace i18n alineado al bootstrap. **No** fork de catálogos `tareasProgramadas.*` / `menu.*` GEN en JSON host (sí `menu.tareasProgramadas` de dominio).
4. **Backend DualPath** — rutas laravel-core (`PAQSUITE_TASKS_ROUTES_ENABLED`); `TaskProcessRegistry`; repositorios/SP DualPath; **no** Eloquent ad-hoc (regla 74). Migraciones/SP vía GEN-34 / `installation:update` (CREATE/ALTER, sin DROP).
5. **Scheduler** — cron `schedule:run` + comando host **`pedidosweb:tasks-run-due`** cada minuto (primero `RdsDeferredBackupExecutionPoller`, luego due).
6. **Mobile** — ruta **excluida** (`pedidosWebMobilePolicy`).
7. **Proceso `BACKUP_DATABASE`** — `BackupDatabaseProcess` del laravel-core; **prohibido** copiar formulario o handler a `App\`. MONO: tenant fijo, **sin** picker multi-empresa; destino S3 (`TASKS_BACKUP_S3_ARN_PREFIX`). Windows `INIT`/`NOINIT`; RDS sobrescribe, polling `rds_task_status`, **lock RDS global por instalación**. Manual HTTP en RDS: **`deferManualRdsPolling`** + poller (SDK ≥ **1.3.18-beta.32**; pin vigente **≥ 1.3.18-beta.38**). Gateway in-memory **solo lab**; Forge/RDS usa gateway DualPath del host.
8. **Comportamiento SDK (pin ≥ laravel-core 1.3.18-beta.38 / react-core 2.4.30-beta.19)** — sin reimplementar:

| Framework CC | Comportamiento en host |
|--------------|------------------------|
| **#17** | Nombre backup: día `-sNN` / mes `-mNN` |
| **#18** | Eliminación **física** de definiciones + confirmación (énfasis si hay historial) |
| **#19** | Estado final **EXITOSA** tras éxito real; rehidratación de checkboxes de nombre |
| **#20** | Bloqueo de ejecución/simulación **manual** si la definición (o proceso) está **inactiva** |

9. **Operación** — [runbook-gen-13-backup-pedidosweb.md](../../06-operacion/runbook-gen-13-backup-pedidosweb.md).

## Fuera de alcance

- Scheduler premium / orquestación ERP externa (SPEC-101-22: cron maestros en ERP).
- Jobs de dominio PedidosWeb distintos de backup.
- Backup / módulo tareas en Capacitor.
- Diseñador / emisión **GEN-15** (CC PQ **#19** residual).
- Reimplementar motor/UI GEN-13; defectos de SDK = Framework.

## Pin SDK vigente

`paqsuite/laravel-core` **≥ 1.3.18-beta.38** · `@paqsuite/react-core` **≥ 2.4.30-beta.19**. Locks Cloudsmith (regla **45**); **sin** `path`/`file:` al monorepo.

## Criterios de aceptación medibles

- [x] **CA-SPEC-09-01:** Admin abre Tareas programadas desde General y lista definiciones/procesos/ejecuciones/historial con chrome de proceso i18n.
- [x] **CA-SPEC-09-02:** Definición `BACKUP_DATABASE` se guarda (MONO, sin picker) y la ejecución manual deja fila en historial; cierre **EXITOSA** si el backup OK (defer RDS vía scheduler).
- [x] **CA-SPEC-09-03:** Host **no** copia handler/formulario de backup; in-memory solo lab; DualPath SP.
- [x] **CA-SPEC-09-04:** Runbook lista S3, `pedidosweb:tasks-run-due`, `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING`; ruta **excluida** native.
- [x] **CA-SPEC-09-05:** Pins ≥ objetivo; smoke FW #17–#20 (nombre día/mes + tildes al reeditar; baja física; run bloqueado si inactiva).

## Historial de unificación (Parte I 15/11/2026)

Incorpora y elimina:

- `updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update.md` (CC #19)
- `updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update-01.md` (CC #20)
- `updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update-02.md` (CC #22)
