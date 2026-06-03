# HU-101-006 — Carga de renglones con artículos

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-006-carga-renglones |
| **SPEC origen** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-101-005; HU-101-007, HU-101-008 |

## Narrativa

Como **usuario que carga un comprobante**,  
quiero **agregar y editar renglones de artículos con cantidades y precios**,  
para **armar el detalle del pedido o presupuesto**.

## Alcance incluido

- Alta/edición/baja de renglones en pantalla única pedido/presupuesto
- Autocompletar/búsqueda artículo por código o descripción
- Validaciones `ArticulosPrecioCero`, `ArticulosSinPrecio` (parámetros — SPEC-001-04)
- Al menos un renglón obligatorio para grabar (HU-101-009/010)

## Reglas de negocio

1. **Cliente (`C`):** no puede modificar precio unitario ni descuentos/bonificaciones en renglón (producto §10.5).
2. **Vendedor (`V`) y supervisor (`S`):** la posibilidad de editar **precio** y **descuento** en renglón **no** la fija el portal; la definen **parámetros generales del ERP** en la base del tenant:
   - Precio: `ModificaPrecioV`, `ModificaPrecioS`
   - Descuento artículo: `ModificaBonArtV`, `ModificaBonArtS`
3. Si el parámetro está deshabilitado en ERP → control de renglón en solo lectura y validación backend al grabar.
4. Fuente de parámetros: producto §10.6; consumo en runtime: SPEC-001-04 (pendiente).
5. **Descuento por cantidad:** al agregar renglón, descuento inicial = bonificación del artículo; al cambiar cantidad, aplicar regla `pq_pedidosweb_descuentocantidad` (mayor cantidad ≤ ingresada). Ver **[pantalla-carga-comprobante-ui.md](../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md)** §12. Esta regla aplica **aunque** no haya permiso `ModificaBonArt*`.

## Fuera de alcance

- Importación Excel (SPEC-001-07 documental)
- Parametrización desde el portal (solo lectura de valores ERP)

## Criterios de aceptación

- [ ] **CA-01:** Usuario agrega renglón con artículo válido y cantidad > 0.
- [ ] **CA-02:** Artículo sin precio rechazado o advertido según parámetro.
- [ ] **CA-03:** Eliminar renglón actualiza totales en pantalla.
- [ ] **CA-04:** UI DevExtreme; sin `<input>` nativo final para controles cubiertos por DX.
- [ ] **CA-05:** Cliente no puede modificar precio/lista/descuento artículo (producto §10.5).
- [ ] **CA-06:** Con `ModificaPrecioV=false` (seed/ERP), vendedor ve precio de renglón deshabilitado; con `true`, editable.
- [ ] **CA-07:** Con `ModificaBonArtS=false`, supervisor no edita descuento de renglón; coherente con parámetro ERP, no con rol hardcodeado.

## Veredicto B1

**Lista para TR** (SPEC-101-10).
