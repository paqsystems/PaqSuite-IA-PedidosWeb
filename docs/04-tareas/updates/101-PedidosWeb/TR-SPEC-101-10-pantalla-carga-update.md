# TR-SPEC-101-10 — Pantalla carga (update — UI cantidad)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-10-pantalla-carga](../../101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md) |
| **HU update** | [HU-101-006-carga-renglones-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-006-carga-renglones-update.md) |
| **SPEC update** | [SPEC-101-10-pantalla-carga-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. FE: leer `CargaUnidadesVenta` (cabecera inicial / parámetros runtime).
2. Modal renglón: un solo NumberBox «cantidad»; al confirmar, materializar ambos campos vía API o cálculo FE+BE validado.
3. Al abrir edición: precargar valor según modo.
4. Actualizar `pantalla-carga-comprobante-ui.md`.
5. Vitest conversión; E2E opcional.
6. Rama mobile carga: misma semántica.

## AC técnicos

- [ ] **AC-CC10-T-U1:** Un solo control cantidad.
- [ ] **AC-CC10-T-U2:** Modo false/true correcto en UI + payload.
