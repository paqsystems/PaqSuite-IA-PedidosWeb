# Portal de Pedidos Web - Definición conceptual final para OpenSpec / Cursor

## 1. Propósito

El **Portal de Pedidos Web** es un sistema web multiempresa orientado a vendedores y clientes para cargar pedidos, cargar presupuestos, consultar información comercial proveniente del ERP y registrar un circuito mínimo de seguimiento comercial sobre presupuestos.

El sistema no debe ser tratado como un CRM completo. Debe entenderse como un **portal comercial conectado al ERP**, con funcionalidades de CRM mínimo para tratativas, cierre de presupuestos e indicadores básicos.

## 2. Alcance del MVP

El MVP debe incluir obligatoriamente:

1. Login, recuperación de contraseña y expiración por inactividad.
2. Resolución multiempresa por subdominio en web.
3. Carga de pedidos.
4. Carga de presupuestos.
5. Edición de pedidos ingresados.
6. Eliminación de pedidos ingresados.
7. Edición de presupuestos ingresados.
8. Eliminación de presupuestos ingresados.
9. Conversión presupuesto a pedido.
10. Conversión pedido a presupuesto.
11. Copia de comprobantes anteriores como base de uno nuevo.
12. Consultas de pedidos ingresados, pedidos pendientes y presupuestos ingresados.
13. Consultas de stock, deuda, cheques e historial de ventas.
14. Envío de mail al crear o modificar pedido/presupuesto.
15. Tratativas simples vinculadas a presupuestos.
16. Cierre de presupuestos con motivo.
17. Dashboard básico.
18. Logs de integración ERP.
19. Auditoría liviana de creación y última modificación.
20. Tests unitarios, integración y E2E.

## 3. Fuera del MVP inicial

Quedan fuera de la primera implementación, salvo preparación técnica para evolución posterior:

- CRM completo.
- App móvil nativa.
- Login Google/Microsoft.
- Doble factor.
- Bloqueo por intentos fallidos.
- Reglas complejas de backoffice administrativo.
- Parámetros generales administrables desde la web.
- PDF adjunto al mail.
- Notificación al descargar al ERP.
- Notificación al cerrar/cumplir pedido.
- Auditoría completa de historial de cambios.
- Carga masiva por Excel, aunque debe quedar prevista como mejora posterior.

## 4. Arquitectura objetivo

### 4.1 Monorepo

Estructura sugerida:

```text
/backend   Laravel API
/frontend  React + DevExtreme
/docs      documentación funcional, modelo de datos, OpenSpec, prompts y decisiones
```

### 4.2 Backend

Backend en Laravel API REST pura.

Capas obligatorias:

- Controllers
- Services
- Repositories
- DTOs
- Models Eloquent
- Policies
- Jobs
- Events
- Middleware

Regla central: **el controller no contiene lógica de negocio**. El controller valida entrada básica, construye DTOs, llama services y devuelve respuestas JSON estándar.

Los repositories solo acceden a datos. No calculan descuentos, estados, permisos funcionales ni totales.

Los services concentran reglas de negocio: grabación, edición, conversión, validación, cálculo, auditoría y coordinación transaccional.

### 4.3 Frontend

Frontend en React + DevExtreme.

Debe usar:

- Layout principal.
- Login.
- Menú lateral o navegación principal.
- Grillas configurables DevExtreme.
- Pantalla responsive, inicialmente orientada a web/desktop, dejando base para PWA o mobile futuro.

El sistema actual tiene una estética oscura y operativa tipo ERP. La nueva versión puede modernizar UX, pero debe conservar la lógica de trabajo intensivo con grillas, filtros, búsqueda, exportación y layouts configurables.

## 5. Multiempresa

### 5.1 Web

La URL tendrá formato:

```text
{empresa}.crm.paqsystems.com
```

La empresa determina la base de datos:

```text
pq_pedidosweb_{empresa}
```

Durante desarrollo puede forzarse empresa = `desarrollo`.

Si el subdominio no es válido o no existe la base correspondiente, se debe mostrar una pantalla de error clara.

### 5.2 Mobile futuro

Para acceso móvil futuro, la aplicación deberá solicitar empresa, recordando las empresas usadas para selección posterior.

## 6. Integración con ERP

El ERP inicial es Tango Gestión. El portal trabaja sobre una base SQL Server separada, sincronizada con datos provenientes del ERP.

### 6.1 ERP a Portal

El ERP alimenta:

- Clientes.
- Direcciones de entrega.
- Vendedores.
- Artículos.
- Condiciones de venta.
- Transportes.
- Listas de precios.
- Precios por artículo.
- Descuentos por cantidad.
- Stock.
- Deuda.
- Cheques.
- Resumen de cuenta.
- Historial de ventas.
- Parámetros generales.

La actualización será mixta: periódica por lotes, controlando novedades, con opción manual. Frecuencia deseada inicial: una vez al día configurable desde el circuito de tareas programadas del proyecto PaqSuite 2025IA ERP.

### 6.2 Portal a ERP

El portal genera pedidos/presupuestos en:

- `pq_pedidosweb_pedidoscabecera`
- `pq_pedidosweb_pedidosdetalle`

La descarga al ERP será realizada por un proceso del lado ERP definido en PaqSuite 2025IA ERP.

## 7. Seguridad y usuarios

### 7.1 Tipos de usuario funcionales

- Vendedor.
- Vendedor supervisor.
- Cliente.

No hay administrador funcional web en el MVP. Los parámetros generales se administran desde ERP o herramientas internas.

### 7.2 Asociación de login

Cada login debe estar asociado a un único cliente o a un único vendedor. No puede estar asociado a ambos ni a múltiples entidades.

### 7.3 Visibilidad

- Cliente: solo ve su propia información.
- Vendedor común: solo ve clientes asignados.
- Supervisor: ve todos los clientes.

### 7.4 Autenticación

Debe incluir:

- Login con usuario y contraseña.
- Recuperación de contraseña.
- Cambio de contraseña desde avatar o menú de usuario.
- Expiración de sesión por inactividad.

No incluir en MVP:

- Bloqueo por intentos fallidos.
- 2FA.
- Login social.

## 8. Menú inicial

Opciones mínimas:

1. Carga de pedidos/presupuestos.
2. Presupuestos ingresados.
3. Pedidos ingresados.
4. Pedidos pendientes.
5. Deuda de clientes.
6. Cheques en cartera.
7. Historial de ventas.
8. Stock.
9. Tratativas / seguimiento de presupuestos.
10. Dashboard.
11. Logs de integración.

## 9. Estados de comprobantes

Estados principales:

| Estado | Significado |
|---:|---|
| -1 | Pedido en proceso de modificación o descarga/control transitorio |
| 0 | Pedido ingresado en web, aún no descargado al ERP |
| 1 | Pedido pendiente en ERP, ya descargado |
| 2 | Pedido cerrado/cumplido en ERP |
| 99 | Presupuesto ingresado |

Al eliminar un pedido o presupuesto ingresado, se borra de cabecera y detalle, respetando permisos y controles de simultaneidad.

## 10. Carga de pedidos y presupuestos

La pantalla de carga debe ser única para pedido y presupuesto. Cambia el estado final y puede variar tonalidad/identificación visual.

### 10.1 Inicio del proceso

Casos:

- Nuevo pedido.
- Nuevo presupuesto.
- Edición de pedido ingresado.
- Edición de presupuesto ingresado.
- Conversión presupuesto a pedido.
- Conversión pedido a presupuesto.
- Copia de comprobante anterior.

### 10.2 Selección de cliente

- Cliente: no elige; se usa su propio cliente.
- Vendedor no supervisor: elige solo entre clientes asignados.
- Supervisor: elige entre todos los clientes.

Al elegir cliente se inicializan datos de cabecera.

### 10.3 Cabecera

Campos principales:

- Cliente.
- Nro Pedido visible / número operativo.
- Código interno GUID.
- Vendedor.
- Perfil.
- Condición de venta.
- Transporte.
- Dirección de entrega.
- Expreso.
- Dirección expreso.
- Nivel.
- Lista de precios.
- Moneda.
- Incluye IVA.
- Bonificación 1.
- Bonificación 2.
- Bonificación 3.
- Bonificación neta.
- Leyenda 1 a 5.
- Observaciones.
- Fecha de entrega opcional.

### 10.4 Inicialización de cabecera desde cliente

Al seleccionar cliente:

- Vendedor = vendedor del cliente.
- Condición de venta = condición del cliente.
- Transporte = transporte del cliente o parámetro por defecto.
- Dirección de entrega = dirección habitual del cliente.
- Expreso y dirección = datos del cliente.
- Nivel = nivel del cliente.
- Lista de precios = lista del cliente o parámetro por defecto.
- Moneda e incluye IVA = datos de lista de precios.
- Bonificación 1 = bonificación del cliente.
- Bonificaciones 2 y 3 = 0.
- Bonificación neta = cálculo acumulado de las tres bonificaciones, salvo permiso de carga manual.
- Leyendas = leyendas del cliente según parámetros ClienteLeyenda1..5.
- Observaciones = vacío.
- Perfil = parámetro CodPerfilPedidos.

### 10.5 Permisos de edición por atributo

La edición de cabecera y renglones depende de parámetros generales por tipo de usuario:

- C: cliente.
- V: vendedor común.
- S: supervisor.

Ejemplos:

- ModificaCondVtaC/V/S.
- ModificaTranspC/V/S.
- ModificaDirEntrC/V/S.
- ModificaExpresoC/V/S.
- ModificaNivelC/V/S.
- ModificaListaPrecV/S.
- ModificaBonCliV/S.
- ModificaBonArtV/S.
- ModificaPrecioV/S.

Los clientes no modifican precio, lista ni descuentos de artículo.

# 10.6 Listado de Parámetros definidos.


ArticulosPrecioCero : si admite cargar artículos con precios en cero
ArticulosSinPrecio : si admite cargar artículos sin precio en la lista

CargaRecurrente : si tras una carga de un pedido/presupuesto, vuelve a cargar un pedido o al listado
ClienteLeyenda1 : si inicializa la leyenda 1 con la leyenda 1 del cliente
ClienteLeyenda2 : si inicializa la leyenda 1 con la leyenda 1 del cliente
ClienteLeyenda3 : si inicializa la leyenda 1 con la leyenda 1 del cliente
ClienteLeyenda4 : si inicializa la leyenda 1 con la leyenda 1 del cliente
ClienteLeyenda5 : si inicializa la leyenda 1 con la leyenda 1 del cliente
ClientesInhabilitados : si procesa clientes inhabilitados o no
CodClasifArticulos : si limita la carga de artículos a sólo los de esa clasificación
CodPerfilPedidos : cuál es el perfil de pedidos por defecto
CodTransporte : código de transporte por defecto si el cliente no tiene transporte
DetallePorMail : Si en la impresión del mail muestra el detalle o no

DiasVentasDetalladas : Cuántos días anteriores de venta trae desde el ERP
FechaControl : Fecha-hora que se usa para controlar la edición de pedidos durante la bajada al ERP
ListaPrecios : Lista de precios por defecto cuando el cliente no tiene ninguna
Mail_DireccionRemitente : la dirección del remitente con que deben salir los mails.
mailCCO : mails que deben salir como copia oculta (puede ser más de uno)
MinutosAviso : (para uso ERP)
MinutosBloqueo : (para uso ERP)
MinutosWeb : (para uso ERP)
ModificaBonArtS : si el vendedor supervisor puede modificar el descuento del artículo
ModificaBonArtV : si el vendedor común puede modificar el descuento del artículo
ModificaBonCliS : : si el vendedor supervisor puede modificar el descuento del cliente
ModificaBonCliV  : si el vendedor común puede modificar el descuento del cliente.
ModificaCondVtaC : si el cliente puede modificar el la condición de venta
ModificaCondVtaS : si el vendedor supervisor puede modificar el la condición de venta
ModificaCondVtaV : si el vendedor puede modificar el la condición de venta
ModificaDirEntrC  : si el cliente puede modificar la dirección de entrega
ModificaDirEntrS : si el vendedor supervisor puede modificar la dirección de entrega
ModificaDirEntrV : si el vendedor puede modificar la dirección de entrega
ModificaExpresoC : si el cliente puede modificar el expreso y su dirección
ModificaExpresoS : si el vendedor supervisor puede modificar el expreso y su dirección
ModificaExpresoV : si el vendedor puede modificar el expreso y su dirección
ModificaListaPrecS : : si el vendedor supervisor puede modificar la lista de precios
ModificaListaPrecV : si el vendedor puede modificar la lista de precios
ModificaNivelC : : si el cliente puede modificar el nivel
ModificaNivelDesktop : (para uso en ERP)
ModificaNivelS : si el vendedor supervisor puede modificar el nivel
ModificaNivelV : si el vendedor puede modificar el nivel
ModificaPrecioS  : si el vendedor supervisor puede modificar precios
ModificaPrecioV : si el vendedor puede modificar precios
ModificaTranspC : si el cliente puede modificar el Transporte
ModificaTranspS : si el vendedor supervisor puede modificar el Transporte
ModificaTranspV : si el vendedor puede modificar el Transporte
NivelExtremo : sólo admite en ese campo los valores 0 y 100.
NOeliminaPedido : si se puede eliminar el pedido.
NOmodificaPedido : si se puede modificar el pedido.
RedistribucionManual : (para uso en ERP).
TalonarioFacturaA : (para uso en ERP)
TalonarioFacturaB : (para uso en ERP)
TalonarioFacturaE : (para uso en ERP)

## 11. Reglas de bonificaciones y precios

### 11.1 Bonificación de cabecera

Existen tres bonificaciones de cabecera:

- bonif_1
- bonif_2
- bonif_3

Se combinan en una bonificación neta:

```text
bonif_neta = (1 - (1 - bonif_1/100) * (1 - bonif_2/100) * (1 - bonif_3/100)) * 100
```

Por defecto se calcula automáticamente, pero puede ser ingresada manualmente si los parámetros lo habilitan.

Tango recibe una única bonificación equivalente.

### 11.2 Bonificación / descuento de renglón

Cada renglón tiene una bonificación/descuento propio.

Regla:

1. Si existe descuento por cantidad para el artículo y cantidad cargada, usar ese valor.
2. Si no existe, usar bonificación del maestro de artículos.
3. Si el usuario tiene permiso, puede modificar manualmente el descuento del renglón.

La bonificación de cabecera es complementaria a la bonificación de renglón.

### 11.3 Precio

El precio se toma de la lista de precios seleccionada. Si el usuario tiene permiso de modificación de precio, puede modificarlo manualmente. En caso de conflicto prevalece el precio manual vigente hasta que el usuario cambie artículo/lista o solicite recalcular.

Parámetros:

- ArticulosSinPrecio: permite cargar artículos sin precio en lista.
- ArticulosPrecioCero: permite cargar artículos con precio cero.

## 12. Renglones de pedido/presupuesto

### 12.1 Campos mínimos

- Renglón.
- Código de artículo.
- Descripción.
- Cantidad decimal.
- Precio.
- Descuento / bonificación de renglón.
- Importe lista.
- Precio neto.
- Importe neto.
- Importe total con IVA.
- IVA.
- Stock informativo.
- Stock base informativo cuando corresponda.

### 12.2 Reglas

- Debe existir al menos un artículo.
- No se permiten renglones de observación sin artículo.
- La cantidad debe ser mayor a cero.
- La cantidad admite decimales.
- El precio debe ser mayor o igual a cero.
- Las bonificaciones deben estar entre 0 y 100.
- Puede haber artículos repetidos en distintos renglones.
- Los cálculos deben actualizarse al editar cada renglón.
- Deben mostrarse totales en tiempo real, con y sin impuestos.

### 12.3 Regla de nivel

El nivel restringe compatibilidad de cantidades:

- Nivel 0 o 100: cualquier cantidad.
- Nivel 50: cantidades pares.
- Nivel 25 o 75: múltiplos de 4.
- Otros niveles: validar que `cantidad * nivel / 100` produzca una cantidad compatible según la regla de negocio vigente.

Si el parámetro NivelExtremo está activo, el nivel solo puede ser 0 o 100.

## 13. Stock

El stock es informativo. No bloquea la carga.

Se debe mostrar:

- Stock real.
- Stock comprometido.
- Stock disponible ERP = stock real - comprometido/pedidos pendientes ERP.
- Stock disponible neto = disponible ERP - pedidos web ingresados no descargados.
- Stock base para artículos con escala/presentación.

Para artículos con base, se muestra además la sumatoria de stock de artículos que comparten la misma base.

## 14. Acciones de grabación

Botones principales:

- Grabar pedido: guarda con estado 0.
- Grabar presupuesto: guarda con estado 99.
- Cancelar: solicita confirmación y descarta cambios.

Al grabar:

1. Validar datos obligatorios.
2. Validar al menos un renglón.
3. Calcular totales.
4. Generar o conservar GUID según corresponda.
5. Guardar cabecera y detalle en transacción.
6. Registrar fecha/usuario creación o modificación.
7. Mostrar confirmación con últimos caracteres del GUID y número visible.
8. Enviar mail una vez grabado el comprobante.
9. Según CargaRecurrente, volver a nueva carga o al listado correspondiente.

## 15. Conversión y cierre de presupuestos

### 15.1 Presupuesto a pedido

Al convertir un presupuesto a pedido:

- Si se mantienen todos los renglones y cantidades, el cierre es positivo total.
- Si se quitan renglones o se reducen cantidades, el cierre es parcialmente positivo y requiere clasificación.
- El nuevo comprobante queda como pedido estado 0.

### 15.2 Pedido a presupuesto

Debe permitirse mientras el pedido no haya sido descargado al ERP.

### 15.3 Eliminación de presupuesto

Al eliminar un presupuesto, debe solicitar motivo negativo de cierre/rechazo.

## 16. Tratativas de presupuestos

Solo aplican a presupuestos.

Campos mínimos sugeridos:

- id_tratativa.
- cod_pedido / cod_presupuesto.
- fecha_hora.
- usuario.
- comentario.
- resultado.
- proxima_fecha opcional.
- proxima_accion opcional.

Debe existir tabla de resultados de tratativas.

## 17. Consultas

Todas las consultas deben respetar visibilidad por usuario.

### 17.1 Pedidos ingresados

Estados 0 y eventualmente -1 cuando corresponda control operativo.

Debe mostrar:

- Cliente.
- Fecha.
- Número visible.
- Últimos caracteres del GUID.
- Total.
- Estado.
- Acciones: ver, editar, eliminar según permisos.

### 17.2 Presupuestos ingresados

Estado 99.

Debe permitir:

- Ver detalle.
- Editar.
- Eliminar con motivo de rechazo.
- Convertir a pedido.
- Registrar tratativas.

### 17.3 Pedidos pendientes

Estado 1.

Solo consulta, sin edición ni eliminación.

### 17.4 Deuda

Por cliente o todos según perfil.

Debe mostrar comprobantes con saldo, vencimiento, saldo y saldo acumulado.

Debe mostrar la fecha de ultima actualización (campo fecha_proceso del archivo), que es el mismo para todos los registros, por ende, fuera ir en la carátula del proceso.

### 17.5 Cheques

Por cliente o todos según perfil.

Debe mostrar cheques en cartera o aplicados con fecha posterior al día.

Debe mostrar la fecha de ultima actualización (campo fecha_proceso del archivo), que es el mismo para todos los registros, por ende, fuera ir en la carátula del proceso.

### 17.6 Historial de ventas

Debe mostrar ventas de un período determinado por parámetro DiasVentasDetalladas. El detalle debe abrir en modal.

Debe mostrar la fecha de ultima actualización (campo fecha_proceso del archivo), que es el mismo para todos los registros, por ende, fuera ir en la carátula del proceso.


### 17.7 Stock

Consulta no restringida por cliente. Busca por código o descripción, con opción “todos”.

Debe mostrar la fecha de ultima actualización (campo fecha_proceso del archivo), que es el mismo para todos los registros, por ende, fuera ir en la carátula del proceso.


### 17.8 UX de grillas

Las grillas deben permitir:

- Filtros básicos.
- Búsqueda.
- Ordenamiento.
- Layout configurable/guardado si ya está disponible por infraestructura común.
- Exportación Excel/PDF.
- Paginación.

No se requieren filtros avanzados combinables en MVP.

## 18. Mails

Se envía mail al crear o modificar pedido/presupuesto, una vez grabado correctamente.

No se envía mail al eliminar, al descargar al ERP ni al cerrar/cumplir pedido.

MVP:

- Texto simple.
- Sin PDF adjunto.
- Plantilla por empresa.
- Plantilla editable por empresa en etapa posterior; si no hay pantalla web, puede quedar en base/configuración.
- Con o sin detalle según DetallePorMail.

Destinatarios:

- Cliente.
- Vendedor asignado.
- Vendedor que cargó el comprobante si es distinto.
- Copias definidas en parámetros generales.

## 19. Dashboard básico

Primera versión:

- Tasa de cierre de presupuestos por vendedor.
- Ranking de motivos de rechazo.
- Artículos CORE sin movimiento o baja rotación.
- Pedidos por vendedor.
- Top clientes si el dato está disponible.

## 20. Logs de integración

Debe existir `pq_pedidosweb_logs_integracion` para registrar errores o eventos relevantes de integración con ERP.

Campos mínimos:

- id.
- fecha.
- tipo.
- mensaje.
- payload opcional.
- empresa.
- origen.
- severidad.

Debe existir endpoint de consulta para monitoreo.

## 21. Control de simultaneidad con descarga ERP

Para evitar edición/eliminación mientras se descarga al ERP:

1. El proceso ERP informa fecha/hora de inicio en parámetro de control.
2. Solo descarga registros estado 0.
3. Antes de editar/eliminar, el portal verifica que no haya descarga activa dentro del margen definido por MinutosBloqueo + MinutosAviso.
4. Si permite editar, puede marcar temporalmente el pedido en estado -1.
5. Al finalizar o cancelar la edición, vuelve al estado correcto.
6. Si la descarga ERP termina, limpia el parámetro de control.

Se recomienda agregar fecha/hora de inicio de modificación por pedido para robustez futura.

## 22. Auditoría liviana

Cada comprobante debe guardar:

- Fecha de creación.
- Usuario de creación.
- Fecha de última modificación.
- Usuario de última modificación.

No se requiere historial completo de cambios en MVP.

## 23. Criterios técnicos para Cursor

Cursor debe trabajar por etapas. No pedir “hacer todo el sistema”.

Orden recomendado:

1. Estructura base backend.
2. Modelos Eloquent.
3. Repositories.
4. Services.
5. Controllers.
6. Autenticación.
7. Consultas.
8. Logs de integración.
9. Frontend base.
10. Pantalla clave de pedidos.
11. Presupuestos y conversión.
12. Tratativas y motivos de cierre.
13. Dashboard.
14. Mails.
15. Tests.
16. Hardening multiempresa.

## 24. Preguntas pendientes mínimas antes de codificar

1. Confirmar si el número visible secuencial será por empresa y por tipo de comprobante o único para pedidos/presupuestos. : Unico para pedidos/presupuestos. por Empresa
2. Confirmar nombre definitivo de tablas nuevas para tratativas, resultados y motivos de cierre.  : A definir
3. Confirmar si `estado = -1` se usará para modificación, descarga o ambos; conviene separar con campo adicional si se desea mayor precisión. : se usa para indicar que se está modificando el pedido, que no sea descargado al ERP en ese momento.
4. Confirmar si el cálculo de IVA debe guardarse por renglón, cabecera o ambos. : Ambos
5. Confirmar si el mail saldrá por SMTP propio, AWS SES u otro proveedor. : del mismo modo que sale el mail de "olvidé la contraseña" en el Login

