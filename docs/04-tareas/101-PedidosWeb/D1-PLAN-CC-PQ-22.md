# D1 — Plan CC PQ #22 (14/11/2026) — Adopción SDK GEN-13 / Backup

| Campo | Valor |
|-------|--------|
| **Origen** | 00-ControlCalidad-PQ #22 |
| **Parte G** | 14/11/2026 |
| **TR** | [TR-GEN-13-tareas-programadas](../001-Generaliddes/TR-GEN-13-tareas-programadas.md) (update-01 incorporado I 15/11) |
| **Estado plan** | **D ejecutado** 14/11/2026 · **I** 15/11/2026 [I-CC-PQ-GEN-13](./I-CC-PQ-GEN-13-cierre-formal.md) |
| **Jira** | Epic [PIAPEDWEB-50](https://paqsystems.atlassian.net/browse/PIAPEDWEB-50) · Task [PIAPEDWEB-52](https://paqsystems.atlassian.net/browse/PIAPEDWEB-52) |

---

# Plan de implementación - TR-GEN-13-tareas-programadas-update-01

## Alcance entendido

Adopción en **PedidosWeb** de la oleada SDK Framework GEN-13 / Backup: bump Cloudsmith a `laravel-core` **≥ 1.3.18-beta.38** y `react-core` **≥ 2.4.30-beta.19**, ritual de cableado (sin forks), y smoke de comportamientos FW CC **#17–#20** (nombre `-sNN`/`-mNN`, baja física, EXITOSA + rehidratación checkboxes, bloqueo run si inactiva). **No** reimplementar motor/UI GEN-13 ni tocar GEN-15 / Smart Capture.

## Fuentes leídas

- **SPEC:** [SPEC-001-09-tareas-programadas](../../05-open-spec/001-Generaliddes/SPEC-001-09-tareas-programadas.md) (update-02 unificado I 15/11)
- **HU:** [HU-GEN-13-tareas-programadas](../../03-historias-usuario/001-Generaliddes/HU-GEN-13-tareas-programadas.md)
- **TR:** [TR-GEN-13-tareas-programadas](../001-Generaliddes/TR-GEN-13-tareas-programadas.md)
- **Antecedente D1:** [D1-PLAN-CC-PQ-20.md](./D1-PLAN-CC-PQ-20.md)
- **Reglas:** ficha GEN-13 (**44**) · i18n bump (**43**) · Cloudsmith (**45**) · SP (**74**) · mobile (**80**) · sin DROP sin consentimiento
- **Código host (estado actual):**
  - Pins: `laravel-core` **1.3.18-beta.34** · `react-core` **2.4.30-beta.16**
  - FE: `TareasProgramadasSdkRoutes.tsx` — `ProcessPageLayout` + NavLink (Procesos/Definiciones/Ejecuciones/Historial) + páginas SDK; `registerTareasProgramadasI18nResources` en `i18n.ts`
  - BE: `PedidosWebGenCapabilitiesServiceProvider` — `BackupDatabaseProcess` + gateways reales/memory + `RdsDeferredBackupExecutionPoller`; `pedidosweb:tasks-run-due` en `Kernel`
  - Mobile: `/general/tareas-programadas` excluida (`pedidosWebMobilePolicy.test.ts`)
- **Publish Cloudsmith (verificado D1):** `laravel-core` **1.3.18-beta.38** y `react-core` **2.4.30-beta.19** **disponibles** (`composer show` / `npm view`). `CLOUDSMITH_READ_TOKEN` definido en la sesión.
- **Diff Framework source (VERSION = pin objetivo):**
  - FE **no** exporta `TaskScheduledLayout` / `TaskScheduledTabs` (solo `Task*Page` + `registerTareasProgramadasI18nResources`). Host ya cumple con **solapas equivalentes** (`ProcessPageLayout` + nav).
  - BE: guarda `definition inactive` / proceso inactivo en `TaskExecutionService`; naming/checkboxes/hard-delete viven en pin ≥ objetivo (oleada #17–#19; #20 con guarda ya en trunk).

## Impacto esperado

### Base de datos

- Sin DDL host nuevo salvo lo que traiga `paqsuite:installation:update` / deploy SP del pin (CREATE/ALTER; **sin** DROP).
- Tras bump: sync GEN-34 no destructivo si el manifiesto del pin lo exige; smoke contra BD de `.env` sin vaciar tablas.

### Backend

| Área | Acción |
|------|--------|
| `composer.json` + lock | Bump `paqsuite/laravel-core` → **1.3.18-beta.38** (regla **45**, `auth.json` existente) |
| Provider / Kernel / comando | **Revisar** binding post-bump; no reescribir `BackupDatabaseProcess` ni poller; ajustar solo si firma/DI del pin cambió |
| App\ | **Prohibido** copiar handler/formulario |
| Ops | Confirmar `PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING` en Forge/RDS (doc); no tocar Environment Forge en D salvo pedido |

### Frontend

| Área | Acción |
|------|--------|
| `package.json` + lock | Bump `@paqsuite/react-core` → **2.4.30-beta.19** |
| Diff exports | Tras install: `index` del pin; si aparece `TaskScheduledLayout`, migrar chrome; **si no** (estado Framework hoy), **mantener** NavLink + `ProcessPageLayout` |
| i18n | Conservar `registerTareasProgramadasI18nResources`; no agregar `tareasProgramadas.*` GEN a JSON host |
| Mobile | Re-correr test exclusión; sin abrir ruta native |

### Tests

| Tipo | Propuesta |
|------|-----------|
| Unit FE | `pedidosWebMobilePolicy.test.ts` (regresión exclusión) |
| Unit/Feature BE | Tests existentes poller/backup si siguen verdes tras bump; no inventar suite FW |
| Manual / smoke | Checklist TR §4 (= CA HU 22-02…22-07) en PedidosWeb web |

### Documentación

- Actualizar [integracion-framework-sdk.md](../../01-arquitectura/integracion-framework-sdk.md) (hoy desfasado: beta.32 / beta.13).
- Actualizar [runbook-gen-13-backup-pedidosweb.md](../../06-operacion/runbook-gen-13-backup-pedidosweb.md) (pin ≥ beta.38, smoke #17–#20).
- Opcional: nota breve en ritual bump si el host lo documenta aparte.

### DevOps

- Sin cambio de pipeline obligatorio; bumps van en locks commiteables.
- Forge: solo verificar env ya documentados (defer RDS, S3); no deploy productivo en D1/D local.

## Orden de trabajo

1. Bump `laravel-core` **1.3.18-beta.38** + regenerar `composer.lock`.
2. Bump `react-core` **2.4.30-beta.19** + regenerar `package-lock.json`.
3. Diff cableado FE/BE vs pin instalado (exports GEN-13; DI provider).
4. Ajuste mínimo de wrapper **solo** si el pin exige cambio (p. ej. nuevo layout); si no, dejar shell actual.
5. GEN-34 / `installation:update` si el pin trae SP/manifiesto (no DROP).
6. Tests unit acotados + smoke manual §4 TR.
7. Docs pin/runbook.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Breaking change DI/firma entre beta.34→38 o beta.16→19 | Diff provider + compile/tests tras bump; fix mínimo |
| FW CC #20 aún “Implementando” en tablero FW | Guarda inactive ya en `TaskExecutionService` del source; smoke host; si falla, documentar residual Framework (no fork) |
| `TaskScheduledLayout` ausente en SDK | Mantener solapas host (TR: “o solapas SDK equivalentes”); no inventar layout |
| Smoke backup real en BD compartida | Preferir lab/`memory` o entorno dedicado; no DROP; defer RDS según runbook |
| Docs de pin desactualizados | Paso 7 obligatorio del orden |

## Tests a ejecutar

1. `composer show paqsuite/laravel-core` → **1.3.18-beta.38**
2. `npm list @paqsuite/react-core` → **2.4.30-beta.19**
3. Vitest: `pedidosWebMobilePolicy.test.ts`
4. PHPUnit: suite backup/poller existente (si aplica al pin)
5. Smoke manual TR §4 (chrome SDK, checkboxes/nombre, EXITOSA, inactiva, hard delete, native excluida)

## Dudas / bloqueos

- **Ningún bloqueo de publish:** beta.38 / beta.19 están en Cloudsmith.
- **Decisión acotada (no bloquea D):** si tras bump el pin **sigue** sin `TaskScheduledLayout`, se conserva el chrome NavLink actual (ya alineado a ficha práctica). Solo migrar si el export aparece.
- **FW #20:** si el smoke de inactiva falla en el pin publicado, abrir residual Framework; **no** parchear motor en PedidosWeb.

## Confirmación de alcance

- Sin cambio funcional fuera de SPEC/HU/TR: **Sí**
- Sin GEN-15 / Smart Capture / jobs dominio PedidosWeb: **Sí**
- Sin `path`/`file:` al monorepo Framework en git: **Sí**
- Sin bootstrap destructivo / DROP: **Sí**
