# HU-101-019 — Envío de mail al grabar o modificar

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-019-mail-grabar |
| **SPEC origen** | [SPEC-101-13-mails](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-101-009, HU-101-010; SPEC-001-04 (`DetallePorMail`) |

## Narrativa

Como **destinatario configurado**,  
quiero **recibir un correo cuando se graba o modifica un comprobante**,  
para **enterarme sin ingresar al portal**.

## Reglas de negocio

1. Mismo **canal técnico** que recuperación de contraseña (GEN-02).
2. Contenido según **`DetallePorMail`**: con detalle → tabla de renglones; sin detalle → **solo se omite la tabla** (cabecera completa).
3. **No** mail en eliminación, descarga ERP ni cierre pedido ERP (producto §18).
4. Fallo de mail no debe dejar comprobante inconsistente (log error). Si la grabación fue exitosa pero el mail falló, la UI **avisa al usuario** con toast informativo no bloqueante (TR-101-13 §6).
5. **Destinatarios (TO)** — unión sin duplicados de direcciones válidas, en este orden de resolución:
   1. **Cliente:** `pq_pedidosweb_clientes.e_mail` del `pq_pedidosweb_pedidoscabecera.cod_cliente` del comprobante.
   2. **Vendedor del cliente:** `pq_pedidosweb_vendedores.e_mail` del vendedor en `pq_pedidosweb_clientes.cod_vended` (no el usuario que cargó si difiere).
   3. **Supervisor:** `pq_pedidosweb_vendedores.mail_supervisor` del mismo registro de vendedor del cliente, **solo si** está informado y es **distinto** del `e_mail` del vendedor del cliente (comparación case-insensitive).
   4. **Adicionales paramétricos:** direcciones en **`MailDestinatariosAdicionales`** — formato oficial **`;`** (parser tolerante `;` y `,`); ver TR-101-13 §3.1.
6. Direcciones vacías o inválidas se omiten; no abortan el envío si queda al menos un destinatario válido (si no queda ninguno → log error, sin mail).
7. **`mailCCO`** (copia oculta global) es independiente: si el ERP lo define, aplica además como BCC según política de canal (TR).
8. **Asunto:** `{nombreEmpresa} - {tipoComprobante} {accionComprobante}` según **locale de sesión** del usuario que operó.
9. **Intro:** plantilla única por acción (`ingresado` / `modificado`) con código = últimos caracteres de **`guidSufijo`** y nombre de empresa (tenant / `EMPRESAS_CONEXION`; fallback «Empresa»).
10. **Formatos:** fecha prioriza `MM/DD/YYYY` ante duda; importes con signo adelante; bonificaciones como `valor %` (espacio antes de `%`). Detalle en [TR-SPEC-101-13-mails](../../04-tareas/101-PedidosWeb/TR-SPEC-101-13-mails.md) §3.2–3.4.
11. **Campos del cuerpo:** cabecera (13 filas), detalle condicional (7 columnas) y pie (**solo texto fijo i18n**, sin leyendas ni URL) según TR-101-13 §3.2 — **cerrado producto 2026-06-02**.
12. **Idiomas del mail:** claves `mail.comprobanteNotification.*` en **`es`**, **`en`**, **`fr`**, **`pt`**, **`it`** (locales habilitados del portal).

## Criterios de aceptación

- [ ] **CA-01:** Tras grabar pedido 0 se dispara envío (mock en tests).
- [ ] **CA-02:** Con `DetallePorMail` activo el cuerpo incluye **tabla de renglones**; con `DetallePorMail = 0` la tabla **no** aparece y la cabecera sí.
- [ ] **CA-03:** Con cliente, vendedor y `mail_supervisor` distintos, los tres reciben el mail (test con fakes/logs).
- [ ] **CA-04:** Si `mail_supervisor` coincide con `e_mail` del vendedor del cliente, **no** se agrega duplicado.
- [ ] **CA-05:** Cada dirección en `MailDestinatariosAdicionales` (`;`) se incluye; emails repetidos entre fuentes aparecen una sola vez.
- [ ] **CA-06:** Tras modificar comprobante (pedido 0 / presupuesto 99) se reenvía con las mismas reglas; asunto/intro reflejan **modificado**.
- [ ] **CA-07:** Asunto y cuerpo usan locale de sesión (claves i18n, sin literales hardcodeados en código); existen traducciones en **es, en, fr, pt, it**.
- [ ] **CA-08:** Si grabó OK pero el mail no salió, el usuario ve un **toast informativo** (no error bloqueante); el comprobante permanece guardado.

## Veredicto B1

**Lista para TR** (SPEC-101-13). Destinatarios, plantillas y aviso UI ante fallo mail cerrados en TR (2026-06-02).
