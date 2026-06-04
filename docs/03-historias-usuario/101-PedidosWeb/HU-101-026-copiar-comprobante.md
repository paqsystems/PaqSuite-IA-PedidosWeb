# HU-101-026 — Copiar comprobante

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-026-copiar-comprobante |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must (AMB-C04) |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **copiar un comprobante anterior como base de uno nuevo**,  
para **agilizar cargas repetitivas**.

## Reglas de negocio

1. Origen puede ser pedido o presupuesto visible según permisos.
2. Nuevo comprobante obtiene nuevo GUID y número visible; estado según tipo elegido (0 o 99).
3. Trazabilidad opcional al origen en cabecera si el modelo lo soporta.
4. Aplicar validaciones de alta (cliente, renglones, parámetros).

## Criterios de aceptación

- [ ] **CA-01:** Acción «Copiar» llama `POST /api/v1/comprobantes/copiar` y abre pantalla con `resultado.borrador` **sin persistir** en BD.
- [ ] **CA-02:** Usuario puede cambiar tipo pedido/presupuesto antes de grabar si el flujo lo permite.
- [ ] **CA-03:** Grabar copia invoca `POST /api/v1/comprobantes/grabar` y persiste como comprobante nuevo independiente (nuevo GUID / número visible).

## Veredicto B1

**Lista para TR** (SPEC-101-04/10).
