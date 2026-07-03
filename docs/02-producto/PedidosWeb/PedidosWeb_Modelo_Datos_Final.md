# Portal de Pedidos Web - Modelo de datos final consolidado

## 1. Criterio general

El sistema debe conservar los nombres existentes de tablas y campos porque la base funciona como capa de integración con Tango Gestión y con procesos ERP externos.

Convención principal:

```text
pq_pedidosweb_*
```

La base es SQL Server y el proyecto Laravel debe mapear los modelos Eloquent respetando:

- nombres exactos de tablas,
- claves primarias existentes,
- claves compuestas cuando existan,
- tipos decimales,
- fechas,
- ausencia o presencia real de timestamps,
- compatibilidad con procesos ERP.

## 2. Tablas transaccionales principales

### 2.1 pq_pedidosweb_pedidoscabecera

Tabla central para pedidos y presupuestos.

Clave primaria:

- cod_pedido

Campos existentes principales:

| Campo | Tipo funcional | Descripción |
|---|---|---|
| cod_pedido | string | GUID interno del comprobante |
| cod_cliente | string | Cliente del comprobante |
| fecha | datetime | Fecha de generación |
| nivel | int | Nivel aplicado |
| observaciones | text | Observaciones libres |
| incluye_iva | bit | Lista incluye IVA |
| moneda | bit | Moneda de lista |
| estado | int | Estado operativo |
| tal_pedido_tango | int | Talonario ERP/Tango |
| nro_pedido_tango | string | Número ERP/Tango |
| cod_usuario_web | string | Usuario web asociado |
| fecha_modif | datetime | Última modificación |
| total | decimal | Total del comprobante |
| total_iva | decimal | Total de IVA |
| leyenda_1..5 | string | Leyendas comerciales |
| descuento | decimal | Bonificación neta equivalente |
| bonif_1 | decimal | Bonificación cabecera 1 |
| bonif_2 | decimal | Bonificación cabecera 2 |
| bonif_3 | decimal | Bonificación cabecera 3 |
| cod_perfil | string | Perfil de pedido |
| cod_vended | string | Vendedor |
| cod_condvta | int | Condición de venta |
| id_de | int | Dirección de entrega |
| cod_transpor | string | Transporte |
| lista_precios | int | Lista de precios |
| expreso | string | Expreso |
| expreso_dire | string | Dirección expreso |
| fecha_entrega | datetime | Fecha de entrega opcional |

Estados:

| Estado | Significado |
|---:|---|
| -1 | Pedido en modificación web (evita descarga ERP) |
| 0 | Pedido ingresado web |
| 1 | Pedido pendiente ERP |
| 2 | Pedido cerrado/cumplido ERP |
| 98 | Presupuesto cerrado (conversión, cierre comercial o rechazo) |
| 99 | Presupuesto ingresado / activo |

Campos recomendados a agregar si no existen:

| Campo | Tipo sugerido | Motivo |
|---|---|---|
| nro_visible | bigint/int | Número operativo visible por empresa |
| usuario_creacion | varchar(50) | Auditoría liviana |
| fecha_creacion | datetime | Auditoría liviana |
| usuario_modificacion | varchar(50) | Auditoría liviana |
| fechahora_inicio_proceso | datetime null | Inicio de sesión de edición en **-1** (auditoría; no usar para vigencia del bloqueo) |
| fechahora_ultima_actividad | datetime null | Última interacción en edición **-1**; vigencia del bloqueo con `MinutosWeb` |
| origen_comprobante | varchar(50) null | Si proviene de copia/conversión |
| cod_pedido_origen | varchar(50) null | Trazabilidad pedido → presupuesto (conversión §15.2) |
| cod_presupuesto_origen | varchar(50) null | Trazabilidad presupuesto → pedido (conversión §15.1) |

### 2.2 pq_pedidosweb_pedidosdetalle

Detalle de renglones.

Clave primaria compuesta:

- cod_pedido
- renglon

Campos existentes:

| Campo | Tipo funcional | Descripción |
|---|---|---|
| cod_pedido | string | FK lógica a cabecera |
| renglon | int | Número de renglón |
| cod_articulo | string | Artículo |
| cantidad | decimal recomendado | Cantidad; debe corregirse si está como int |
| bonificacion | decimal | Descuento/bonificación de renglón |
| precio | decimal | Precio de venta |
| precio_neto | decimal | Precio luego de descuentos |
| precio_bruto | decimal | Precio antes de descuentos/IVA según regla |
| porc_iva | decimal | Porcentaje IVA |
| iva | decimal | Importe IVA |

Recomendaciones:

- `cantidad` debe ser decimal, no int.
- Conviene guardar importes calculados si el ERP o reportes lo requieren; si no, pueden recalcularse.
- Deben conservarse renglones repetidos de un mismo artículo.

Campos recomendados:

| Campo | Tipo sugerido | Motivo |
|---|---|---|
| descripcion_articulo | varchar(100) null | Congelar descripción al momento de venta |
| importe_lista | decimal(15,2) | Auditoría comercial |
| importe_neto | decimal(15,2) | Consulta rápida |
| importe_total | decimal(15,2) | Consulta rápida con IVA |
| descuento_origen | varchar(20) | cantidad/articulo/manual |
| precio_origen | varchar(20) | lista/manual |

## 3. Maestros comerciales

### 3.1 pq_pedidosweb_clientes

Clave primaria:

- cod_client

Campos:

| Campo | Descripción |
|---|---|
| cod_client | Código cliente |
| nombre | Razón social/nombre |
| fantasia | Nombre de fantasía |
| cod_vended | Vendedor asignado |
| lista_precios | Lista de precios habitual |
| cod_condvta | Condición de venta |
| cod_transpor | Transporte habitual |
| bonificacion | Bonificación cliente |
| nivel | Nivel por defecto |
| expreso | Expreso |
| expreso_dire | Dirección expreso |
| cod_login | Login asociado si el usuario es cliente |
| e_mail | Mail cliente |
| razon_soci | Razón social (consulta deuda y mails) |
| leyenda_1..5 | Leyendas por defecto |

Relaciones:

- Cliente 1:N direcciones de entrega.
- Cliente 1:N pedidos/presupuestos.
- Cliente N:1 vendedor.
- Cliente N:1 lista de precios.
- Cliente N:1 condición de venta.
- Cliente N:1 transporte.

### 3.2 pq_pedidosweb_clientesde

Direcciones de entrega.

Clave primaria compuesta:

- cod_client
- id_de

Campos:

| Campo | Descripción |
|---|---|
| cod_client | Cliente |
| id_de | Id dirección |
| cod_DE | Código dirección |
| direccion | Dirección |
| localidad | Localidad |
| c_postal | Código postal |
| cod_provin | Provincia |
| habitual | Indica dirección habitual |

### 3.3 pq_pedidosweb_vendedores

Campos detectados:

| Campo | Descripción |
|---|---|
| cod_vended | Código vendedor |
| nombre | Nombre |
| supervisor | Indica si ve todos los clientes |
| mail_supervisor | Mail del supervisor del vendedor (destinatario mail comercial si ≠ e_mail del vendedor) |
| e_mail | Mail del vendedor |
| cod_login | Login asociado |
| otros campos | Según script vigente |

Regla: un login de vendedor corresponde a un solo vendedor.

### 3.4 pq_pedidosweb_articulos

Clave primaria:

- codigo

Campos:

| Campo | Descripción |
|---|---|
| codigo | Código artículo |
| descripcion | Descripción (`varchar(60)`) |
| bonificacion | Bonificación por defecto |
| usa_esc | Usa escala |
| base | Código base para presentaciones |
| valor1 | Valor escala 1 |
| valor2 | Valor escala 2 |
| porc_iva | Porcentaje IVA |

Relación con escalas (§3.5–3.6):

- Si `usa_esc = true`, `valor1` y `valor2` referencian `pq_pedidosweb_escalas_detalle.cod_valor`.
- `base` sigue siendo el código de presentación para agregados de stock (consulta stock); no confundir con `cod_escala`.

Regla de stock base:

- Si base está vacío, se muestra stock propio.
- Si base tiene valor, se muestra stock propio y sumatoria de artículos con la misma base.

### 3.5 pq_pedidosweb_escalas_cabecera

Maestro de escalas comerciales (unidades / presentaciones configurables).

Clave primaria:

- cod_escala

Campos:

| Campo | Descripción |
|---|---|
| cod_escala | Código de escala (2 caracteres) |
| descrip_es | Descripción de la escala |
| nro_escala | Número ordinal de escala (ERP) |

Relaciones:

- Cabecera **1:N** `pq_pedidosweb_escalas_detalle` por `cod_escala`.

### 3.6 pq_pedidosweb_escalas_detalle

Valores permitidos por escala.

Clave primaria compuesta:

- cod_escala
- cod_valor

Campos:

| Campo | Descripción |
|---|---|
| cod_escala | FK lógica a cabecera |
| cod_valor | Código del valor dentro de la escala |
| desc_valor | Descripción corta del valor |

Relaciones:

- Detalle **N:1** cabecera por `cod_escala`.
- Referenciado desde `pq_pedidosweb_articulos.valor1` / `valor2` cuando `usa_esc = true`.

### 3.7 pq_pedidosweb_stock

Clave primaria:

- cod_articulo

Campos:

| Campo | Descripción |
|---|---|
| cod_articulo | Artículo |
| stock | Stock real |
| comprometido | Stock comprometido |
| uma_fecha | Fecha de actualización |

Cálculos (detalle en [consulta-stock.md](consulta-stock.md)):

- Disponible neto = `stock - comprometido - comprometido_web` (`comprometido_web` = suma detalle con cabecera `estado = 0`).
- Con `base` no vacío: agregados `stock_base`, `comprometido_base`, `comprometido_base_web`, `disponible_neto_base`.

### 3.8 pq_pedidosweb_listaprecios

Clave primaria:

- cod_lista

Campos:

| Campo | Descripción |
|---|---|
| cod_lista | Lista |
| incluye_iva | Indica si los precios incluyen IVA |
| moneda | Moneda |
| descripcion | Descripción |
| decimales | Cantidad de decimales |

### 3.9 pq_pedidosweb_listaprecios_articulos

Clave primaria compuesta:

- cod_lista
- cod_articulo

Campos:

| Campo | Descripción |
|---|---|
| cod_lista | Lista |
| cod_articulo | Artículo |
| precio | Precio |

### 3.10 pq_pedidosweb_descuentocantidad

Clave primaria compuesta:

- cod_articu
- cantidad

Campos:

| Campo | Descripción |
|---|---|
| cod_articu | Artículo |
| cantidad | Cantidad desde/aplicable |
| descuento | Descuento |

Regla: si hay descuento por cantidad, prevalece sobre bonificación del artículo, salvo edición manual permitida.

### 3.11 pq_pedidosweb_condventa

Clave primaria:

- codigo

Campos:

| Campo | Descripción |
|---|---|
| codigo | Código condición venta |
| descripcion | Descripción |

### 3.12 pq_pedidosweb_transportes

Clave primaria:

- codigo

Campos:

| Campo | Descripción |
|---|---|
| codigo | Código transporte |
| descripcion | Descripción |

### 3.13 pq_pedidosweb_perfil

Clave primaria:

- cod_perfil

Campos:

| Campo | Descripción |
|---|---|
| cod_perfil | Código perfil |
| descripcion | Descripción |

### 3.14 pq_pedidosweb_provincias

Clave primaria:

- cod_provin

Campos:

| Campo | Descripción |
|---|---|
| cod_provin | Provincia |
| nombre_pro | Nombre |

## 4. Tablas de consulta provenientes del ERP

### 4.1 pq_pedidosweb_cheques

Fuente de verdad de consulta: **[consulta-cheques.md](consulta-cheques.md)** (columnas ERP, join clientes, contrato API/UI).

Resumen — clave primaria compuesta: `interno`, `numero`.

Campos principales: `cod_cliente`, `Banco`, `fecha`, `Importe`, `Origen`, `Estado`, `fecha_proceso`. Nombre del cliente vía join a `pq_pedidosweb_clientes.nombre` (no `razon_soci`).

Compatibilidad legacy (`cod_client`, minúsculas): ver §4 de `consulta-cheques.md`.

### 4.2 pq_pedidosweb_deuda

Fuente de verdad de consulta: **[consulta-deuda.md](consulta-deuda.md)** (columnas ERP, join clientes, contrato API/UI).

Resumen — clave primaria compuesta: `cod_cliente`, `t_comp`, `n_comp`, `fecha_vto`.

Campos principales: `fecha_emis`, `fecha_vto`, `saldo`, `fecha_proceso`. Razón social vía join a `pq_pedidosweb_clientes.razon_soci`.

Compatibilidad legacy (`tipo_comprobante`, `nro_comprobante`, `fecha`): ver §4 de `consulta-deuda.md`.

### 4.3 pq_pedidosweb_resumencuenta

Tabla de resumen/cuenta corriente importada del ERP.

Clave detectada:

- id_gva12
- id_gva46

Debe usarse para consultas de cuenta corriente si corresponde.

### 4.4 pq_pedidosweb_ventadetallada

Fuente de verdad de consulta: **[consulta-historial-ventas.md](consulta-historial-ventas.md)** (columnas ERP, contrato API/UI).

Historial de ventas detallado importado del ERP. Clave primaria: `id_gva53` (no se expone en API/UI).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `cod_cli` | varchar(10) NOT NULL | Código cliente |
| `razon_soci` | varchar(60) NULL | Razón social (denormalizada en export ERP) |
| `n_remito` | varchar(14) NULL | Número remito |
| `t_comp` | varchar(3) NOT NULL | Tipo comprobante |
| `n_comp` | varchar(14) NOT NULL | Número comprobante |
| `fecha_emi` | datetime NULL | Fecha emisión |
| `cond_vta` | int NULL | Condición de venta |
| `porc_desc` | decimal(6,2) NULL | Porcentaje descuento |
| `cotiz` | decimal(15,2) NULL | Cotización |
| `moneda` | varchar(3) NULL | Moneda |
| `total_comp` | decimal(15,2) NULL | Total comprobante |
| `cod_transp` | varchar(10) NULL | Código transporte |
| `nom_transp` | varchar(60) NULL | Nombre transporte |
| `cod_articu` | varchar(15) NULL | Código artículo |
| `descripcio` | varchar(60) NULL | Descripción artículo |
| `cod_dep` | varchar(10) NULL | Depósito |
| `um` | varchar(10) NULL | Unidad de medida |
| `cantidad` | decimal(15,2) NULL | Cantidad |
| `precio` | decimal(15,2) NULL | Precio |
| `tot_s_imp` | decimal(15,2) NULL | Total sin impuestos |
| `n_comp_rem` | varchar(15) NULL | Número comprobante remito |
| `cant_rem` | decimal(15,2) NULL | Cantidad remito |
| `fecha_rem` | datetime NULL | Fecha remito |
| `fecha_proceso` | datetime NULL | Fecha actualización ERP (carátula) |
| `id_gva53` | int NOT NULL PK | Identificador ERP (interno) |

Filtro consulta: `fecha_emi >= today - DiasVentasDetalladas`.

## 5. Seguridad

El documento funcional vigente menciona seguridad equivalente al proyecto ERP, sin empresa dentro de la base de cada cliente.

Tablas conceptuales:

- Usuarios.
- Roles.
- Atributos/opciones de menú por rol.
- Permisos usuario-rol.

En el script aparece `pq_pedidosweb_login`. Debe revisarse contra el módulo común de seguridad de PaqSuite 2025IA ERP.

Regla: el login debe vincularse a cliente o vendedor, solo uno.

## 6. Parámetros generales

Los parámetros vienen desde ERP. Deben mapearse en una tabla común de parámetros o en la tabla ya definida por PaqSuite ERP.

Parámetros funcionales principales:

| Parámetro | Uso |
|---|---|
| ArticulosPrecioCero | Permite artículos con precio cero |
| ArticulosSinPrecio | Permite artículos sin precio en lista |
| CargaRecurrente | Define comportamiento post grabación |
| ClienteLeyenda1..5 | Inicializa leyendas desde cliente |
| ClientesInhabilitados | Permite cargar clientes inhabilitados |
| CodClasifArticulos | Filtro de artículos por clasificación |
| CodPerfilPedidos | Perfil por defecto |
| CodTransporte | Transporte por defecto |
| DetallePorMail | Incluye detalle en mail |
| DiasResumenCuenta | Días para resumen |
| DiasVentasDetalladas | Días historial ventas |
| FechaControl | Control de descarga ERP |
| ListaPrecios | Lista por defecto |
| Mail_DireccionRemitente | Remitente |
| MailDestinatariosAdicionales | Mails extra al grabar/modificar comprobante |
| mailCCO | Copias ocultas globales |
| MinutosAviso | Margen aviso descarga |
| MinutosBloqueo | Margen bloqueo descarga |
| MinutosWeb | Inactividad sesión web (GEN-02) y ventana de modificación pedido **-1** (`fechahora_ultima_actividad`) |
| NivelExtremo | Solo permite nivel 0/100 |
| NOeliminaPedido | Bloquea eliminación |
| NOmodificaPedido | Bloquea modificación |
| TalonarioFacturaA/B/E | Talonarios ERP |

Parámetros de permisos por tipo de usuario:

- ModificaBonArtS / V.
- ModificaBonCliS / V.
- ModificaCondVtaC / S / V.
- ModificaDirEntrC / S / V.
- ModificaExpresoC / S / V.
- ModificaListaPrecS / V.
- ModificaNivelC / S / V.
- ModificaPrecioS / V.
- ModificaTranspC / S / V.

## 7. Tablas nuevas recomendadas para MVP

### 7.1 pq_pedidosweb_tratativas

Para seguimiento simple de presupuestos.

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| id_tratativa | bigint identity PK | Identificador |
| cod_pedido | varchar(50) | Presupuesto asociado |
| fecha_hora | datetime | Fecha/hora |
| cod_usuario_web | varchar(50) | Usuario |
| comentario | nvarchar(max) | Comentario |
| id_resultado | int null | Resultado |
| proxima_fecha | datetime null | Próxima fecha |
| proxima_accion | nvarchar(255) null | Próxima acción |
| created_at | datetime | Creación |
| updated_at | datetime | Modificación |

### 7.2 pq_pedidosweb_tratativas_resultados

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| id_resultado | int identity PK | Identificador |
| descripcion | varchar(80) | Resultado |
| activo | bit | Vigente |

### 7.3 pq_pedidosweb_motivos_cierre

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| id_motivo | int identity PK | Identificador |
| tipo_cierre | varchar(20) | positivo/parcial/negativo |
| descripcion | varchar(100) | Motivo |
| activo | bit | Vigente |

### 7.4 pq_pedidosweb_presupuestos_cierres

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| id_cierre | bigint identity PK | Identificador |
| cod_presupuesto | varchar(50) | Presupuesto original |
| cod_pedido_generado | varchar(50) null | Pedido generado |
| tipo_cierre | varchar(20) | positivo/parcial/negativo |
| id_motivo | int null | Motivo |
| fecha_cierre | datetime | Fecha |
| cod_usuario_web | varchar(50) | Usuario |
| observacion | nvarchar(max) null | Observación |

Al registrar un cierre, la cabecera del presupuesto en `pq_pedidosweb_pedidoscabecera` pasa de **estado 99** a **estado 98**.

### 7.5 pq_pedidosweb_logs_integracion

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| id_log | bigint identity PK | Identificador |
| fecha | datetime | Fecha/hora |
| tipo | varchar(50) | Tipo de evento/error |
| severidad | varchar(20) | info/warning/error |
| origen | varchar(50) | ERP/web/job |
| mensaje | nvarchar(max) | Mensaje |
| payload | nvarchar(max) null | JSON opcional |
| procesado | bit | Si fue revisado |

### 7.6 pq_pedidosweb_articulos_core

Opcional para dashboard de artículos CORE si no se trae desde ERP.

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| cod_articulo | varchar(15) PK | Artículo core |
| activo | bit | Activo |
| observacion | varchar(255) null | Nota |

### 7.7 pq_asistente_ia_proveedores

Tabla transversal PaqSuite (antes `pq_pedidosweb_asistente_ia_proveedores`) para el catálogo funcional de proveedores LLM habilitables por el producto.

Objetivo:

- centralizar nombre visible, URLs de ayuda y capacidades declaradas;
- desacoplar la configuración del proveedor respecto de la credencial concreta del usuario;
- facilitar seeds iniciales con proveedores conocidos;
- permitir activar o desactivar proveedores sin tocar código.

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| id_proveedor | int identity PK | Identificador técnico |
| provider_id | varchar(50) unique | `providerId` lógico estable para frontend y backend |
| nombre_visible | varchar(80) | Nombre a mostrar en UI |
| tipo_integracion | varchar(50) | API pública, agregador, runtime local, nube administrada |
| soporta_byok | bit | Si admite credenciales aportadas por cliente o usuario |
| soporta_imagenes | bit | Capacidad declarada por defecto |
| requiere_base_url_editable | bit | Si el usuario debe informar endpoint propio |
| url_documentacion | varchar(255) null | Documentación principal |
| url_onboarding | varchar(255) null | `supportUrl` para onboarding y ayuda de configuración |
| activo | bit | Habilitación lógica |
| observacion | varchar(255) null | Nota funcional |

Seed inicial recomendado:

- `ollama`
- `openai`
- `anthropic`
- `googleGemini`
- `azureOpenAi`
- `openRouter`
- `groq`
- `mistral`

### 7.8 pq_asistente_ia_credenciales

Tabla transversal PaqSuite (antes `pq_pedidosweb_asistente_ia_credenciales`) para persistir, de forma separada de `users`, la configuración sensible del asistente IA por usuario.

Objetivo:

- aislar secretos y configuración sensible del perfil general del usuario;
- permitir rotación y revocación sin contaminar la tabla de usuarios;
- reducir exposición accidental en consultas, listados o exportaciones de usuarios;
- dejar base para integración `BYOK` con proveedores externos.

| Campo | Tipo sugerido | Descripción |
|---|---|---|
| id_credencial | bigint identity PK | Identificador técnico |
| user_id | bigint | Usuario del portal al que pertenece la configuración |
| provider_id | varchar(50) | `providerId` seleccionado por el usuario |
| base_url | varchar(255) | Endpoint base configurado |
| api_key_encrypted | nvarchar(max) | Credencial cifrada |
| model_id | varchar(120) | Modelo configurado |
| supports_vision | bit | Si la configuración admite imágenes |
| is_enabled | bit | Habilitación lógica |
| created_at | datetime | Alta |
| updated_at | datetime | Última modificación |

Reglas iniciales recomendadas:

- una configuración activa por usuario en la primera versión;
- referencia a un `providerId` existente en `pq_asistente_ia_proveedores`;
- la credencial no se guarda nunca en texto plano;
- el backend cifra antes de persistir;
- el backend descifra solo al momento de invocar al proveedor;
- la clave o mecanismo de descifrado debe quedar fuera de la base de datos;
- la credencial completa no debe exponerse en UI, logs, respuestas API ni mensajes de error.

## 8. Relaciones principales

```text
clientes 1:N clientesde
clientes 1:N pedidoscabecera
vendedores 1:N clientes
pedidoscabecera 1:N pedidosdetalle
articulos 1:1 stock
articulos N:M listas mediante listaprecios_articulos
listaprecios 1:N listaprecios_articulos
condventa 1:N clientes
condventa 1:N pedidoscabecera
transportes 1:N clientes
transportes 1:N pedidoscabecera
presupuestos/pedidos 1:N tratativas (solo presupuestos estado 99)
presupuestos estado 99 1:0/1 cierres → al cerrar pasa a estado 98
presupuestos estado 98 1:1 cierres (histórico)
motivos_cierre 1:N cierres
asistente_ia_proveedores 1:N asistente_ia_credenciales
users 1:0/1 asistente_ia_credenciales
```

## 9. Modelos Eloquent prioritarios

Crear modelos iniciales:

- PedidoCabecera.
- PedidoDetalle.
- Cliente.
- ClienteDireccionEntrega.
- Vendedor.
- Articulo.
- Stock.
- ListaPrecios.
- ListaPreciosArticulo.
- CondicionVenta.
- Transporte.
- Perfil.
- Cheque.
- Deuda.
- VentaDetallada.
- Tratativa.
- TratativaResultado.
- MotivoCierre.
- PresupuestoCierre.
- LogIntegracion.
- AsistenteIaProveedor.
- AsistenteIaCredencial.

Para claves compuestas, Laravel requiere tratamiento especial: definir repositories con queries explícitas o trait de clave compuesta.

## 10. Cambios de base recomendados antes de comenzar

1. Cambiar `pq_pedidosweb_pedidosdetalle.cantidad` a decimal si actualmente es int.
2. Agregar campos de auditoría liviana a cabecera si no existen.
3. Definir número visible secuencial por empresa y tipo de comprobante.
4. Crear tablas de tratativas, resultados, motivos de cierre y logs integración.
5. `estado = -1` solo modificación web; vigencia con `fechahora_ultima_actividad` + `MinutosWeb`.
6. Confirmar tabla definitiva de parámetros generales dentro del esquema PaqSuite.
7. Crear catálogo inicial de proveedores del asistente IA.
8. Crear tabla dedicada para credenciales `BYOK` del asistente IA, separada de `users`.
