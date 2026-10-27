# APIs de importación — mejoras posibles para volumen masivo

| Campo | Valor |
|---|---|
| Producto | PedidosWeb |
| Alcance | APIs de importación de maestros y transacciones |
| Estado | **Standby — observar producción** |
| Fecha | 2026-10-22 |
| Documentos relacionados | [APIs-Importacion-datos-maestros.md](./APIs-Importacion-datos-maestros.md) · [APIs-Importacion-transacciones.md](./APIs-Importacion-transacciones.md) |

## 1. Objetivo

Registrar el análisis de robustez de las APIs de importación ante volúmenes elevados de información, especialmente:

- agotamiento del timeout HTTP/PHP/SQL;
- JSON demasiado extenso para los límites del servidor o proxy;
- consumo elevado de memoria;
- procesamiento incompleto o difícil de reanudar;
- concurrencia de múltiples lotes del mismo tenant.

Este documento es un registro de mejoras posibles. **No autoriza ni activa cambios en producción**. La decisión inicial es observar el comportamiento real en producción y ajustar el diseño con evidencia.

## 2. Situación implementada

Las APIs exponen un endpoint por recurso:

- `POST /api/v1/integracion/maestros/{recurso}`
- `POST /api/v1/integracion/transacciones/{recurso}`

El contrato actual usa lotes JSON con:

```json
{
  "modo": "delta",
  "fechaProceso": "2026-10-20T00:00:00-03:00",
  "origen": "tango",
  "items": []
}
```

La persistencia de negocio se realiza mediante stored procedures. Los recursos de volumen se cargan por lotes y el origen debe paginar la información.

## 3. Protecciones existentes

### 3.1 Límite de ítems

Maestros y transacciones tienen un máximo de **1500 ítems por request**:

- `backend/config/integracion_maestros.php`
- `backend/config/integracion_transacciones.php`

Si se supera el límite, el request se rechaza con HTTP `422` y una respuesta de lote demasiado grande.

Este límite acota la cantidad de registros, pero no el tamaño total del body en bytes.

### 3.2 Procesamiento por ítem

El backend recorre el array recibido y procesa cada registro mediante el stored procedure correspondiente. Los errores se informan por índice y clave natural.

La respuesta limita el detalle a **50 errores**, evitando que una gran cantidad de errores genere una respuesta excesivamente extensa.

### 3.3 Staging y snapshots

Los snapshots utilizan tablas técnicas de staging y un procedimiento `finalize` para aplicar el conjunto recibido. En transacciones, stock, deuda y cheques, el reemplazo final se ejecuta dentro de una transacción SQL Server.

El historial de ventas utiliza delta/upsert y ventanas de origen; no se pretende enviar el histórico completo en un único snapshot.

### 3.4 Control de frecuencia

Las rutas tienen un rate limit de **60 requests por minuto por token y tenant**.

Este control protege la frecuencia de llamadas, pero no limita el tiempo de ejecución ni el tamaño individual de un lote.

## 4. Riesgos identificados

### 4.1 No hay límite explícito de payload en bytes

El máximo de 1500 ítems no impide que el JSON sea demasiado grande. Esto puede suceder especialmente con:

- artículos con muchos campos;
- escalas con `valores[]` anidados;
- clientes con información extensa;
- historial de ventas con muchos campos opcionales;
- valores inesperadamente grandes enviados por el ERP.

El body puede ser rechazado antes de llegar a Laravel por límites de:

- `post_max_size` de PHP;
- `client_max_body_size` de Nginx;
- `maxAllowedContentLength` de IIS;
- proxy, gateway o balanceador;
- servidor web o plataforma de hosting.

En ese caso, Laravel no necesariamente puede devolver el envelope normal de la API.

### 4.2 El JSON completo se carga en memoria

El request se decodifica y valida como un array completo. Durante el procesamiento pueden mantenerse simultáneamente:

- el payload validado;
- el array `items`;
- las claves procesadas;
- los errores acumulados;
- estructuras internas del framework.

No existe streaming del JSON ni lectura incremental del body.

### 4.3 Procesamiento síncrono

Cada ítem ejecuta una llamada individual al stored procedure `apply`. Para un lote de 1500 ítems puede haber aproximadamente:

- hasta 1500 llamadas `apply`;
- validaciones y escrituras SQL por ítem;
- una llamada adicional de `finalize` para snapshots;
- escritura del log de integración.

El cliente queda esperando toda la operación y el endpoint debe completar antes del timeout configurado en la cadena HTTP/PHP/worker/SQL.

### 4.4 No existe actualmente un flujo asíncrono

No hay en el contrato vigente:

- respuesta `202 Accepted`;
- identificador de importación consultable;
- endpoint de progreso;
- procesamiento mediante queue worker;
- checkpoint por subbloque;
- reanudación desde el último ítem;
- retry coordinado por el servidor.

La configuración local además utiliza `QUEUE_CONNECTION=sync`, por lo que una cola no sería asíncrona mientras no se configure un backend y worker reales.

### 4.5 Timeouts no definidos por la funcionalidad

No se establecen específicamente en el módulo:

- timeout máximo del request;
- timeout de consulta SQL Server;
- timeout del worker;
- timeout de Nginx/IIS;
- timeout del proxy o gateway;
- política de cancelación o reintento.

El comportamiento depende de la configuración del entorno de despliegue.

### 4.6 Snapshots largos y concurrencia

Un snapshot grande puede mantener operaciones de staging y reemplazo durante un tiempo considerable. También debe observarse qué ocurre si llegan dos snapshots concurrentes del mismo recurso y tenant.

El modelo actual identifica cada lote mediante `batch_id`, pero no expone un control de concurrencia o una regla de “último snapshot válido” a nivel HTTP.

## 5. Mejoras posibles

Estas mejoras quedan registradas para evaluar después de observar producción.

### 5.1 Definir límite máximo en bytes

Agregar un límite contractual explícito, por ejemplo:

- máximo recomendado por request: entre **5 MB y 10 MB**;
- valor configurable por entorno;
- rechazo controlado con HTTP `413` o `422`;
- código de error específico;
- documentación del límite en OpenAPI y en el manual de integración.

La validación debe coordinarse con PHP, Nginx/IIS, proxy, gateway y plataforma de hosting. El límite de aplicación no alcanza si una capa anterior rechaza el body.

### 5.2 Ajustar el tamaño recomendado del lote

Mantener el máximo lógico de 1500 como límite duro y definir además un tamaño operativo recomendado, posiblemente entre **250 y 1000 ítems**, según las mediciones de producción.

El tamaño óptimo debe depender del recurso. No necesariamente conviene usar el mismo tamaño para:

- provincias o condiciones de venta;
- artículos;
- clientes;
- escalas;
- historial de ventas;
- stock.

### 5.3 Incorporar procesamiento asíncrono

Para cargas que superen el tiempo operativo aceptable:

1. El ERP envía el lote.
2. La API valida el envelope y registra una importación.
3. La API responde `202 Accepted` con un `importacionId`.
4. Un worker procesa el lote.
5. El ERP consulta el estado.
6. El resultado final contiene contadores y errores.

Estados posibles:

- `recibido`;
- `procesando`;
- `finalizado`;
- `finalizado_con_errores`;
- `abortado`;
- `cancelado`.

Esta alternativa requiere definir persistencia de importaciones, retención, seguridad, reintentos y observabilidad.

### 5.4 Procesamiento interno por subbloques

Aunque el cliente envíe un lote permitido, el servidor podría dividirlo internamente en bloques más pequeños para:

- reducir memoria;
- acotar transacciones;
- registrar progreso;
- limitar el tiempo de cada operación;
- facilitar retries.

En snapshots se debe preservar la atomicidad del conjunto completo. No se debe convertir un snapshot en una sucesión de reemplazos parciales sin una decisión explícita de negocio.

### 5.5 Idempotencia y reanudación

Incorporar, si la operación lo requiere:

- `Idempotency-Key`;
- identidad estable de lote;
- número de bloque;
- hash o checksum del bloque;
- resultado persistido por bloque;
- reenvío seguro del mismo bloque;
- expiración y limpieza de importaciones incompletas.

Esto evita duplicaciones y permite que el ERP reanude una importación sin reenviar innecesariamente todo el universo.

### 5.6 Timeouts coordinados

Documentar y probar valores compatibles para:

- PHP `max_execution_time`;
- timeout de conexión y consulta SQL Server;
- timeout del worker;
- timeout de Nginx o IIS;
- timeout del proxy/gateway;
- timeout del cliente ERP.

El timeout del cliente debe ser superior al timeout esperado del servidor, con margen para respuesta y red. En el flujo asíncrono, el cliente no debería mantener abierta la conexión durante todo el procesamiento.

### 5.7 Observabilidad y pruebas de carga

Agregar métricas por recurso y lote:

- tamaño del body en bytes;
- cantidad de ítems;
- duración total;
- duración de validación;
- duración de `apply`;
- duración de `finalize`;
- cantidad de errores;
- memoria utilizada;
- timeout o cancelación;
- concurrencia por tenant.

Realizar pruebas con datos representativos y registrar al menos:

- artículos;
- clientes;
- stock;
- deuda;
- cheques;
- historial de ventas;
- snapshots completos;
- múltiples lotes simultáneos.

## 6. Criterios para reabrir este documento

Se recomienda pasar este documento de `Standby` a planificación cuando ocurra alguno de estos eventos:

- HTTP `413`, `400` o body incompleto por tamaño;
- timeout del endpoint;
- consumo excesivo de memoria;
- procesamiento que exceda el tiempo operativo acordado;
- necesidad de reintentar manualmente un lote completo;
- duplicación o ambigüedad por reenvíos;
- snapshots concurrentes con resultado incorrecto;
- crecimiento de los lotes por encima del límite actual;
- pedido de integración con archivos o universos mayores.

## 7. Estado de decisión

**Decisión actual: standby.**

La implementación vigente se mantiene sin cambios mientras se observa producción. El origen debe respetar:

- máximo de 1500 ítems por request;
- paginación por recurso;
- ventanas para historial de ventas;
- orden de carga de maestros antes de transacciones;
- reintentos controlados desde el ERP.

Luego de obtener métricas reales, se decidirá si alcanza con:

1. ajustar tamaños de lote y límites de infraestructura;
2. agregar límite de bytes y observabilidad;
3. incorporar subbloques;
4. migrar determinados recursos a procesamiento asíncrono.

