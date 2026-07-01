# HU-101-032 — Mobile login tenant PedidosWeb

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-032-mobile-login-tenant |
| **SPEC origen** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Patrón** | [04-patron-login-tenant-mobile-mono.md](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Must |
| **Release** | `v1.2.0-mobile` |
| **Estado** | **Especificado** — smoke Android emulador OK (F v1 2026-06-30) |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | [HU-GEN-11-mobile-login-tenant](../001-Generaliddes/HU-GEN-11-mobile-login-tenant.md); [HU-GEN-11-mobile-config-api](../001-Generaliddes/HU-GEN-11-mobile-config-api.md); HU-101-031 |

## Narrativa

Como **usuario PedidosWeb en app mobile**,  
quiero **elegir la empresa (tenant) y autenticarme**,  
para **acceder al portal del cliente correcto**.

## Alcance incluido

- Login PedidosWeb native con tenant + usuario + contraseña (DevExtreme).
- Ejemplos placeholder: `desarrollo`, `demo`, `ankasdelsur`, `quento`.
- Post-login exitoso → **`/consultas/stock`** (no `/dashboard`).
- Ocultar enlaces forgot/reset password en v1 native.
- Config API (engrane) según HU-GEN-11-mobile-config-api.
- Extender `client.ts` / `authStorage` para tenant dinámico en native.

## Fuera de alcance

- Recuperación contraseña mobile v1.
- Landing dashboard.

## Reglas de negocio

1. Smoke QA: tenant **`desarrollo`** + usuarios seed existentes.
2. Mismo contrato login que web (`POST /auth/login`).
3. Perfil comercial obligatorio (403 `auth.noCommercialProfile`).

## Criterios de aceptación

- [x] **CA-01:** Login native PedidosWeb con tenant `desarrollo` + seed OK (Android emulador).
- [x] **CA-02:** Tras login → `/consultas/stock`.
- [ ] **CA-03:** Web login sin regresión (sin campo tenant).
- [x] **CA-04:** Enlaces recuperación ocultos en native v1.
- [x] **CA-05:** Config API operativa en native (engranaje + health).

## Escenarios Gherkin

```gherkin
Feature: Login mobile PedidosWeb

  Scenario: Vendedor ingresa con tenant desarrollo
    Given app native PedidosWeb
    When login con tenant desarrollo y credenciales seed
    Then navego a consulta stock
```

## Veredicto B1

**Lista para TR** (`TR-SPEC-101-17-mobile-v1-login-tenant`).
