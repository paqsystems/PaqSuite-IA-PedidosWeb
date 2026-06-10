# Consulta de cheques — fuente de verdad

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente |
| **HU** | [HU-101-022-consulta-cheques](../../03-historias-usuario/101-PedidosWeb/HU-101-022-consulta-cheques.md) |
| **API** | `GET /api/v1/consultas/cheques` |
| **UI** | `/consultas/cheques` — `ChequesPage`, proceso `pw_cheques` |
| **Backend** | `App\Services\PedidosWeb\ChequesConsultaService` |
| **Permiso** | `Permiso_Repo` sobre procedimiento `pw_consultacheques` |
| **Visibilidad** | Filtra por **universo de clientes visibles** del usuario; query opcional `cod_cliente` (supervisor/vendedor) |

---

## 1) Objetivo

Exponer **cheques en cartera o aplicados a futuro** importados del ERP (tabla `pq_pedidosweb_cheques`): interno, número de cheque, cliente, nombre del cliente, banco, fecha, importe, origen y estado.

Consulta **informativa** (no modifica datos). La fecha de última sincronización ERP va en carátula (`metadata.fecha_proceso`).

**Filtro de negocio (§17.5):** solo cheques con `fecha` **≥ día actual** (cartera o aplicados con fecha posterior al día).

---

## 2) Tablas y joins

| Dato en pantalla | Columna BD | Notas |
|------------------|------------|--------|
| Nro. interno | `pq_pedidosweb_cheques.interno` | Identificador interno ERP |
| Número | `pq_pedidosweb_cheques.numero` | Número de cheque |
| Cliente | `pq_pedidosweb_cheques.cod_cliente` | Código cliente |
| Nombre | `pq_pedidosweb_clientes.nombre` | Join `clientes.cod_client = cheques.cod_cliente` — **no** usar `razon_soci` |
| Banco | `pq_pedidosweb_cheques.Banco` | Nombre del banco |
| Fecha | `pq_pedidosweb_cheques.fecha` | Fecha del cheque |
| Importe | `pq_pedidosweb_cheques.Importe` | Importe del cheque |
| Origen | `pq_pedidosweb_cheques.Origen` | Origen / cartera según ERP |
| Estado | `pq_pedidosweb_cheques.Estado` | Estado cartera/aplicado/etc. |

**Join obligatorio en consulta:**

```sql
FROM pq_pedidosweb_cheques ch
LEFT JOIN pq_pedidosweb_clientes c ON c.cod_client = ch.cod_cliente
WHERE ch.fecha >= CAST(GETDATE() AS date)
```

**Orden:** `fecha` descendente, luego `cod_cliente` ascendente, luego `numero` ascendente.

> **Nota nombres de columna:** el script ERP puede exportar `Banco`, `Importe`, `Origen` y `Estado` con esa capitalización. SQL Server trata identificadores sin comillas como case-insensitive; en bootstrap dev MVP se usan minúsculas equivalentes (§4).

---

## 3) Clave primaria (script ERP)

Compuesta:

- `interno`
- `numero`

---

## 4) Compatibilidad legacy (bootstrap dev MVP)

Si la tabla **no** tiene columnas ERP con nombres canónicos, resolver con `PedidosWebSchemaBootstrap::chequesColumnMap()`:

| ERP (canónico) | Legacy bootstrap dev |
|----------------|----------------------|
| `cod_cliente` | `cod_client` |
| `Banco` | `banco` |
| `Importe` | `importe` |
| `Origen` | `origen` |
| `Estado` | `estado` |

`interno` y `numero` son iguales en ERP y bootstrap dev.

La API **siempre** expone las mismas propiedades JSON (§6); la detección es solo interna.

**Nombre cliente:** siempre `pq_pedidosweb_clientes.nombre` (no `razon_soci`).

---

## 5) Visibilidad y filtros

| Perfil | Comportamiento |
|--------|----------------|
| **Cliente** | Solo cheques de su `cod_client` (universo visible = él mismo) |
| **Vendedor / supervisor** | Cheques de todos los clientes visibles, o filtro `?cod_cliente=` si se informa |
| **Query `cod_cliente`** | Opcional; debe pertenecer al universo visible → **404** si no |
| **Filtro fecha** | `fecha >= hoy` (§17.5) — obligatorio en consulta |

**BD sin tabla:** si no existe `pq_pedidosweb_cheques`, la API responde **200** con `items: []` y `total: 0`.

---

## 6) Contrato API (`resultado.items[]`)

| Propiedad JSON | Tipo | Origen |
|----------------|------|--------|
| `interno` | string | `cheques.interno` |
| `numero` | string | `cheques.numero` |
| `codCliente` | string | `cheques.cod_cliente` (o legacy `cod_client`) |
| `nombreCliente` | string | `clientes.nombre` |
| `banco` | string | `cheques.Banco` (o legacy `banco`) |
| `fecha` | string \| null | ISO 8601 — `cheques.fecha` |
| `importe` | number | `cheques.Importe` (o legacy `importe`) — redondeo **2 decimales** en API |
| `origen` | string | `cheques.Origen` (o legacy `origen`) |
| `estado` | string | `cheques.Estado` (o legacy `estado`) |

**Metadata:** `metadata.fecha_proceso` — `MAX(fecha_proceso)` de `pq_pedidosweb_cheques`, o `null` si no hay datos. Mismo valor para todos los registros de la exportación ERP; se muestra en carátula UI (`consultas.fechaProceso`).

**Paginación:** `page`, `page_size` (máx. 100), `total`, `total_pages`.

**Query opcional:** `cod_cliente`.

---

## 7) Implementación

- Servicio dedicado `ChequesConsultaService` — consulta paginada en SQL con join a clientes.
- Delegado desde `ConsultaListadoService::cheques()`.
- Detección de columnas vía `PedidosWebSchemaBootstrap::chequesColumnMap()`.
- Filtro `fecha >= hoy` en SQL, no en PHP post-carga.
- No recalcular importes en PHP; datos vienen del ERP.

---

## 8) UI (grilla)

Columnas visibles (i18n `consultas.column.*`):

| # | `dataField` | Clave i18n | Formato |
|---|-------------|------------|---------|
| 1 | `interno` | `consultas.column.interno` | texto |
| 2 | `numero` | `consultas.column.numero` | texto |
| 3 | `codCliente` | `consultas.column.cliente` | texto |
| 4 | `nombreCliente` | `consultas.column.nombre` | texto |
| 5 | `banco` | `consultas.column.banco` | texto |
| 6 | `fecha` | `consultas.column.fecha` | `dd/MM/yyyy` |
| 7 | `importe` | `consultas.column.importe` | `#,##0.00` |
| 8 | `origen` | `consultas.column.origen` | texto |
| 9 | `estado` | `consultas.column.estado` | texto |

`data-testid` página: `page-consulta-cheques`. Export Excel GEN-03 sobre grilla visible.

---

## 9) Referencias

- Producto §17.5: [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md)  
- Modelo §4.1: [PedidosWeb_Modelo_Datos_Final.md](PedidosWeb_Modelo_Datos_Final.md)  
- TR API: [TR-SPEC-101-07-consultas-api.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-07-consultas-api.md)  
- TR UI: [TR-SPEC-101-11-consultas-ui.md](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md)
