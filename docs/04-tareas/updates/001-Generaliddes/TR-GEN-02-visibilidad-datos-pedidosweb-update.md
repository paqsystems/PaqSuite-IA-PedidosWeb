# TR-GEN-02 — Visibilidad datos (update — contactos API clientes)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-GEN-02-visibilidad-datos-pedidosweb](../../001-Generaliddes/TR-GEN-02-visibilidad-datos-pedidosweb.md) |
| **HU update** | [HU-GEN-02-visibilidad-datos-pedidosweb-update](../../../03-historias-usuario/updates/001-Generaliddes/HU-GEN-02-visibilidad-datos-pedidosweb-update.md) |
| **SPEC update** | [SPEC-001-02-acceso-y-seguridad-update](../../../05-open-spec/updates/001-Generaliddes/SPEC-001-02-acceso-y-seguridad-update.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #11, 18/08/2026 |
| **Última actualización** | 2026-08-18 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. En `VisibilityDataService::listVisibleClients` (o mapper equivalente): adjuntar `contactos` por `cod_client` (eager load o lookup batch; **no** N+1).
2. Nueva ruta `GET /api/v1/clientes/{codCliente}` **después** de las rutas más específicas (`cabecera-inicial`, `direcciones-entrega`):
   - mismo permiso `paqsuite_visibility.procedimientos.clientes`
   - mismo universo `visibleClientsForUser`
   - 404 si no visible / no existe
   - `resultado` = un objeto `VisibleClientItem` (no array)
3. OpenAPI: `VisibleClientContactItem`; `contactos` en `VisibleClientItem`; `ApiEnvelopeVisibleClient` para el GET unitario; `example` de envelope.
4. Tests feature: listado con 0 y N contactos; unitario 200/404; vendedor no ve contactos de cartera ajena.
5. FE: no mapear `contactos` en selector de carga (regresión HU-101-004).

### Envelope / paths

| Método | Path | `resultado` |
|--------|------|-------------|
| GET | `/api/v1/clientes` | array de ítems + `contactos[]` |
| GET | `/api/v1/clientes/{codCliente}` | un ítem + `contactos[]` |

401/400 tenant / 403: sin cambio respecto al listado vigente.

## AC técnicos

- [x] **AC-CC11-T-V1:** Listado incluye `contactos` camelCase.
- [x] **AC-CC11-T-V2:** GET unitario 200/401/403/404.
- [x] **AC-CC11-T-V3:** OpenAPI `resultado` tipado (no `ApiEnvelope` genérico).
- [x] **AC-CC11-T-V4:** Sin N+1; sin filtrar mails/teléfonos de clientes no visibles.
