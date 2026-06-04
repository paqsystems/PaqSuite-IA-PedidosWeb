# HU-101-007 — Cálculo de bonificación neta

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-007-bonificacion-neta |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario que carga un comprobante**,  
quiero **ver la bonificación neta calculada correctamente**,  
para **aplicar descuentos comerciales sin errores manuales**.

## Reglas de negocio

1. Bonificación neta derivada de bonificaciones 1–3 según producto §10.4.
2. Edición manual de bonificaciones de cabecera solo si los **parámetros ERP** lo permiten para el tipo de usuario (**V** / **S**): `ModificaBonCliV`, `ModificaBonCliS` (y coherencia con `ModificaBonArt*` en renglón — HU-101-006).
3. **Vendedor y supervisor:** habilitación de descuentos **prefijada en el ERP**, no decidida en código del portal.
4. Recálculo al cambiar bonificaciones de cabecera o renglón.

## Criterios de aceptación

- [ ] **CA-01:** Cambio en bonificación 1 del cliente actualiza bonificación neta.
- [ ] **CA-02:** Valores coherentes con reglas documentadas en TR (casos unitarios).
- [ ] **CA-03:** Tests unitarios del service cubren al menos 3 combinaciones.

## Veredicto B1

**Lista para TR** (SPEC-101-04).
