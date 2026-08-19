# TR-SPEC-101-02 — Modelos (update-01 — clientescontactos)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-02-modelos](../../101-PedidosWeb/TR-SPEC-101-02-modelos.md) |
| **HU update** | Transversal (API: HU-GEN-02-update) |
| **SPEC update** | [SPEC-101-02-modelos-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-02-modelos-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #11, 18/08/2026 |
| **Última actualización** | 2026-08-18 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. DDL idempotente tabla `pq_pedidosweb_clientescontactos`:
   - `id` INT IDENTITY PK
   - `cod_client` (mismo tipo que `pq_pedidosweb_clientes.cod_client`)
   - `cod_contacto`, `nombre`, `telefono` nullable, `mail` nullable
   - UNIQUE (`cod_client`, `cod_contacto`)
2. Modelo Eloquent (p. ej. `PqPedidoswebClienteContacto`): `$table`, `$primaryKey = 'id'`, `$timestamps = false`, fillable/casts.
3. Relación `PqPedidoswebCliente::contactos()` hasMany.
4. Bootstrap schema dev / `ensure-schema` si el producto lo usa para maestros: **CREATE si falta**, sin DROP.
5. Documentar en `PedidosWeb_Modelo_Datos_Final.md` §3 en Parte I (o en D si se toca producto).

Acceso de lectura en API: misma vía que el listado de clientes vigente (`VisibilityDataService` / query sobre modelos). No introducir SQL concatenado. SP MUST: si en D se despliega SP, el listado de contactos debe ir por procedimiento; si se mantiene Eloquent del GET `/clientes` actual, documentar la excepción igual que ese endpoint.

**Excepción SP (D, 2026-08-18):** se mantiene Eloquent como el GET `/api/v1/clientes` vigente (`VisibleClientsResolver` + `PqPedidoswebClienteContacto`). No se desplegó SP nuevo. Lectura batch por `whereIn(cod_client)` sin N+1.

## AC técnicos

- [x] **AC-CC11-T-M1:** Tabla existe; unique (`cod_client`, `cod_contacto`).
- [x] **AC-CC11-T-M2:** Modelo + `hasMany` desde cliente; sin lógica de negocio en el modelo.
- [x] **AC-CC11-T-M3:** Sin DROP de tablas existentes.
