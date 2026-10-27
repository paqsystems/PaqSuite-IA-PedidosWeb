# TR-SPEC-101-23b — Importación de stock, deuda y cheques

Jira: [PIAPEDWEB-44](https://paqsystems.atlassian.net/browse/PIAPEDWEB-44)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-050](../../03-historias-usuario/101-PedidosWeb/HU-101-050-integracion-transacciones-saldos.md) |
| **SPEC relacionada** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Prioridad** | **Must** |
| **Dependencias** | TR-23a plataforma · maestros de artículos/clientes disponibles o fixtures equivalentes |
| **Estado** | **C1 apto con observaciones** — autoriza D1 |
| **Última actualización** | 2026-10-22 |

## 1) Objetivo y alcance

Implementar los handlers y stored procedures de:

| Recurso | Tabla | Clave natural | Modos |
|---------|-------|---------------|-------|
| `stock` | `pq_pedidosweb_stock` | `cod_articulo` | snapshot / delta |
| `deuda` | `pq_pedidosweb_deuda` | `(cod_cliente, t_comp, n_comp, fecha_vto)` | snapshot / delta |
| `cheques` | `pq_pedidosweb_cheques` | `(interno, numero)` | snapshot / delta |

### Incluido

- Validación de FK de artículos/clientes.
- Normalización camelCase → columnas canónicas.
- Delta merge/upsert sin borrar ausentes.
- Snapshot replace-conjunto atómico por request y recurso.
- Fecha de proceso y metadatos.
- Rechazo de claves duplicadas según modo.
- SP y adapters PHP.
- Tests feature representativos y tests de atomicidad.

### Fuera de alcance

- Historial → TR-23c.
- DDL de tablas de dominio salvo gap documentado.
- SQL directo desde controller/handler PHP.
- Importar pedidos/presupuestos o calcular comprometido web.

## 2) Criterios de aceptación

| HU | Verificación TR |
|----|-----------------|
| CA-01…CA-04 | Feature stock: delta, snapshot, FK y campos prohibidos |
| CA-05…CA-07 | Feature deuda: esquema canónico, snapshot atómico y delta |
| CA-08…CA-10 | Feature cheques: mapeo, snapshot y duplicados |
| CA-11 | Revisión de adapters: solo SP |
| CA-12 | Consulta GET refleja snapshot con visibilidad propia |

## 3) Contrato de handlers y SP

Convención final de familias:

```text
pq_sp_integracion_transacciones_stock
pq_sp_integracion_transacciones_deuda
pq_sp_integracion_transacciones_cheques
```

Cada SP recibe como mínimo tenant resuelto, `batch_id`/identificador de request, modo, fecha de proceso, origen y columnas explícitas del ítem. El uso de payload JSON queda reservado a los casos en que el contrato físico lo requiera y debe documentarse.

| Handler | SP principal | Snapshot |
|---------|--------------|----------|
| `StockTransaccionHandler` | `pq_sp_integracion_transacciones_stock` | true |
| `DeudaTransaccionHandler` | `pq_sp_integracion_transacciones_deuda` | true |
| `ChequesTransaccionHandler` | `pq_sp_integracion_transacciones_cheques` | true |

El nombre exacto de la acción (`_apply`, `_upsert`, `_snapshot_finalize`, etc.) se fija en C1/D1, conservando la familia contractual anterior.

## 4) Políticas de persistencia

### 4.1 Snapshot

1. Crear un contexto/batch aislado por tenant y recurso.
2. Validar todo el lote antes de publicar cambios.
3. Si hay FK inválida, clave duplicada o ítem inválido: abortar y devolver 422.
4. Si todo es válido, reemplazar el conjunto del recurso dentro de una transacción SQL controlada por SP.
5. Claves no enviadas salen del universo snapshot.
6. No ejecutar `TRUNCATE`, `DROP` ni SQL ad-hoc desde PHP.

Snapshot vacío → 422 sin modificar la tabla.

### 4.2 Delta

- Aplicar ítems válidos y continuar ante rechazos.
- HTTP 200 con `error: 0` y contadores.
- Merge/upsert: columnas omitidas no pisan valores existentes.
- Claves ausentes no se eliminan.
- La primera ocurrencia de una clave duplicada puede aplicarse; posteriores se reportan como `rechazados`.
- `items: []` es no-op 200.

## 5) Mapeos y validaciones

### Stock

| JSON | Persistencia |
|------|--------------|
| `codArticulo` | `cod_articulo` |
| `stock` | columna de stock canónica |
| `comprometido` | columna de comprometido ERP |
| `fechaProceso` | `uma_fecha` |

- Validar artículo existente.
- Aceptar `stockeable=0`.
- Rechazar por ítem `comprometidoWeb`, `stockBase`, `comprometidoBase` y `disponibleNeto*`.
- No calcular ni persistir comprometido web/netos.

### Deuda

| JSON | Persistencia |
|------|--------------|
| `codCliente` | `cod_cliente` |
| `tComp` | `t_comp` |
| `nComp` | `n_comp` |
| `fechaEmis` | `fecha_emis` |
| `fechaVto` | `fecha_vto` |
| `saldo` | `saldo` |

- Validar cliente existente.
- No requerir ni persistir razón social desde el lote.
- Escribir exclusivamente el esquema canónico, no columnas legacy.
- Guardar `fecha_proceso` de la carátula para la metadata de consulta.

### Cheques

| JSON | Persistencia |
|------|--------------|
| `interno`, `numero` | clave natural |
| `codCliente` | código de cliente |
| `banco` | `Banco` |
| `fecha` | fecha del cheque |
| `importe` | `Importe` |
| `origen` | `Origen` |
| `estado` | `Estado` |

- Validar cliente existente.
- No persistir nombre de cliente.
- No aplicar en import el filtro `fecha >= hoy`.
- Los negativos son válidos.

## 6) Adapter PHP y datos

Ubicación sugerida:

```text
app/Services/PedidosWeb/Integracion/Transacciones/Handlers/
app/Services/PedidosWeb/Integracion/Transacciones/Repositories/
```

El repository invoca stored procedures con parámetros tipados. No usar Eloquent, Query Builder, `whereIn()` de listas runtime ni SQL concatenado sobre tablas de negocio.

Para snapshots concurrentes, el aislamiento debe incluir tenant, recurso y batch; el mecanismo técnico final (staging, TVP u otro contrato SP) se documenta en D1 sin cambiar el comportamiento funcional.

## 7) Tests

Crear o extender:

```text
tests/Feature/Api/PedidosWeb/IntegracionTransaccionSaldosTest.php
tests/Unit/PedidosWeb/Integracion/IntegracionTransaccionSaldosTest.php
```

Casos mínimos:

- Stock delta válido y FK inválida.
- Stock snapshot vacío, inválido y atómico.
- Campos prohibidos de stock.
- Deuda canónica, duplicado snapshot y delta parcial.
- Cheques mapeo, snapshot atómico y duplicado delta.
- Revisión de no modificación de pedidos/presupuestos.
- Verificación de invocación de SP mediante repository fake/unit o SQL Server de integración.

## 8) Plan de tareas

1. Contratos de repository y nombres finales de SP.
2. Handler y SP de stock.
3. Handler y SP de deuda.
4. Handler y SP de cheques.
5. Registro de handlers.
6. OpenAPI específico de ítems.
7. Feature/unit tests.

Orden de smoke: `stock` → `deuda` → `cheques`.

## 9) Riesgos y decisiones para C1

- Confirmar nombres físicos exactos de columnas de stock y cheques contra el modelo vigente.
- Confirmar mecanismo de staging/TVP para snapshot sin inventar DDL de dominio.
- Mantener el contrato de atomicidad aunque el mecanismo SP se defina en D1.

### Checklist normas transversales

- [ ] Endpoints nuevos con policy/middleware en código.
- [ ] Matriz endpoint ↔ permiso actualizada.
- [ ] OpenAPI en `/api/documentation` coherente con código y matriz.
- [x] 401 y 403 documentados por operación protegida en TR-23a.
- [x] Envelope JSON con `error` entero y `resultado` objeto.
- [x] `X-Paq-Cliente` documentado en TR-23a.
- [ ] Tests API incluyen 401 y 403 en la suite transversal.
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR.

## 10) Veredicto C1

**Apto con observaciones para D1.** D1 debe cerrar columnas físicas y mecanismo de staging/TVP antes de implementar los SP; la atomicidad funcional queda fija por el SPEC.
