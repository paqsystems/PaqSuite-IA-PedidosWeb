# HU-GEN-02-expiracion-inactividad — Expiración de sesión por inactividad

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-expiracion-inactividad |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generaliddes / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-09-12 (Parte I — CC PQ #16) |
| **Dependencias** | HU-GEN-02-login-sesion; SPEC-001-04 (parámetro `MinutosWeb`) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Expiración sesión por inactividad (alcance) | Detector frontend + 401 backend |
| Parámetro `MinutosWeb` (decisión SPEC) | Lectura configuración global |
| Entregable: contrato autenticación MVP (expiración) | AC + TR |
| Consumo SPEC-001-04 | Parámetro crítico MVP |
| Trazabilidad HU-SPEC: MinutosWeb | Objeto de esta HU |
| CC PQ #16 — suspensión temporal | Flag `inactivityLogoutEnabled` hasta SDK Framework |

## Narrativa

Como **usuario autenticado**,  
quiero **que la sesión expire tras un período sin actividad configurable**,  
para **reducir el riesgo de uso no autorizado en equipos compartidos**.

**Vigencia operativa (CC PQ #16):** mientras PedidosWeb no consuma el SDK de Framework, el logout automático por inactividad está **suspendido** en el host para evitar cortes operativos no deseados. El comportamiento canónico de esta HU se **restablece** al adoptar GEN sesión (`inactivityLogoutEnabled=true`).

## Contexto funcional

SPEC-001-02 incluye explícitamente **expiración de sesión por inactividad**. El umbral se obtiene del parámetro **`MinutosWeb`** (producto §10.6; consumo en SPEC-001-04). Contrato de autenticación MVP debe documentar expiración en TR/OpenAPI.

## Alcance incluido

- Tiempo máximo de inactividad desde parámetro **`MinutosWeb`**.
- Frontend: detector de actividad del usuario y cierre de sesión al superar umbral (**hoy deshabilitado en runtime** vía D1-31).
- Backend: rechazo de sesión expirada con HTTP 401 (token inválido / revocado).
- Al expirar (cuando el timer esté activo): limpiar sesión local, mensaje informativo, redirigir a login.
- Regla: actividad del usuario renueva contador de inactividad (cuando el timer esté activo).
- Interruptor documentado para reactivar al integrar SDK Framework.

## Fuera de alcance

- Revocación manual de todas las sesiones del usuario.
- SSO o refresh token rotativo avanzado.
- 2FA (SPEC fuera de alcance).
- Implementar el motor de inactividad del Framework en este host (adopción SDK).

## Reglas de negocio

1. El valor de minutos proviene de **`MinutosWeb`** (SPEC-001-02 → SPEC-001-04).
2. La actividad del usuario renueva el contador de inactividad (cuando el timer está habilitado).
3. Tras expiración (o token inválido), cualquier API protegida responde **401**.
4. Fallback si parámetro ausente: documentar default en HU-GEN-04 / TR (SPEC-001-04).
5. **RN-CC16-01 (D1-31):** Mientras `inactivityLogoutEnabled === false`, el frontend **no** dispara logout por timer de inactividad.
6. **RN-CC16-02:** El logout explícito del usuario (menú avatar) sigue disponible y funcional.
7. **RN-CC16-03:** La exposición de `inactivityTimeoutMinutes` / `MinutosWeb` puede permanecer; no obliga a cerrar sesión si el timer está deshabilitado.
8. **RN-CC16-04:** Al integrar el SDK Framework (sesión GEN), se **reactiva** el logout por inactividad (`inactivityLogoutEnabled=true`).

## Criterios de aceptación

- [x] Parámetro `MinutosWeb` leído desde configuración global (SPEC-001-04) / `sessionContext.inactivityTimeoutMinutes`.
- [x] Default documentado si parámetro ausente (TR / fallback 10).
- [x] **CA-CC-01:** Tras login, si el usuario interactúa antes de `MinutosWeb`, la sesión **no** expira (motor).
- [x] **CA-CC-02:** El contador de inactividad se **reinicia** con cada evento de actividad válido (TR-GEN-02 RN-02).
- [ ] **CA-CC-03:** Sin actividad durante `MinutosWeb` → cierre de sesión y redirect login — **suspendido operativamente** mientras D1-31 esté vigente; se revalida al reactivar el flag.
- [x] **CA-CC-04:** Test unitario del controlador / renovación del contador.
- [x] **CA-CC16-01:** Con suspensión activa, sin interacción más allá de `MinutosWeb` el usuario **no** es redirigido a login por inactividad.
- [x] **CA-CC16-02:** Logout manual sigue llevando a login y limpia credenciales locales.
- [x] **CA-CC16-03:** Token inválido / 401 sigue forzando re-login.
- [x] **CA-CC16-04:** Punto único de reactivación documentado (`inactivityLogoutEnabled` + comentario CC #16).

## Escenarios Gherkin

```gherkin
Feature: Expiración por inactividad (SPEC-001-02 / MinutosWeb)

  Scenario: Sesión expira por inactividad
    Given un usuario autenticado
    And MinutosWeb configurado en N minutos
    And el logout por inactividad está habilitado
    When permanece N minutos sin actividad
    Then la sesión se cierra
    And es redirigido al login con mensaje de sesión expirada

  Scenario: Actividad renueva contador
    Given un usuario autenticado
    And el logout por inactividad está habilitado
    When realiza actividad antes de alcanzar MinutosWeb
    Then la sesión permanece activa

  Scenario: API rechaza token expirado
    Given una sesión con token inválido o revocado
    When llama a un endpoint protegido
    Then recibe HTTP 401

  Scenario: Parámetro MinutosWeb ausente
    Given MinutosWeb no configurado
    When el sistema necesita el umbral
    Then aplica default documentado en TR

  Scenario: Suspensión temporal (CC PQ #16)
    Given un usuario autenticado
    And el logout por inactividad está suspendido
    When permanece sin actividad más allá de MinutosWeb
    Then la sesión permanece activa
    And no se muestra el mensaje de sesión expirada por inactividad

  Scenario: Logout manual intacto durante la suspensión
    Given un usuario autenticado
    When ejecuta logout desde el menú avatar
    Then es redirigido al login
```

## Supuestos explícitos

- Aviso previo N minutos antes del cierre: no en SPEC-001-02; opcional en TR.
- Pestaña en background: comportamiento exacto en TR.
- Llamadas API exitosas como actividad: cerrado en TR-GEN-02 RN-02 / §6.1.
- Lista cerrada de eventos de actividad (incl. `Tab`, scroll, capture DevExtreme): TR-GEN-02 §6.1 y regla `.cursor/rules/sesion-inactividad-expiracion.mdc`.
- Suspensión D1-31 es temporal hasta adopción SDK Framework.

## Preguntas abiertas

- ¿Valor default numérico de `MinutosWeb` si falta en BD? (operativo: fallback 10 en FE)
- ¿Aviso previo incluido en MVP?

## Riesgos de ambigüedad

- Dependencia de SPEC-001-04 / HU-GEN-04 para lectura del parámetro; implementar orden Fase 0.
- Olvidar reactivar `inactivityLogoutEnabled` tras adoptar SDK Framework.

## Veredicto B1

**Lista para TR:** Sí con observaciones (default MinutosWeb y aviso previo)

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 09/06/2026 | Parte I CC PQ #1 | Umbral desde última actividad |
| 12/09/2026 | Parte I CC PQ #16 | Suspensión logout inactividad hasta SDK Framework (D1-31) |
