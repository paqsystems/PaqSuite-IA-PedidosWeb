# Consulta de stock — fuente de verdad

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente |
| **HU** | [HU-101-018-consulta-stock](../../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md) |
| **API** | `GET /api/v1/consultas/stock` |
| **UI** | `/consultas/stock` — `StockPage`, proceso `pw_stock` |
| **Backend** | `App\Services\PedidosWeb\StockConsultaService` |
| **Permiso** | `Permiso_Repo` sobre procedimiento `pw_consultastock` |
| **Visibilidad** | **No** filtra por cliente; búsqueda opcional `q` por código o descripción de artículo |

---

## 1) Objetivo

Exponer stock **informativo** (no bloquea carga) con disponibilidad **neta** descontando compromiso ERP y pedidos web en estado **ingresado** (`estado = 0`), y métricas **agregadas por código base** cuando el artículo tiene presentaciones/escala.

---

## 2) Tablas y joins

| Dato en pantalla | Origen |
|------------------|--------|
| Código artículo | `pq_pedidosweb_stock.cod_articulo` |
| Descripción | `pq_pedidosweb_articulos.descripcion` — join `articulos.codigo = stock.cod_articulo` |
| Stock | `pq_pedidosweb_stock.stock` |
| Comprometido | `pq_pedidosweb_stock.comprometido` |
| Comprometido web | Ver §3 |
| Disponible neto | Ver §4 |
| Código base | `pq_pedidosweb_articulos.base` (solo informativo en fila) |
| Stock base / Comprometido base / Comprometido base web / Disponible neto base | Ver §5 — **solo si** `articulos.base` ≠ vacío |

---

## 3) Comprometido web (por artículo)

Suma de cantidades en pedidos **ingresados** no descargados:

```sql
SELECT d.cod_articulo, SUM(d.cantidad) AS comprometido_web
FROM pq_pedidosweb_pedidosdetalle d
INNER JOIN pq_pedidosweb_pedidoscabecera c ON d.cod_pedido = c.cod_pedido
WHERE c.estado = 0
GROUP BY d.cod_articulo
```

- **Incluye** solo `pq_pedidosweb_pedidoscabecera.estado = 0`.
- **No incluye** estados `-1`, `1`, presupuestos `99`/`98`, etc.

---

## 4) Disponible neto (por artículo)

```
disponible_neto = stock - comprometido - comprometido_web
```

Donde `comprometido_web` es `0` si el artículo no tiene pedidos en estado `0`.

---

## 5) Métricas por código base

**Condición:** `NULLIF(LTRIM(RTRIM(articulos.base)), '') IS NOT NULL`.

Todos los artículos con el **mismo** `articulos.base` participan en los agregados.

| Campo API | Cálculo |
|-----------|---------|
| `stockBase` | `SUM(pq_pedidosweb_stock.stock)` de filas cuyo artículo tiene `articulos.base = <base del artículo en curso>` |
| `comprometidoBase` | `SUM(pq_pedidosweb_stock.comprometido)` mismo criterio |
| `comprometidoBaseWeb` | `SUM(detalle.cantidad)` con `cabecera.estado = 0` para **todos** los `cod_articulo` cuya `articulos.base` coincide |
| `disponibleNetoBase` | `stockBase - comprometidoBase - comprometidoBaseWeb` |

Si `articulos.base` está vacío o solo espacios: `stockBase`, `comprometidoBase`, `comprometidoBaseWeb` y `disponibleNetoBase` responden **`null`** (no se muestran agregados base).

**Lookup carga de artículos** (`GET /api/v1/articulos`, listbox en [pantalla-carga-comprobante-ui.md](./pantalla-carga-comprobante-ui.md) §3.1): usa los mismos agregados §5 vía `ArticuloCargaLookupService` (subconsulta `SUM` por `articulos.base`; **no** join directo `stock.cod_articulo = articulos.base`).

---

## 6) Contrato API (`resultado.items[]`)

| Propiedad JSON | Tipo | Descripción |
|----------------|------|-------------|
| `codArticulo` | string | Código artículo |
| `descripcion` | string | Descripción maestra |
| `stock` | number | Stock real |
| `comprometido` | number | Comprometido ERP |
| `comprometidoWeb` | number | §3 |
| `disponibleNeto` | number | §4 |
| `codBase` | string \| null | `articulos.base` si tiene valor |
| `stockBase` | number \| null | §5 |
| `comprometidoBase` | number \| null | §5 |
| `comprometidoBaseWeb` | number \| null | §5 |
| `disponibleNetoBase` | number \| null | §5 |

**Metadata:** `metadata.fecha_proceso` — `MAX(uma_fecha)` de `pq_pedidosweb_stock`, o `null` si no hay tabla/datos.

**Decimales:** API redondea cantidades a **2 decimales**; grilla usa formato DevExtreme `#,##0.00`.

**BD sin tabla stock:** si no existe `pq_pedidosweb_stock`, la API responde **200** con `items: []` y `total: 0` (no error 500). Subconsultas de pedidos/artículos se omiten si faltan esas tablas.

**Paginación:** `page`, `page_size` (máx. 100), `total`, `total_pages`.

**Filtro:** query `q` — `LIKE` sobre `cod_articulo` y `descripcion`.

---

## 7) Implementación y rendimiento

- Servicio dedicado `StockConsultaService` ejecuta **una consulta paginada** con:
  - subconsulta agregada `comprometido_web` por artículo;
  - subconsultas agregadas por `articulos.base` (stock/comprometido y comprometido web);
  - joins a maestro artículo.
- Evita N+1 de Eloquent `with('articulo')` + cálculos en PHP por fila.
- Orden: `cod_articulo` ascendente.

---

## 8) UI (grilla)

Columnas visibles (i18n `consultas.column.stock*`):

1. Código artículo  
2. Descripción  
3. Stock  
4. Comprometido  
5. Comprometido web  
6. Disponible neto  
7. Código base (si aplica)  
8. Stock base  
9. Comprometido base  
10. Comprometido base web  
11. Disponible neto base  

`data-testid` página: `page-consulta-stock`. Export Excel GEN-03 sobre grilla visible. Columnas numéricas: formato `#,##0.00`.

**CC PQ #4:** toggle grilla/pivot; `consultaId` `CONSULTA_STOCK`; métricas pivot `stock`, `comprometido`, `comprometidoWeb`, `disponibleNeto`. Ver §9.

---

## 9) UI (vista pivot) — CC PQ #4

| Campo | Valor |
|-------|--------|
| `consultaId` | `CONSULTA_STOCK` |
| Componente | `ConsultaInformePivotPage` |
| `pivotBase` sugerido | Filas: `codArticulo`, `descripcion`; valores: `disponibleNeto` (sum) |
| Fórmulas | Idénticas a §5 (`disponibleNeto = stock − comprometido − comprometidoWeb`) |
| Sin filtro cliente | Igual que grilla; `q` vía refresh servidor |

---

## 10) Referencias

- Producto §13: [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md)  
- Modelo §3.4–3.5: [PedidosWeb_Modelo_Datos_Final.md](PedidosWeb_Modelo_Datos_Final.md)  
- TR API: [TR-SPEC-101-07-consultas-api.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-07-consultas-api.md)  
- TR UI: [TR-SPEC-101-11-consultas-ui.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md)
