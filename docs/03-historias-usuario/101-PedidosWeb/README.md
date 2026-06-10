# Historias de usuario — 101-PedidosWeb

Convención: **`HU-101-NNN-tema.md`** derivadas de [PedidosWeb_SPEC_MVP.md](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md) y slices `SPEC-101-xx`.

**A1 (2026-06-01):** Cerrado en SPEC madre §14.  
**B1 (2026-06-01):** 27 HU enriquecidas en esta carpeta.

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

## Dependencias transversales

- **GEN-01 / GEN-03:** shell, grillas, layouts, export Excel.
- **GEN-02:** auth y visibilidad base (heredado en 101-001/002/006).
- **SPEC-001-04:** parámetros (`MinutosWeb`, `DiasVentasDetalladas`, permisos por atributo); consulta parámetros → [TR-GEN-04](../../04-tareas/001-Generaliddes/TR-GEN-04-consulta-parametros.md).

## Siguiente paso

Parte **C:** cerrada (2026-06-01) — [TR por slice](../../04-tareas/101-PedidosWeb/README.md).  
Parte **D + F:** cerradas (2026-06-03) — [D-VERIFICACION-101](../../04-tareas/101-PedidosWeb/D-VERIFICACION-101.md), [F-101-PedidosWeb-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-PedidosWeb-cierre-formal.md).

**Manual de usuario:** [PedidosWeb.md](../../99-manual-usuario/PedidosWeb.md)
