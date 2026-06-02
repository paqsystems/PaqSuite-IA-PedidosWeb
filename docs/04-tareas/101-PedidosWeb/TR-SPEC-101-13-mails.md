# TR-SPEC-101-13 — Mails comerciales al grabar o modificar

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-019-mail-grabar](../../03-historias-usuario/101-PedidosWeb/HU-101-019-mail-grabar.md) |
| **SPEC relacionada** | [SPEC-101-13-mails](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-04-services-pedidos; TR-SPEC-101-05-controllers-rest; [TR-GEN-02-recuperacion-contrasena](../001-Generaliddes/TR-GEN-02-recuperacion-contrasena.md) (canal mail); [SPEC-001-04](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) (`DetallePorMail`, `MailDestinatariosAdicionales`, `Mail_DireccionRemitente`, `mailCCO`) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** [HU-101-019-mail-grabar](../../03-historias-usuario/101-PedidosWeb/HU-101-019-mail-grabar.md)  
**Referencia SPEC:** [SPEC-101-13-mails](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Envío de correo tras grabar o modificar pedido/presupuesto usando el mismo canal técnico que recuperación de contraseña.

### Narrativa
Como **destinatario configurado**, quiero **recibir un correo al grabar o modificar un comprobante**, para **enterarme sin ingresar al portal**.

### In scope / Out of scope
- **In scope:** disparo post-grabación y post-modificación (pedido 0, presupuesto 99); resolución destinatarios TO; `DetallePorMail`; log de error sin inconsistencia de comprobante; BCC `mailCCO` si paramétrico.
- **Out of scope:** mail en eliminación, descarga ERP, cierre pedido ERP; PDF adjunto; mail en conversión/cierre salvo que HU futura lo pida.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Tras grabar pedido estado 0 se dispara envío (mock/fake en tests).
- **AC-02:** Tras modificar pedido 0 o presupuesto 99 se reenvía con mismas reglas.
- **AC-03:** Con `DetallePorMail` activo el cuerpo incluye renglones/resumen acordado en TR (tabla o texto).
- **AC-04:** Destinatarios TO — unión sin duplicados (case-insensitive), en orden de resolución:
  1. `pq_pedidosweb_clientes.e_mail` del `cod_client` del comprobante
  2. `pq_pedidosweb_vendedores.e_mail` del `clientes.cod_vended`
  3. `pq_pedidosweb_vendedores.mail_supervisor` del mismo vendedor, **solo si** informado y ≠ `e_mail` vendedor (case-insensitive)
  4. Direcciones en parámetro **`MailDestinatariosAdicionales`** (lista `;` o `,` — cerrar en D1)
- **AC-05:** Emails vacíos/inválidos se omiten; si no queda destinatario válido → log error, **sin** mail, comprobante persistido OK.
- **AC-06:** Direcciones repetidas entre fuentes aparecen una sola vez en TO.
- **AC-07:** Mismo **canal** que `TR-GEN-02-recuperacion-contrasena` (driver, config `mail.php`, remitente `Mail_DireccionRemitente`).
- **AC-08:** Fallo SMTP no revierte grabación; error en log integración o canal dedicado.
- **AC-09:** Sin secretos en repo; tests con `Mail::fake()` o log sink.

### Escenarios Gherkin

```gherkin
Feature: Mail al grabar comprobante

  Scenario: Destinatarios cliente vendedor supervisor
    Given cliente, vendedor y supervisor con emails distintos
    When graba pedido nuevo
    Then se envía un mail TO con las tres direcciones

  Scenario: Supervisor igual a vendedor no duplica
    Given mail_supervisor igual a e_mail vendedor
    When graba pedido
    Then solo una copia al vendedor en TO

  Scenario: DetallePorMail activo
    Given parametro DetallePorMail = 1
    When graba presupuesto
    Then el cuerpo incluye detalle de renglones

  Scenario: Fallo de envío no revierte grabación
    Given SMTP configurado para fallar
    When graba pedido
    Then el comprobante queda persistido
    And queda registro de error de mail
```

---

## 3) Reglas de Negocio

1. **RN-01:** Canal técnico = stack mail GEN-02 (Laravel Mail / config tenant).
2. **RN-02:** Asunto y cuerpo vía plantillas i18n o texto funcional acordado (cerrar claves en D1); locale del usuario de sesión o `es` fallback.
3. **RN-03:** `DetallePorMail` (ERP): si activo, incluir resumen/renglones; si inactivo, mensaje corto con cabecera.
4. **RN-04:** `mailCCO` global → BCC adicional si parámetro definido (independiente de TO).
5. **RN-05:** Normalizar emails (trim, lowercase para deduplicar).
6. **RN-06:** No mail en eliminación (HU-101-012) ni eventos fuera de §18 producto.
7. **RN-07:** Vendedor destinatario = vendedor del **cliente** del comprobante, no necesariamente usuario que cargó.

---

## 4) Impacto en Datos

### Tablas afectadas
- Lectura: `pq_pedidosweb_clientes`, `pq_pedidosweb_vendedores`, parámetros ERP
- Escritura log: `pq_pedidosweb_logs_integracion` (tipo `mail_error`) o tabla log acordada — alinear con TR-SPEC-101-08

### Seed mínimo para tests
- Cliente con `e_mail`
- Vendedor con `e_mail` y `mail_supervisor` distinto
- Parámetro `MailDestinatariosAdicionales` con 2 emails
- `DetallePorMail` on/off en casos de prueba

---

## 5) Contratos de API y OpenAPI

> No expone endpoints nuevos; comportamiento **post-commit** en services de grabación/modificación (TR-SPEC-101-04/05).

### 5.1 Endpoints del slice

| Método | Path | Notas |
|--------|------|--------|
| — | — | Side-effect en `POST/PUT` pedidos y presupuestos existentes |

### 5.2 Detalle por operación

Documentar en OpenAPI de **TR-SPEC-101-05** en `description` de `POST /api/v1/pedidos`, `PUT /api/v1/pedidos/{cod}`, `POST/PUT presupuestos`:

- «Dispara envío de mail comercial según SPEC-101-13; fallo de mail no altera respuesta 200 de persistencia.»

### 5.3 Actualización matriz permisos

- [ ] Sin filas nuevas; comportamiento en operaciones Alta/Modi existentes

---

## 6) Cambios Frontend

### Pantallas / componentes
- Sin UI específica; mensaje opcional no bloqueante si falla mail (toast info) — cerrar en D1
- No exponer destinatarios en respuesta API

### data-testid sugeridos
- N/A

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `ComprobanteMailService` + resolución destinatarios | AC-04–06 |
| T2 | Backend | Plantillas según `DetallePorMail` | AC-03 |
| T3 | Backend | Hook post-save en PedidoService/PresupuestoService | AC-01–02, AC-08 |
| T4 | Backend | Reutilizar mailer GEN-02 | AC-07 |
| T5 | Tests | Feature con Mail::fake + logs | AC-09 |
| T6 | Docs | Parámetros SPEC-001-04 | Sin secretos |

---

## 8) Estrategia de Tests

- **Unit:** `resolveDestinatarios()` deduplicación, omitir inválidos, supervisor = vendedor.
- **Integration:** Grabar pedido → assert sent; DetallePorMail on/off; fallo mail → 200 + log.
- **E2E:** Opcional verificación indirecta (no exigir bandeja real); flujo §9 paso 6 «mail enviado» con fake/monitoreo QA.

---

## 9) Riesgos y Edge Cases

- SMTP producción distinto de QA → variables entorno documentadas.
- Lista `MailDestinatariosAdicionales` mal formada → parse robusto + log.
- Locale mail vs locale UI desalineados.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Canal idéntico a recuperación contraseña verificado
- [ ] Tests fake sin secretos

### Checklist normas transversales

- [ ] Sin endpoints nuevos sin documentar side-effect en 101-05
- [ ] Envelope de grabación sin cambio por fallo mail
- [ ] Sin ampliación de alcance (PDF, mail eliminación)

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- `ComprobanteMailService`, plantillas mail
- Integración en services grabación TR-SPEC-101-04

### OpenAPI
- Descripciones POST/PUT pedidos/presupuestos

### Docs
- Parámetros mail en runbook QA
