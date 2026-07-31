# TR-SPEC-101-04 — Services pedidos (update — conversión cantidad)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-04-services-pedidos](../../101-PedidosWeb/TR-SPEC-101-04-services-pedidos.md) |
| **HU update** | [HU-101-006-carga-renglones-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-006-carga-renglones-update.md) |
| **SPEC update** | [SPEC-101-04-services-pedidos-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10, 30/07/2026 |
| **Última actualización** | 2026-07-30 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. Helper compartido p.ej. `CargaUnidadesVentaConverter` / método en service:
   - inputs: `cantidadUsuario`, `equivalenciaVentas`, `cargaUnidadesVenta`.
   - outputs: `cantidad`, `cantidadVenta`.
2. Integrar en validación/grabación de renglones; persistir ambos campos.
3. Reutilizar desde Excel y asistente.
4. PHPUnit: false/true; equiv 0→1; importes usan `cantidad`.

## AC técnicos

- [ ] **AC-CC10-T-S1:** Helper unit-tested.
- [ ] **AC-CC10-T-S2:** Grabación escribe ambos campos.
