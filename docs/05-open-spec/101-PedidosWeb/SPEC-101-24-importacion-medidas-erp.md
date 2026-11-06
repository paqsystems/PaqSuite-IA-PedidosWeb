# SPEC-101-24 — Importación asíncrona de medidas ERP → PedidosWeb

| Campo | Valor |
|---|---|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **SPEC relacionado** | [SPEC-101-22-importacion-maestros-erp.md](SPEC-101-22-importacion-maestros-erp.md) |
| **Producto** | PedidosWeb |
| **Estado** | A1 apto con observaciones — autoriza Parte B |
| **Prioridad** | Must para completar los maestros requeridos por el ERP |
| **Ámbito** | Backend HTTP servidor-a-servidor; sin pantalla nueva |
| **Tabla destino** | `pq_pedidosweb_medidas` |
| **Origen funcional** | Maestro `MEDIDA` del ERP Tango |

## 1. Objetivo

Agregar el recurso `medidas` al protocolo de importaciones asíncronas de
maestros para que el ERP pueda actualizar `pq_pedidosweb_medidas` mediante
lotes JSON, con respuesta rápida `202 Accepted`, procesamiento posterior,
idempotencia, auditoría y consulta del resultado final.

Este SPEC queda limitado al contrato común de importaciones asíncronas y a la
implementación del recurso `medidas`. Las etapas `condventa`, `articulos`,
resto de maestros y `clientes` con nodos hijos pertenecen al roadmap de
implementación del protocolo y quedan fuera del alcance funcional de este
SPEC.

La tabla de medidas se actualiza exclusivamente mediante el contrato de
integración. No se agrega una pantalla ABM en PedidosWeb.

## 2. Adopción de capacidades GEN

Esta funcionalidad debe adoptar:

- **GEN-19:** resolución del tenant mediante `X-Paq-Cliente`.
- **GEN-20:** envelope `{ error, respuesta, resultado }`, con `error: 0` en
  respuestas exitosas.
- **GEN-36:** recepción de lotes, staging, procesamiento asíncrono, consulta
  de estado, idempotencia y locks.

La persistencia de negocio debe ejecutarse mediante stored procedures, según la
regla 74. El endpoint no puede ejecutar SQL CRUD ad-hoc ni `TRUNCATE`, `DROP` o
recreación de tablas.

## 3. Rutas

No se crea una familia de rutas paralela. Se reutiliza el contrato vigente:

```text
POST /api/v1/integracion/importaciones/medidas
GET  /api/v1/integracion/importaciones/{importacionId}
```

El `importacionId` lo genera el ERP y se mantiene igual para todos los lotes
del ciclo.

La respuesta de `GET` debe validar el tenant resuelto por
`X-Paq-Cliente`; un identificador de otro tenant no puede revelar estado,
payload ni errores.

## 4. Autenticación y transporte

| Pieza | Regla |
|---|---|
| Auth | `Authorization: Bearer {integrationToken}` |
| Ability | `pw_integracion_importacion` |
| Tenant | `X-Paq-Cliente`, obligatorio |
| Content-Type | `application/json` |
| Rate limit | 60 solicitudes por minuto por token y tenant, salvo ajuste aprobado en TR |
| Idempotencia | `importacionId` + `numeroLote` + hash del payload |
| Error de auth | 401 sin Bearer válido; 403 sin ability |
| Error de concurrencia | 409 si ya existe otro ciclo en ejecución para el mismo tenant y recurso |

El ERP debe detener el envío de los lotes siguientes cuando recibe un 409 o
cualquier rechazo de ciclo. Debe registrar el error y avisar al usuario
cuando el proceso sea manual, o dejarlo en el registro operativo cuando sea
automático.

## 5. Contrato del lote

```json
{
  "importacionId": "IMP-20261025-000123",
  "modo": "snapshot",
  "ultimoEnvio": true,
  "numeroLote": 1,
  "fechaProceso": "2026-10-25T00:00:00-03:00",
  "origen": "tango",
  "items": [
    {
      "idMedida": 1,
      "codMedida": "UN",
      "descMedida": "Unidad"
    }
  ]
}
```

### 5.1 Campos de carátula

| Campo | Obligatorio | Regla |
|---|---:|---|
| `importacionId` | Sí | Lo genera el ERP; identifica el ciclo completo |
| `modo` | Sí | `snapshot` o `delta` |
| `ultimoEnvio` | Sí | `true` cierra la recepción del ciclo |
| `numeroLote` | Sí | Secuencia positiva, sin duplicados |
| `fechaProceso` | Sí | Fecha del proceso del ERP, no la llegada HTTP |
| `origen` | No | Texto corto para auditoría |
| `items` | Sí | Array de medidas; puede ser vacío en `snapshot` |

Todos los lotes de un ciclo deben conservar el mismo tenant, recurso, modo y
`fechaProceso`. Un lote repetido con el mismo hash debe responder de manera
idempotente sin duplicar ni volver a aplicar operaciones. El mismo número de
lote con payload diferente debe rechazarse.

### 5.2 Campos de cada medida

| JSON | Tabla | Tipo / regla |
|---|---|---|
| `idMedida` | `id_medida` | Entero; obligatorio; lo informa el ERP y no es identity |
| `codMedida` | `cod_medida` | Texto de hasta 10 caracteres; obligatorio y clave de unicidad |
| `descMedida` | `desc_medida` | Texto de hasta 40 caracteres; opcional |

`idMedida` forma parte del payload porque `id_medida` no es una columna
identity/autogenerada en la tabla destino. El upsert se realiza por
`codMedida`; si el código ya existe y llega otro `idMedida`, el backend
actualiza también ese identificador técnico. La validación definitiva de
longitudes y nulabilidad debe quedar confirmada contra el esquema instalado.

## 6. Semántica delta y snapshot

### 6.1 Delta

- Inserta medidas nuevas.
- Actualiza únicamente los atributos presentes del ítem.
- No elimina medidas ausentes.
- Un ítem inválido se informa como error de ítem; los demás ítems del ciclo
  continúan.

### 6.2 Snapshot

- El conjunto recibido en todos los lotes representa el universo completo de
  medidas vigente en el ERP.
- Cuando llega `ultimoEnvio=true`, el worker compara el staging completo con
  `pq_pedidosweb_medidas`.
- Las medidas ausentes se eliminan físicamente al finalizar el snapshot.
- La tabla no tiene referencias funcionales en el sistema, por lo que no se
  requiere una clasificación `eliminadosErroneos` por FK para este recurso.
- El `snapshot` vacío está permitido y significa que el universo ERP está
  vacío; debe protegerse mediante la marca explícita `modo=snapshot` y
  `ultimoEnvio=true`.
- La eliminación solo se ejecuta al finalizar un ciclo íntegro, nunca lote por
  lote.

Si falta un lote, hay un número duplicado incompatible o el ciclo expira, no se
ejecutan eliminaciones y el ciclo termina como `abortado` o `expirado`.

## 7. Respuesta de recepción

La recepción válida persiste el lote y devuelve inmediatamente:

```json
{
  "error": 0,
  "respuesta": "integracion.importacion.recibida",
  "resultado": {
    "importacionId": "IMP-20261025-000123",
    "estado": "recibida",
    "recurso": "medidas",
    "modo": "snapshot",
    "numeroLote": 1,
    "ultimoEnvio": true
  }
}
```

HTTP: **202 Accepted**.

La recepción no debe esperar a que termine el upsert, el análisis de ausencias
ni las eliminaciones.

## 8. Consulta de estado y resultado

Mientras el ciclo no tenga resultado final, `GET` debe devolver los datos de
recepción y estado, pero todos los contadores de procesamiento y el detalle de
errores deben ser `null`. No se devuelven resultados provisorios.

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "importacionId": "IMP-20261025-000123",
    "estado": "procesando",
    "recurso": "medidas",
    "modo": "snapshot",
    "recibidos": null,
    "procesados": null,
    "agregados": null,
    "modificados": null,
    "eliminados": null,
    "agregadosErroneos": null,
    "modificadosErroneos": null,
    "eliminadosErroneos": null,
    "errores": null,
    "lotesRecibidos": 1,
    "ultimoLoteRecibido": 1,
    "iniciadoEn": "2026-10-25T15:00:00-03:00",
    "finalizadoEn": null
  }
}
```

En el estado final, los seis contadores de operación deben satisfacer:

```text
procesados =
  agregados +
  modificados +
  eliminados +
  agregadosErroneos +
  modificadosErroneos +
  eliminadosErroneos
```

Los ítems con `codMedida` ausente, vacío o inválido se informan como
`rechazados` y quedan fuera de `procesados` y de los seis contadores, porque
no permiten determinar si se intentaba realizar un alta o una modificación.
Los tres contadores terminados en `Erroneos` representan operaciones que se
intentaron pero no pudieron aplicarse. `eliminados` incluye las bajas físicas
por ausencia en un snapshot.

Ejemplo de respuesta final:

```json
{
  "error": 0,
  "respuesta": "integracion.importacion.finalizada",
  "resultado": {
    "importacionId": "IMP-20261025-000123",
    "estado": "finalizado_con_errores",
    "recurso": "medidas",
    "modo": "snapshot",
    "recibidos": 3,
    "procesados": 3,
    "agregados": 1,
    "modificados": 1,
    "eliminados": 0,
    "agregadosErroneos": 1,
    "modificadosErroneos": 0,
    "eliminadosErroneos": 0,
    "errores": [
      {
        "lote": 1,
        "indice": 2,
        "clave": "3",
        "codigo": "integracion.maestro.medidaInvalida",
        "detalle": "codMedida supera la longitud permitida"
      }
    ],
    "lotesRecibidos": 1,
    "ultimoLoteRecibido": 1,
    "iniciadoEn": "2026-10-25T15:00:00-03:00",
    "finalizadoEn": "2026-10-25T15:00:02-03:00"
  }
}
```

El detalle completo de errores se conserva inicialmente. La TR debe establecer
un límite de tamaño de respuesta y una estrategia posterior de paginación o
consulta por referencia si las mediciones muestran que el payload es
excesivo.

## 9. Estados

Estados mínimos:

- `recibida`
- `pendiente_procesamiento`
- `procesando`
- `finalizado`
- `finalizado_con_errores`
- `abortado`
- `expirado`

La transición a `pendiente_procesamiento` ocurre al recibir el lote con
`ultimoEnvio=true`. El worker debe adquirir un lock por
`tenant + recurso`; mientras el ciclo esté activo, otro ciclo para `medidas`
debe recibir 409.

La operación de un ciclo finalizado no se puede mezclar con otro ciclo.
Reintentos del mismo lote se resuelven por idempotencia, no por concurrencia
paralela.

## 10. Persistencia y procesamiento

Se reutilizan las tablas técnicas de GEN-36:

- cabecera de importación;
- lotes recibidos;
- payload JSON original;
- hash e identidad de lote;
- estado, timestamps y contadores;
- detalle de errores.

Conservar el JSON original es obligatorio en esta primera etapa para facilitar
auditoría, reintentos y diagnóstico. La velocidad del endpoint de recepción
tiene prioridad sobre la optimización del procesamiento posterior.

El worker debe:

1. validar integridad y secuencia de lotes;
2. procesar el staging completo;
3. invocar el stored procedure de medidas;
4. aplicar upsert y bajas físicas según el modo;
5. actualizar contadores y errores;
6. registrar el estado final y liberar el lock.

La cola debe ser realmente asíncrona (`database`, Redis u otra aprobada).
`QUEUE_CONNECTION=sync` no es válido para este flujo.

## 11. Stored procedures

La implementación debe entregar y versionar, como mínimo, la familia:

```text
pq_sp_integracion_importaciones_medidas_recibir_lote
pq_sp_integracion_importaciones_medidas_procesar
pq_sp_integracion_importaciones_medidas_consultar
```

Los nombres finales y parámetros se fijan en la TR. Deben soportar ejecución
local y gateway con la misma firma lógica. La API no debe conocer ni ejecutar
SQL de tablas de negocio directamente.

## 12. Etapa de implementación

### Etapa única — Medidas

- Agregar el recurso `medidas`.
- Crear el JSON de prueba y la request correspondiente en la collection de
  Postman.
- Verificar delta, snapshot, snapshot vacío, idempotencia y baja física.

## 13. Tareas Postman obligatorias

La TR de cada etapa debe actualizar:

1. Los JSON existentes de la collection para agregar:
   `agregadosErroneos`, `modificadosErroneos` y `eliminadosErroneos` en los
   ejemplos de resultado final.
2. Una request `medidas - recibir lote 1/1` con:
   - `POST /api/v1/integracion/importaciones/medidas`;
   - variables existentes de tenant, token, fecha e `importacionId`;
   - payload de prueba con al menos alta, modificación y un registro inválido;
   - `ultimoEnvio: true`;
   - tests de HTTP 202, `error: 0`, recurso y estado de recepción.
3. Una request `importacion - consultar resultado medidas` con:
   - `GET /api/v1/integracion/importaciones/{{importacionId}}`;
   - tests para estado no final con contadores `null`;
   - tests para estado final y suma de los seis contadores.
4. Una secuencia de prueba para reenvío idempotente del mismo lote.
5. Una secuencia de prueba de concurrencia que verifique HTTP 409 y que el
   emisor no continúe enviando lotes después del rechazo.

La actualización debe hacerse sobre el archivo local:

```text
backend/storage/app/postman/integracion/PedidosWeb-Integracion.postman_collection.json
```

No publicar cambios en un workspace remoto de Postman sin autorización
explícita.

## 14. Criterios de aceptación

1. Existe la ruta `POST .../importaciones/medidas` y acepta el contrato común.
2. El ERP genera `importacionId`; PedidosWeb no lo reemplaza.
3. La recepción válida responde 202 sin esperar el procesamiento.
4. Un `GET` no final devuelve contadores y errores en `null`.
5. El resultado final expone los seis contadores y su suma coincide con
   `procesados`.
6. El snapshot elimina físicamente las medidas ausentes solo al cerrar un
   ciclo válido.
7. Un snapshot vacío se acepta y deja el universo de medidas vacío.
8. Un delta no elimina medidas ausentes.
9. El reenvío idéntico de un lote no duplica ni reaplica operaciones.
10. Un segundo ciclo concurrente para el mismo tenant y recurso recibe 409.
11. El endpoint de consulta no filtra ni expone datos de otro tenant.
12. La collection de Postman contiene los JSON de prueba, la consulta de
   resultado y las aserciones indicadas.
13. Las escrituras se ejecutan mediante stored procedures y quedan auditadas.

## 15. Explicación en lenguaje humano de decisiones operativas

### Retención del JSON

Se guarda cada JSON recibido aunque ocupe espacio. Así, si algo falla, se puede
ver exactamente qué envió el ERP, reintentar o investigar sin pedirle al
usuario que reproduzca el problema. El endpoint termina rápido porque guarda
el paquete y deja el trabajo pesado para después.

### Tamaño de errores

Al principio se devuelve el detalle completo porque facilita la operación:
el usuario puede saber qué medida falló y por qué. Si un ciclo muy grande
produce una respuesta incómodamente pesada, se cambia el mecanismo a
consulta paginada sin cambiar la recepción ni perder los errores guardados.

### Concurrencia

Se evita procesar dos ciclos del mismo recurso al mismo tiempo. Si un ciclo de
medidas está corriendo y llega otro, PedidosWeb responde 409. El ERP debe
frenar el resto de los lotes y registrar el incidente. Esto evita que dos
snapshots se pisen y terminen con un universo imprevisible.

## 16. Decisiones A1 cerradas

| ID | Tema | Decisión |
|---|---|---|
| AMB-A1-101-24-01 | Alcance | Este SPEC cubre el contrato común y `medidas`; las demás etapas quedan fuera del alcance funcional |
| AMB-A1-101-24-02 | Clave de upsert | `codMedida`/`cod_medida` es la clave de unicidad; `idMedida` es un dato recibido y actualizable |
| AMB-A1-101-24-03 | Ítem no clasificable | Si `codMedida` falta, está vacío o es inválido, el ítem queda `rechazado`, fuera de `procesados` y de los seis contadores |
| AMB-D1-101-24-01 | Medida ausente en snapshot | Se elimina físicamente al finalizar el snapshot; no existen referencias funcionales que lo impidan |
| AMB-D1-101-24-02 | Identificador técnico | El ERP envía `idMedida`; si cambia para un `codMedida` existente, el backend actualiza `id_medida` |

## 17. Preguntas pendientes

1. Definir el límite inicial de bytes del request y de la respuesta final.
2. Confirmar la retención operativa de payloads y errores.

## Actualización D1

Las decisiones D1 sobre referencias e identificador técnico quedan cerradas con
las reglas `AMB-D1-101-24-01` y `AMB-D1-101-24-02`. Se verificó en el diseñador
de la tabla que `id_medida` no es identity y el contrato existente del proyecto
ya transmite `idMedida`; por lo tanto, el endpoint lo recibe y el backend lo
persiste junto con `codMedida` y `descMedida`.

## Veredicto A1

**Apto con observaciones — autoriza Parte B.**

Las observaciones operativas restantes no bloquean la implementación si se
confirman en D1 sin modificar el alcance.

## 18. Definición de listo

- [ ] SPEC revisado y aprobado en A1.
- [ ] TR por etapa, incluyendo SP, OpenAPI, tests y Postman.
- [ ] Contrato de contadores alineado en documentación y collection.
- [ ] Etapa única aprobada con `medidas`.
