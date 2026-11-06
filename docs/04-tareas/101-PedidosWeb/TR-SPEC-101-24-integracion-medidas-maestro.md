# TR-SPEC-101-24b — Handler y stored procedures del maestro de medidas

| Campo | Valor |
|---|---|
| **HU relacionada** | [HU-101-053](../../03-historias-usuario/101-PedidosWeb/HU-101-053-integracion-medidas-maestro.md) |
| **SPEC relacionada** | [SPEC-101-24](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **Prioridad** | Must |
| **Estado** | C1 apto con observaciones — listo para D1 |
| **Dependencia** | TR-SPEC-101-24a |

## 1. Objetivo

Implementar el recurso `medidas` con upsert por `cod_medida`, soporte delta y
snapshot, bajas físicas al finalizar el ciclo y contadores finales consistentes.

## 2. Contrato de datos

| JSON | BD | Regla |
|---|---|---|
| `idMedida` | `id_medida` | Obligatorio, lo informa el ERP; no es identity |
| `codMedida` | `cod_medida` | Obligatorio, máximo 10, clave de upsert |
| `descMedida` | `desc_medida` | Opcional, máximo 40 |

Validar longitudes y nulabilidad contra el esquema real antes de cerrar D1.
Los nombres de propiedades de API deben conservar camelCase.

## 3. Comportamiento

### Delta

- Buscar por `cod_medida`.
- Insertar si no existe.
- Modificar solo campos presentes si existe.
- No eliminar ausentes.

### Snapshot

- Acumular todos los lotes del ciclo en staging.
- Esperar `ultimoEnvio=true`.
- Comparar el universo completo recibido con la tabla destino.
- Eliminar físicamente ausentes después de validar el ciclo completo. La tabla
  no tiene referencias funcionales en el sistema.
- Aceptar `items=[]` como universo vacío.
- No eliminar lote por lote.

### Rechazos y contadores

- `codMedida` ausente, vacío o inválido → `rechazado`, fuera de
  `procesados` y de los seis contadores.
- Código inexistente → `agregados`.
- Código existente → `modificados`.
- Baja física por ausencia → `eliminados`.
- Fallo de una operación clasificable → contador `*Erroneos` correspondiente.
- Verificar:

```text
procesados =
  agregados + modificados + eliminados +
  agregadosErroneos + modificadosErroneos + eliminadosErroneos
```

## 4. Stored procedures

Crear scripts idempotentes en:

```text
backend/scripts/sql/integracion-maestros/
```

Familia mínima propuesta:

```text
pq_sp_integracion_importaciones_medidas_apply
pq_sp_integracion_importaciones_medidas_finalize_snapshot
```

`apply` recibe tenant, batch/ciclo, modo, `idMedida`, `codMedida`,
`descMedida`, fecha de proceso y origen. `finalize_snapshot` recibe tenant y
batch/ciclo, calcula ausencias y aplica las bajas físicas.

Los nombres finales y parámetros deben alinearse con el adapter PHP y el
gateway. No usar Eloquent, Query Builder ni `whereIn` de tamaño incierto.

`idMedida` no define la identidad del upsert, pero sí forma parte de los datos
persistidos. Si un `codMedida` existente llega con otro `idMedida`, el SP
actualiza `id_medida` junto con los demás campos presentes.

## 5. Adapter y handler

Crear o completar:

```text
MedidasMaestroHandler
MedidasMaestroSpRepository
```

Responsabilidades:

1. Normalizar y validar el ítem.
2. Clasificar alta/modificación usando `codMedida`.
3. Invocar el SP mediante el repositorio.
4. Traducir errores de validación/FK/negocio a códigos del envelope.
5. Ejecutar finalización solo cuando el ciclo esté completo.
6. Devolver contadores y errores finales al job.

## 6. Medidas referenciadas

La tabla `pq_pedidosweb_medidas` no tiene referencias funcionales en el
sistema. El SP puede eliminar físicamente las medidas ausentes al finalizar el
snapshot, sin clasificación especial por referencia.

## 7. Tests

- Alta delta por `codMedida`.
- Modificación delta por `codMedida`.
- Delta no elimina ausentes.
- Snapshot de varios lotes.
- Snapshot vacío.
- Baja física al finalizar snapshot.
- Ausencia en lote intermedio no elimina.
- `codMedida` faltante/rechazado.
- Error clasificable incrementa el contador correcto.
- Suma de seis contadores igual a `procesados`.
- Reenvío idempotente.
- Persistencia auditada mediante logs.

## 8. Plan de trabajo

| ID | Trabajo | Evidencia |
|---|---|---|
| 24b-01 | Registrar handler `medidas` | Registry test |
| 24b-02 | Crear repository y SP apply | Test delta |
| 24b-03 | Crear finalize snapshot | Test snapshot/vacío |
| 24b-04 | Implementar clasificación y contadores | Unit tests |
| 24b-05 | Mapear errores y logs | Feature tests |
| 24b-06 | Verificar semántica de baja | Eliminación física de ausentes + test |

## 9. Criterios de terminado

- [ ] Upsert por `codMedida`.
- [ ] Delta y snapshot implementados.
- [ ] Bajas solo al finalizar snapshot.
- [ ] Snapshot vacío probado.
- [ ] Rechazos y seis contadores probados.
- [ ] SP desplegables e idempotentes.
- [ ] Sin SQL de negocio directo desde PHP.

### Checklist normas transversales

- [ ] Todo acceso de negocio pasa por stored procedures.
- [ ] No se usa `whereIn` con conjunto de tamaño incierto.
- [ ] Errores y contadores respetan el envelope GEN-20.
- [ ] No se amplía el alcance a otros maestros.
