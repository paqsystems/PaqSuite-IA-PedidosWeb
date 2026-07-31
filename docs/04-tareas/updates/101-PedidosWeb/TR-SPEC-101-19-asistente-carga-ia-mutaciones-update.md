# TR-SPEC-101-19 — Asistente mutaciones (update — cantidad CC #10)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-19-asistente-carga-ia-mutaciones](../../101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **HU update** | [HU-101-040-asistente-carga-ia-articulos-grabar-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar-update.md) |
| **SPEC update** | [SPEC-101-19-asistente-carga-ia-mutaciones-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Tools `addRenglon` / modify / image apply: pasar cantidad por helper compartido (SPEC-101-04-update).
2. PHPUnit en `CargaAsistenteToolsTest` (o equivalente) false/true + default 1.

## AC técnicos

- [ ] **AC-CC10-T-A1:** Texto/tools convierten.
- [ ] **AC-CC10-T-A2:** Extracto imagen convierte.
