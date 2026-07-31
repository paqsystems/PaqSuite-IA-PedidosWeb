# HU-101-040 — Asistente IA artículos (update — cantidad)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-101-040-asistente-carga-ia-articulos-grabar](../../101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) |
| **SPEC update** | [SPEC-101-19-asistente-carga-ia-mutaciones-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **usuario del asistente en carga**, quiero que la **cantidad** informada por texto, voz o imagen se interprete igual que en el modal de renglón según `CargaUnidadesVenta`.

## Criterios de aceptación

- [ ] **CA-CC10-A01:** `addRenglon` / modify aplican helper de conversión.
- [ ] **CA-CC10-A02:** Apply extracto imagen (K) aplica la misma conversión.
- [ ] **CA-CC10-A03:** Cantidad omitida = 1 se interpreta según el parámetro.
