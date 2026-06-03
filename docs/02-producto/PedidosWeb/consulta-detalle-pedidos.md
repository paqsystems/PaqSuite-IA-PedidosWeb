# Consulta detalle de pedidos — fuente de verdad

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente — **implementado** (D1 2026-06-03) |
| **HU** | [HU-101-028-consulta-detalle-pedidos](../../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) |
| **Menú** | **Pedidos** → *Detalle de pedidos* (`grp_pedidos`) |
| **Proceso** | `pw_detallepedidos` |
| **Ruta UI** | `/pedidos/detalle` |
| **API** | `GET /api/v1/consultas/detalle-pedidos` |
| **Permiso** | `Permiso_Repo` sobre `pw_detallepedidos` + visibilidad clientes |

---

## 1) Objetivo

Presentar una **grilla plana** con toda la información de **`pq_pedidosweb_pedidoscabecera`** y **`pq_pedidosweb_pedidosdetalle`**, una fila por renglón.

Incluye **pedidos y presupuestos en todos los estados** (`-1`, `0`, `1`, `2`, `98`, `99`). No es una consulta de acciones de edición: solo lectura + export Excel.

La columna **estado** se muestra como **descripción** (no número crudo), usando las mismas claves i18n que [consulta-comprobantes-cabecera.md](./consulta-comprobantes-cabecera.md) §5.

---

## 2) Join lógico

```sql
FROM pq_pedidosweb_pedidoscabecera c
INNER JOIN pq_pedidosweb_pedidosdetalle d ON d.cod_pedido = c.cod_pedido
LEFT JOIN pq_pedidosweb_clientes cl ON cl.cod_client = c.cod_cliente
LEFT JOIN pq_pedidosweb_articulos a ON a.codigo = d.cod_articulo
-- joins de cabecera: vendedor, perfil, condventa, transporte, listaprecios (igual consulta cabecera)
```

Visibilidad: `c.cod_cliente IN (visibleClientsForUser)`.

Orden sugerido: `c.fecha DESC`, `c.cod_pedido`, `d.renglon`.

---

## 3) Columnas — cabecera (repetidas por renglón)

Todas las columnas de [consulta-comprobantes-cabecera.md](./consulta-comprobantes-cabecera.md) §2 aplican en cada fila de detalle.

**Visible inicial sugerido (mismo criterio operativo):** `codPedido`, `codCliente`, `razonSocial`, `fecha`, `moneda`, `total` cabecera, `codVended`, `vendedorDescripcion`, `listaPrecios`, `listaPreciosDescripcion`, **`estadoTexto`**.

---

## 4) Columnas — detalle (por renglón)

| Columna UI | Origen BD | Notas |
|------------|-----------|--------|
| `codArticulo` | `d.cod_articulo` | |
| `descripcionArticulo` | `a.descripcion` o `d.descripcion_articulo` si congelada | Preferir descripción congelada en detalle si existe |
| `cantidad` | `d.cantidad` | decimal |
| `descuento` / `porcBonif` | `d.porc_bonif` | % bonificación renglón |
| `precioLista` | `d.precio` o `d.importe_lista` | Precio de lista |
| `precioNeto` | `d.precio_neto` | |
| `importeBruto` | `d.precio_bruto` | |
| `importeNeto` | `d.importe_neto` | |
| `ivaNeto` | `d.iva` | Importe IVA del renglón |
| `importeNetoConIva` | `d.importe_total` | Neto con IVA |

Columnas de detalle **visibles inicialmente sugeridas:** `codArticulo`, `descripcionArticulo`, `cantidad`, `descuento`, `precioLista`, `precioNeto`, `importeBruto`, `importeNeto`, `ivaNeto`, `importeNetoConIva`.

---

## 5) Contrato API

Paginación estándar (`page`, `page_size` máx. 100, `total`, `metadata.fecha_proceso`).

Filtros opcionales: `cod_cliente`, `cod_pedido`, `estado`, `q` (código/descripción artículo).

Cada ítem combina propiedades camelCase de cabecera (mismo contrato que [consulta-comprobantes-cabecera.md](./consulta-comprobantes-cabecera.md)) + detalle. Campo `renglon` incluido. Estado numérico en `estado`; texto en UI vía i18n (no exponer `estadoTexto` duplicado salvo que TR lo unifique).

---

## 6) UI

- `DataGridDx` + `ComprobanteConsultaColumns` extendido con columnas de detalle, **o** componente dedicado `DetallePedidosConsultaColumns`.
- Sin columna de acciones de edición.
- Export Excel GEN-03.
- Carátula `fecha_proceso`.

---

## 7) Referencias TR

- API: ampliar [TR-SPEC-101-07-consultas-api](../../04-tareas/101-PedidosWeb/TR-SPEC-101-07-consultas-api.md)
- UI: ampliar [TR-SPEC-101-11-consultas-ui](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md)
