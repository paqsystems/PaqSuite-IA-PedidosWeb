# HU-101-019 — Envío de mail al grabar o modificar

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-019-mail-grabar |
| **SPEC origen** | [SPEC-101-13-mails](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-101-009, HU-101-010; SPEC-001-04 (`DetallePorMail`) |

## Narrativa

Como **destinatario configurado**,  
quiero **recibir un correo cuando se graba o modifica un comprobante**,  
para **enterarme sin ingresar al portal**.

## Reglas de negocio

1. Mismo **canal técnico** que recuperación de contraseña (GEN-02).
2. Contenido con/sin detalle según **`DetallePorMail`**.
3. **No** mail en eliminación, descarga ERP ni cierre pedido ERP (producto §14).
4. Fallo de mail no debe dejar comprobante inconsistente (log error; política en TR).
5. **Destinatarios (TO)** — unión sin duplicados de direcciones válidas, en este orden de resolución:
   1. **Cliente:** `pq_pedidosweb_clientes.e_mail` del `cod_client` del comprobante.
   2. **Vendedor del cliente:** `pq_pedidosweb_vendedores.e_mail` del vendedor en `clientes.cod_vended` (no el usuario que cargó si difiere).
   3. **Supervisor:** `pq_pedidosweb_vendedores.mail_supervisor` del mismo registro de vendedor del cliente, **solo si** está informado y es **distinto** del `e_mail` del vendedor del cliente (comparación case-insensitive).
   4. **Adicionales paramétricos:** todas las direcciones listadas en **`MailDestinatariosAdicionales`** (SPEC-001-04 / producto §10.6); formato lista separada por `;` o `,` (definir en TR).
6. Direcciones vacías o inválidas se omiten; no abortan el envío si queda al menos un destinatario válido (si no queda ninguno → log error, sin mail).
7. **`mailCCO`** (copia oculta global) es independiente: si el ERP lo define, aplica además como BCC según política de canal (TR).

## Criterios de aceptación

- [ ] **CA-01:** Tras grabar pedido 0 se dispara envío (mock en tests).
- [ ] **CA-02:** Con `DetallePorMail` activo el cuerpo incluye renglones/resumen acordado.
- [ ] **CA-03:** Con cliente, vendedor y `mail_supervisor` distintos, los tres reciben el mail (test con fakes/logs).
- [ ] **CA-04:** Si `mail_supervisor` coincide con `e_mail` del vendedor del cliente, **no** se agrega duplicado.
- [ ] **CA-05:** Cada dirección en `MailDestinatariosAdicionales` se incluye; emails repetidos entre fuentes aparecen una sola vez.
- [ ] **CA-06:** Tras modificar comprobante (pedido 0 / presupuesto 99) se reenvía con las mismas reglas de destinatarios.

## Veredicto B1

**Lista para TR** (SPEC-101-13). Destinatarios cerrados (2026-06-01).
