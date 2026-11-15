# Pull Request Report

**Base:** `develop`  
**Compare:** `v1.2.0`  
**Modo SDK:** deploy (Cloudsmith: `laravel-core` **1.3.18-beta.38** / `react-core` **2.4.30-beta.19**)

## Resumen

Cierra GEN-13 / `BACKUP_DATABASE` en PedidosWeb (CC #20/#21/#22): bump SDK Cloudsmith, tests CC #22, unificación OpenSpec F1/F/I. Incluye un ajuste del asistente de carga (confirmación de cambio de cliente).

## SPEC / HU / TR relacionadas

- SPEC: SPEC-001-09-tareas-programadas (Finalizado)
- HU: HU-GEN-13-tareas-programadas (Finalizado)
- TR: TR-GEN-13-tareas-programadas (Finalizado)
- F1: F1-CC-PQ-22-verificacion-agente.md (Aprobado con observaciones)
- I: I-CC-PQ-GEN-13-cierre-formal.md
- Jira: PIAPEDWEB-10 / 46–52 / 49

## Cambios realizados

### Backend
- Pin `paqsuite/laravel-core` 1.3.18-beta.38
- PHPUnit GEN-13 CC #22 (nombre backup, destroy, execution, mail)

### Frontend
- Pin `@paqsuite/react-core` 2.4.30-beta.19
- E2E tareas programadas CC #22 + mocks
- Asistente: `isPendingChoiceReply` para confirmación sí/no de cambio de cliente

### Base de datos
- Sin migraciones host nuevas. GEN-34 / `installation:update` si el pin trae SP de tareas.

### Tests
- PHPUnit Gen13Cc22* (sesión previa 8/8)
- Vitest `pedidosWebMobilePolicy` (7/7)
- E2E `tareas-programadas-cc22.spec.ts`

### Documentación
- SPEC/HU/TR GEN-13 unificados; updates eliminados
- CC PQ #20/#21/#22 Finalizado; #19 residual GEN-15
- Runbook GEN-13 + pins

## Observaciones deploy (regla 17)

| Ítem | Preview |
|------|---------|
| migrate host | No (sin DDL nuevo) |
| GEN-34 | Sí — `paqsuite:installation:update` / `seed-deploy` si el script Forge no lo corre |
| .env nuevo | No (GEN-13 ya en Environment) |
| Solo código | Pins + FE/BE tests + docs |

Forge Preview: `demopedidosweb` (rama `develop`).

### Smoke post-deploy

- [ ] GET `/api/v1/health`
- [ ] Login Preview → General → Tareas programadas (solapas SDK)
- [ ] Definición BACKUP_DATABASE: tildes de nombre persisten al reeditar
- [ ] Native: ruta sigue excluida
