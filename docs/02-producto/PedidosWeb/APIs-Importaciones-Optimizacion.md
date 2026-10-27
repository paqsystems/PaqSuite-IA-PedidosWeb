# Optimización de APIs de importación

| Campo | Valor |
|---|---|
| Producto | PedidosWeb |
| Alcance | APIs de importación de maestros y transacciones |
| Estado | **Propuesta de diseño — pendiente de aprobación** |
| Fecha | 2026-10-24 |
| Documentos relacionados | [APIs-Importacion-datos-maestros.md](./APIs-Importacion-datos-maestros.md) · [APIs-Importacion-transacciones.md](./APIs-Importacion-transacciones.md) · [APIs-importacion-mejoras-posibles.md](./APIs-importacion-mejoras-posibles.md) |

## 1. Problemas a resolver

Las APIs actuales no resuelven correctamente estos escenarios:

1. **Bajas por ausencia:** una baja debe surgir del análisis del universo completo recibido, no de cada JSON parcial. Si el ERP divide un universo en varios requests, procesar cada request como snapshot puede eliminar registros que todavía llegarán en otro request.
2. **Timeouts por volumen:** el endpoint valida, aplica y finaliza el lote dentro de la misma solicitud HTTP. El cliente queda bloqueado hasta terminar el trabajo de base de datos.

El contrato actual admite:

- `POST /api/v1/integracion/maestros/{recurso}`;
- `POST /api/v1/integracion/transacciones/{recurso}`;
- hasta `1500` ítems por request;
- procesamiento síncrono;
- `resultado` con el resumen del lote ya procesado.

No se debe resolver el primer problema aumentando solamente el límite de ítems, ni el segundo aumentando indefinidamente los timeouts.

## 2. Decisión de diseño recomendada

Adoptar un **ciclo de importación explícito**, compuesto por uno o más lotes, con:

1. un `importacionId` o `token` común a todos los JSON del ciclo;
2. un indicador `ultimoEnvio` en cada lote;
3. persistencia de cada lote recibido en staging;
4. respuesta rápida `202 Accepted`, sin ejecutar el ABM completo dentro del request;
5. un único job de finalización cuando se recibe el lote marcado como último;
6. análisis de ausencias y aplicación de bajas solamente en la finalización;
7. endpoint genérico de consulta de estado y resultado.

La idea del prompt es válida, pero requiere una corrección importante: el proceso de background no debe invocarse “después de devolver” la respuesta. Debe despacharse antes de responder, usando una cola real y una transacción que garantice que el lote quedó persistido. Con `QUEUE_CONNECTION=sync` seguiría siendo síncrono y no resolvería el timeout.

## 3. Contrato propuesto

### 3.1 Request de importación

Se agregan al contrato común:

```json
{
  "importacionId": "IMP-20261024-000123",
  "modo": "snapshot",
  "ultimoEnvio": false,
  "numeroLote": 1,
  "fechaProceso": "2026-10-24T00:00:00-03:00",
  "origen": "tango",
  "items": []
}
```

Campos:

| Campo | Obligatorio | Regla |
|---|---:|---|
| `importacionId` | Sí | Identifica el ciclo completo. Debe ser único por tenant, recurso y ciclo. |
| `modo` | Sí | `delta` o `snapshot`. Las bajas por ausencia solamente aplican a `snapshot`. |
| `ultimoEnvio` | Sí | Booleano. `true` cierra la recepción del ciclo. |
| `numeroLote` | Recomendado | Secuencia para detectar duplicados, faltantes y reintentos. |
| `fechaProceso` | Sí | Fecha de referencia del ciclo, no la hora de llegada HTTP. |
| `origen` | No | Identifica el ERP o proceso emisor. |
| `items` | Sí | Lote acotado por el límite actual de cantidad y por un límite de bytes. |

El `importacionId` debe representar el ciclo, no cada JSON. Si se genera un token distinto por request, el backend no puede saber qué ausencias son reales y cuáles pertenecen a lotes aún no recibidos.

### 3.2 Respuesta de recepción

```json
{
  "error": 0,
  "respuesta": "integracion.importacion.recibida",
  "resultado": {
    "importacionId": "IMP-20261024-000123",
    "estado": "recibida",
    "recurso": "clientes",
    "modo": "snapshot",
    "numeroLote": 1,
    "ultimoEnvio": false
  }
}
```

HTTP recomendado: **202 Accepted**. El nodo `resultado` no debe ser `null`; contiene como mínimo el identificador y el estado de recepción. No devuelve todavía contadores finales.

### 3.3 Consulta genérica

Ruta propuesta:

```text
GET /api/v1/integracion/importaciones/{importacionId}
```

La identificación debe quedar limitada al tenant resuelto por `X-Paq-Cliente` y al permiso de integración correspondiente.

Respuesta durante el proceso:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "importacionId": "IMP-20261024-000123",
    "estado": "procesando",
    "recurso": "clientes",
    "modo": "snapshot",
    "recibidos": 12000,
    "procesados": 7800,
    "agregados": null,
    "modificados": null,
    "eliminados": null,
    "erroneos": 0,
    "errores": [],
    "lotesRecibidos": 8,
    "ultimoLoteRecibido": 8,
    "iniciadoEn": "2026-10-24T15:00:00-03:00",
    "finalizadoEn": null
  }
}
```

Respuesta final:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "importacionId": "IMP-20261024-000123",
    "estado": "finalizado_con_errores",
    "recurso": "clientes",
    "modo": "snapshot",
    "recibidos": 12000,
    "procesados": 11980,
    "agregados": 200,
    "modificados": 11700,
    "eliminados": 80,
    "erroneos": 20,
    "errores": [
      {
        "lote": 4,
        "indice": 18,
        "clave": "CL-404",
        "codigo": "integracion.maestro.itemInvalido",
        "detalle": "..."
      }
    ],
    "iniciadoEn": "2026-10-24T15:00:00-03:00",
    "finalizadoEn": "2026-10-24T15:04:12-03:00"
  }
}
```

## 4. Estados y reglas de ciclo

Estados mínimos:

- `recibida`: existe al menos un lote y aún no llegó `ultimoEnvio`;
- `pendiente_procesamiento`: llegó el último lote y el job está encolado;
- `procesando`: el worker inició el ABM;
- `finalizado`: terminó sin errores de ítems;
- `finalizado_con_errores`: terminó con errores parciales;
- `abortado`: falló una condición que impide aplicar el ciclo;
- `expirado`: nunca llegó el último lote dentro del plazo configurado.

Reglas:

1. Todos los lotes del ciclo deben usar el mismo tenant, recurso, modo y `fechaProceso`.
2. Un `importacionId` no puede reutilizarse luego de finalizar.
3. Reenviar el mismo `numeroLote` debe ser idempotente mediante hash del payload o identidad de lote.
4. Un lote posterior a `ultimoEnvio=true` se rechaza.
5. Debe detectarse un `numeroLote` faltante o duplicado antes de finalizar.
6. El último lote vacío puede ser válido para representar un universo completo vacío, pero requiere una confirmación explícita de negocio y protecciones adicionales. No debe habilitarse por defecto para evitar bajas masivas accidentales.
7. Debe existir una expiración para ciclos que quedaron abiertos y una forma de marcarlos como `expirado`; no se deben ejecutar bajas con un ciclo incompleto.

## 5. Semántica de contadores

La propuesta original indica que `agregados`, `modificados` y `eliminados` contarían tanto operaciones exitosas como erróneas. Esa definición es ambigua y puede producir reportes engañosos.

Se recomienda:

- `recibidos`: cantidad total de ítems persistidos en staging;
- `procesados`: cantidad de ítems evaluados por el ABM;
- `agregados`, `modificados`, `eliminados`: solamente operaciones efectivamente aplicadas;
- `erroneos`: cantidad de ítems rechazados o fallidos;
- `errores`: detalle acotado y/o referencia a un log consultable;
- opcionalmente `agregadosConError`, `modificadosConError` y `eliminadosConError` si el negocio necesita separar intentos de resultados.

Así, los contadores son aditivos y no confunden “se intentó borrar” con “se borró”.

## 6. Bajas por ausencia

La baja por ausencia solo puede ejecutarse cuando:

1. el modo es `snapshot`;
2. se recibió `ultimoEnvio=true`;
3. todos los lotes del ciclo están persistidos y válidos;
4. no existe un lote faltante ni inconsistencia de secuencia;
5. el análisis se realiza contra las claves recibidas en **todo** el ciclo;
6. se aplican las reglas de seguridad y referencias de cada recurso.

No se debe comparar cada JSON individual contra la tabla productiva. La comparación debe hacerse con staging completo o con una estructura equivalente de claves del ciclo.

Para recursos con referencias, la ausencia no necesariamente implica baja física. El resultado debe distinguir, según el contrato del recurso:

- eliminados;
- inhabilitados;
- omitidos por referencias;
- rechazados.

En historial de ventas y otros recursos delta no se debe inferir baja por ausencia.

## 7. Persistencia propuesta

Se requieren dos niveles de staging:

### 7.1 Cabecera de importación

Tabla técnica sugerida: `pq_pedidosweb_importaciones`.

Datos mínimos:

- identificador interno;
- `importacion_id`;
- tenant;
- recurso;
- modo;
- origen;
- fecha de proceso;
- estado;
- cantidad de lotes esperados/recibidos;
- número del último lote;
- hash o checksum;
- contadores;
- mensaje/resumen de error;
- timestamps de recepción, inicio y fin;
- fecha de expiración;
- versión de contrato.

### 7.2 Lotes y payload

Tabla técnica sugerida: `pq_pedidosweb_importaciones_lotes`.

Datos mínimos:

- cabecera;
- número de lote;
- `ultimo_envio`;
- cantidad de ítems;
- tamaño del payload;
- hash del payload;
- payload JSON o almacenamiento técnico equivalente;
- estado del lote;
- timestamps.

Para volúmenes altos, se debe comparar guardar JSON completo contra normalizar inmediatamente los ítems a tablas staging por recurso. Guardar el JSON simplifica auditoría y reintentos, pero aumenta almacenamiento y costo de parseo. La recomendación inicial es conservar el payload original y, si las mediciones lo justifican, agregar staging normalizado por recurso.

## 8. Procesamiento en background

Flujo recomendado:

1. Validar auth, tenant, recurso, envelope, límite de bytes y límite de ítems.
2. Crear o actualizar la cabecera y guardar el lote en una transacción.
3. Responder `202`.
4. Si `ultimoEnvio=true`, despachar un job idempotente de finalización.
5. El job adquiere un lock por tenant + recurso + ciclo.
6. Verifica integridad y secuencia de lotes.
7. Procesa staging mediante contratos SP.
8. Ejecuta el análisis de ausencias y las bajas únicamente en este punto.
9. Actualiza contadores, errores y estado final.
10. Libera el lock y registra métricas.

El worker debe utilizar una conexión de cola real (`database`, Redis u otra aprobada) y tener configuración de retry, timeout, backoff y `failed_jobs`. No alcanza con llamar `dispatch()` mientras el entorno conserva `QUEUE_CONNECTION=sync`.

## 9. Alternativas consideradas

### Alternativa A — Procesamiento asíncrono por ciclo completo (recomendada)

Es la propuesta de este documento.

**Ventajas**

- Resuelve simultáneamente bajas por ausencia y timeout.
- El ERP puede enviar múltiples JSON sin que cada request modifique el universo final.
- Permite progreso, reintentos y auditoría.
- Mantiene una única decisión de bajas por ciclo.

**Costos**

- Requiere tablas técnicas, worker, locks, expiración y endpoint de consulta.
- Cambia la integración de respuesta inmediata a eventual.
- Exige definir retención y operación de jobs.

### Alternativa B — Pre-registro y commit explícito

Agregar endpoints separados:

```text
POST /integracion/importaciones
POST /integracion/importaciones/{id}/lotes
POST /integracion/importaciones/{id}/finalizar
GET  /integracion/importaciones/{id}
```

El ERP declara el ciclo, envía lotes y llama explícitamente a `finalizar`.

**Ventajas**

- Evita depender de que el backend interprete `ultimoEnvio`.
- Hace más visible el protocolo y permite validar cantidad/hash esperado.
- Facilita reanudar un ciclo incompleto.

**Costos**

- Más llamadas y estados para el emisor.
- Si el ERP no llama `finalizar`, el ciclo queda pendiente.
- Conviene igualmente procesar en background después del commit.

Es una alternativa técnicamente más robusta para integraciones críticas. Puede incorporarse como evolución de la Alternativa A: `ultimoEnvio` sería un atajo compatible y `finalizar` la forma explícita.

### Alternativa C — Snapshot por ventana o archivo único

El ERP genera un archivo completo por recurso, lo sube, y el backend procesa el archivo asíncronamente.

**Ventajas**

- Reduce la cantidad de requests.
- Simplifica la noción de “universo completo”.
- Es adecuada para cargas realmente grandes.

**Costos**

- Requiere almacenamiento de archivos, checksum, expiración y seguridad de descarga.
- Cambia más el contrato actual.
- No resuelve por sí sola idempotencia, concurrencia ni observabilidad.

No se recomienda como primer cambio porque aumenta el alcance antes de medir si el protocolo por lotes alcanza.

### Alternativa D — Mantener síncrono y aumentar límites

Subir timeouts, memoria y límites de servidor, manteniendo el ABM dentro del request.

**Ventaja**

- Menor cambio de código.

**Desventajas**

- No resuelve las bajas incorrectas por múltiples JSON.
- Traslada el problema al proxy, PHP, SQL o cliente.
- Mantiene al ERP esperando y dificulta reintentos.
- No permite progreso ni recuperación fina.

No se recomienda como solución principal. Puede aplicarse solo como ajuste operativo temporal mientras se implementa A o B.

## 10. Recomendación final para aprobación

Implementar por fases:

### Fase 1 — Contrato y persistencia

- aprobar `importacionId`, `ultimoEnvio`, `numeroLote` y estados;
- agregar límite de payload en bytes;
- crear cabecera y lotes de staging;
- definir idempotencia y expiración;
- agregar endpoint genérico de estado;
- documentar OpenAPI y colección Postman.

### Fase 2 — Procesamiento asíncrono

- configurar una cola real;
- crear job de finalización;
- incorporar lock por tenant + recurso;
- mover el ABM y el análisis de ausencias al worker;
- mantener las operaciones de negocio detrás de stored procedures;
- persistir progreso y resultado.

### Fase 3 — Migración y endurecimiento

- adaptar el ERP emisor;
- habilitar el protocolo recurso por recurso;
- probar reintentos, duplicados, lotes faltantes, expiración y concurrencia;
- realizar pruebas de carga representativas;
- retirar o restringir el camino síncrono cuando todos los recursos estén migrados.

La recomendación es aprobar la **Alternativa A**, dejando preparada la compatibilidad con la **Alternativa B** mediante un endpoint explícito de finalización. No se recomienda implementar todavía el código ni convertir esta propuesta en regla base hasta cerrar la semántica de contadores, la política de universo vacío y el comportamiento ante ciclos incompletos.

## 11. Decisiones que requieren confirmación

1. ¿El identificador será generado por el ERP o por PedidosWeb?
2. ¿Se usará `ultimoEnvio` solamente, o también un endpoint explícito `finalizar`?
3. ¿Se permitirá snapshot vacío para ejecutar bajas masivas?
4. ¿Las bajas serán físicas, inhabilitación o una combinación según recurso?
5. ¿Se aceptará resultado eventual (`202`) para todos los recursos o habrá recursos síncronos pequeños?
6. ¿Qué retención tendrán payloads, errores y resultados?
7. ¿El emisor requiere consultar detalle de errores completo o alcanza un resumen con referencia al log?
8. ¿Qué backend de cola y qué supervisor estarán disponibles en cada ambiente?
9. ¿Cuál será el límite de bytes por request y el tamaño operativo recomendado?
10. ¿Qué concurrencia se permite por tenant y recurso?

## 12. Criterio para convertirlo en regla base

Este documento podrá convertirse en una regla transversal cuando exista una decisión aprobada sobre las preguntas anteriores y se haya cerrado, al menos, un flujo piloto con:

- contrato OpenAPI actualizado;
- SP y staging instalables;
- job real ejecutado fuera de la solicitud HTTP;
- endpoint de consulta probado;
- pruebas de idempotencia y reintento;
- prueba de bajas por ausencia con varios JSON;
- prueba de timeout y recuperación;
- colección Postman actualizada;
- evidencia de carga y concurrencia.
