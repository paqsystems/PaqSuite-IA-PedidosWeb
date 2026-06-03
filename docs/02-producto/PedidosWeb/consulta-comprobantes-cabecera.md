# Consulta de comprobantes (cabecera) — fuente de verdad

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente — **implementado** (2026-06-03) |
| **Ámbito** | Pedidos ingresados, pedidos pendientes, presupuestos activos/cerrados |
| **HU** | [HU-101-015](../../03-historias-usuario/101-PedidosWeb/HU-101-015-consulta-pedidos-ingresados.md), [HU-101-016](../../03-historias-usuario/101-PedidosWeb/HU-101-016-consulta-presupuestos.md), [HU-101-017](../../03-historias-usuario/101-PedidosWeb/HU-101-017-consulta-pedidos-pendientes.md) |
| **API** | `GET /api/v1/consultas/pedidos-ingresados`, `.../pedidos-pendientes`, `.../presupuestos?estado=99\|98` |
| **Backend** | `ConsultaListadoService::mapComprobanteItem` |
| **UI** | `ComprobanteConsultaColumns.tsx` — procesos `pw_pedidosingresados`, `pw_pedidospendientes`, `pw_presupuestosactivos`, `pw_presupuestoscerrados` |

---

## 1) Objetivo

Exponer en grillas DevExtreme **todos los campos de `pq_pedidosweb_pedidoscabecera`** relevantes para operación comercial, con descripciones de maestros asociados. Las consultas existentes filtran por **estado** según producto §17.1–§17.3; la **forma de columnas** es común.

Para detalle de renglones (cabecera + detalle en una sola grilla), ver **[consulta-detalle-pedidos.md](./consulta-detalle-pedidos.md)**.

---

## 2) Tabla origen y joins

| Origen API | Columna BD | Join / notas |
|------------|------------|--------------|
| `codPedido` / `codPresupuesto` | `cod_pedido` | PK |
| `codCliente` | `cod_cliente` | |
| `razonSocial` | `clientes.nombre` | `belongsTo cliente` |
| `fecha` | `fecha` | ISO 8601 |
| `nivel` | `nivel` | |
| `observaciones` | `observaciones` | |
| `incluyeIva` | `incluye_iva` | boolean |
| `moneda` | `moneda` | `0` Extranjera, `1` Corriente |
| `estado` | `estado` | Texto UI vía i18n `consultas.comprobanteEstado.*` |
| `fechaModif` | `fecha_modif` | |
| `total` | `total` | |
| `totalIva` | `total_iva` | |
| `leyenda1`…`leyenda5` | `leyenda_1`…`leyenda_5` | |
| `descuento` | `descuento` | |
| `bonif1`…`bonif3` | `bonif_1`…`bonif_3` | |
| `codPerfil` | `cod_perfil` | |
| `perfilDescripcion` | `perfil.descripcion` | join `pq_pedidosweb_perfil` |
| `codVended` | `cod_vended` | |
| `vendedorDescripcion` | `vendedores.nombre` | join vendedor |
| `codCondvta` | `cod_condvta` | |
| `condicionVentaDescripcion` | `condventa.descripcion` | |
| `idDe` | `id_de` | |
| `direccionEntregaDescripcion` | `clientesde.direccion` | lookup `(cod_client, id_de)` |
| `codTranspor` | `cod_transpor` | |
| `transporteDescripcion` | `transportes.descripcion` | |
| `listaPrecios` | `lista_precios` | |
| `listaPreciosDescripcion` | `listaprecios.descripcion` | |
| `expreso` | `expreso` | |
| `expresoDire` | `expreso_dire` | |
| `fechaEntrega` | `fecha_entrega` | |
| `usuarioCreacion` | `usuario_creacion` | |
| `fechaCreacion` | `fecha_creacion` | |
| `usuarioModificacion` | `usuario_modificacion` | |
| `fechahoraInicioProceso` | `fechahora_inicio_proceso` | |
| `fechahoraUltimaActividad` | `fechahora_ultima_actividad` | |
| `numeroVisible` | `nro_visible` | Columna UI `numero` (oculta por defecto) |

---

## 3) Columnas visibles inicialmente en grilla

Marcadas como visibles por defecto (`visible={true}` en `ComprobanteConsultaColumns`):

| Columna UI | Campo |
|------------|-------|
| Cód. pedido | `codPedido` |
| Cód. cliente | `codCliente` |
| Razón social | `razonSocial` |
| Fecha | `fecha` |
| Moneda | `moneda` (texto Extranjera/Corriente) |
| Total | `total` |
| Cód. vendedor | `codVended` |
| Vendedor | `vendedorDescripcion` |
| Lista precios | `listaPrecios` |
| Descripción lista | `listaPreciosDescripcion` |

El resto de columnas está **disponible** vía selector de columnas DevExtreme (`visible={false}` inicial).

---

## 4) Filtros por consulta (estado)

| Consulta | Estados | Acciones |
|----------|---------|----------|
| Pedidos ingresados | `0`, `-1` | ver, editar, eliminar (0), copiar |
| Pedidos pendientes | `1` | solo ver |
| Presupuestos activos | `99` | ver, editar, convertir, cerrar, copiar |
| Presupuestos cerrados | `98` | ver + columnas cierre |

---

## 5) Textos de estado

| `estado` | Clave i18n |
|----------|------------|
| -1 | `consultas.comprobanteEstado.-1` |
| 0 | `consultas.comprobanteEstado.0` |
| 1 | `consultas.comprobanteEstado.1` |
| 2 | `consultas.comprobanteEstado.2` |
| 98 | `consultas.comprobanteEstado.98` |
| 99 | `consultas.comprobanteEstado.99` |

---

## 6) Implementación

| Capa | Archivo |
|------|---------|
| API | `backend/app/Services/PedidosWeb/ConsultaListadoService.php` |
| Tipos / mapper | `frontend/src/features/consultas/api/consultaApi.ts` |
| Columnas | `frontend/src/features/consultas/components/ComprobanteConsultaColumns.tsx` |
