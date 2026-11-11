# HU-GEN-13 — Tareas programadas y backup BD (update CC PQ #20)

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-13-tareas-programadas-update |
| **HU base** | [HU-GEN-13-tareas-programadas](../../001-Generaliddes/HU-GEN-13-tareas-programadas.md) |
| **SPEC origen** | [SPEC-001-09-tareas-programadas-update-01](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update-01.md) · [SPEC-001-09-tareas-programadas-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update.md) · base [SPEC-001-09-tareas-programadas](../../../05-open-spec/001-Generaliddes/SPEC-001-09-tareas-programadas.md) |
| **Épica** | 001 — Generalidades / GEN-13 |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **TR relacionada** | [TR-GEN-13-tareas-programadas-update](../../../04-tareas/updates/001-Generaliddes/TR-GEN-13-tareas-programadas-update.md) |
| **Jira** | [PIAPEDWEB-47](https://paqsystems.atlassian.net/browse/PIAPEDWEB-47) |
| **Origen** | 00-ControlCalidad-PQ #20 — 11/11/2026 |

## Origen

Control de calidad PQ 11/11/2026 — ítems **GEN-13 · Reemplazar proceso de tareas programadas por cableado SDK Framework** y **GEN-13 · Incorporar proceso Backup (`BACKUP_DATABASE`)**. Residual de CC #19 (wrapper host incompleto + smoke de backup pendiente).

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **administrador de sistemas**,  
quiero **usar el módulo de Tareas programadas del SDK** (con chrome de proceso y backup de BD real),  
para **operar definiciones y ejecuciones** sin una pantalla host paralela ni un gateway de laboratorio en Forge.

## Alcance incluido

- Reemplazo del wrapper actual: `TaskProcessesPage` + `ProcessPageLayout`; i18n; token + `X-Paq-Cliente`; MONO `single`.
- Backend DualPath SP / `installation:update`; scheduler `tasks:run-due`.
- Menú `gen_tareasprogramadas` conservado; no native.
- `BACKUP_DATABASE` vía `BackupDatabaseProcess` del laravel-core (Windows/RDS, S3, lock RDS); in-memory **solo lab**.
- Smoke: una definición + ejecución manual + historial.

## Fuera de alcance

- Jobs PedidosWeb distintos de backup; cron ERP maestros; backup mobile; GEN-15.

## Criterios de aceptación

- [ ] **CA-20-01:** Admin abre Tareas programadas desde General y ve definiciones/ejecuciones dentro de `ProcessPageLayout` (título i18n, sin `<h1>` ES).
- [ ] **CA-20-02:** No queda una segunda UI/API host de tareas; `TareasProgramadasPage` solo delega al SDK.
- [ ] **CA-20-03:** Crear definición `BACKUP_DATABASE` sin picker de empresas; ejecución manual con estado en historial.
- [ ] **CA-20-04:** En Forge/RDS no se usa el gateway in-memory; runbook lista S3/`tasks:run-due`.
- [ ] **CA-20-05:** La ruta no aparece en menú/native Capacitor.
