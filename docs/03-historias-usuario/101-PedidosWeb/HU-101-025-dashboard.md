# HU-101-025 — Dashboard operativo §4.1

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-025-dashboard |
| **SPEC origen** | [SPEC-101-14-dashboard](../../05-open-spec/101-PedidosWeb/SPEC-101-14-dashboard.md), SPEC madre §4.1 |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | Parámetro `MinutosWeb`; reemplaza demo `GET /dashboard/resumen` |

## Narrativa

Como **usuario comercial**,  
quiero **ver indicadores operativos al entrar al dashboard**,  
para **monitorear pedidos y presupuestos de mi universo visible**.

## Indicadores (8)

Q/$ presupuestos activos (99), Q/$ pedidos ingresados, Q/$ pedidos pendientes (1), top cliente $ presupuestos, top cliente $ pedidos ingresados.

## Regla pedidos ingresados (AMB-C09)

Incluir estados **0** y **-1**, **excluyendo** comprobantes donde  
`fechahora_ultima_actividad + MinutosWeb >= fechahora_actual` (modificación **-1** activa — ver HU-101-011).

## UI

- Patrón visual (fuente de verdad): [`patron-dashboard-operativo-ui.md`](../../02-producto/PedidosWeb/patron-dashboard-operativo-ui.md) — layout tipo Producción (Tango), 8 KPIs en tarjetas agrupadas.

## Criterios de aceptación

- [ ] **CA-01:** Los 8 indicadores visibles tras login con datos seed.
- [ ] **CA-02:** Coherentes con consultas para mismo usuario.
- [ ] **CA-03:** Un solo símbolo moneda por tenant.
- [ ] **CA-04:** E2E §9 paso dashboard verde.
- [ ] **CA-05:** Top clientes (indicadores 7 y 8): empate en métrico → desempate por **razón social** A–Z (TR-101-14 AC-06).

## Veredicto B1

**Lista para TR** (SPEC-101-14). Desempate top clientes cerrado en TR (2026-06-02).
