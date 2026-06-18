# Consulta de deuda — fuente de verdad

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente |
| **HU** | [HU-101-021-consulta-deuda](../../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md) |
| **API** | `GET /api/v1/consultas/deuda` |
| **UI** | `/consultas/deuda` — `DeudaPage`, proceso `pw_deuda` |
| **Backend** | `App\Services\PedidosWeb\DeudaConsultaService` |
| **Permiso** | `Permiso_Repo` sobre procedimiento `pw_deudaclientes` |
| **Visibilidad** | Filtra por **universo de clientes visibles** del usuario; query opcional `cod_cliente` (supervisor/vendedor) |

---

## 1) Objetivo

Exponer la **deuda importada del ERP** (tabla `pq_pedidosweb_deuda`) con comprobantes pendientes: código de cliente, razón social, tipo y número de comprobante, fechas de emisión y vencimiento, y saldo.

Consulta **informativa** (no modifica datos). La fecha de última sincronización ERP va en carátula (`metadata.fecha_proceso`).

---

## 2) Tablas y joins

| Dato en pantalla | Columna BD | Notas |
|------------------|------------|--------|
| Cliente | `pq_pedidosweb_deuda.cod_cliente` | Código cliente |
| Razón social | `pq_pedidosweb_clientes.razon_soci` | Join `clientes.cod_client = deuda.cod_cliente`; si `razon_soci` es NULL, fallback `clientes.nombre` |
| Tipo | `pq_pedidosweb_deuda.t_comp` | Tipo comprobante (ERP) |
| Número | `pq_pedidosweb_deuda.n_comp` | Número comprobante (ERP) |
| Fecha | `pq_pedidosweb_deuda.fecha_emis` | Fecha de emisión |
| Vencimiento | `pq_pedidosweb_deuda.fecha_vto` | Fecha de vencimiento |
| Saldo | `pq_pedidosweb_deuda.saldo` | Saldo pendiente del comprobante |

**Join obligatorio en consulta:**

```sql
FROM pq_pedidosweb_deuda d
LEFT JOIN pq_pedidosweb_clientes c ON c.cod_client = d.cod_cliente
```

**Orden:** `fecha_vto` descendente, luego `cod_cliente` ascendente.

---

## 3) Clave primaria (script ERP)

Compuesta:

- `cod_cliente`
- `t_comp`
- `n_comp`
- `fecha_vto`

---

## 4) Compatibilidad legacy (bootstrap dev antiguo)

Si la tabla **no** tiene columnas ERP (`t_comp`, `n_comp`, `fecha_emis`), `PedidosWebSchemaBootstrap::deudaColumnMap()` resuelve automáticamente:

| ERP (canónico) | Legacy bootstrap |
|----------------|------------------|
| `t_comp` | `tipo_comprobante` |
| `n_comp` | `nro_comprobante` |
| `fecha_emis` | `fecha` |

La API **siempre** expone las mismas propiedades JSON (§6); la detección es solo interna.

**Razón social:** si falta columna `razon_soci` en `pq_pedidosweb_clientes`, se usa `nombre`.

---

## 5) Visibilidad y filtros

| Perfil | Comportamiento |
|--------|----------------|
| **Cliente** | Solo deuda de su `cod_client` (universo visible = él mismo) |
| **Vendedor / supervisor** | Deuda de todos los clientes visibles, o filtro `?cod_cliente=` si se informa |
| **Query `cod_cliente`** | Opcional; debe pertenecer al universo visible → **404** si no |

**No aplica** filtro de saldo distinto de cero en MVP: se listan filas presentes en `pq_pedidosweb_deuda` (ERP define qué exporta).

**BD sin tabla:** si no existe `pq_pedidosweb_deuda`, la API responde **200** con `items: []` y `total: 0`.

---

## 6) Contrato API (`resultado.items[]`)

| Propiedad JSON | Tipo | Origen |
|----------------|------|--------|
| `codCliente` | string | `deuda.cod_cliente` |
| `razonSocial` | string | `clientes.razon_soci` (fallback `nombre`) |
| `tipo` | string | `deuda.t_comp` (o legacy `tipo_comprobante`) |
| `numero` | string | `deuda.n_comp` (o legacy `nro_comprobante`) |
| `fecha` | string \| null | ISO 8601 — `fecha_emis` (o legacy `fecha`) |
| `vencimiento` | string \| null | ISO 8601 — `fecha_vto` |
| `saldo` | number | `deuda.saldo` — redondeo **2 decimales** en API |

**Metadata:** `metadata.fecha_proceso` — `MAX(fecha_proceso)` de `pq_pedidosweb_deuda`, o `null` si no hay datos. Mismo valor para todos los registros de la exportación ERP; se muestra en carátula UI (`consultas.fechaProceso`).

**Paginación:** `page`, `page_size` (máx. 100), `total`, `total_pages`.

**Query opcional:** `cod_cliente`.

---

## 7) Implementación

- Servicio dedicado `DeudaConsultaService` — **una consulta paginada** con join a clientes.
- Delegado desde `ConsultaListadoService::deuda()`.
- Detección de columnas vía `PedidosWebSchemaBootstrap::deudaColumnMap()` y `clienteRazonSocialColumn()`.
- No recalcular saldos en PHP; datos vienen del ERP.

---

## 8) UI (grilla)

Columnas visibles (i18n `consultas.column.*`):

| # | `dataField` | Clave i18n |
|---|-------------|------------|
| 1 | `codCliente` | `consultas.column.cliente` |
| 2 | `razonSocial` | `consultas.column.razonSocial` |
| 3 | `tipo` | `consultas.column.tipo` |
| 4 | `numero` | `consultas.column.numero` |
| 5 | `fecha` | `consultas.column.fecha` — formato `dd/MM/yyyy` |
| 6 | `vencimiento` | `consultas.column.vencimiento` — formato `dd/MM/yyyy` |
| 7 | `saldo` | `consultas.column.saldo` — formato `#,##0.00` |

`data-testid` página: `page-consulta-deuda`. Export Excel GEN-03 sobre grilla visible.

**CC PQ #4:** toggle grilla/pivot; `consultaId` `CONSULTA_DEUDA`; `pivotBase` sugerido: filas cliente + tipo; valor `saldo` (sum). Ver §9.

**Pendiente HU (no MVP actual):** columna «saldo acumulado» mencionada en producto §17.4 — reservada para etapa posterior.

---

## 9) UI (vista pivot) — CC PQ #4

| Campo | Valor |
|-------|--------|
| `consultaId` | `CONSULTA_DEUDA` |
| Componente | `ConsultaInformePivotPage` |
| Dataset | Mismo `DeudaConsultaService` vía API pivot data |
| Visibilidad | Universo clientes visible (GEN-02) |
| Activación | `PIVOTS_ENABLED` + migraciones/seed pivots |

---

## 10) Referencias

- Producto §17.4: [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md)  
- Modelo §4.2: [PedidosWeb_Modelo_Datos_Final.md](PedidosWeb_Modelo_Datos_Final.md)  
- TR API: [TR-SPEC-101-07-consultas-api.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-07-consultas-api.md)  
- TR UI: [TR-SPEC-101-11-consultas-ui.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md)
