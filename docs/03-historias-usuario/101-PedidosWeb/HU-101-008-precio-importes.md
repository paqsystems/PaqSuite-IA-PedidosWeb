# HU-101-008 — Precio neto, importes e IVA

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-008-precio-importes |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **ver precios netos e importes con IVA en renglón y totales en cabecera**,  
para **conocer el monto final del comprobante**.

## Reglas de negocio

1. IVA **persistido** en renglón y cabecera (SPEC madre §5).
2. Totales de cabecera = suma coherente de renglones tras redondeo definido en TR.
3. Recálculo en tiempo real en UI y validación final en service al grabar.
4. El **precio neto** editable en renglón para **vendedor/supervisor** respeta `ModificaPrecioV` / `ModificaPrecioS` (parámetros ERP); el portal recalcula importes pero no ignora el bloqueo paramétrico.

## Criterios de aceptación

- [ ] **CA-01:** Alta de renglón actualiza subtotales y total cabecera en UI.
- [ ] **CA-02:** Tras grabar, importes en BD coinciden con pantalla (transacción).
- [ ] **CA-03:** Tests unitarios de totales/IVA ≥ umbral slice (§12 madre).

## Preguntas abiertas

Redondeo por línea vs cabecera — cerrar en TR (AMB-M07).

## Veredicto B1

**Lista para TR** (SPEC-101-04).
