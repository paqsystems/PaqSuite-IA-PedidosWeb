# HU-101-001 — Login de usuario (verificación PedidosWeb)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-001-login |
| **SPEC origen** | [SPEC-101-06-seguridad-visibilidad](../../05-open-spec/101-PedidosWeb/SPEC-101-06-seguridad-visibilidad.md) |
| **HU canónica** | [HU-GEN-02-login-sesion](../001-Generaliddes/HU-GEN-02-login-sesion.md) |
| **Épica** | 101 — PedidosWeb / Seguridad |
| **Prioridad** | Must |
| **Estado** | Heredada — verificación |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-GEN-02-login-sesion implementada en `v1.1.0-paq` |

## Trazabilidad SPEC

| Criterio SPEC madre / 101-06 | Cobertura |
|------------------------------|-----------|
| Login con credenciales y envelope MONO | Verificación contra GEN-02 |
| `X-Paq-Cliente` en requests | CA-03 |
| Bootstrap sesión post-login | CA-01 |
| Vínculo comercial único (cliente **o** vendedor) | CA-06, CA-07, RN-02 |
| Acceso al menú tras login válido | CA-08 |

## Narrativa

Como **usuario del portal PedidosWeb**,  
quiero **iniciar sesión con mis credenciales**,  
para **acceder al menú y procesos comerciales autorizados**.

## Alcance de esta HU (101)

**No reimplementar.** Validar que el login existente (GEN-02) cumple el contrato PedidosWeb: tenant header, perfil comercial, redirección al shell, mensajes i18n.

## Reglas de negocio

1. Cada login debe resolverse a **una sola** entidad comercial: cliente **o** vendedor/supervisor (`CommercialProfileResolver` + `SessionContextBuilder`).
2. Si existen **cliente y vendedor** con el mismo `cod_login` → **403** `auth.noCommercialProfile` (perfil ambiguo).
3. Si no hay fila en `pq_pedidosweb_clientes` ni `pq_pedidosweb_vendedores` para el `cod_usuario_web` → **403** `auth.noCommercialProfile`.
4. Login válido debe permitir `GET /user/menu` con ítems según rol (no shell vacío por error de auth).

## Usuarios seed (QA)

| `codigo` | Escenario |
|----------|-----------|
| `usuario.perfilAmbiguo.mvp` | Cliente **y** vendedor con `cod_login = AMBIG01` |
| `usuario.sinVinculo.mvp` | Permiso OK, sin vínculo comercial |
| `cliente.mvp` | Cliente único → login OK + menú |

## Fuera de alcance

- Nueva pantalla de login (ya entregada GEN-02).
- Tenancy `EMPRESAS_CONEXION` completo (HU-101-003, etapa posterior).

## Criterios de aceptación

- [ ] **CA-01:** Login exitoso con usuario seed (`cliente.mvp`) → shell y menú.
- [ ] **CA-02:** Credenciales inválidas → 401 y mensaje genérico.
- [ ] **CA-03:** Requests API autenticados incluyen `X-Paq-Cliente` coherente con sesión.
- [ ] **CA-04:** Usuario sin perfil comercial → 403 documentado.
- [ ] **CA-05:** E2E smoke login existente sigue verde.
- [ ] **CA-06:** Perfil ambiguo (cliente + vendedor mismo `cod_login`) → 403 `auth.noCommercialProfile` (backend + E2E).
- [ ] **CA-07:** Sin entidad asignada (`usuario.sinVinculo.mvp`) → 403 y mensaje en login (E2E).
- [ ] **CA-08:** Con entidad asignada → dashboard/shell y menú lateral con ítems (E2E).

## Escenarios Gherkin

```gherkin
Feature: Login y vínculo comercial (HU-101-001)

  Scenario: Perfil comercial ambiguo
    Given el usuario "usuario.perfilAmbiguo.mvp" con cliente y vendedor mismo cod_login
    When intenta iniciar sesión con credenciales válidas
    Then permanece en la pantalla de login
    And ve el mensaje auth.noCommercialProfile

  Scenario: Sin entidad comercial
    Given el usuario "usuario.sinVinculo.mvp" sin fila cliente ni vendedor
    When intenta iniciar sesión
    Then permanece en login
    And ve auth.noCommercialProfile

  Scenario: Entidad asignada y menú
    Given el usuario "cliente.mvp" con perfil cliente único
    When inicia sesión correctamente
    Then accede al dashboard con shell visible
    And el menú lateral muestra procesos autorizados
```

## Pruebas E2E

Archivo: `frontend/tests/e2e/auth-login-profiles.spec.ts`

| # | Caso | `data-testid` / aserción |
|---|------|---------------------------|
| 1 | Doble entidad (ambiguo) | `auth-error-no-commercial-profile`, sin shell |
| 2 | Sin entidad | `auth-error-no-commercial-profile`, sin shell |
| 3 | Con entidad + menú | `shellHeader`, sidebar sin `menuSidebarEmptyState` |

Ejecutar: `npm run test:e2e -- tests/e2e/auth-login-profiles.spec.ts`

## Pruebas backend (Feature)

- `AuthLoginTest::testLoginWithAmbiguousCommercialProfileReturns403`
- `AuthLoginTest::testLoginWithoutCommercialProfileReturns403`
- `AuthLoginTest::testLoginSuccessLoadsMenuForCliente`

## Veredicto B1

**Lista para TR de verificación** (TR-SPEC-101-06), no para re-codificar login.
