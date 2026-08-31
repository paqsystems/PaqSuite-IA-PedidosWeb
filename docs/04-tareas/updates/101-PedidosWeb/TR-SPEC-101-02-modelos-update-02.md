# TR-SPEC-101-02 — Modelos (update-02 — stockeable)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-02-modelos](../../101-PedidosWeb/TR-SPEC-101-02-modelos.md) |
| **SPEC update** | [SPEC-101-02-modelos-update-02](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-02-modelos-update-02.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. `ALTER` idempotente `pq_pedidosweb_articulos.stockeable` bit NOT NULL DEFAULT 1.
2. Modelo Eloquent + bootstrap schema si aplica.
3. Documentar en modelo de datos §3.4.

## AC técnicos

- [ ] **AC-CC12-T-M1:** Columna presente en tenant.
- [ ] **AC-CC12-T-M2:** Default true para filas existentes.
