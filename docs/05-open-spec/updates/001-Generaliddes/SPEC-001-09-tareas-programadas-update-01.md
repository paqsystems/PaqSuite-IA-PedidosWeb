# SPEC-001-09 — Tareas programadas (update CC PQ #20)

| Campo | Valor |
|-------|--------|
| **SPEC base** | [SPEC-001-09-tareas-programadas](../../001-Generaliddes/SPEC-001-09-tareas-programadas.md) |
| **SPEC-update previo** | [SPEC-001-09-tareas-programadas-update](./SPEC-001-09-tareas-programadas-update.md) (CC #19; residual GEN-13 aislado aquí) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #20, 11/11/2026 |
| **Jira** | [PIAPEDWEB-46](https://paqsystems.atlassian.net/browse/PIAPEDWEB-46) |
| **GEN Framework** | 13 — Tareas programadas + proceso `BACKUP_DATABASE` |
| **Última actualización** | 11/11/2026 (Parte G) |

## Delta de alcance (respecto del update CC #19)

El update #19 pidió adoptar GEN-13 + `BACKUP_DATABASE`. En host quedó un **wrapper incompleto** (`TareasProgramadasPage` monta `TaskProcessesPage` **sin** `ProcessPageLayout`) y el backup usa gateway **in-memory** de lab. Este update **aísla** ese residual: reemplazar el proceso cableado hoy por el contrato SDK y completar Backup. **GEN-15** permanece en CC #19 / [SPEC-001-06-emision-update](./SPEC-001-06-emision-update.md).

### In scope (CC #20)

1. **UI canónica GEN-13** — montar `TaskProcessesPage` (definiciones + ejecuciones) con chrome **`ProcessPageLayout`**; títulos vía i18n (`t` / `resolveMenuCaption`); **prohibido** `<h1>` literal ES o segunda pantalla host.
2. **Contrato host** — token + headers plataforma (`X-Paq-Cliente`); `tenancyMode` MONO (`single`); namespace i18n alineado al bootstrap (`register*` del SDK si el pin lo exporta). **No** fork de catálogos `tasks.*` / `menu.*` GEN en JSON host.
3. **Backend DualPath** — rutas laravel-core (`PAQSUITE_TASKS_ROUTES_ENABLED`); `TaskProcessRegistry` del SDK; repositorios/SP DualPath; **no** Eloquent ad-hoc ni SQL de negocio (regla 74). Completar `installation:update` / deploy SP GEN-13.
4. **Scheduler** — disparo operativo documentado e instalado (`tasks:run-due` / Laravel scheduler / cron Forge). Sin este disparo la UI no ejecuta definiciones en producción.
5. **Menú / permisos** — conservar `gen_tareasprogramadas` / `/general/tareas-programadas` / `grp_general` orden 64; permiso administrativo MVP (no perfil Cliente); **mobile:** ruta **fuera** de native (`pedidosWebMobilePolicy`).
6. **Retiro del proceso actual** — el wrapper `TareasProgramadasPage` solo puede existir como página delgada que delega 100 % al export SDK (más `ProcessPageLayout`).
7. **Proceso `BACKUP_DATABASE`** — registrar con `BackupDatabaseProcess` del laravel-core; **prohibido** copiar formulario o handler a `App\`. MONO: tenant fijo, **sin** picker multi-empresa; destino S3 (`TASKS_BACKUP_S3_ARN_PREFIX`). Windows `INIT`/`NOINIT`; RDS sobrescribe, polling `rds_task_status`, **lock RDS global por instalación**. Gateway in-memory **solo lab**; Forge/RDS usa gateway real del SDK.
8. **Operación** — runbook Forge/GEN-34 (variables S3, ventanas, smoke). Al menos una **definición** `BACKUP_DATABASE` y **ejecución manual** con fila en historial.

### Fuera de scope (CC #20)

- Jobs de dominio PedidosWeb distintos de backup (p. ej. import Excel batch).
- Cron ERP de maestros ([SPEC-101-22](../../101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md)).
- Backup mobile.
- Diseñador / emisión GEN-15 (CC #19).

### Fuentes normativas

- Ficha GEN-13: regla **44** (`44-host-framework-gen-cableado-componentes.mdc`).
- [backups-programables-mono-multi.md](../../../00-contexto/_mono/Tareas-programadas/backups-programables-mono-multi.md) (SoT host; contrato Windows/RDS del Framework, sin copiar handlers).
- Pin SDK vigente: `TaskProcessesPage`, `TaskProcessRegistry`, `BackupDatabaseProcess`.

### Criterios de aceptación medibles (SPEC)

- [ ] **CA-SPEC-09-20-01:** Tareas programadas se abre con `ProcessPageLayout`; no hay `<h1>` ES ni UI host duplicada.
- [ ] **CA-SPEC-09-20-02:** Definición `BACKUP_DATABASE` se crea (MONO, sin picker) y la ejecución manual deja fila en historial.
- [ ] **CA-SPEC-09-20-03:** Host **no** copia handler/formulario de backup; in-memory solo en lab.
- [ ] **CA-SPEC-09-20-04:** Cron/`tasks:run-due` y variables S3 documentadas en runbook; ruta **excluida** en native.

### HU / TR derivadas

- [HU-GEN-13-tareas-programadas-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-13-tareas-programadas-update.md)
- [TR-GEN-13-tareas-programadas-update](../../../04-tareas/updates/001-Generaliddes/TR-GEN-13-tareas-programadas-update.md)
