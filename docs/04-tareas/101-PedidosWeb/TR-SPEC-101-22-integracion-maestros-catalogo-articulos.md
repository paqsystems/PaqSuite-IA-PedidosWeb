# TR-SPEC-101-22b — Importación maestros: catálogo, artículos y precios

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-047](../../03-historias-usuario/101-PedidosWeb/HU-101-047-integracion-maestros-catalogo-articulos.md) |
| **SPEC relacionada** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Prioridad** | **Must** |
| **Dependencias** | [TR-SPEC-101-22-integracion-maestros-plataforma](TR-SPEC-101-22-integracion-maestros-plataforma.md) (22a) |
| **Estado** | **Actualización 22d especificada** — listas de precios anidadas; pendiente D |
| **C1** | [F-101-22-cierre-c1](F-101-22-cierre-c1-importacion-maestros-erp.md) (2026-10-21) |
| **D1** | [D1-PLAN-101-22b-integracion-maestros-catalogo-articulos](D1-PLAN-101-22b-integracion-maestros-catalogo-articulos.md) (2026-10-21) |
| **Última actualización** | 2026-10-21 |

---

## 1) Resumen

Implementar **10 recursos** POST maestros con handlers PHP + **stored procedures** SQL Server (regla 74). Políticas delta/snapshot según A1-C01…C04 del SPEC.

### In scope

- Scripts `CREATE/ALTER PROCEDURE` en `backend/scripts/sql/integracion-maestros/`
- Handlers registrados en `IntegracionMaestroRegistry`
- OpenAPI: un path documentado por recurso (reutiliza schemas 22a)
- Feature tests por recurso representativo + snapshot condventa + listas de precios con `articulos[]`

### Out of scope

- Clientes / parámetros → TR-22c

---

## 2) AC (HU-101-047)

| AC | Verificación |
|----|----------------|
| CA-01…CA-03 | Feature provincias / condventa / vendedores |
| CA-04…CA-05 | Feature escalas anidadas + snapshot detalle |
| CA-06…CA-07 | Feature articulos delta parcial + eliminarSiAusente |
| CA-08 | Verificar que `listaprecios` procesa sus precios mediante `items[]` anidado y que no se expone `listaprecios-articulos` como request independiente |
| CA-09 | Feature descuentocantidad delta |
| CA-10 | OpenAPI 10 paths |

---

## 3) Catálogo recursos → SP → handler

Convención SP: `pq_sp_integracion_maestros_{recurso_snake}_{accion}`.

| `{recurso}` URL | Handler PHP | SP principal | `supportsSnapshot` |
|-----------------|-------------|--------------|-------------------|
| `provincias` | `ProvinciasMaestroHandler` | `pq_sp_integracion_maestros_provincias_apply` | true |
| `condventa` | `CondventaMaestroHandler` | `pq_sp_integracion_maestros_condventa_apply` | true |
| `transportes` | `TransportesMaestroHandler` | `pq_sp_integracion_maestros_transportes_apply` | true |
| `perfiles` | `PerfilesMaestroHandler` | `pq_sp_integracion_maestros_perfiles_apply` | true |
| `vendedores` | `VendedoresMaestroHandler` | `pq_sp_integracion_maestros_vendedores_apply` | true (replace; no delete ausentes) |
| `listaprecios` | `ListapreciosMaestroHandler` | `pq_sp_integracion_maestros_listaprecios_apply` + SP de precios hijos | true |
| `escalas` | `EscalasMaestroHandler` | `pq_sp_integracion_maestros_escalas_apply` + `_detalle_apply` | true |
| `articulos` | `ArticulosMaestroHandler` | `pq_sp_integracion_maestros_articulos_apply` | true (replace; no delete ausentes) |
| `descuentocantidad` | `DescuentocantidadMaestroHandler` | `pq_sp_integracion_maestros_descuentocantidad_apply` | **false** |

Cada `@accion` SP recibe parámetros tipados (modo, clave natural, payload JSON o columnas explícitas — **preferir columnas explícitas** en TR de implementación). Snapshot catálogo chico: lógica post-lote en SP o SP `*_finalize_snapshot` invocado desde handler tras procesar ítems (documentar en script).

### 3.1 Políticas en SP/handler

| Grupo | Delta | Snapshot |
|-------|-------|----------|
| provincias, condventa, transportes, perfiles | MERGE campos enviados | DELETE códigos BD no presentes en lote si sin FK; FK → omitido |
| listaprecios | MERGE cabecera + upsert de `articulos[]` | Lista ausente: eliminar primero precios hijos; si está referenciada, inactivar si existe atributo; si no, conservar; sin referencia, eliminar |
| escalas | MERGE cabecera + merge detalle por ítem | Detalle hijo = universo `valores[]` por cabecera del lote |
| vendedores, articulos | MERGE | REPLACE campos enviados; `eliminarSiAusente` en ítem |
| descuentocantidad | MERGE | **422** si se solicita snapshot |

### 3.2 JSON ítems (camelCase → SP)

SoT columnas: [PedidosWeb_Modelo_Datos_Final.md](../../02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md) §3.

**Escalas** (AMB-M-101-22-01):

```json
{
  "codEscala": "01",
  "descripEs": "Unidades",
  "nroEscala": 1,
  "valores": [
    { "codValor": "U", "descValor": "Unidad" }
  ],
  "eliminarSiAusente": false
}
```

**Listas de precios**:

```json
{
  "codLista": 1,
  "incluyeIva": false,
  "moneda": "1",
  "descripcion": "Lista Ankas A",
  "decimales": 2,
  "articulos": [
    { "codArticulo": "AB", "precio": 3824 },
    { "codArticulo": "AC", "precio": 2361 }
  ]
}
```

En esta primera versión la ausencia de un artículo dentro de una lista presente no elimina su precio hijo; la baja masiva de precios hijos se ejecuta al procesar una lista ausente del snapshot.

**Artículos** (campos mínimos): `codigo`, `descripcion`, `bonificacion`, `usaEsc`, `base`, `valor1`, `valor2`, `porcIva`, `equivalenciaVentas`, `stockeable`, `especial` (bool).

**Vendedores:** ignorar `codLogin` en mapper.

Mapeo completo campo a campo: tabla en anexo D1 (no bloquea C).

---

## 4) Repositorio / adapter PHP

Patrón único por recurso:

```text
App\Services\PedidosWeb\Integracion\Maestros\Handlers\{Name}MaestroHandler
App\Services\PedidosWeb\Integracion\Maestros\Repositories\{Name}MaestroSpRepository
```

Repository: `DB::statement('EXEC pq_sp_integracion_maestros_...', [...])` — sin Eloquent en tablas `pq_pedidosweb_*`.

Handler:

1. Normaliza ítem (tipos bool/char).
2. Valida FK mínima (ej. lista existe) — opcional pre-check PHP o dejar error SQL mapeado a `integracion.maestro.fkFaltante`.
3. Invoca SP por ítem en **delta**; en **snapshot** catálogo chico invoca finalize tras loop.

---

## 5) Scripts SQL

Carpeta: `backend/scripts/sql/integracion-maestros/`

Crear primero una tabla técnica de staging para snapshots, por ejemplo
`pq_pedidosweb_integracion_maestros_stage`, con `batch_id` único por request,
tenant, recurso, clave natural, `fecha_proceso`, origen y estado. La tabla no
contiene el payload comercial final, no se expone por API y debe tener limpieza
idempotente de batches finalizados o expirados.

| Archivo | Contenido |
|---------|-----------|
| `00-deploy-all.sql` | Orden de deploy |
| `sp_pq_integracion_maestros_provincias_apply.sql` | … uno por SP |
| … | |
| `sp_pq_integracion_maestros_escalas_apply.sql` | cabecera + detalle |

Los `apply` reciben `batch_id` además de las columnas explícitas y registran la
clave natural procesada en staging. Los `finalize_snapshot` reciben únicamente
ese `batch_id`, por lo que el borrado de ausentes queda aislado por tenant,
recurso y ejecución concurrente. `fechaProceso` y `origen` son metadatos del
lote, no su identidad.

Incluir tabla técnica y procedimientos en pipeline install/update producto
(documentar en PR; no ejecutar destructivo en `Ankas_del_sur` sin consentimiento).

---

## 6) OpenAPI

Extender `PedidosWebOpenApiPaths.php`: 10 entradas POST con `{recurso}` fijo o path literal por recurso (preferido **path literal** por claridad Swagger):

- `/api/v1/integracion/maestros/provincias`
- … (10 paths)

RequestBody ref `IntegracionMaestroLoteRequest`; response ref envelope 22a.

---

## 7) Tests

| Test | Escenario |
|------|-----------|
| `IntegracionMaestroCondventaSnapshotTest` | snapshot elimina código huérfano |
| `IntegracionMaestroArticulosDeltaPartialTest` | 1 ítem malo, resto OK |
| `IntegracionMaestroListapreciosArticulosSnapshotRejectedTest` | 422 |
| `IntegracionMaestroEscalasNestedTest` | cabecera + 2 valores |

Requieren SQL Server con SP desplegados o skip marcado si CI sin SP.

---

## 8) Plan tareas D1

1. Tabla staging + scripts SP (10 familias + escalas detalle)
2. Repositories + handlers
3. Registry config; retirar `listaprecios-articulos` y registrar `articulos[]` en `listaprecios`
4. OpenAPI paths
5. Feature tests

**Orden implementación:** 22a plataforma → provincias/condventa (smoke) → resto catálogo → articulos → `listaprecios` con `items[]` anidado.

---

## 9) Checklist

- [x] Todo acceso negocio vía SP
- [x] Staging de snapshots aislado por `batch_id`, tenant y recurso
- [x] Sin `whereIn` masivo (regla 77)
- [x] `supportsSnapshot=false` en registry para descuentocantidad
- [x] OpenAPI 10 paths
- [ ] Pedidos/presupuestos intactos (assert SQL count en test de integración)
