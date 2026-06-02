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

Regla central: **el controller no contiene lógica de negocio**. El controller valida entrada básica, construye DTOs, llama services y devuelve respuestas JSON estándar según el envelope MONO (`error`, `respuesta`, `resultado`) — ver `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`.

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

## 5. Multiempresa (MONO)

Patrón estándar PaqSuite (mismo que el resto de productos MONO):

- **`docs/_base/resolucion-host-cliente-sql-mono.md`**
- **`docs/_mono/README-host-y-tenant.md`**
- Regla **`15-host-subdominio-base-datos-y-branding.md`**

**No** es MULTI ERP (`X-Company-Id`). Cada cliente final tiene su base; el slug **`{cliente}`** (en documentación funcional a veces «empresa») se resuelve antes del login.

### 5.1 Constantes PedidosWeb

| Concepto | Valor |
|---|---|
| `{proyecto}` | `pedidosweb` |
| Entrada | `https://{cliente}.pedidosweb.paqsystems.com` |
| Frontend | `https://frontend.pedidosweb.paqsystems.com` |
| Backend | `https://backend.pedidosweb.paqsystems.com` |
| Header API | `X-Paq-Cliente: {cliente}` |
| Base SQL por tenant | `pq_pedidosweb_{cliente}` |
| Desarrollo sin subdominio | `cliente = desarrollo` (middleware local + fila `EMPRESAS_CONEXION`) |
| `EMPRESAS_CONEXION` | `CODIGO_TENANT` = `{cliente}`, `proyecto` = `pedidosweb` |

No se modela `empresa_id` en tablas operativas dentro de cada base del tenant.

### 5.2 Mobile futuro

La app solicitará el slug del cliente, recordará tenants usados y enviará `X-Paq-Cliente` a `backend.pedidosweb.paqsystems.com`.

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

La visibilidad de ítems por perfil (cliente / vendedor / supervisor) se rige por permisos y roles (§7); el seed de `pq_menus` define qué procesos existen.

**Controles de presentación del menú (header, post-login):** además del icono hamburguesa (mostrar/ocultar panel lateral), el shell incluye expandir/contraer todas las ramas del árbol y alternar vista «todas las ramas» vs «solo opciones operativas» (procesos con ruta). Son preferencias de UI; no sustituyen permisos. Se persisten **por usuario o por terminal**, nunca por empresa ni como default global. Detalle: `docs/00-contexto/_mono/01-experiencia-base/menu-general.md`.

### 8.1 Valores por defecto de experiencia (MVP)

Fuente de verdad de producto para SPEC-001-01 y implementación:

| Parámetro | Valor MVP | Referencia técnica |
|-----------|-----------|-------------------|
| Idioma por defecto del producto | `es` | Si `users.locale` vacío: `navigator.language`; si no soportado → `es`. Detalle: `docs/00-contexto/_mono/01-experiencia-base/idioma-multilingual.md` |
| Tema por defecto (usuario sin preferencia) | `generic.light` | Preferencia por usuario en MONO (`apariencia-temas.md`); catálogo DevExtreme cerrado en contexto |

## 9. Estados de comprobantes

Estados principales:

| Estado | Significado |
|---:|---|
| -1 | Pedido **en modificación en el portal** (evita descarga al ERP mientras dure). Si hay interrupción (cierre de sesión, corte de red), la ventana de recuperación se calcula con **`fechahora_ultima_actividad` + `MinutosWeb`** (§21), no con la hora de inicio del proceso. |
| 0 | Pedido ingresado en web, aún no descargado al ERP |
| 1 | Pedido pendiente en ERP, ya descargado |
| 2 | Pedido cerrado/cumplido en ERP |
| 98 | Presupuesto **cerrado** (conversión a pedido, cierre comercial o rechazo; ver §15) |
| 99 | Presupuesto ingresado / **activo** |

Al **eliminar** un **pedido** ingresado (estado 0), se borra de cabecera y detalle, respetando permisos y controles de simultaneidad.

Al **cerrar o rechazar** un **presupuesto** (estado 99), el comprobante pasa a **estado 98** y se registra el cierre en `pq_pedidosweb_presupuestos_cierres` (motivo, tipo de cierre, pedido generado si aplica). **No** se elimina físicamente de cabecera/detalle.

## 10. Carga de pedidos y presupuestos

La pantalla de carga debe ser **única** para pedido y presupuesto: **mismo proceso transaccional**, misma cabecera/renglones; cambia el **estado final** según el botón elegido y puede variar tonalidad/identificación visual.

### 10.1 Pantalla única y botones de grabación

Botones visibles en la misma pantalla (además de **Cancelar**):

- **Grabar pedido** → persiste o genera comprobante en **estado 0**.
- **Grabar presupuesto** → persiste o genera comprobante en **estado 99**.

Combinaciones operativas soportadas en **una sola pantalla**:

| Situación de partida | Acción del usuario | Resultado |
|----------------------|-------------------|-----------|
| Alta nueva | Grabar pedido | Pedido **0** (nuevo código). |
| Alta nueva | Grabar presupuesto | Presupuesto **99** (nuevo código). |
| Pedido **0** en edición | Grabar pedido | Pedido **0** actualizado (mismo código). |
| Presupuesto **99** en edición | Grabar presupuesto | Presupuesto **99** actualizado (mismo código). |
| Pedido **0** en edición | Grabar presupuesto | Presupuesto **99** nuevo; pedido origen deja de ser operable como ingresado (§15.2). |
| Presupuesto **99** en edición | Grabar pedido | Pedido **0** nuevo; presupuesto origen pasa a **98** + cierre (§15.1). |

Las conversiones **no** requieren pantalla aparte: se disparan con el botón correspondiente desde la carga/edición.

### 10.2 Casos de entrada

Casos de entrada a la pantalla (todos comparten UI):

- Nuevo pedido / nuevo presupuesto.
- Edición de pedido ingresado (**0** / **-1** según §21).
- Edición de presupuesto ingresado (**99**).
- Apertura desde consulta, conversión implícita vía botones §10.1 o **copia** de comprobante anterior.

### 10.3 Selección de cliente

- Cliente: no elige; se usa su propio cliente.
- Vendedor no supervisor: elige solo entre clientes asignados.
- Supervisor: elige entre todos los clientes.

Al elegir cliente se inicializan datos de cabecera.

### 10.4 Cabecera

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

### 10.5 Inicialización de cabecera desde cliente

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

### 10.7 Permisos de edición por atributo

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
CodMotivoCierreExitoso : id_motivo (catálogo pq_pedidosweb_motivos_cierre, tipo positivo) aplicado al convertir presupuesto a pedido sin pedir motivo en pantalla
CodPerfilPedidos : cuál es el perfil de pedidos por defecto
CodTransporte : código de transporte por defecto si el cliente no tiene transporte
DetallePorMail : Si en la impresión del mail muestra el detalle o no

DiasVentasDetalladas : Cuántos días anteriores de venta trae desde el ERP
FechaControl : Fecha-hora que se usa para controlar la edición de pedidos durante la bajada al ERP
ListaPrecios : Lista de precios por defecto cuando el cliente no tiene ninguna
Mail_DireccionRemitente : la dirección del remitente con que deben salir los mails.
MailDestinatariosAdicionales : lista de mails adicionales al notificar grabación/modificación de comprobante (puede ser más de uno; separador `;` o `,`)
mailCCO : mails que deben salir como copia oculta en envíos del sistema (puede ser más de uno)
MinutosAviso : (para uso ERP)
MinutosBloqueo : (para uso ERP)
MinutosWeb : minutos de **inactividad admitidos** durante una modificación de pedido en estado **-1** (ventana desde `fechahora_ultima_actividad`; §21). El mismo parámetro alimenta la **expiración de sesión web** por inactividad (MONO / GEN-02).
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

- El **presupuesto original** pasa a **estado 98** (cerrado) con registro en `pq_pedidosweb_presupuestos_cierres`.
- El **`id_motivo`** del cierre exitoso se toma del parámetro **`CodMotivoCierreExitoso`** (catálogo `pq_pedidosweb_motivos_cierre`, tipo positivo); el portal no solicita motivo en este flujo (MVP: sin cierre parcial).
- El nuevo comprobante queda como pedido estado **0** con **`cod_presupuesto_origen`** en cabecera.
- En **`pq_pedidosweb_presupuestos_cierres`**: `cod_presupuesto` del origen y **`cod_pedido_generado`** del pedido nuevo (trazabilidad bidireccional; §15.4).

### 15.2 Pedido a presupuesto

Debe permitirse mientras el pedido no haya sido descargado al ERP.

### 15.3 Rechazo / cierre negativo de presupuesto

Al rechazar un presupuesto ingresado (estado 99), debe solicitar **motivo negativo** de cierre. El comprobante pasa a **estado 98** y se registra en `pq_pedidosweb_presupuestos_cierres`. No se elimina físicamente de cabecera y detalle.

### 15.4 Trazabilidad presupuesto ↔ pedido (MVP)

Al convertir presupuesto **99 → pedido 0** con presupuesto cerrado en **98**:

1. **`pq_pedidosweb_presupuestos_cierres`**: `cod_presupuesto`, `cod_pedido_generado`, motivo y tipo de cierre.
2. **Cabecera del pedido nuevo**: campo **`cod_presupuesto_origen`** (código del presupuesto cerrado).

**No** se requiere tabla de relación adicional en MVP: `presupuestos_cierres` es el registro formal del vínculo; la cabecera del pedido permite consulta directa.

## 16. Tratativas de presupuestos

Solo aplican a presupuestos en **estado 99** (activos).

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

### 17.2 Presupuestos ingresados (activos)

Estado **99** únicamente.

Debe permitir:

- Ver detalle.
- Editar.
- Cerrar/rechazar con motivo negativo (pasa a **estado 98**).
- Convertir a pedido (presupuesto pasa a **estado 98**).
- Registrar tratativas.

### 17.2.1 Presupuestos cerrados

Estado **98**.

Solo consulta (sin edición ni conversión). Debe permitir ver detalle y datos del cierre registrado en `pq_pedidosweb_presupuestos_cierres`.

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
- Con o sin detalle según parámetro 'DetallePorMail'.

Destinatarios (TO al grabar o modificar):

- **Cliente** (`clientes.e_mail`).
- **Vendedor del cliente** (`vendedores.e_mail` según `clientes.cod_vended`).
- **Supervisor** (`vendedores.mail_supervisor` del mismo vendedor), solo si es distinto del mail del vendedor del cliente.
- **Todos** los declarados en **`MailDestinatariosAdicionales`**.
- Sin duplicar la misma dirección entre fuentes.

`mailCCO` aplica como copia oculta global del canal, no sustituye la lista anterior.

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
4. Si permite editar, marca el pedido en estado **-1** y registra **`fechahora_inicio_proceso`** (auditoría de cuándo comenzó la sesión de edición).
5. En cada interacción relevante durante la edición (guardado parcial, cambio de renglón, heartbeat acordado en TR), actualizar **`fechahora_ultima_actividad`**.
6. **Ventana de bloqueo / recuperación:** mientras `fechahora_ultima_actividad + MinutosWeb >= fechahora_actual`, el pedido en **-1** se considera **en modificación activa** (otro usuario no debe tomarlo; puede excluirse de KPI — §19). Si la suma es **menor** que ahora, la modificación se considera **interrumpida/vencida** y otro usuario **puede** retomar la edición (HU-101-011).
7. Al confirmar grabación o **Cancelar**, volver a **0** (si sigue ingresado) y limpiar marcas de modificación según TR.
8. Si la descarga ERP termina, limpia el parámetro de control.

**No** usar `fechahora_inicio_proceso` para decidir si el bloqueo sigue vigente; solo **`fechahora_ultima_actividad`**.

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
2. Confirmar nombre definitivo de tablas nuevas para tratativas, resultados y motivos de cierre. : `pq_pedidosweb_tratativas`, `pq_pedidosweb_tratativas_resultados`, `pq_pedidosweb_motivos_cierre`, `pq_pedidosweb_presupuestos_cierres`
3. Confirmar si `estado = -1` se usará para modificación, descarga o ambos; conviene separar con campo adicional si se desea mayor precisión. : se usa para indicar que se está modificando el pedido, que no sea descargado al ERP en ese momento.
4. Confirmar si el cálculo de IVA debe guardarse por renglón, cabecera o ambos. : Ambos
5. Confirmar si el mail saldrá por SMTP propio, AWS SES u otro proveedor. : del mismo modo que sale el mail de "olvidé la contraseña" en el Login
6. Confirmar transición de presupuestos cerrados a **estado 98** (sin borrado físico) y registro en `pq_pedidosweb_presupuestos_cierres`. : **Confirmado** — ver §9 y §15.

