# Cierre F Formal — SPEC-001-11 Mobile Capacitor (v1)

## Alcance del cierre

Cubre los tres TR transversales v1 implementados en Parte D1, coordinados con SPEC-101-17:

| TR | HU |
|----|-----|
| [TR-GEN-11-mobile-capacitor-scaffold](TR-GEN-11-mobile-capacitor-scaffold.md) | [HU-GEN-11-mobile-capacitor-scaffold](../../03-historias-usuario/001-Generaliddes/HU-GEN-11-mobile-capacitor-scaffold.md) |
| [TR-GEN-11-mobile-login-tenant](TR-GEN-11-mobile-login-tenant.md) | [HU-GEN-11-mobile-login-tenant](../../03-historias-usuario/001-Generaliddes/HU-GEN-11-mobile-login-tenant.md), [HU-GEN-11-mobile-config-api](../../03-historias-usuario/001-Generaliddes/HU-GEN-11-mobile-config-api.md) |
| [TR-GEN-11-mobile-shell](TR-GEN-11-mobile-shell.md) | [HU-GEN-11-mobile-shell-exclusiones](../../03-historias-usuario/001-Generaliddes/HU-GEN-11-mobile-shell-exclusiones.md) |

**SPEC:** [SPEC-001-11-mobile-capacitor.md](../../05-open-spec/001-Generaliddes/SPEC-001-11-mobile-capacitor.md)

**Revisión C1:** [F-GEN-11-cierre-c1](F-GEN-11-cierre-c1.md)  
**Verificación D:** [D-VERIFICACION-101-17-mobile-v1](../101-PedidosWeb/D-VERIFICACION-101-17-mobile-v1.md)

## Resultado global

- **Aprobado con observaciones**

Épica transversal **implementada y smoke Android emulador OK**. Tag `v1.2.0-mobile` pendiente de smoke iOS.

## Resumen por TR

| TR | Resultado D | Observación |
|----|-------------|-------------|
| TR-GEN-11-mobile-capacitor-scaffold | Aprobado | Capacitor 8, mixed content dev, StatusBar, cleartext |
| TR-GEN-11-mobile-login-tenant | Aprobado | Tenant-first, Preferences, health, `normalizeApiBaseUrl` |
| TR-GEN-11-mobile-shell | Aprobado | Drawer, safe area, menú filtrado, UX native post-smoke |

## Verificación automatizada (2026-06-30)

| Comando | Resultado |
|---------|-----------|
| `npm run build` / `build:mobile` | OK |
| `npm test -- --run` | OK |
| `npx cap sync` | OK |

## Smoke mínimo pre-tag

| # | Caso | Estado |
|---|------|--------|
| 1 | Android emulador: login tenant → shell → stock kardex | **OK** |
| 2 | iOS: mismo flujo | **Pendiente** (Mac/CI) |
| 3 | Web: sin regresión login/dashboard | **Pendiente** (manual) |

## Observaciones (no bloqueantes)

| ID | Tema | Notas |
|----|------|-------|
| OBS-01 | Smoke iOS | Pendiente pre-tag |
| OBS-02 | Plugins push/biometría | Fuera v1 (AMB-M-001-11-03) |
| OBS-03 | Deep links | Fuera v1 (AMB-M-001-11-05) |
| OBS-04 | Menú árbol web | Controles expandir/ramas ocultos native v1 — ver TR-GEN-11-shell |

## Veredicto

**F formal cerrado** — SPEC-001-11 v1 listo como épica implementada. Tag release `v1.2.0-mobile` tras smoke iOS (Android emulador cubierto en F-101-17).
