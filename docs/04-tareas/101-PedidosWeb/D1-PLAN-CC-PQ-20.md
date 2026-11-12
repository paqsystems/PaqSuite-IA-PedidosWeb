# D1 — Plan CC PQ #20 (11/11/2026) — GEN-13 residual (UI + BACKUP_DATABASE)

| Campo | Valor |
|-------|--------|
| **Origen** | 00-ControlCalidad-PQ #20 |
| **Parte G** | 11/11/2026 |
| **TR** | [TR-GEN-13-tareas-programadas-update](../updates/001-Generaliddes/TR-GEN-13-tareas-programadas-update.md) |
| **Estado plan** | **Listo para parte D** (sin código en D1) |
| **Jira** | Epic [PIAPEDWEB-46](https://paqsystems.atlassian.net/browse/PIAPEDWEB-46) · Task [PIAPEDWEB-48](https://paqsystems.atlassian.net/browse/PIAPEDWEB-48) |

---

# Plan de implementación — TR-GEN-13-tareas-programadas-update

## Alcance entendido

Cerrar el **residual GEN-13** dejado en CC #19: el host ya registra `BACKUP_DATABASE` y expone una ruta, pero la UI no cumple ficha (falta `ProcessPageLayout`, i18n SDK, flujo definiciones/ejecuciones) y el backup queda atado al **gateway in-memory** de lab. CC #20 **no** toca GEN-15, jobs de dominio PedidosWeb, cron ERP ni mobile backup.

Entregable D: módulo operable para admin (definición `BACKUP_DATABASE` + ejecución manual con historial), scheduler documentado, runbook S3/Forge, exclusión native verificada.

## Fuentes leídas

- **SPEC:** [SPEC-001-09-tareas-programadas-update-01](../../05-open-spec/updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update-01.md)
- **HU:** [HU-GEN-13-tareas-programadas-update](../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-13-tareas-programadas-update.md)
- **TR:** [TR-GEN-13-tareas-programadas-update](../updates/001-Generaliddes/TR-GEN-13-tareas-programadas-update.md)
- **TR base / D1 previo:** [TR-GEN-13-tareas-programadas](../001-Generaliddes/TR-GEN-13-tareas-programadas.md) · [D1-PLAN-CC-PQ-19.md](./D1-PLAN-CC-PQ-19.md)
- **Reglas:** GEN-13 ficha **44** · SP **74** · mobile **80** · sin DROP en BD compartida
- **Código host (estado actual):**
  - `frontend/src/features/gen/pages/TareasProgramadasPage.tsx` — solo `TaskProcessesPage`, sin `ProcessPageLayout`
  - `PedidosWebGenCapabilitiesServiceProvider.php` — registry + `InMemoryMonoRdsBackupGateway`
  - `backend/app/Console/Kernel.php` — **sin** `tasks:run-due`
  - `frontend/src/features/i18n/i18n.ts` — **sin** `registerTareasProgramadasI18nResources`
  - Ruta única `/general/tareas-programadas` (sin hijas `nueva` / `editar` / ejecuciones)
- **Pin SDK (sin bump salvo bloqueo):**
  - `paqsuite/laravel-core` **1.3.18-beta.28**
  - `@paqsuite/react-core` **2.4.30-beta.7**
- **Diff pin (lectura `node_modules` / `vendor`):**
  - FE exporta: `ProcessPageLayout`, `TaskProcessesPage`, `TaskDefinitionsPage`, `TaskDefinitionFormPage`, `TaskExecutionsPage`, `registerTareasProgramadasI18nResources`
  - `TaskProcessesPage` = catálogo de procesos (no sustituye definiciones/ejecuciones)
  - BE: `BackupDatabaseProcess`, SP repos, artisan **`tasks:run-due`** (registrado; host no lo programa)
  - **No hay** implementación AWS/RDS concreta de `MonoRdsBackupGateway` en el pin — solo interfaz + `InMemory` del host

## Impacto esperado

### Base de datos

- Sin migraciones host nuevas salvo las que traiga `paqsuite:deploy-sdk-sql` / `installation:update` para tablas/SP **GEN-13** (CREATE/ALTER idempotente; **sin** DROP).
- Smoke: verificar tablas de definiciones/ejecuciones tras deploy SP; seed/alta operativa de al menos una definición `BACKUP_DATABASE` (SQL documentado o UI).
- BD objetivo dev: la configurada en `backend/.env` — operaciones no destructivas por defecto.

### Backend

| Área | Acción probable |
|------|-----------------|
| `PedidosWebGenCapabilitiesServiceProvider` | Condicionar bind `MonoRdsBackupGateway`: in-memory solo `local`/`testing`; prod/staging → adapter real (ver bloqueo B1) |
| `InMemoryMonoRdsBackupGateway` | Alinear firma con interfaz (`overwrite` en `startExport`) |
| `Kernel.php` | Programar `tasks:run-due` (frecuencia acordada runbook, p. ej. cada minuto con `withoutOverlapping`) |
| Rutas | Ya vía package si `PAQSUITE_TASKS_ROUTES_ENABLED=true`; validar middleware tenant + Sanctum |
| Ops | `php artisan tasks:validate-catalog` post-wiring |
| Forge | Documentar env S3/RDS/AWS; no ejecutar backup real contra ERP compartido sin consentimiento |

### Frontend

| Área | Acción probable |
|------|-----------------|
| `TareasProgramadasPage.tsx` | Patrón **página delgada**: `ProcessPageLayout` + título (`t('menu.tareasProgramadas')` o caption menú cuando exista helper) |
| Rutas | Ampliar árbol `/general/tareas-programadas/*` según SDK: definiciones (default), `nueva`, `:id/editar` (`TaskDefinitionFormPage`), opcional ejecuciones / catálogo procesos |
| i18n | `registerTareasProgramadasI18nResources(i18n, 'translation')` en bootstrap; **no** duplicar `tareasProgramadas.*` en JSON host |
| Platform | Unificar headers con patrón instalación (`cliente` + tenancy `single`); evaluar helper equivalente a `buildPlatformHeaders` del SDK si el export está en pin |
| Mobile | Confirmar exclusión (`pedidosWebMobilePolicy.test.ts` ya espera `false`); sin cambio salvo regresión |
| Menú | Conservar `gen_tareasprogramadas`; `menu.tareasProgramadas` ya en 5 locales |

### Tests

| Tipo | Propuesta |
|------|-----------|
| Vitest | Actualizar/ampliar `pedidosWebMobilePolicy.test.ts` si cambian prefijos; smoke ligero de wrapper (render con mock SDK) solo si aporta AC-T-20-01 |
| PHPUnit Feature | API GEN-13: listar procesos incluye `BACKUP_DATABASE`; crear definición + `run` con gateway fake (skip sin tablas SP) |
| Manual | Checklist smoke CC #20: login admin → definiciones → alta BACKUP_DATABASE → ejecutar → historial |

### Documentación

- Nuevo o append: `docs/06-operacion/runbook-gen-13-backup-pedidosweb.md` (vars `.env.example`, cron Forge, S3 ARN, smoke, gateway lab vs prod).
- Actualizar estado en TR-update / enlaces desde CC #20 cuando cierre D.
- Referencia normativa backup: SPEC Framework `SPEC-001-13-update-backups-windows-rds` (ruta citada en CC; copiar solo contrato, no handlers).

### DevOps

- `.env.example`: ya tiene `PAQSUITE_TASKS_ROUTES_ENABLED`, `TASKS_BACKUP_S3_ARN_PREFIX`; documentar AWS/RDS adicionales en runbook.
- Forge: scheduled job `php artisan schedule:run` + entrada `tasks:run-due` en `Kernel`.
- GEN-34: `installation:update` / `paqsuite:deploy-sdk-sql` en pipeline local documentado (sin correr destructivo en chat).

## Orden de trabajo

1. **Verificación pin (sin bump)** — Confirmar exports y props en `react-core` 2.4.30-beta.7; listar rutas hijas que exige `TaskDefinitionsPage` (`onNew` / `onEdit`). Diff contra template `create-app` si está disponible en Framework (referencia cableado).
2. **Bootstrap i18n** — `registerTareasProgramadasI18nResources` tras `i18n.init` en `frontend/src/features/i18n/i18n.ts`.
3. **Router GEN-13** — En `pedidosWebRoutes.tsx`, rutas anidadas bajo `/general/tareas-programadas` con wrappers delgados (layout común `ProcessPageLayout` + `data-testid="page-tareas-programadas"` en raíz o por vista según AC).
4. **Sustituir entrada incorrecta** — Dejar de usar solo `TaskProcessesPage` como pantalla principal; pantalla por defecto = **definiciones** (`TaskDefinitionsPage`) o shell con tabs si el patrón create-app lo define en D.
5. **Platform/token** — Mismo criterio que emisiones: token Sanctum + `cliente` tenant; evitar duplicar lógica fuera del SDK.
6. **Backend gateway** — Feature flag por `APP_ENV` / `TASKS_BACKUP_GATEWAY=fake|aws` (nombre a fijar en D): lab → `InMemoryMonoRdsBackupGateway`; no-lab → adapter RDS (B1).
7. **Scheduler** — `Kernel::schedule` → `tasks:run-due` + nota en runbook Forge.
8. **SQL GEN-13** — `paqsuite:deploy-sdk-sql` y/o `installation:update` (con consentimiento explícito si implica riesgo en BD compartida); `tasks:validate-catalog`.
9. **Smoke + tests** — Definición + ejecución manual en dev; PHPUnit/Vitest según tabla.
10. **Docs + Jira D** — Runbook, comentario PIAPEDWEB-48, preparar F1.

## Riesgos

| # | Riesgo | Mitigación |
|---|--------|------------|
| R1 | Host monta solo `TaskProcessesPage`; no se pueden crear definiciones BACKUP | Ampliar rutas + `TaskDefinitionsPage` / `TaskDefinitionFormPage` (hallazgo D1) |
| R2 | SPEC pide “gateway real SDK” pero pin solo define **interfaz** `MonoRdsBackupGateway` | Bloqueo B1: implementación infra host delgada (AWS RDS API) o bump Framework si publican clase |
| R3 | `InMemoryMonoRdsBackupGateway` desalineado con interfaz (`overwrite`) | Corregir en D; PHPStan/tests |
| R4 | Sin SP/tablas GEN-13 en BD → API 500 | Deploy SQL + skip tests documentado |
| R5 | `tasks:run-due` sin scheduler → AC-T-20-04 falla en prod | `Kernel` + runbook Forge |
| R6 | Backup real contra `Ankas_del_sur` / RDS prod | Smoke solo manual en dev; gateway fake por defecto local |
| R7 | PedidosWeb sin `resolveMenuCaption` / menú SDK | Título vía `t('menu.tareasProgramadas')` (ya en locales); opcional mejora futura menú GEN-07 |
| R8 | CC #19 dejó GEN-34 / `label_key` menú pendiente | Fuera de CC #20 salvo bloqueo de permisos; anotar en smoke |

## Tests a ejecutar

| Comando / acción | Cuándo |
|------------------|--------|
| `php artisan tasks:validate-catalog` | Tras wiring BE |
| PHPUnit (nuevo o existente Feature GEN-13) | Parte E |
| `npm run test` — `pedidosWebMobilePolicy.test.ts` | Tras rutas |
| Smoke manual checklist (definición + run + historial) | Parte D/F1 |
| Opcional: `php artisan test --filter=…` emission si no toca GEN-13 | Regresión |

## Dudas / bloqueos

| ID | Tema | Decisión necesaria |
|----|------|-------------------|
| B1 | **Gateway RDS producción** | **Resuelto (D):** `RdsSqlServerBackupGateway` (msdb, igual Partes); lab → `PAQSUITE_TASKS_BACKUP_RDS_GATEWAY=memory` |
| B2 | **Perfil backup PedidosWeb** | **Resuelto (D):** default **`rds`**; Windows vía `PAQSUITE_TASKS_BACKUP_BACKEND=windows` + `WindowsSqlServerMultiDiskBackupGateway`; UI `backupBackend` desde `VITE_PAQSUITE_TASKS_BACKUP_BACKEND` (carpeta solo en Windows) |
| B3 | **Ruta por defecto** | **Resuelto (D):** redirect a **`/general/tareas-programadas/definiciones`** |
| B4 | **installation:update en dev** | ¿Ejecutar en esta D sobre `DB_DATABASE` actual o solo `deploy-sdk-sql`? Requiere consentimiento si hay riesgo en BD ERP |

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR CC #20:** **Sí**
- **Explícitamente fuera:** GEN-15, rollout 6 consultas emisión, jobs dominio, import ERP, backup mobile, bump SDK salvo bloqueo documentado en B1
- **No se amplía** a reimplementar formulario/handler backup en `App\` ni segunda API de tareas

---

## Pin SDK (acordado — sin bump en D1)

| Paquete | Versión | Notas |
|---------|---------|--------|
| `paqsuite/laravel-core` | **1.3.18-beta.28** | `BackupDatabaseProcess`, `tasks:run-due`, SP repos |
| `@paqsuite/react-core` | **2.4.30-beta.7** | Páginas tasks + `registerTareasProgramadasI18nResources` |

## Gap respecto D1-PLAN-CC-PQ-19 (Fase 3)

| Ítem Fase 3 #19 | CC #20 |
|-----------------|--------|
| Definición BACKUP + smoke | **In scope** |
| Runbook S3 / cron | **In scope** |
| `ProcessPageLayout` + flujo definiciones | **In scope** (corrección wrapper) |
| Gateway solo lab vs prod | **In scope** |
| Rollout 6 consultas emisión | **Fuera** (#19) |

## Próximo paso (parte D)

1. Resolver **B1** (gateway prod) con usuario o lectura Framework antes de bind prod.
2. Implementar orden §7–9 con commits solo si el usuario autoriza.
3. Parte **E** tests + checklist smoke; **F1** con evidencia.

---

## Estado implementación host (actualizado 11/11/2026)

| Tema | Entregado |
|------|-----------|
| UI GEN-13 | Solapas SDK (`TaskScheduledLayout`), rutas `/ejecuciones`, i18n register |
| Backend | `PedidosWebGenCapabilitiesServiceProvider`, gateway RDS/Windows + lab memory |
| Scheduler | **`pedidosweb:tasks-run-due`** (poll `RdsDeferredBackupExecutionPoller` + due) |
| SDK pin | `paqsuite/laravel-core` **1.3.18-beta.32**, `@paqsuite/react-core` **2.4.30-beta.13** |
| Manifiesto | `pedidosweb-1.3.18-beta.32.json` |
| Runbook | [runbook-gen-13-backup-pedidosweb.md](../../06-operacion/runbook-gen-13-backup-pedidosweb.md) |
| Forge RDS | `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true`, gateway `rds` |

*Generado: Parte D1 · 11/11/2026 · tabla de estado añadida tras implementación D.*
