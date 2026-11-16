# HU-GEN-13 — Tareas programadas y backup BD

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-13-tareas-programadas |
| **SPEC origen** | [SPEC-001-09-tareas-programadas](../../05-open-spec/001-Generaliddes/SPEC-001-09-tareas-programadas.md) |
| **Épica** | 001 — Generalidades / GEN-13 |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **TR relacionada** | [TR-GEN-13-tareas-programadas](../../04-tareas/001-Generaliddes/TR-GEN-13-tareas-programadas.md) |
| **Jira** | [PIAPEDWEB-51](https://paqsystems.atlassian.net/browse/PIAPEDWEB-51) (CC #22) · [PIAPEDWEB-47](https://paqsystems.atlassian.net/browse/PIAPEDWEB-47) (CC #20) · Epic SPEC [PIAPEDWEB-10](https://paqsystems.atlassian.net/browse/PIAPEDWEB-10) |
| **Unificación I** | 15/11/2026 — CC #19 + #20 + #22. F1 [F1-CC-PQ-22](../../04-tareas/101-PedidosWeb/F1-CC-PQ-22-verificacion-agente.md) |
| **Origen** | 00-ControlCalidad-PQ #19 (03/11/2026); residuales #20 (11/11) y #22 (14/11) |

## Narrativa

Como **administrador de sistemas**,  
quiero **programar y operar tareas de mantenimiento** (incluido backup de base de datos) con el módulo GEN-13 del SDK,  
para **automatizar operación** sin pantallas host paralelas ni scripts manuales en el servidor.

## Alcance incluido

- Módulo **Tareas programadas** SDK (`gen_tareasprogramadas`, `/general/tareas-programadas`) con `ProcessPageLayout` y solapas Procesos / Definiciones / Ejecuciones / Historial.
- i18n vía `registerTareasProgramadasI18nResources` + `menu.tareasProgramadas`.
- Backend DualPath SP; scheduler `pedidosweb:tasks-run-due`; pin `laravel-core` **≥ 1.3.18-beta.38** / `react-core` **≥ 2.4.30-beta.19**.
- Proceso **`BACKUP_DATABASE`** (`BackupDatabaseProcess`); MONO sin picker; S3; Windows/RDS; defer polling; in-memory solo lab.
- Comportamientos SDK FW CC #17–#20 (nombre `-sNN`/`-mNN`, baja física, EXITOSA + checkboxes, bloqueo si inactiva).
- Ruta **excluida** en native.

## Fuera de alcance

- Cron ERP de importación maestros; jobs PedidosWeb distintos de backup; mobile; GEN-15.

## Criterios de aceptación

- [x] **CA-01:** Admin abre Tareas programadas desde General y ve definiciones/ejecuciones dentro de `ProcessPageLayout` (título i18n, sin `<h1>` ES).
- [x] **CA-02:** No queda una segunda UI/API host de tareas; `TareasProgramadasPage` solo delega al SDK.
- [x] **CA-03:** Crear definición `BACKUP_DATABASE` sin picker de empresas; ejecución manual con estado en historial.
- [x] **CA-04:** En Forge/RDS no se usa el gateway in-memory; runbook lista S3, `pedidosweb:tasks-run-due` y `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true`.
- [x] **CA-05:** La ruta no aparece en menú/native Capacitor.
- [x] **CA-06:** Pins `composer show` / `npm list` ≥ objetivo; docs de pin/runbook coinciden.
- [x] **CA-07:** Checkboxes fecha/día/mes se guardan y rehidratan; nombre con `-sNN`/`-mNN` cuando corresponde.
- [x] **CA-08:** Ejecución manual con backup OK cierra en **EXITOSA** (defer vía scheduler si aplica).
- [x] **CA-09:** Definición inactiva no permite ejecutar ni simular manualmente (UI + API).
- [x] **CA-10:** Eliminar definición = baja física + confirmación; si hay historial, mensaje destacado.

## Historial de unificación (Parte I 15/11/2026)

Incorpora y elimina `HU-GEN-13-tareas-programadas-update.md` (CC #20) y `HU-GEN-13-tareas-programadas-update-01.md` (CC #22).
