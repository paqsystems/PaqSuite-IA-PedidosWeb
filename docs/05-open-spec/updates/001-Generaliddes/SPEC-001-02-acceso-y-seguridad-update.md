# SPEC-001-02-update — Suspender logout por inactividad (hasta SDK Framework)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-001-02-acceso-y-seguridad-update |
| **SPEC base** | [SPEC-001-02-acceso-y-seguridad](../../001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #16 · **12/09/2026** |
| **HU relacionadas** | [HU-GEN-02-expiracion-inactividad-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-02-expiracion-inactividad-update.md) |
| **TR relacionadas** | [TR-GEN-02-expiracion-inactividad-update](../../../04-tareas/updates/001-Generaliddes/TR-GEN-02-expiracion-inactividad-update.md) |
| **Última actualización** | 2026-09-12 |

## Objetivo

**Suspender momentáneamente** el cierre de sesión por inactividad en PedidosWeb (detector frontend / `SessionLifecycleManager`), sin eliminar el código ni el contrato `MinutosWeb` / `inactivityTimeoutMinutes`. La funcionalidad se **restablecerá** cuando el host integre el SDK de Framework (GEN de sesión / login compartido).

## Decisiones (CC PQ #16)

| ID | Tema | Decisión |
|----|------|----------|
| D1-31 | Logout por inactividad | **Deshabilitado** en runtime del host mientras `inactivityLogoutEnabled === false`. El motor (`createInactivityController`, `useInactivityTimeout`, exposición de `MinutosWeb`) permanece en el repo. Logout manual y 401 por token inválido **no** cambian. |

## In scope

- Flag/constante de producto que desactiva el timer de inactividad en el shell autenticado.
- Ajuste de E2E que hoy asume expiración por inactividad.
- Documentación OpenSpec / CC de la suspensión temporal y criterio de restablecimiento (adopción SDK Framework).

## Fuera de scope

- Cambiar semántica de `MinutosWeb` en backend / `sessionContext`.
- Eliminar `sessionInactivity.ts` / tests unitarios del controlador.
- Implementar el motor de inactividad del Framework (eso es adopción SDK, no este update).
- Cambiar logout manual del menú avatar ni flujo `POST /auth/logout`.

## Criterios de aceptación medibles

- [ ] **CA-CC16-01:** Usuario autenticado permanece en sesión aunque pase el umbral `MinutosWeb` / `inactivityTimeoutMinutes` sin actividad.
- [ ] **CA-CC16-02:** Logout manual (avatar) sigue cerrando sesión.
- [ ] **CA-CC16-03:** Request protegida con token inválido sigue forzando login (401).
- [ ] **CA-CC16-04:** Existe un interruptor único y documentado (`inactivityLogoutEnabled`) para reactivar al adoptar SDK Framework.

## Definición de listo

- [ ] HU/TR-update alineados
- [ ] Flag desactivado en `SessionLifecycleManager` (o equivalente)
- [ ] E2E de inactividad ajustado al comportamiento suspendido
- [ ] CC #16 marcado *Procesado*
