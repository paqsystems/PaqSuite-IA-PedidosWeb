# HU-GEN-13 — Tareas programadas y backup BD

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-13-tareas-programadas |
| **SPEC origen** | [SPEC-001-09-tareas-programadas-update](../../05-open-spec/updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update.md) · base [SPEC-001-09-tareas-programadas](../../05-open-spec/001-Generaliddes/SPEC-001-09-tareas-programadas.md) |
| **Épica** | 001 — Generalidades / GEN-13 |
| **Prioridad** | Must |
| **Estado** | En Control Calidad |
| **TR relacionada** | [TR-GEN-13-tareas-programadas](../../04-tareas/001-Generaliddes/TR-GEN-13-tareas-programadas.md) |
| **Origen** | 00-ControlCalidad-PQ #19 — 03/11/2026 |

## Origen

Control de calidad PQ 03/11/2026 — ítems **GEN-13 · Tareas programadas** y **Backups programados**.

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **administrador de sistemas**,  
quiero **programar tareas de mantenimiento** (incluido backup de base de datos) desde el portal,  
para **automatizar operación** sin scripts manuales en el servidor.

## Alcance incluido

- Módulo **Tareas programadas** SDK (`gen_tareasprogramadas`, `/general/tareas-programadas`).
- Definiciones, ejecuciones, i18n `ProcessPageLayout`.
- Proceso **`BACKUP_DATABASE`** disponible en selector de proceso (UI SDK).
- Despliegue SQL/migraciones GEN-13 con installation update.

## Fuera de alcance

- Cron ERP de importación maestros.
- Mobile.

## Criterios de aceptación

- [ ] **CA-01:** Admin abre Tareas programadas desde General.
- [ ] **CA-02:** Crear definición con proceso `BACKUP_DATABASE` y parámetros MONO (sin picker multi-empresa).
- [ ] **CA-03:** Ejecución manual completa con estado visible en historial de ejecuciones.
- [ ] **CA-04:** No existe pantalla host duplicada de backup.
- [ ] **CA-05:** Runbook deploy documenta variables S3/backup.
