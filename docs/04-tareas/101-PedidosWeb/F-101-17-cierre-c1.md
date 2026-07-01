# F-101-17 — Cierre revisión C1 (mobile Capacitor PedidosWeb v1)

| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Fecha** | 2026-06-30 |
| **Alcance** | Revisión C1 TR v1 (031–033) |
| **Veredicto** | **Apto** — **autorizada Parte D** release `v1.2.0-mobile` |

## Resultado por TR

| TR | HU | Estado C1 | Orden D |
|----|-----|-----------|---------|
| [TR-SPEC-101-17-mobile-v1-scaffold](TR-SPEC-101-17-mobile-v1-scaffold.md) | HU-101-031 | **Apto** | 1 |
| [TR-SPEC-101-17-mobile-v1-login-tenant](TR-SPEC-101-17-mobile-v1-login-tenant.md) | HU-101-032 | **Apto** | 2 |
| [TR-SPEC-101-17-mobile-v1-stock-kardex](TR-SPEC-101-17-mobile-v1-stock-kardex.md) | HU-101-033 | **Apto** | 3 |

Transversal: [F-GEN-11-cierre-c1](../001-Generaliddes/F-GEN-11-cierre-c1.md).

## TR v2/v3 (post v1)

| HU | Release | TR (pendiente Parte C futura) |
|----|---------|-------------------------------|
| HU-101-034 | `v1.2.1-mobile` | TR-SPEC-101-17-mobile-v2-consultas |
| HU-101-035 | `v1.2.1-mobile` | TR-SPEC-101-17-mobile-v2-listados |
| HU-101-036 | `v1.2.2-mobile` | TR-SPEC-101-17-mobile-v3-carga |

## Smoke mínimo pre-tag `v1.2.0-mobile`

1. Android: login tenant `desarrollo` → stock kardex → detalle → filtro.
2. iOS: mismo flujo en simulador/dispositivo (Mac/CI).
3. Web: login sin tenant → dashboard (regresión).

## Siguiente paso

Implementar D1 en orden 1→3; no generar TR v2/v3 hasta cierre F v1.
