# TR-SPEC-101-25b — Datos GET exportación comprobantes ERP

Jira: [PIAPEDWEB-59](https://paqsystems.atlassian.net/browse/PIAPEDWEB-59)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-058](../../03-historias-usuario/101-PedidosWeb/HU-101-058-integracion-export-comprobantes-datos.md) |
| **SPEC relacionada** | [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Épica** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) |
| **Prioridad** | **Must** |
| **Dependencias** | [TR-SPEC-101-25-integracion-export-plataforma](TR-SPEC-101-25-integracion-export-plataforma.md) (25a) · tablas `pq_pedidosweb_pedidoscabecera` / `pq_pedidosweb_pedidosdetalle` |
| **Estado** | **Finalizado** (2026-11-26) |
| **Plan D1** | [D1-PLAN-101-25](D1-PLAN-101-25-exportacion-comprobantes-erp.md) § Fase 2 |
| **Última actualización** | 2026-11-26 |

**Origen:** HU-101-058 · SPEC-101-25 § Cabecera/Renglón/Cierre

---

## 1) Objetivo y alcance

Persistir la **lectura** de comprobantes para export ERP mediante **stored procedures** y mapper PHP camelCase, consumido por `ExportComprobantesService` (25a).

### Incluido

- SQL `CREATE/ALTER PROCEDURE` en `backend/scripts/sql/integracion-export/` + script deploy (patrón `deploy-integracion-maestros-sqlserver.php`).
- SP listado paginado + obtención de renglones del page **sin N+1** desde PHP.
- `StoredProcedureExportComprobantesRepository`.
- `IntegracionExportComprobanteMapper` (cabecera, renglones, cierre).
- Joins equivalentes a `ConsultaListadoService::mapComprobanteItem` / `mapPresupuestoCierre` **reimplementados en SP + mapper** (no llamar servicio consulta).
- OpenAPI: propiedades de `cabecera`/`renglón` en schema compartido (completar 25a).
- Feature tests datos con fixtures multi-estado.

### Fuera de alcance

- HTTP/auth (25a).
- Escritura estados ERP.
- Filtro visibilidad vendedor.

---

## 2) Criterios de aceptación (HU-101-058)

| CA HU | Verificación |
|-------|----------------|
| CA-01 | Feature: joins + `talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb` |
| CA-02…CA-03 | Filtro estado y dedup OR en SQL/page |
| CA-04 | Paginación: totales consistentes páginas 1 y 2 |
| CA-05 | Estados -1 y 99 en fixture |
| CA-06 | Filtros `codCliente` / `codPedido` |
| CA-07 | Orden `fecha` desc, `codPedido` asc |
| CA-08 | Assert ausencia `puede*` / `guidSufijo` |
| CA-09 | Campos mínimos renglón SPEC §4 |
| CA-10 | `cierre` objeto vs null |
| CA-11 | Test invoca SP (integración SQL Server) |
| CA-12 | `codPedido` en presupuestos |
| CA-13 | Estado -1 sin transformación |
| CA-14 | Script en pipeline install/update documentado |

---

## 3) Stored procedures

### 3.1 Convención

| SP | Rol |
|----|-----|
| `pq_sp_integracion_export_comprobantes_list` | Page cabeceras + `@total` OUTPUT; predicado estado OR + filtros AND; ORDER BY `fecha DESC`, `cod_pedido ASC`; OFFSET/FETCH; **result set 2** = renglones de los `cod_pedido` del page |

**Decisión C1:** **un solo SP**, **dos result sets** (cabeceras + renglones). No implementar SP de detalle separado en MVP.

### 3.2 Predicado estado

Para cada par `(desde, hasta)`:

```sql
(c.estado >= @desde AND c.estado <= @hasta)
```

Unir con **OR**. Deduplicar por `c.cod_pedido` en la consulta paginada (DISTINCT o subconsulta).

### 3.3 Filtros AND

- `@cod_cliente` NULL = sin filtro; si no null, igualdad.
- `@cod_pedido` idem.
- `@fecha_desde` / `@fecha_hasta` datetime2 — ya normalizados en ART desde PHP.

### 3.4 Parámetros SP (orientativo)

| Parámetro | Tipo | Notas |
|-----------|------|--------|
| `@estado_rangos_json` | nvarchar(max) | JSON array `[{"desde":0,"hasta":0},…]` desde PHP |
| `@page` | int | ≥1 |
| `@page_size` | int | 1…1000 |
| `@cod_cliente` | varchar(...) | nullable |
| `@cod_pedido` | varchar(...) | nullable |
| `@fecha_desde` | datetime2 | nullable |
| `@fecha_hasta` | datetime2 | nullable |
| `@total` | int OUTPUT | count cabeceras (misma predicado, sin paginar) |

Rangos: solo **`@estado_rangos_json`** en D1 (TVP diferido).

### 3.5 NOLOCK

Lecturas SP: tablas cabecera/detalle y joins maestros con hint **NOLOCK** / patrón `SqlServerReadHint` coherente con consultas (regla 75). Sin `NOLOCK` en escrituras (no aplica).

---

## 4) Mapeo JSON

### 4.1 Cabecera

Fuente columnas: SPEC-101-25 § Cabecera + documentación consulta cabecera citada en SPEC.

| Regla | Implementación mapper |
|-------|----------------------|
| Identificador | Siempre `codPedido` (mapear desde `cod_pedido`) |
| MUST integración | `talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb` |
| SHOULD | `codPedidoOrigen`, `codPresupuestoOrigen`, `origenComprobante` si columnas no null |
| Joins texto | `razonSocial`, `vendedorDescripcion`, etc. — mismos joins que consulta |
| Prohibido | Omitir keys `puede*`; no emitir `guidSufijo` |

### 4.2 Renglón

Lista mínima SPEC:  
`renglon`, `codArticulo`, `descripcionArticulo`, `especial`, `cantidad`, `cantidadVenta`, `porcBonif`, `precioLista`, `precioNeto`, `importeBruto`, `importeNeto`, `ivaNeto`, `importeNetoConIva`.

Agrupar por `cod_pedido` en PHP tras result set 2; orden `renglon` ASC.

### 4.3 Cierre

- Siempre clave `cierre` en cada ítem.
- Si `estado <> 98` → `null`.
- Si `estado = 98`: join **`pq_pedidosweb_presupuestos_cierres`** (+ motivo) y shape `mapPresupuestoCierre`; sin registro → `null`.

---

## 5) Capa PHP

```text
App/Services/PedidosWeb/Integracion/Export/
  ExportComprobantesRepositoryInterface.php
  StoredProcedureExportComprobantesRepository.php
  IntegracionExportComprobanteMapper.php
  Dto/ExportComprobantesPage.php
  Dto/ExportComprobantesItem.php
```

- `DB::statement` / `pdo` con binding tipado — **sin** Eloquent en tablas de negocio.
- Gateway: misma firma SP vía adaptador laravel-core si aplica (sin SQL libre).

---

## 6) OpenAPI (complemento 25a)

Documentar en schema `IntegracionExportComprobanteCabecera` y `IntegracionExportComprobanteRenglon` las propiedades MUST/SHOULD (lista derivada de SPEC; no duplicar flags `puede*`).

---

## 7) Tests

```text
tests/Feature/Api/PedidosWeb/IntegracionExportComprobantesDataTest.php
tests/Fixtures/PedidosWeb/IntegracionExportComprobantesFixture.php
```

Fixture mínimo:

- Cabeceras estados -1, 0, 1, 98, 99.
- Una cabecera 0 con 2 renglones; una sin detalle.
- Presupuesto 98 con y sin fila cierre.
- Clientes distintos para filtro `codCliente`.

Tests pueden extender `IntegracionExportComprobantesPlatformTest` cuando SP esté cableado.

---

## 8) Despliegue SQL

- Carpeta: `backend/scripts/sql/integracion-export/`
- Archivos: `pq_sp_integracion_export_comprobantes_list.sql`, `00-deploy-all.sql`
- Script PHP: `backend/scripts/deploy-integracion-export-sqlserver.php` (análogo maestros)
- Referencia en runbook install/update del producto (sin ejecutar destructivo en `Ankas_del_sur` sin consentimiento).

---

## 9) Plan de implementación (orden D1)

1. SP list + deploy script.
2. Repository + mapper con tests unitarios de mapper (arrays fake).
3. Ensamblado `items[]` en repositorio.
4. Cablear en `ExportComprobantesService`.
5. Feature tests datos end-to-end.
6. Completar schemas OpenAPI cabecera/renglón.

---

## 10) Decisiones C1

| # | Tema | Decisión |
|---|------|----------|
| C1-01 | 1 SP vs 2 | Un SP, dos result sets |
| C1-02 | Tabla cierre | `pq_pedidosweb_presupuestos_cierres` |
| C1-03 | Mapper | `IntegracionExportComprobanteMapper` standalone (no `ConsultaListadoService`) |
| C1-04 | OpenAPI cabecera | Checklist [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) §2 + MUST integración |

Cierre: [F-101-25-cierre-c1](F-101-25-cierre-c1-exportacion-comprobantes-erp.md).

## 11) Checklist

- [x] SP desplegado en scripts del repo.
- [x] Sin Query Builder en lectura export.
- [x] NOLOCK en lecturas según regla 75.
- [x] Tests CA-01…CA-14 HU-058 (feature `IntegracionExportComprobantesDataTest`).
- [x] Sin `tipoComprobante` en API.

## 12) Veredicto

| Fase | Estado |
|------|--------|
| C | Generada 2026-11-26 |
| C1 | **Apto con observaciones** — autoriza D1 (post 25a o en paralelo según plan) |
| D / F1 / F / I | **Finalizado** 2026-11-26 — [F-101-25-cierre-formal](F-101-25-cierre-formal.md) |