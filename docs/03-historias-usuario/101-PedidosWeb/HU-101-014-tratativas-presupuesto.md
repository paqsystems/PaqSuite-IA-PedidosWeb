# HU-101-014 — Tratativas de presupuesto

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-014-tratativas-presupuesto |
| **SPEC origen** | [SPEC-101-12-tratativas-cierre](../../05-open-spec/101-PedidosWeb/SPEC-101-12-tratativas-cierre.md) |
| **Prioridad** | **Should** (AMB-C01) |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **vendedor**,  
quiero **registrar tratativas sobre presupuestos activos**,  
para **dejar seguimiento comercial mínimo sin un CRM completo**.

## Alcance incluido

- Solo presupuestos **estado 99**
- Campos mínimos producto §16: fecha/hora, usuario, comentario, resultado, próxima fecha/acción opcionales
- Tablas `pq_pedidosweb_tratativas` y `pq_pedidosweb_tratativas_resultados`

## Fuera de alcance

- Cierre/rechazo → 98 (HU-101-027, HU-101-013)
- CRM avanzado

## Criterios de aceptación

- [ ] **CA-01:** Alta de tratativa sobre presupuesto 99 visible en historial del comprobante.
- [ ] **CA-02:** No permite tratativas sobre presupuesto 98 o pedido.
- [ ] **CA-03:** Slice puede diferirse post E2E §9 sin bloquear release MVP.

## Veredicto B1

**Lista para TR** (SPEC-101-12) — prioridad Should.
