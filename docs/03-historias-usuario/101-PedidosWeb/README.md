# Historias de usuario — 101-PedidosWeb

Convención: **`HU-101-NNN-tema.md`** derivadas de [PedidosWeb_SPEC_MVP.md](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md) y slices `SPEC-101-xx`.

**A1 (2026-06-01):** Cerrado en SPEC madre §14.  
**B1 (2026-06-01):** 27 HU enriquecidas en esta carpeta.  
**B1 (2026-06-17):** SPEC-101-16 — HU-101-029 y HU-101-030 **B1 cerrado** ([F-101-16-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-16-cierre-b1.md)); estado **Especificado**.  
**B1 (2026-06-30):** SPEC-101-17 — HU-101-031…036 **B1 cerrado** ([F-101-17-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-17-cierre-b1.md)); estado **Especificado**.  
**B1 (2026-07-13):** SPEC-101-18/19/20 — HU-101-037…042 **B1 cerrado** ([F-101-18-20-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-b1-asistente-carga-ia.md)); estado **Especificado**.  
**B (2026-07-19):** SPEC-101-21 — HU-101-043…045 **generadas**.  
**B1 (2026-07-19):** SPEC-101-21 — HU-101-043…045 **B1 cerrado** ([F-101-21-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-b1-importacion-masiva.md)); estado **Especificado**.  
**C (2026-06-17):** TR-16a y TR-16b generadas.  
**C1 (2026-06-17):** Revisión C1 **Apto** — [F-101-16-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-16-cierre-c1.md); listo **D1**.

## Índice

| HU | Prioridad | SPEC slice | Título |
|----|-----------|------------|--------|
| [HU-101-001](HU-101-001-login.md) | Must | 101-06 | Login (heredado GEN-02) |
| [HU-101-002](HU-101-002-recuperacion-contrasena.md) | Must | 101-06 | Recuperación contraseña (heredado) |
| [HU-101-003](HU-101-003-resolucion-tenant.md) | Etapa posterior | 101-01 | Resolución tenant `EMPRESAS_CONEXION` |
| [HU-101-004](HU-101-004-seleccion-cliente.md) | Must | 101-10 | Selección de cliente |
| [HU-101-005](HU-101-005-inicializacion-cabecera.md) | Must | 101-10 | Inicialización cabecera desde cliente |
| [HU-101-006](HU-101-006-carga-renglones.md) | Must | 101-10 | Carga de renglones |
| [HU-101-007](HU-101-007-bonificacion-neta.md) | Must | 101-04 | Bonificación neta |
| [HU-101-008](HU-101-008-precio-importes.md) | Must | 101-04 | Precio neto e importes / IVA |
| [HU-101-009](HU-101-009-grabar-pedido.md) | Must | 101-04, 101-10 | Grabación pedido (estado 0) |
| [HU-101-010](HU-101-010-grabar-presupuesto.md) | Must | 101-04, 101-10 | Grabación presupuesto (estado 99) |
| [HU-101-011](HU-101-011-editar-pedido.md) | Must | 101-04 | Edición pedido ingresado |
| [HU-101-012](HU-101-012-eliminar-pedido.md) | Must | 101-04 | Eliminación pedido estado 0 |
| [HU-101-013](HU-101-013-conversion-presupuesto-pedido.md) | Must | 101-04 | Conversión presupuesto → pedido |
| [HU-101-014](HU-101-014-tratativas-presupuesto.md) | **Should** | 101-12 | Tratativas presupuesto activo |
| [HU-101-015](HU-101-015-consulta-pedidos-ingresados.md) | Must | 101-07, 101-11 | Consulta pedidos ingresados |
| [HU-101-016](HU-101-016-consulta-presupuestos.md) | Must | 101-07, 101-11 | Presupuestos activos (99) y cerrados (98) |
| [HU-101-017](HU-101-017-consulta-pedidos-pendientes.md) | Must | 101-07, 101-11 | Consulta pedidos pendientes |
| [HU-101-018](HU-101-018-consulta-stock.md) | Must | 101-07, 101-11 | Consulta stock |
| [HU-101-019](HU-101-019-mail-grabar.md) | Must | 101-13 | Mail al grabar/modificar |
| [HU-101-020](HU-101-020-logs-integracion.md) | **Should** | 101-08 | Logs de integración |
| [HU-101-021](HU-101-021-consulta-deuda.md) | Must | 101-07, 101-11 | Consulta deuda |
| [HU-101-022](HU-101-022-consulta-cheques.md) | Must | 101-07, 101-11 | Consulta cheques |
| [HU-101-023](HU-101-023-historial-ventas.md) | Must | 101-07, 101-11 | Historial de ventas |
| [HU-101-024](HU-101-024-conversion-pedido-presupuesto.md) | Must | 101-04 | Conversión pedido → presupuesto |
| [HU-101-025](HU-101-025-dashboard.md) | Must | 101-14 | Dashboard §4.1 |
| [HU-101-026](HU-101-026-copiar-comprobante.md) | Must | 101-04, 101-10 | Copiar comprobante |
| [HU-101-027](HU-101-027-cierre-rechazo-presupuesto.md) | Must | 101-04, 101-12 | Cierre/rechazo presupuesto → 98 |
| [HU-101-028](HU-101-028-consulta-detalle-pedidos.md) | Must | 101-07 (B3), 101-11 (B3) | Consulta detalle pedidos (cabecera+renglón) |
| [HU-101-029](HU-101-029-proceso-excel-pedido-individual.md) | **Should** | 101-16 | Proceso Excel `PEDIDO_INDIVIDUAL` — **B1 cerrado** |
| [HU-101-030](HU-101-030-importacion-excel-pantalla-carga.md) | **Should** | 101-16 | Import Excel en pantalla de carga — **B1 cerrado** |

## SPEC-101-17 — Mobile Capacitor

| HU | Release | TR v1 | Prioridad | Título |
|----|---------|-------|-----------|--------|
| [HU-101-031](HU-101-031-mobile-v1-scaffold.md) | `v1.2.0-mobile` | [TR-SPEC-101-17-mobile-v1-scaffold](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-scaffold.md) | Must | Scaffold Capacitor PedidosWeb |
| [HU-101-032](HU-101-032-mobile-login-tenant.md) | `v1.2.0-mobile` | [TR-SPEC-101-17-mobile-v1-login-tenant](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-login-tenant.md) | Must | Login tenant + usuario + contraseña |
| [HU-101-033](HU-101-033-mobile-consulta-stock-kardex.md) | `v1.2.0-mobile` | [TR-SPEC-101-17-mobile-v1-stock-kardex](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-stock-kardex.md) | Must | Consulta stock kardex |
| [HU-101-034](HU-101-034-mobile-v2-consultas-kardex.md) | `v1.2.1-mobile` | (pendiente) | Must | Consultas kardex (v2) |
| [HU-101-035](HU-101-035-mobile-v2-listados-kardex.md) | `v1.2.1-mobile` | (pendiente) | Must | Listados kardex (v2) |
| [HU-101-036](HU-101-036-mobile-v3-carga-pedidos.md) | `v1.2.2-mobile` | (pendiente) | Must | Carga pedidos UX mobile (v3) |

**SPEC:** [SPEC-101-17-mobile-capacitor-pedidosweb.md](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) — **C1 v1 (2026-06-30):** [F-101-17-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-17-cierre-c1.md). Transversal: [HU-GEN-11-*](../001-Generaliddes/README.md#spec-001-11--mobile-capacitor).

## SPEC-101-18 / 19 / 20 — Asistente IA en carga

| HU | SPEC | Prioridad | Título |
|----|------|-----------|--------|
| [HU-101-037](HU-101-037-asistente-carga-ia-panel-gate.md) | 18 | Should | Panel, gate BYOK, orquestación |
| [HU-101-038](HU-101-038-asistente-carga-ia-audio-imagen.md) | 18 | Should | Audio Web Speech + imagen entrada |
| [HU-101-039](HU-101-039-asistente-carga-ia-cliente-cabecera.md) | 19 | Should | Cliente, cabecera, cambio cliente |
| [HU-101-040](HU-101-040-asistente-carga-ia-articulos-grabar.md) | 19 | Should | Artículos, grabar, apply imagen |
| [HU-101-041](HU-101-041-asistente-carga-ia-consulta-stock.md) | 20 | Should | Consulta stock |
| [HU-101-042](HU-101-042-asistente-carga-ia-consultas-cliente.md) | 20 | Should | Deuda, cheques, historial |

**A1:** [F-101-18-20-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) · **B1:** [F-101-18-20-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-b1-asistente-carga-ia.md) · **C1:** [F-101-18-20-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-c1-asistente-carga-ia.md) — listo **D1**.

| TR | HU | Título |
|----|-----|--------|
| [TR-SPEC-101-18](../../04-tareas/101-PedidosWeb/TR-SPEC-101-18-asistente-carga-ia-shell.md) | 037, 038 | Shell, gate, audio, imagen |
| [TR-SPEC-101-19](../../04-tareas/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) | 039, 040 | Mutaciones A–D, I, J, K |
| [TR-SPEC-101-20](../../04-tareas/101-PedidosWeb/TR-SPEC-101-20-asistente-carga-ia-consultas.md) | 041, 042 | Consultas E–H |

## SPEC-101-21 — Importación masiva

| HU | Prioridad | Título |
|----|-----------|--------|
| [HU-101-043](HU-101-043-proceso-excel-pedido-masivo.md) | Should | Proceso Excel `PEDIDO_MASIVO` (catálogo, handler, agrupación) — **B1 cerrado** |
| [HU-101-044](HU-101-044-pantalla-importacion-masiva.md) | Should | Pantalla grilla, import UI, grabación FE, modales — **B1 cerrado** |
| [HU-101-045](HU-101-045-consultar-borrador-importacion-masiva.md) | Should | Consultar borrador en carga solo lectura — **B1 cerrado** |

**SPEC:** [SPEC-101-21](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) · **A1:** [F-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-a1-importacion-masiva.md) · **B1:** [F-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-b1-importacion-masiva.md) · **C:** [F-cierre-c](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c-importacion-masiva.md) · **C1:** [F-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c1-importacion-masiva.md) — **autoriza D1**.

| TR | HU | Título |
|----|-----|--------|
| [TR-21a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo.md) | 043 | Proceso Excel `PEDIDO_MASIVO` |
| [TR-21b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-pantalla-importacion-masiva.md) | 044 | Pantalla + grabación FE |
| [TR-21c](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) | 045 | Consultar readonly |

## Dependencias transversales

- **GEN-01 / GEN-03:** shell, grillas, layouts, export Excel.
- **GEN-02:** auth y visibilidad base (heredado en 101-001/002/006).
- **GEN-07:** motor importación Excel (`HU-GEN-07-*`) — requerido por HU-101-029/030 y HU-101-043/044.
- **SPEC-001-04:** parámetros (`MinutosWeb`, `DiasVentasDetalladas`, permisos por atributo); consulta parámetros → [TR-GEN-04](../../04-tareas/001-Generaliddes/TR-GEN-04-consulta-parametros.md).

## Siguiente paso

Parte **C (SPEC-101-16):** **cerrada** — [TR-16a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-16-proceso-excel-pedido-individual.md), [TR-16b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-16-importacion-excel-pantalla-carga.md).

Parte **D1 (SPEC-101-16):** implementar TR en orden 16a → 16b ([F-101-16-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-16-cierre-c1.md) — C1 **Apto**).

Parte **C (SPEC-101-17 mobile v1):** **cerrada** — [F-101-17-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-17-cierre-c1.md) — C1 **Apto**; listo **D1** v1.

Parte **D1 (SPEC-101-17 mobile v1):** implementar TR en orden scaffold → login → stock kardex (coordinado con TR-GEN-11-*).

Parte **B1 (SPEC-101-18/19/20 asistente carga):** **cerrada** — HU-101-037…042 ([F-101-18-20-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-b1-asistente-carga-ia.md)).

Parte **B (SPEC-101-21 importación masiva):** **generada** — HU-101-043…045.

Parte **B1 (SPEC-101-21):** **cerrada** — [F-101-21-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-b1-importacion-masiva.md).

Parte **C (SPEC-101-21):** **generada** — [F-101-21-cierre-c](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c-importacion-masiva.md).

Parte **C1 (SPEC-101-21):** **Apto** — [F-101-21-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-21-cierre-c1-importacion-masiva.md); autoriza **D1** (21a → 21b → 21c).

Parte **C1 (SPEC-101-18/19/20):** **Apto** — [F-101-18-20-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-c1-asistente-carga-ia.md).

Parte **D1 (SPEC-101-18/19/20):** plan [D1-PLAN-101-18-20](../../04-tareas/101-PedidosWeb/D1-PLAN-101-18-20-asistente-carga-ia.md) — pendiente confirmación para Parte D.

Parte **C/D/F (MVP):** cerradas (2026-06-01 / 2026-06-03) — [TR por slice](../../04-tareas/101-PedidosWeb/README.md), [D-VERIFICACION-101](../../04-tareas/101-PedidosWeb/D-VERIFICACION-101.md), [F-101-PedidosWeb-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md).

**Manual de usuario:** [PedidosWeb.md](../../99-manual-usuario/PedidosWeb.md)
