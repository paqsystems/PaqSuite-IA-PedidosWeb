# SPEC-001-02 — Acceso y seguridad (update — contactos en API clientes)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-001-02-acceso-y-seguridad-update |
| **SPEC base** | [SPEC-001-02-acceso-y-seguridad](../../001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-18 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #11 — **18/08/2026** |
| **Dependencias** | [SPEC-101-02-modelos-update-01](../101-PedidosWeb/SPEC-101-02-modelos-update-01.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Exponer los contactos de cada cliente visible en la **API de clientes** (listado y ficha unitaria), para consumidores **terceros**. PedidosWeb **no** usa el nodo en UI.

## In scope (delta)

Dueño vigente de `GET /api/v1/clientes`: TR-GEN-02-visibilidad (helper `visibleClientsForUser` + endpoint).

### Grupal

`GET /api/v1/clientes` — cada ítem de `resultado` incluye nodo hijo **`contactos`**: array (posiblemente vacío) con todos los contactos de ese `cod_client`.

### Unitario

Hoy **no** existe `GET /api/v1/clientes/{codCliente}` de ficha (sí `.../cabecera-inicial` y `.../direcciones-entrega`, de carga).

**Cerrar:** agregar `GET /api/v1/clientes/{codCliente}` con el **mismo shape** que un ítem del listado (incl. `contactos`), misma visibilidad y permiso `pw_clientes_visibles`. Fuera del universo → **404** sin fuga. No sustituye ni mezcla `cabecera-inicial`.

### Contrato JSON (camelCase)

Cada contacto:

| Campo API | Origen columna |
|-----------|----------------|
| `id` | `id` |
| `codContacto` | `cod_contacto` |
| `nombre` | `nombre` |
| `telefono` | `telefono` |
| `mail` | `mail` |

Orden sugerido: `codContacto` ascendente.

Envelope `{ error, respuesta, resultado }` sin cambio de forma. OpenAPI: extender `VisibleClientItem` + envelope unitario tipado.

Visibilidad: solo contactos de clientes ya visibles; no nuevo permiso de menú.

## Fuera de scope

- Pantallas PedidosWeb (carga, consultas, mail de comprobante, asistente).
- ABM de contactos.
- Cambiar reglas §7.3 de cartera.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU visibilidad | [HU-GEN-02-visibilidad-datos-pedidosweb-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-02-visibilidad-datos-pedidosweb-update.md) |
| HU selector (no usar nodo) | [HU-101-004-seleccion-cliente-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-004-seleccion-cliente-update.md) |
| TR visibilidad | [TR-GEN-02-visibilidad-datos-pedidosweb-update](../../../04-tareas/updates/001-Generaliddes/TR-GEN-02-visibilidad-datos-pedidosweb-update.md) |

## Definición de listo (update)

- [ ] Listado y GET unitario incluyen `contactos`.
- [ ] 401/403/404 según GEN-02 vigente.
- [ ] OpenAPI `resultado` tipado.
- [ ] FE carga no depende del nodo (regresión selector).

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 18/08/2026 | CC PQ #11 | Nodo `contactos` en API clientes |
| 18/08/2026 | Parte G | Volcado SPEC-update |
