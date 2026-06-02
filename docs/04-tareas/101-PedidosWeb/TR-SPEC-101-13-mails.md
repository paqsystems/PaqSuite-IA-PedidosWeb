# TR-SPEC-101-13 — Mails comerciales al grabar o modificar

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-019-mail-grabar](../../03-historias-usuario/101-PedidosWeb/HU-101-019-mail-grabar.md) |
| **SPEC relacionada** | [SPEC-101-13-mails](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-04-services-pedidos; TR-SPEC-101-05-controllers-rest; [TR-GEN-02-recuperacion-contrasena](../001-Generaliddes/TR-GEN-02-recuperacion-contrasena.md) (canal mail); [SPEC-001-04](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) (`DetallePorMail`, `MailDestinatariosAdicionales`, `Mail_DireccionRemitente`, `mailCCO`) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-02 |

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
  1. `pq_pedidosweb_clientes.e_mail` del `pq_pedidosweb_pedidoscabecera.cod_cliente` del comprobante
  2. `pq_pedidosweb_vendedores.e_mail` del `pq_pedidosweb_clientes.cod_vended` (join por `cod_cliente`)
  3. `pq_pedidosweb_vendedores.mail_supervisor` del mismo vendedor, **solo si** informado y ≠ `e_mail` vendedor (case-insensitive)
  4. Direcciones en parámetro **`MailDestinatariosAdicionales`** (lista separada por **`;`** — ver §3.2)
- **AC-05:** Emails vacíos/inválidos se omiten; si no queda destinatario válido → log error, **sin** mail, comprobante persistido OK.
- **AC-06:** Direcciones repetidas entre fuentes aparecen una sola vez en TO.
- **AC-07:** Mismo **canal** que `TR-GEN-02-recuperacion-contrasena` (driver, config `mail.php`, remitente `Mail_DireccionRemitente`).
- **AC-08:** Fallo SMTP no revierte grabación; error en log integración o canal dedicado.
- **AC-09:** Sin secretos en repo; tests con `Mail::fake()` o log sink.
- **AC-10:** Si la grabación responde **200** pero el mail no se envió, la API expone indicador en `resultado` (ej. `mailEnviado: false`) y la UI muestra **toast informativo** i18n (no bloqueante, no revierte grabación) — ver §6.

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
2. **RN-02:** Asunto y cuerpo vía **claves i18n** (`backend/lang/{locale}/mail.php`) y vistas Blade `backend/resources/views/emails/comprobante-notification-*.blade.php`; **locale = sesión del usuario** que grabó/modificó; fallback `es` si ausente. **Locales obligatorios** (mismos que el portal): **`es`**, **`en`**, **`fr`**, **`pt`**, **`it`** — bloque completo `comprobanteNotification.*` en cada uno.
3. **RN-03:** `DetallePorMail` (ERP): si **activo (1)**, incluir **tabla de renglones**; si **inactivo (0)**, **omitir solo la tabla** — el bloque de cabecera del mail se mantiene completo.
4. **RN-04:** `mailCCO` global → BCC adicional si parámetro definido (independiente de TO).
5. **RN-05:** Normalizar emails (trim, lowercase para deduplicar).
6. **RN-06:** No mail en eliminación (HU-101-012) ni eventos fuera de §18 producto.
7. **RN-07:** Vendedor destinatario = vendedor del **cliente** del comprobante, no necesariamente usuario que cargó.
8. **RN-08:** El `Mailable` / `ComprobanteMailService` debe resolver `accionComprobante`: `ingresado` (alta nueva) | `modificado` (PUT o grabación sobre comprobante existente) y `tipoComprobante`: `pedido` | `presupuesto`.
9. **RN-09 (UI — cerrado D1-06):** Si el comprobante se grabó OK pero falló el envío de mail, la pantalla de carga (TR-101-10) muestra un **toast informativo** al usuario (DevExtreme `notify` o equivalente del proyecto). **No** es error bloqueante; **no** revierte la grabación. El fallo también se registra en log (AC-08).

### 3.1 Parámetro `MailDestinatariosAdicionales` (cerrado D1)

| Regla | Valor |
|-------|--------|
| Formato **oficial** de guardado/documentación | Lista separada por **`;`** — ej. `compras@acme.com;gerencia@acme.com` |
| Parser en runtime | Tolerante: acepta **`;`** y **`,`** (legado) |
| Normalización | `trim` + lowercase para deduplicar (case-insensitive) |
| Inválidos | Se omiten; log |
| Sin destinatarios válidos | No envía mail; log; comprobante persistido OK |

### 3.2 Plantillas y contenido del mail (cerrado D1 — legacy)

#### Asunto

Patrón i18n (según locale de sesión):

```text
{nombreEmpresa} - {tipoComprobante} {accionComprobante}
```

Ejemplo ES: `Lacapol - Pedido ingresado`

#### Intro (cuerpo)

Una plantilla i18n por acción (`intro.ingresado` / `intro.modificado`) con variables:

| Variable | Origen |
|----------|--------|
| `tipoComprobante` | Label i18n (`pedido` / `presupuesto`) |
| `guidSufijo` | **Últimos caracteres** del sufijo GUID del comprobante (ej. `C48EB0`) |
| `nombreEmpresa` | Ver §3.3; fallback i18n `Empresa` |

Ejemplo ES ingresado: *«Se ha ingresado el pedido con código C48EB0 a la web de Lacapol.»*  
Ejemplo ES modificado: *«Se ha modificado el pedido con código C48EB0 a la web de Lacapol.»*

#### Bloque cabecera (siempre en el mail)

Orden de visualización y origen de datos (**cerrado producto 2026-06-02**):

| # | Etiqueta mail (i18n) | Origen / cálculo | Formato |
|---|----------------------|------------------|---------|
| 1 | Fecha | `pq_pedidosweb_pedidoscabecera.fecha` | §3.4 |
| 2 | Cliente | `pq_pedidosweb_pedidoscabecera.cod_cliente` | Texto |
| 3 | Razón Social | `pq_pedidosweb_clientes.nombre` (join: `pq_pedidosweb_pedidoscabecera.cod_cliente` → `pq_pedidosweb_clientes.cod_client`) | Texto |
| 4 | Vendedor | `pq_pedidosweb_pedidoscabecera.cod_vended` + `pq_pedidosweb_vendedores.nombre` | `{cod} ( {nombre} )` |
| 5 | Transporte | `pq_pedidosweb_pedidoscabecera.cod_transpor` + `pq_pedidosweb_transportes.nombre` | `{cod} ( {nombre} )` |
| 6 | Lista de Precios | `pq_pedidosweb_pedidoscabecera.lista_precios` + `pq_pedidosweb_listaprecios.descripcion` | `{cod} ( {descripcion} )` |
| 7 | Condición de Venta | `pq_pedidosweb_pedidoscabecera.cod_condvta` + `pq_pedidosweb_condventa.descripcion` | `{cod} ( {descripcion} )` |
| 8 | Nivel | `pq_pedidosweb_pedidoscabecera.nivel` | Numérico |
| 9 | Cantidades | `SUM(pq_pedidosweb_pedidosdetalle.cantidad)` del comprobante | Numérico |
| 10 | Importe Bruto | `pq_pedidosweb_pedidoscabecera.total` | §3.4 (signo adelante) |
| 11 | Importe Neto | `pq_pedidosweb_pedidoscabecera.total + pq_pedidosweb_pedidoscabecera.total_iva` | §3.4 |
| 12 | Bonificación | `pq_pedidosweb_pedidoscabecera.descuento` | §3.4 (`valor %` con espacio) |
| 13 | Observaciones | `pq_pedidosweb_pedidoscabecera.observaciones` | **Siempre** mostrar la línea (vacío permitido) |

> Referencias de datos según [modelo oficial](../../02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md): tablas `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`, etc.

> Estos campos definen **contenido del correo**, no obligatoriedad de la pantalla de carga (TR-101-10).

#### Tabla renglones (`DetallePorMail = 1`)

Orden de columnas (**cerrado producto 2026-06-02**):

| # | Etiqueta mail (i18n) | Origen / cálculo | Formato |
|---|----------------------|------------------|---------|
| 1 | Código | `pq_pedidosweb_pedidosdetalle.cod_articulo` | Texto |
| 2 | Descripción | `pq_pedidosweb_articulos.descripcion` (join por `cod_articulo`) | Texto |
| 3 | Cant. | `pq_pedidosweb_pedidosdetalle.cantidad` | Numérico |
| 4 | Precio | `pq_pedidosweb_pedidosdetalle.precio` | §3.4 |
| 5 | % Bonif. | `pq_pedidosweb_pedidosdetalle.porc_bonif` | §3.4 (`valor %`) |
| 6 | Precio neto | `pq_pedidosweb_pedidosdetalle.precio_neto` | §3.4 |
| 7 | Importe | `pq_pedidosweb_pedidosdetalle.cantidad × pq_pedidosweb_pedidosdetalle.precio_neto` | §3.4 |

#### Pie

**Cerrado producto 2026-06-02:** el pie del mail es **únicamente texto fijo i18n** — clave `mail.comprobanteNotification.footerConsulta`. **Sin** leyendas dinámicas (`leyenda_1`…`leyenda_5`), **sin** URL del portal ni enlaces.

| Referencia ES | Clave i18n |
|---------------|------------|
| *«Consulte en nuestro sitio por el estado del mismo.»* | `footerConsulta` |

### 3.3 `nombreEmpresa`

| Etapa | Resolución |
|-------|------------|
| MVP (stub tenant) | Slug `{cliente}` de `X-Paq-Cliente` / URL (`lacapol` → display `Lacapol`) |
| Con `EMPRESAS_CONEXION` (SPEC-101-01) | Nombre comercial de la fila del tenant |
| Fallback | Clave i18n `mail.comprobanteNotification.empresaFallback` → «Empresa» |

### 3.4 Formato de fechas, importes y bonificaciones

Formatear en **backend** antes de renderizar la plantilla (`formatMailDate`, `formatMailAmount`, `formatMailPercent` o equivalente).

| Tipo | Regla |
|------|--------|
| **Fecha** | Ante la duda, **`MM/DD/YYYY`** (ej. `06/02/2026`) |
| **Importe** | Signo de moneda **siempre adelante** (ej. `$ 197,91`) |
| **Bonificación cabecera (`descuento`) y `porc_bonif` renglón** | Valor + **espacio** + **`%`** al final (ej. `0,00 %`) |

Separadores decimales adicionales pueden seguir locale de sesión cuando no contradigan las reglas anteriores.

### 3.5 Implementación de referencia

| Artefacto | Ruta |
|-----------|------|
| Claves i18n | `backend/lang/{es,en,fr,pt,it}/mail.php` → `comprobanteNotification.*` |
| Asunto Blade | `backend/resources/views/emails/comprobante-notification-subject.blade.php` |
| Cuerpo Blade | `backend/resources/views/emails/comprobante-notification-body.blade.php` |
| Mailable | `ComprobanteNotificationMail` (crear en implementación) |

#### Claves i18n mínimas (`comprobanteNotification`)

| Grupo | Claves | Uso |
|-------|--------|-----|
| Asunto / intro | `subject`, `intro.ingresado`, `intro.modificado`, `empresaFallback` | Asunto e intro |
| Catálogos | `tipoComprobante.*`, `tipoComprobanteIntro.*`, `accionComprobante.*` | Labels según tipo y acción |
| Cabecera | `cabecera.fecha`, `cabecera.cliente`, `cabecera.razonSocial`, `cabecera.vendedor`, `cabecera.transporte`, `cabecera.listaPrecios`, `cabecera.condicionVenta`, `cabecera.nivel`, `cabecera.cantidades`, `cabecera.importeBruto`, `cabecera.importeNeto`, `cabecera.descuento`, `cabecera.observaciones` | Etiquetas §3.2 cabecera (campo BD: `pq_pedidosweb_pedidoscabecera.descuento`) |
| Detalle | `detalle.codigo`, `detalle.descripcion`, `detalle.cantidad`, `detalle.precio`, `detalle.porcBonif`, `detalle.precioNeto`, `detalle.importe` | Encabezados tabla renglones (campo BD: `pq_pedidosweb_pedidosdetalle.porc_bonif`) |
| Pie | `footerConsulta` | Texto fijo de cierre (§3.2 Pie) |

Redacción completa en **los 5 locales** del portal antes de cerrar TR de implementación.

---

## 4) Impacto en Datos

### Tablas afectadas

**Lectura (mail):**

- `pq_pedidosweb_pedidoscabecera` — cabecera del comprobante
- `pq_pedidosweb_pedidosdetalle` — renglones (si `DetallePorMail = 1`)
- `pq_pedidosweb_clientes` — razón social, `e_mail`
- `pq_pedidosweb_vendedores` — nombre vendedor, `e_mail`, `mail_supervisor`
- `pq_pedidosweb_transportes` — nombre transporte
- `pq_pedidosweb_listaprecios` — descripción lista
- `pq_pedidosweb_condventa` — descripción condición venta
- `pq_pedidosweb_articulos` — descripción artículo
- Parámetros ERP (`DetallePorMail`, `MailDestinatariosAdicionales`, …)
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

Documentar en OpenAPI de **TR-SPEC-101-05** en `description` de `POST /api/v1/comprobantes/grabar` (y alias pedidos/presupuestos si existen):

- «Dispara envío de mail comercial según SPEC-101-13; fallo de mail no altera respuesta 200 de persistencia.»
- Campo opcional en `resultado`: **`mailEnviado`** (`boolean`) — `true` si el mail se despachó; `false` si falló o no hubo destinatarios válidos (UI toast TR-101-13 §6).

### 5.3 Actualización matriz permisos

- [ ] Sin filas nuevas; comportamiento en operaciones Alta/Modi existentes

---

## 6) Cambios Frontend

### Pantallas / componentes

- **Pantalla de carga** (`PedidosCargaPage`, TR-101-10): tras grabación exitosa (`error === 0`), si `resultado.mailEnviado === false`, mostrar **toast informativo** (tipo *warning* o *info*, no *error*).
- Texto desde i18n activo — clave sugerida: `pedidos.carga.mailEnvioFallido` (equivalentes en los 5 locales del portal).
- Mensaje humano (referencia ES): *«El comprobante se guardó correctamente, pero no pudimos enviar el correo de aviso.»*
- No exponer destinatarios ni detalle SMTP al usuario.
- No impedir continuar ni cerrar la pantalla.

### data-testid sugeridos

| Control | data-testid |
|---------|-------------|
| Toast fallo mail (contenedor o trigger verificable) | `toast-mail-envio-fallido` |

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `ComprobanteMailService` + resolución destinatarios | AC-04–06 |
| T2 | Backend | Plantillas según `DetallePorMail` | AC-03 |
| T3 | Backend | Hook post-save en PedidoService/PresupuestoService | AC-01–02, AC-08 |
| T4 | Backend | Reutilizar mailer GEN-02 | AC-07 |
| T5 | Tests | Feature con Mail::fake + logs + assert `mailEnviado` | AC-09–10 |
| T6 | Docs | Parámetros SPEC-001-04 | Sin secretos |
| T7 | Frontend | Toast i18n en pantalla carga si `mailEnviado === false` | AC-10 |

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
