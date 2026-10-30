# TR-SPEC-101-23c — Importación delta de historial de ventas ERP

Jira: [PIAPEDWEB-45](https://paqsystems.atlassian.net/browse/PIAPEDWEB-45)

| Campo | Valor |
|-------|-------|
| **HU relacionada** | [HU-101-051](../../03-historias-usuario/101-PedidosWeb/HU-101-051-integracion-transacciones-historial-ventas.md) |
| **SPEC relacionada** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Prioridad** | **Must** |
| **Dependencias** | TR-23a plataforma · clientes disponibles o fixtures equivalentes |
| **Estado** | **Finalizado** (F/I 2026-10-29) |
| **Última actualización** | 2026-10-29 |

## 1) Objetivo y alcance

Implementar el recurso:

```text
POST /api/v1/integracion/transacciones/historial-ventas
```

sobre `pq_pedidosweb_ventadetallada`, con modo exclusivamente `delta` y upsert por `id_gva53`.

### Incluido

- Handler y repository del historial.
- Stored procedure `pq_sp_integracion_transacciones_historial_ventas`.
- Validación de campos obligatorios y cliente.
- Mapeo de campos de la consulta existente.
- Actualización por reenvío sin duplicar.
- Conservación de filas ausentes.
- Fecha de proceso.
- OpenAPI del ítem.
- Tests de snapshot rechazado, upsert, FK y no borrado.

### Fuera de alcance

- Snapshot.
- Borrado o reconciliación del histórico ausente.
- Validar `codArticu` contra el maestro de artículos.
- Cambiar el GET o aplicar `DiasVentasDetalladas` durante la escritura.
- Pedidos, deuda, stock, cheques, UI, cron e idempotencia.

## 2) Criterios de aceptación

| HU | Verificación TR |
|----|-----------------|
| CA-01 | Feature de ruta y `resultado.recurso` |
| CA-02 | Snapshot → 422 y `modoNoSoportado` |
| CA-03 | Cada campo obligatorio faltante → rechazo por ítem |
| CA-04 | Reenvío de `idGva53` deja una sola fila actualizada |
| CA-05 | Cliente inválido rechazado; válidos continúan |
| CA-06 | `fecha_proceso` desde fila o carátula según contrato resuelto |
| CA-07 | Fila ausente del delta permanece |
| CA-08 | `DiasVentasDetalladas` solo afecta GET |
| CA-09 | Repository usa únicamente el SP del recurso |

## 3) Contrato de ítem

Campos obligatorios:

```json
{
  "idGva53": 1001,
  "codCliente": "C001",
  "tipo": "FC",
  "numero": "0001-00001234",
  "fechaEmision": "2026-10-22",
  "codArticu": "A001"
}
```

Los campos restantes de la consulta de historial son opcionales/nullable según el modelo vigente. La tabla definitiva camelCase → columna física debe quedar en el anexo de implementación de esta TR antes de D1.

Reglas:

- `idGva53`, `codCliente`, `tipo`, `numero` y `fechaEmision` son obligatorios.
- `codCliente` debe existir.
- `codArticu` no requiere FK de artículo.
- `fechaEmision` conserva la fecha del comprobante.
- `fechaProceso` de carátula prevalece para todas las filas y se persiste en `fecha_proceso`.
- `fecha_proceso` no se expone como campo de ítem en GET.

## 4) Política delta y SP

| Operación | Resultado |
|-----------|-----------|
| `modo=delta` | Procesa el lote; admite parcial 200 |
| `modo=snapshot` | 422 `integracion.transaccion.modoNoSoportado` |
| Misma clave `idGva53` | Upsert, sin duplicar |
| Fila ausente del lote | Permanece |
| Cliente inválido | Rechazo por ítem |
| `items: []` | 200 no-op |

Stored procedure:

```text
pq_sp_integracion_transacciones_historial_ventas
```

Debe recibir tenant, batch/request, `fechaProceso`, origen y parámetros del ítem de forma tipada. El repository PHP no ejecuta SQL ad-hoc.

## 5) OpenAPI

Documentar:

- Path literal de historial.
- `modo` con valor permitido `delta`.
- Campos obligatorios y opcionales.
- Ejemplo de upsert.
- Respuestas 200, 401, 403, 404 y 422.
- Error `integracion.transaccion.modoNoSoportado`.

Reutilizar schemas transversales de TR-23a y no duplicar el envelope GEN-20.

## 6) Adapter PHP

Ubicación sugerida:

```text
app/Services/PedidosWeb/Integracion/Transacciones/Handlers/HistorialVentasTransaccionHandler.php
app/Services/PedidosWeb/Integracion/Transacciones/Repositories/HistorialVentasSpRepository.php
```

Flujo:

1. Validar modo y campos mínimos.
2. Normalizar tipos y fecha.
3. Validar cliente mediante el contrato permitido del handler/SP.
4. Invocar SP de upsert.
5. Convertir resultado a `aplicados` o `rechazados`.
6. Mantener los errores limitados al contrato común.

No aplicar `DiasVentasDetalladas` en este flujo.

## 7) Tests

Crear o extender:

```text
tests/Feature/Api/PedidosWeb/IntegracionTransaccionHistorialVentasTest.php
tests/Unit/PedidosWeb/Integracion/HistorialVentasTransaccionHandlerTest.php
```

Casos mínimos:

- Delta válido.
- Snapshot rechazado.
- Campo obligatorio faltante.
- Cliente inválido con otro ítem válido.
- Reenvío de `idGva53`.
- Ausente preservado.
- Fecha de proceso.
- `codArticu` inexistente aceptado.
- Uso exclusivo del repository/SP mediante fake.

## 8) Plan de tareas

1. Cerrar tabla de mapeo campo a campo.
2. Definir firma final del SP.
3. Implementar repository y handler.
4. Registrar recurso en registry.
5. OpenAPI.
6. Tests feature/unit.

## 9) Riesgos y decisiones para C1

- La fecha de carátula prevalece en `fecha_proceso`; no usar una fecha de fila para reemplazarla.
- Completar la lista de campos opcionales desde el contrato de consulta sin ampliar el alcance.
- Mantener la semántica histórica: no borrar ausentes y no exigir maestro de artículos.

### Checklist normas transversales

- [ ] Endpoint nuevo con policy/middleware en código.
- [ ] Matriz endpoint ↔ permiso actualizada.
- [ ] OpenAPI en `/api/documentation` coherente con código y matriz.
- [x] 401 y 403 documentados por operación protegida en TR-23a.
- [x] Envelope JSON con `error` entero y `resultado` objeto.
- [x] `X-Paq-Cliente` documentado en TR-23a.
- [ ] Tests API incluyen 401 y 403 en la suite transversal.
- [x] Sin ampliación de alcance fuera de SPEC/HU/TR.

## 10) Veredicto C1

**Apto con observaciones para D1.** La precedencia de fechas queda cerrada: fecha del comprobante en `fechaEmision` y fecha de carátula en `fecha_proceso`. D1 debe completar el mapeo opcional antes de implementar.

## 11) Parte D — implementación (2026-10-29)

| Ítem | Evidencia |
|------|-----------|
| Recurso `historial-ventas` | `integracion_transacciones.php` (`supports_snapshot` false) |
| SP apply | `pq_sp_integracion_transacciones_historial_ventas_apply` en deploy SQL |
| 422 snapshot | `IntegracionMaestroController::receive()` + test plataforma |
| Tests | `IntegracionTransaccionSaldosFeatureTest::testHistorialVentasDeltaVacioCompletaAsync` |

Pendiente: QA ERP delta con ítems reales.
