# HU-101-013 — Conversión de presupuesto a pedido

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-013-conversion-presupuesto-pedido |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **convertir un presupuesto activo en pedido con “Grabar pedido” en la pantalla de carga**,  
para **cerrar la oportunidad y generar un pedido ingresado**.

## Reglas de negocio

1. Presupuesto origen pasa a **estado 98** + registro en `pq_pedidosweb_presupuestos_cierres`.
2. Pedido nuevo en **estado 0**.
3. **No** existe cierre parcial/positivo ni clasificación por renglones (AMB-C05).
4. Conversión puede permitir ajustar renglones según producto §15.1 sin tipo “parcial” en BD.
5. **Motivo de cierre exitoso (paramétrico):** leer parámetro ERP **`CodMotivoCierreExitoso`** (SPEC-001-04 / producto §10.6 ampliado en SPEC madre §5.3). El valor es el **`id_motivo`** (entero) vigente en `pq_pedidosweb_motivos_cierre` con `tipo_cierre = positivo` y `activo = 1`.
6. El portal **no** muestra selector de motivo en conversión; aplica el código del parámetro y valida que el motivo exista y esté activo.
7. En `presupuestos_cierres`: `id_motivo` = valor del parámetro; `tipo_cierre` positivo; `cod_pedido_generado` = pedido nuevo.
8. **Trazabilidad (sin tabla relación extra):** `cod_presupuesto_origen` en cabecera del pedido nuevo + registro en `presupuestos_cierres` (§15.4 producto).

## Criterios de aceptación

- [ ] **CA-01:** Conversión total genera pedido 0 y presupuesto 98.
- [ ] **CA-02:** Presupuesto 98 no aparece en listado activos (99).
- [ ] **CA-03:** Pedido nuevo tiene `cod_presupuesto_origen`; cierre tiene `cod_pedido_generado` coincidente.
- [ ] **CA-04:** E2E camino feliz documentado en TR.
- [ ] **CA-05:** Con `CodMotivoCierreExitoso` configurado, `presupuestos_cierres.id_motivo` coincide con el parámetro sin intervención del usuario.
- [ ] **CA-06:** Si el parámetro está vacío, el motivo no existe o está inactivo → error de negocio claro (no cerrar en 98).
- [ ] **CA-07:** Seed/documentación incluye al menos un motivo positivo de ejemplo y valor de parámetro para tests.

## Veredicto B1

**Lista para TR** (SPEC-101-04/05/12).
