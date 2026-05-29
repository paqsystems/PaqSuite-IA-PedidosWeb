# HU-GEN-02-login-sesion — Login, bootstrap de sesión y logout

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-02-login-sesion |
| **SPEC origen** | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Épica** | 001 — Generaliddes / Acceso y seguridad |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-05-28) |
| **Última actualización** | 2026-05-28 |
| **Dependencias** | SPEC-001-05 (tenancy MONO, `X-Paq-Cliente`); HU-GEN-01-idioma |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Login y ciclo de sesión (alcance) | Login, bootstrap, logout |
| Entregable: contrato autenticación MVP (login, logout) | AC + OpenAPI en TR |
| Fuera alcance: 2FA, login social | Out of scope |
| MONO: sin selector empresa | Post-login directo al shell |
| Trazabilidad HU-SPEC: login, bootstrap, logout | Objeto de esta HU |

## Narrativa

Como **usuario del portal**,  
quiero **iniciar sesión con credenciales y cerrar sesión de forma segura**,  
para **acceder solo a los procesos autorizados y salir sin dejar sesión activa**.

## Contexto funcional

SPEC-001-02 establece el marco de **autenticación y ciclo de sesión** para el MVP. Incluye login y logout; recuperación, cambio de contraseña e inactividad están en HUs hermanas. Roles vía seed (`Pq_Permiso`), sin ABM UI. Producto §7.4 referenciado como fuente de autenticación.

## Alcance incluido

- Pantalla de login con credenciales y enlace a recuperación (flujo en HU hermana).
- API de autenticación con token/sesión.
- Post-login: cargar preferencias (`locale`, `theme`); resolver menú autorizado; redirigir al shell **sin selección de empresa** (MONO).
- Logout: invalidar sesión backend, limpiar cliente, redirect login.
- Validación de usuario activo con asignación en `Pq_Permiso` (seed; SPEC alcance permisos).

## Fuera de alcance

- Recuperación de contraseña (HU-GEN-02-recuperacion-contrasena).
- Cambio de contraseña y primer ingreso (HU-GEN-02-cambio-contrasena).
- Expiración por inactividad (`MinutosWeb`; HU-GEN-02-expiracion-inactividad).
- 2FA, login social, anti-fuerza bruta avanzado (SPEC fuera de alcance).
- Administración de seguridad vía UI (SPEC fuera de alcance).

## Reglas de negocio

1. Solo usuarios con permisos seed válidos (`Pq_Permiso`) operan tras autenticación.
2. En MONO no hay paso intermedio de selección de empresa (coherente SPEC-001-05).
3. Requests API autenticados incluyen token y **`X-Paq-Cliente`** según tenancy (producto/SPEC MVP referenciados).
4. Mensaje genérico ante credenciales inválidas (no filtrar existencia de cuenta).

## Criterios de aceptación

- [ ] Login exitoso devuelve token y datos mínimos de usuario (nombre, preferencias).
- [ ] Credenciales incorrectas: HTTP 401 y mensaje genérico en UI.
- [ ] Usuario sin `Pq_Permiso` válido: rechazo con mensaje de falta de acceso.
- [ ] Post-login: redirección al shell sin pantalla de empresa.
- [ ] Logout invalida sesión; request con token anterior → 401.
- [ ] Tenant inválido: error controlado según arquitectura MONO.
- [ ] E2E: login válido → shell; logout → pantalla login.
- [ ] Contrato OpenAPI login/logout documentado en TR.

## Escenarios Gherkin

```gherkin
Feature: Login y sesión (SPEC-001-02)

  Scenario: Login exitoso en MONO
    Given un usuario con credenciales válidas y Pq_Permiso asignado
    When inicia sesión con X-Paq-Cliente válido
    Then recibe token de sesión
    And es redirigido al shell sin selector de empresa

  Scenario: Credenciales inválidas
    Given credenciales incorrectas
    When intenta login
    Then recibe HTTP 401
    And ve mensaje genérico de error

  Scenario: Usuario sin permisos
    Given un usuario sin fila válida en Pq_Permiso
    When intenta login con credenciales correctas
    Then no accede al shell
    And ve mensaje de falta de acceso

  Scenario: Logout invalida sesión
    Given un usuario autenticado
    When cierra sesión
    Then el token queda invalidado
    And la siguiente petición autenticada responde 401
```

## Supuestos explícitos

- Campos `activo`, `inhabilitado`, `first_login` en `users`: no detallados en SPEC-001-02; validar en TR con producto §7.4.
- Laravel Sanctum como mecanismo de token: stack no nombrado en SPEC.
- Estructura exacta de `POST /api/v1/auth/login`: entregable TR/OpenAPI del SPEC.

## Preguntas abiertas

- ¿Campos obligatorios de validación pre-login además de Pq_Permiso?

## Riesgos de ambigüedad

- Bootstrap post-login depende de HU-GEN-01 (preferencias) y HU-GEN-02-autorizacion-menu-api; orden de implementación en TR.

## Veredicto B1

**Lista para TR:** Sí con observaciones (contrato API y validaciones de usuario)
