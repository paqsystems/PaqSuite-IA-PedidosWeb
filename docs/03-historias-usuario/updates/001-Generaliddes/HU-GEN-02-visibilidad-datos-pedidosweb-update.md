# HU-GEN-02 — Visibilidad datos (update — contactos API clientes)

| Campo | Valor |
|-------|--------|
| **HU base** | [HU-GEN-02-visibilidad-datos-pedidosweb](../../001-Generaliddes/HU-GEN-02-visibilidad-datos-pedidosweb.md) |
| **SPEC update** | [SPEC-001-02-acceso-y-seguridad-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-02-acceso-y-seguridad-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #11, 18/08/2026 |
| **Última actualización** | 2026-08-18 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Narrativa (delta)

Como **consumidor autenticado de la API de clientes** (portal u otra aplicación),  
quiero **recibir los contactos (nombre, teléfono, mail) de cada cliente visible**,  
para **usarlos fuera de PedidosWeb sin ABM ni pantallas nuevas en el portal**.

## Criterios de aceptación

- [ ] **CA-CC11-V01:** `GET /api/v1/clientes` incluye `contactos` (array) en cada ítem; vacío si no hay filas.
- [ ] **CA-CC11-V02:** `GET /api/v1/clientes/{codCliente}` devuelve el mismo shape (incl. `contactos`) si el cliente está en el universo visible.
- [ ] **CA-CC11-V03:** Cliente fuera de visibilidad → 404; sin permiso base → 403; sin auth → 401. Igual que el listado vigente.
- [ ] **CA-CC11-V04:** No se listan contactos de clientes no visibles.
- [ ] **CA-CC11-V05:** Campos por contacto: `id`, `codContacto`, `nombre`, `telefono`, `mail`.

## Fuera de alcance

- UI PedidosWeb; ABM contactos; mails de grabación de comprobante (`e_mail` del maestro cliente sigue igual).
