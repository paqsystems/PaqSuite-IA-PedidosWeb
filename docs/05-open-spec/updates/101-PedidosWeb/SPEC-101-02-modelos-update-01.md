# SPEC-101-02 — Modelos (update-01 — clientes contactos)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-02-modelos-update-01 |
| **SPEC base** | [SPEC-101-02-modelos](../../101-PedidosWeb/SPEC-101-02-modelos.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-18 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #11 — **18/08/2026** |
| **Dependencias** | Migración aditiva (sin DROP); [SPEC-001-02-acceso-y-seguridad-update](../001-Generaliddes/SPEC-001-02-acceso-y-seguridad-update.md) (payload API) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Incorporar maestro **contactos de cliente** (teléfono y mail) para exposición en la API de clientes. **Sin** consumo en pantallas PedidosWeb.

## Nomenclatura canónica (Parte G)

| Concepto | Nombre canónico | Nota CC |
|----------|-----------------|---------|
| Tabla | `pq_pedidosweb_clientescontactos` | CC: `PQ_PEDIDOSWEB_CLIENTESCONTACTOS` (informal / casing ERP) |
| FK cliente | `cod_client` | Igual que `pq_pedidosweb_clientes` |
| Código contacto | `cod_contacto` | CC: `cod_contacto` |
| Nombre | `nombre` | CC: «Nombre Contacto» |
| Teléfono | `telefono` | — |
| Mail | `mail` | Distinto de `e_mail` del maestro cliente |
| Surrogate | `id` | **Sí:** identity entero. El maestro `pq_pedidosweb_clientes` **no** usa `id` (`PK = cod_client`); `clientesde` usa `id_de` en clave compuesta. Contactos: `id` PK + unique (`cod_client`, `cod_contacto`). |

## In scope (delta)

### `pq_pedidosweb_clientescontactos`

| Columna | Tipo | Regla |
|---------|------|-------|
| `id` | int identity | PK |
| `cod_client` | string | FK lógica a `pq_pedidosweb_clientes.cod_client`; obligatorio |
| `cod_contacto` | string | Código de contacto; unique con `cod_client` |
| `nombre` | string | Nombre del contacto |
| `telefono` | string nullable | — |
| `mail` | string nullable | — |

Relación: `Cliente` 1:N `ClienteContacto`. Sin timestamps salvo que el DDL ERP los traiga.

Modelo Eloquent: PK `id`, casts, `belongsTo` cliente; **sin** reglas de negocio.

Alta/edición de filas: **fuera de PedidosWeb** (ERP / integración). El portal solo **lee**.

## Fuera de scope

- DROP / recreate de `pq_pedidosweb_clientes`.
- ABM web de contactos.
- Usar contactos en carga, mails de comprobante o selector de cliente.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| TR modelos | [TR-SPEC-101-02-modelos-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-02-modelos-update-01.md) |
| API clientes | [SPEC-001-02-acceso-y-seguridad-update](../001-Generaliddes/SPEC-001-02-acceso-y-seguridad-update.md) |

## Definición de listo (update)

- [ ] Tabla/migración o SQL idempotente `CREATE TABLE IF NOT EXISTS` / `ALTER` aditivo.
- [ ] Modelo Eloquent + relación desde cliente.
- [ ] Unique (`cod_client`, `cod_contacto`).

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 18/08/2026 | CC PQ #11 | Tabla contactos de cliente |
| 18/08/2026 | Parte G | Volcado SPEC-update-01 |
