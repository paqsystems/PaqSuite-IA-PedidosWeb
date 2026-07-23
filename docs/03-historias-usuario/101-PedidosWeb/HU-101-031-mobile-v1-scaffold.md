# HU-101-031 — Mobile v1 scaffold PedidosWeb

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-031-mobile-v1-scaffold |
| **SPEC origen** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Must |
| **Release** | `v1.2.0-mobile` |
| **Estado** | **Especificado** — smoke Android emulador OK (F v1 2026-06-30) |
| **B1** | Enriquecida (2026-06-30) |
| **Dependencias** | [HU-GEN-11-mobile-capacitor-scaffold](../001-Generaliddes/HU-GEN-11-mobile-capacitor-scaffold.md) |

## Narrativa

Como **equipo PedidosWeb**,  
quiero **integrar Capacitor con identidad y URLs del producto**,  
para **generar la app instalable PedidosWeb en Android e iOS**.

## Alcance incluido

- `appId`: `com.paqsystems.pedidosweb`, `appName`: `PedidosWeb`.
- URL API default: `https://backend.pedidosweb.paqsystems.com/api/v1`.
- `.env.mobile` / modo build con variables PedidosWeb (`VITE_DEVEXTREME_LICENSE`, etc.).
- Splash e iconos placeholder (assets mínimos v1).
- Documentar smoke Android + iOS en `docs/_base/01-mobile/03-comandos-generacion-aplicaciones.md` (referencia).

## Fuera de alcance

- Login tenant (HU-101-032).
- Consulta stock (HU-101-033).

## Criterios de aceptación

- [x] **CA-01:** `cap sync` con build PedidosWeb OK.
- [x] **CA-02:** App muestra branding PedidosWeb al abrir.
- [x] **CA-03:** APK debug instala en dispositivo Android (emulador Pixel 6 API 34).
- [ ] **CA-04:** Build iOS debug documentado/ejecutado en Mac o CI.

## Veredicto B1

**Lista para TR** (`TR-SPEC-101-17-mobile-v1-scaffold`).
