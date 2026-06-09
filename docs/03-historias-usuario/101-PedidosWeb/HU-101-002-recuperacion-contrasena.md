# HU-101-002 — Recuperación de contraseña (verificación PedidosWeb)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-002-recuperacion-contrasena |
| **SPEC origen** | [SPEC-101-06-seguridad-visibilidad](../../05-open-spec/101-PedidosWeb/SPEC-101-06-seguridad-visibilidad.md) |
| **HU canónica** | [HU-GEN-02-recuperacion-contrasena](../001-Generaliddes/HU-GEN-02-recuperacion-contrasena.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario que olvidó su contraseña**,  
quiero **solicitar un enlace de restablecimiento**,  
para **volver a acceder al portal de forma segura**.

## Alcance (101)

Verificar flujo forgot/reset existente (GEN-02) con UI DevExtreme, i18n y mismo canal de mail que usará HU-101-019.

## Criterios de aceptación

- [ ] **CA-01:** Flujo forgot → email/log → reset con contraseña válida.
- [ ] **CA-02:** Token inválido o expirado → error controlado.
- [ ] **CA-03:** E2E `password-recovery.spec.ts` verde.

## Veredicto B1

**Lista para TR de verificación** en SPEC-101-06.
