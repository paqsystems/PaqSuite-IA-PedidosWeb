# HU-GEN-02-expiracion-inactividad-update — Suspender logout por inactividad

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-expiracion-inactividad-update |
| **HU base** | [HU-GEN-02-expiracion-inactividad](../../001-Generaliddes/HU-GEN-02-expiracion-inactividad.md) |
| **SPEC origen** | [SPEC-001-02-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-02-acceso-y-seguridad-update.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #16 · **12/09/2026** |
| **TR** | [TR-GEN-02-expiracion-inactividad-update](../../../04-tareas/updates/001-Generaliddes/TR-GEN-02-expiracion-inactividad-update.md) |
| **Última actualización** | 2026-09-12 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **operador / soporte**,  
quiero **que la sesión no se cierre sola por inactividad mientras PedidosWeb aún no consume el SDK de Framework**,  
para **evitar cortes operativos no deseados** hasta restablecer el comportamiento canónico con GEN.

## Reglas de negocio

1. **RN-CC16-01 (D1-31):** Mientras esté vigente esta suspensión, el frontend **no** dispara logout por timer de inactividad.
2. **RN-CC16-02:** El logout explícito del usuario (menú avatar) sigue disponible y funcional.
3. **RN-CC16-03:** La exposición de `inactivityTimeoutMinutes` / `MinutosWeb` puede permanecer; no obliga a cerrar sesión si el timer está deshabilitado.
4. **RN-CC16-04:** Al integrar el SDK Framework (sesión GEN), se **reactiva** el logout por inactividad (quitar o poner en `true` el interruptor documentado).

## Criterios de aceptación

- [ ] **CA-CC16-01:** Con sesión activa y sin interacción durante un tiempo mayor a `MinutosWeb`, el usuario **no** es redirigido a login por inactividad.
- [ ] **CA-CC16-02:** Logout manual sigue llevando a login y limpia credenciales locales.
- [ ] **CA-CC16-03:** Token inválido / 401 sigue forzando re-login.
- [ ] **CA-CC16-04:** El código deja un punto único de reactivación documentado (comentario + constante).

## Escenarios Gherkin

```gherkin
Feature: Suspensión temporal de logout por inactividad (CC PQ #16)

  Scenario: Inactividad no cierra sesión
    Given un usuario autenticado
    And el logout por inactividad está suspendido
    When permanece sin actividad más allá de MinutosWeb
    Then la sesión permanece activa
    And no se muestra el mensaje de sesión expirada por inactividad

  Scenario: Logout manual intacto
    Given un usuario autenticado
    When ejecuta logout desde el menú avatar
    Then es redirigido al login
```

## Fuera de alcance

- Rediseñar eventos de actividad o política multi-tab.
- Migrar el motor al SDK Framework en este update.
