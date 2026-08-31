# HU-101-008 — Precio neto, importes e IVA

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-008-precio-importes |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado (Parte I CC PQ #12) |
| **Última actualización** | 2026-08-30 (Parte I — CC PQ #12) |
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
5. **CC PQ #12:** El modal de renglón muestra precio unitario neto = precio cargado − bonif. renglón − bonif. neta de cabecera (misma base de cálculo de importes); se actualiza al cambiar precio/bonificaciones en el modal.

## Criterios de aceptación

- [ ] **CA-01:** Alta de renglón actualiza subtotales y total cabecera en UI.
- [ ] **CA-02:** Tras grabar, importes en BD coinciden con pantalla (transacción).
- [ ] **CA-03:** Tests unitarios de totales/IVA ≥ umbral slice (§12 madre).
- [ ] **CA-CC12-I01:** El modal de renglón muestra precio unitario neto = precio cargado − bonif. renglón − bonif. neta de cabecera (misma base de cálculo de importes).
- [ ] **CA-CC12-I02:** Se actualiza al cambiar precio/bonificaciones en el modal.

## Historial CC PQ #12 (28/08/2026) — Parte I 30/08/2026

Precio unitario neto en modal de renglón (RN-5, CA-CC12-I01…I02). Unificación delta `HU-101-008-precio-importes-update` (archivo eliminado en Parte I).

## Preguntas abiertas

Redondeo por línea vs cabecera — cerrar en TR (AMB-M07).

## Veredicto B1

**Lista para TR** (SPEC-101-04).
