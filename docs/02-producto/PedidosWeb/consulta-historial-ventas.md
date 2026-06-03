# Consulta historial de ventas — fuente de verdad

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente |
| **HU** | [HU-101-023-historial-ventas](../../03-historias-usuario/101-PedidosWeb/HU-101-023-historial-ventas.md) |
| **API** | `GET /api/v1/consultas/historial-ventas` |
| **UI** | `/consultas/historial` — `HistorialVentasPage`, proceso `pw_historialventas` |
| **Backend** | `App\Services\PedidosWeb\HistorialVentasConsultaService` |
| **Permiso** | `Permiso_Repo` sobre procedimiento `pw_historialventas` |
| **Visibilidad** | Filtra por **universo de clientes visibles**; query opcional `cod_cliente` |

---

## 1) Objetivo

Exponer el **historial de ventas detallado** importado del ERP (`pq_pedidosweb_ventadetallada`) con todos los atributos de línea/comprobante exportados, dentro del período **`DiasVentasDetalladas`**.

Consulta **informativa**. `fecha_proceso` e `id_gva53` **no** se exponen en ítems (carátula / PK interna).

---

## 2) Tabla `pq_pedidosweb_ventadetallada`

| Dato en pantalla | Columna BD | API JSON |
|------------------|------------|----------|
| Cliente | `cod_cli` | `codCliente` |
| Razón social | `razon_soci` | `razonSocial` |
| Nº remito | `n_remito` | `nRemito` |
| Tipo | `t_comp` | `tipo` |
| Número | `n_comp` | `numero` |
| Fecha emisión | `fecha_emi` | `fechaEmision` |
| Cond. venta | `cond_vta` | `condVta` |
| % desc. | `porc_desc` | `porcDesc` |
| Cotización | `cotiz` | `cotiz` |
| Moneda | `moneda` | `moneda` |
| Total comprob. | `total_comp` | `totalComp` |
| Cód. transporte | `cod_transp` | `codTransp` |
| Transporte | `nom_transp` | `nomTransp` |
| Código artículo | `cod_articu` | `codArticulo` |
| Descripción | `descripcio` | `descripcion` |
| Depósito | `cod_dep` | `codDep` |
| U.M. | `um` | `um` |
| Cantidad | `cantidad` | `cantidad` |
| Precio | `precio` | `precio` |
| Total s/ imp. | `tot_s_imp` | `totSinImp` |
| Nº comp. remito | `n_comp_rem` | `nCompRem` |
| Cant. remito | `cant_rem` | `cantRem` |
| Fecha remito | `fecha_rem` | `fechaRem` |

**Excluidos de ítems:** `fecha_proceso` (metadata carátula), `id_gva53` (PK ERP).

**Clave primaria:** `id_gva53`.

**Filtro período:**

```sql
WHERE fecha_emi >= DATEADD(day, -@DiasVentasDetalladas, CAST(GETDATE() AS date))
```

**Orden:** `fecha_emi` DESC, `cod_cli` ASC, `n_comp` ASC.

---

## 5) Visibilidad y filtros

| Perfil | Comportamiento |
|--------|----------------|
| **Cliente** | Solo ventas de su `cod_cli` |
| **Vendedor / supervisor** | Ventas de clientes visibles, o `?cod_cliente=` |
| **Query `cod_cliente`** | Opcional; universo visible → **404** si no |

**BD sin tabla:** **200** con `items: []`.

---

## 6) Contrato API (`resultado.items[]`)

Ver tabla §2. Decimales redondeados a **2 cifras** en API. Fechas ISO 8601.

**Metadata:**

| Campo | Origen |
|-------|--------|
| `fecha_proceso` | `MAX(fecha_proceso)` de la tabla |
| `dias_ventas_detalladas` | Parámetro ERP `DiasVentasDetalladas` |

**Paginación:** `page`, `page_size` (máx. 100), `total`, `total_pages`.

---

## 7) Implementación

- `HistorialVentasConsultaService` — SQL paginado sobre `pq_pedidosweb_ventadetallada`.
- Delegado desde `ConsultaListadoService::historialVentas()`.
- Modal UI muestra la misma fila con todas las columnas (acción ver detalle).

---

## 8) UI (grilla)

22 columnas visibles según §2 (`dataField` = propiedad JSON). Formato fechas `dd/MM/yyyy`; importes `#,##0.00`.

`data-testid` página: `page-consulta-historial`. Export Excel GEN-03.

---

## 9) Referencias

- Modelo §4.4: [PedidosWeb_Modelo_Datos_Final.md](PedidosWeb_Modelo_Datos_Final.md)  
- Producto §17.6: [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md)  
- TR API: [TR-SPEC-101-07-consultas-api.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-07-consultas-api.md)  
- TR UI: [TR-SPEC-101-11-consultas-ui.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md)
