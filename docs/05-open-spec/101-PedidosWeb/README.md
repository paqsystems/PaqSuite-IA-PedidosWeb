# SPEC 101 — PedidosWeb (MVP)

**SPEC madre:** [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md)  
**Producto:** `docs/02-producto/PedidosWeb/`  
**HU (parte B):** `docs/03-historias-usuario/101-PedidosWeb/` (a generar)  
**TR (parte C):** [docs/04-tareas/101-PedidosWeb/](../../04-tareas/101-PedidosWeb/README.md) — **15 TR** (2026-06-01)

## Revisión A1

| Campo | Valor |
|-------|--------|
| **Fecha cierre decisiones** | 2026-06-01 |
| **Veredicto** | **Apto** — decisiones humanas cerradas (§14 del SPEC madre) |
| **Parte B** | **Lista para iniciar** (enriquecimiento HU) |

## Índice de slices (`SPEC-101-xx`)

| ID | Archivo | Prioridad épica |
|----|---------|-----------------|
| 01 | [SPEC-101-01-backend-base.md](SPEC-101-01-backend-base.md) | Etapa posterior (`EMPRESAS_CONEXION`) |
| 02 | [SPEC-101-02-modelos.md](SPEC-101-02-modelos.md) | Must |
| 03 | [SPEC-101-03-repositories.md](SPEC-101-03-repositories.md) | Must |
| 04 | [SPEC-101-04-services-pedidos.md](SPEC-101-04-services-pedidos.md) | Must |
| 05 | [SPEC-101-05-controllers-rest.md](SPEC-101-05-controllers-rest.md) | Must |
| 06 | [SPEC-101-06-seguridad-visibilidad.md](SPEC-101-06-seguridad-visibilidad.md) | Must (verificar herencia GEN-02) |
| 07 | [SPEC-101-07-consultas-api.md](SPEC-101-07-consultas-api.md) | Must |
| 08 | [SPEC-101-08-logs-integracion.md](SPEC-101-08-logs-integracion.md) | **Should** |
| 09 | [SPEC-101-09-frontend-base.md](SPEC-101-09-frontend-base.md) | Must (verificar herencia GEN-01) |
| 10 | [SPEC-101-10-pantalla-carga.md](SPEC-101-10-pantalla-carga.md) | Must |
| 11 | [SPEC-101-11-consultas-ui.md](SPEC-101-11-consultas-ui.md) | Must |
| 12 | [SPEC-101-12-tratativas-cierre.md](SPEC-101-12-tratativas-cierre.md) | **Should** (tratativas); cierre 98 vía §5.3 madre |
| 13 | [SPEC-101-13-mails.md](SPEC-101-13-mails.md) | Must |
| 14 | [SPEC-101-14-dashboard.md](SPEC-101-14-dashboard.md) | Must |
| 15 | [SPEC-101-15-tests-hardening.md](SPEC-101-15-tests-hardening.md) | Must |
| 16 | [SPEC-101-16-importacion-pedido-individual-excel.md](SPEC-101-16-importacion-pedido-individual-excel.md) | **Should** — A1+B1+C+C1 cerrados; listo D1 |
| 17 | [SPEC-101-17-mobile-capacitor-pedidosweb.md](SPEC-101-17-mobile-capacitor-pedidosweb.md) | **Must** — **A1 + B1 + C1 v1 cerrados** (2026-06-30); **autorizada Parte D** `v1.2.0-mobile` |

## Épica mobile (`v1.2.0-mobile`)

| Campo | Valor |
|-------|--------|
| **A1** | **Apto con observaciones** (2026-06-30) |
| **B1** | **Cerrado** — [F-101-17-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-17-cierre-b1.md) |
| **C1 v1** | **Apto** — [F-101-17-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-17-cierre-c1.md) |
| **TR v1** | TR-101-17-mobile-v1-scaffold, login-tenant, stock-kardex |
| **HU v2/v3** | HU-101-034 … HU-101-036 (TR pendiente) |

| Fase | Tag | Contenido |
|------|-----|-----------|
| v1 | `v1.2.0-mobile` | Capacitor Android+iOS, login tenant, consulta **stock** kardex |
| v2 | `v1.2.1-mobile` | Todos listados y consultas kardex |
| v3 | `v1.2.2-mobile` | Carga pedidos mobile |

Transversal: [SPEC-001-11](../001-Generaliddes/SPEC-001-11-mobile-capacitor.md) · Patrón login: [`04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md)

## Dependencias transversales pendientes

| Tema | Documento | Nota |
|------|-----------|------|
| Parámetros generales | SPEC/contexto **001-04** (dedicado, en preparación) | Bloquea consumo formal de `MinutosWeb`, `DiasVentasDetalladas`, etc. |
| Tenancy `EMPRESAS_CONEXION` | [SPEC-101-01](SPEC-101-01-backend-base.md) | **Etapa posterior**; MVP actual usa stub `paq.tenant` |
| Export PDF consultas | [SPEC-001-06-emision](../001-Generaliddes/SPEC-001-06-emision.md) | Fuera MVP portal; consultas = Excel (GEN-03) |

## Orden sugerido Fase 1

Ver §10 de [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md). Omitir **101-01** hasta etapa tenancy; iniciar por **02 → 03 → 06 (verificación) → 04 → 05 → …**
