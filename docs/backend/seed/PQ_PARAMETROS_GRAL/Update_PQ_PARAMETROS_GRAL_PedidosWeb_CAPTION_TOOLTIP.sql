/*
  Actualiza CAPTION y TOOLTIP — Programa PedidosWeb
  Fuente: PQ_PARAMETROS_GRAL.PedidosWeb.seed.json
  NO modifica tipo_valor ni columnas Valor_* (verificados en BD).

  Antes de ejecutar:
    USE [Ankas_del_sur];  -- o la Company DB correspondiente
*/

SET NOCOUNT ON;
BEGIN TRANSACTION;

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Admitir artículos con precio cero',
       [TOOLTIP] = N'Si está activo, el portal permite cargar renglones cuyo precio en la lista seleccionada es cero. Si está inactivo, el sistema rechaza o advierte según las validaciones de carga.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ArticulosPrecioCero';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ArticulosPrecioCero';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Admitir artículos sin precio en lista',
       [TOOLTIP] = N'Si está activo, permite cargar artículos que no tienen precio definido en la lista de precios vigente. Si está inactivo, la carga exige un precio válido en lista.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ArticulosSinPrecio';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ArticulosSinPrecio';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Carga recurrente post grabación',
       [TOOLTIP] = N'Define el comportamiento tras grabar un pedido o presupuesto: si está activo, el flujo vuelve a una nueva carga; si está inactivo, regresa al listado o pantalla anterior según la implementación del portal.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'CargaRecurrente';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'CargaRecurrente';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Inicializar leyenda 1 desde cliente',
       [TOOLTIP] = N'Si está activo, al seleccionar un cliente la leyenda 1 de la cabecera se completa con la leyenda 1 del maestro de clientes.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ClienteLeyenda1';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ClienteLeyenda1';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Inicializar leyenda 2 desde cliente',
       [TOOLTIP] = N'Si está activo, al seleccionar un cliente la leyenda 2 de la cabecera se completa con la leyenda 2 del maestro de clientes.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ClienteLeyenda2';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ClienteLeyenda2';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Inicializar leyenda 3 desde cliente',
       [TOOLTIP] = N'Si está activo, al seleccionar un cliente la leyenda 3 de la cabecera se completa con la leyenda 3 del maestro de clientes.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ClienteLeyenda3';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ClienteLeyenda3';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Inicializar leyenda 4 desde cliente',
       [TOOLTIP] = N'Si está activo, al seleccionar un cliente la leyenda 4 de la cabecera se completa con la leyenda 4 del maestro de clientes.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ClienteLeyenda4';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ClienteLeyenda4';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Inicializar leyenda 5 desde cliente',
       [TOOLTIP] = N'Si está activo, al seleccionar un cliente la leyenda 5 de la cabecera se completa con la leyenda 5 del maestro de clientes.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ClienteLeyenda5';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ClienteLeyenda5';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Procesar clientes inhabilitados',
       [TOOLTIP] = N'Si está activo, el portal permite operar con clientes marcados como inhabilitados en el ERP. Si está inactivo, esos clientes quedan excluidos de selección o carga.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ClientesInhabilitados';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ClientesInhabilitados';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Clasificación de artículos permitida',
       [TOOLTIP] = N'Código de clasificación que limita la búsqueda y carga de artículos solo a los pertenecientes a esa clasificación. Cero o vacío según convención ERP significa sin filtro adicional.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'CodClasifArticulos';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'CodClasifArticulos';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Motivo de cierre exitoso (presupuesto)',
       [TOOLTIP] = N'Identificador (`id_motivo`) del catálogo `pq_pedidosweb_motivos_cierre` con tipo positivo y activo. Se aplica automáticamente al convertir un presupuesto en pedido sin solicitar motivo en pantalla.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'CodMotivoCierreExitoso';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'CodMotivoCierreExitoso';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Perfil de pedidos por defecto',
       [TOOLTIP] = N'Código del perfil comercial que se asigna por defecto al inicializar la cabecera de un pedido o presupuesto cuando el cliente no aporta otro perfil.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'CodPerfilPedidos';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'CodPerfilPedidos';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Transporte por defecto',
       [TOOLTIP] = N'Código de transporte que se propone en cabecera cuando el cliente seleccionado no tiene transporte habitual definido en el maestro.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'CodTransporte';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'CodTransporte';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Incluir detalle de renglones en mail',
       [TOOLTIP] = N'Si está activo, el correo de notificación al grabar o modificar incluye la tabla de renglones además del bloque de cabecera. Si está inactivo, se envía solo la cabecera (sin tabla de artículos).'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'DetallePorMail';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'DetallePorMail';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Días para resumen de cuenta',
       [TOOLTIP] = N'Cantidad de días hacia atrás que se consideran al armar consultas o resúmenes de cuenta corriente del cliente en el portal.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'DiasResumenCuenta';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'DiasResumenCuenta';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Días de historial de ventas',
       [TOOLTIP] = N'Cantidad de días anteriores de ventas que el portal solicita o muestra al consultar el historial comercial del cliente (consulta Must del MVP).'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'DiasVentasDetalladas';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'DiasVentasDetalladas';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Fecha-hora de control ERP',
       [TOOLTIP] = N'Marca de fecha y hora de referencia para controlar qué pedidos en edición web pueden descargarse al ERP durante procesos de sincronización o bloqueo batch.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'FechaControl';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'FechaControl';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Lista de precios por defecto',
       [TOOLTIP] = N'Código de lista de precios que se propone en cabecera cuando el cliente no tiene lista asignada en su ficha comercial.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ListaPrecios';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ListaPrecios';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Dirección remitente de mails',
       [TOOLTIP] = N'Dirección de correo electrónico que aparece como remitente (`From`) en los envíos comerciales del portal (grabación/modificación de comprobantes). Debe ser una casilla válida autorizada en el servidor SMTP.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'Mail_DireccionRemitente';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'Mail_DireccionRemitente';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Destinatarios adicionales de mail',
       [TOOLTIP] = N'Lista de direcciones de correo que reciben copia de las notificaciones al grabar o modificar comprobantes, además de los destinatarios derivados del cliente/usuario. Separador canónico punto y coma (`;`); el runtime tolera coma.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'MailDestinatariosAdicionales';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'MailDestinatariosAdicionales';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Copia oculta global (CCO)',
       [TOOLTIP] = N'Direcciones de correo incluidas en copia oculta (BCC) en todos los envíos comerciales configurados del módulo. Puede contener varias direcciones separadas por punto y coma.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'mailCCO';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'mailCCO';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Minutos de aviso (ERP)',
       [TOOLTIP] = N'Margen en minutos usado por procesos ERP de descarga o sincronización para advertir antes de tomar un pedido web en edición. Consumo principal en el ERP, no en el portal MVP.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'MinutosAviso';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'MinutosAviso';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Minutos de bloqueo (ERP)',
       [TOOLTIP] = N'Ventana en minutos que el ERP utiliza para bloquear o reservar pedidos en edición web durante la bajada al sistema central. Consumo principal en el ERP.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'MinutosBloqueo';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'MinutosBloqueo';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Minutos de inactividad web',
       [TOOLTIP] = N'Minutos de inactividad permitidos antes de expirar la sesión del portal (GEN-02) y, para pedidos en estado -1, ventana de vigencia del bloqueo de edición medida desde `fechahora_ultima_actividad`.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'MinutosWeb';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'MinutosWeb';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Código de moneda (display)',
       [TOOLTIP] = N'Código ISO o interno de moneda usado en formatos de importe del portal (dashboard, mails, consultas) cuando no se obtiene de otra fuente transaccional.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'MonedaCodigo';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'MonedaCodigo';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Símbolo de moneda',
       [TOOLTIP] = N'Símbolo mostrado antes de los importes en pantallas y correos (por ejemplo `$`). Se antepone al valor numérico con el formato acordado de localización.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'MonedaSimbolo';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'MonedaSimbolo';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar descuento artículo',
       [TOOLTIP] = N'Si está activo, el perfil supervisor (S) puede modificar manualmente el descuento o bonificación de cada renglón en la pantalla de carga.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaBonArtS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaBonArtS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar descuento artículo',
       [TOOLTIP] = N'Si está activo, el vendedor común (V) puede modificar manualmente el descuento o bonificación de cada renglón. Los clientes (C) nunca tienen esta capacidad.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaBonArtV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaBonArtV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar bonificación cliente',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede editar las bonificaciones de cabecera originadas en el cliente (bonif_1, bonif_2, bonif_3 / bonif_neta según reglas del módulo).'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaBonCliS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaBonCliS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar bonificación cliente',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede editar las bonificaciones de cabecera del cliente. Los clientes (C) no modifican bonificaciones comerciales de cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaBonCliV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaBonCliV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Cliente: modificar condición de venta',
       [TOOLTIP] = N'Si está activo, el usuario con perfil cliente (C) puede cambiar la condición de venta en la cabecera del comprobante.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaCondVtaC';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaCondVtaC';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar condición de venta',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede cambiar la condición de venta en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaCondVtaS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaCondVtaS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar condición de venta',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede cambiar la condición de venta en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaCondVtaV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaCondVtaV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Cliente: modificar dirección de entrega',
       [TOOLTIP] = N'Si está activo, el cliente (C) puede elegir o cambiar la dirección de entrega en la cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaDirEntrC';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaDirEntrC';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar dirección de entrega',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede cambiar la dirección de entrega en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaDirEntrS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaDirEntrS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar dirección de entrega',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede cambiar la dirección de entrega en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaDirEntrV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaDirEntrV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Cliente: modificar expreso',
       [TOOLTIP] = N'Si está activo, el cliente (C) puede modificar el expreso y su dirección asociada en la cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaExpresoC';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaExpresoC';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar expreso',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede modificar el expreso y su dirección en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaExpresoS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaExpresoS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar expreso',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede modificar el expreso y su dirección en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaExpresoV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaExpresoV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar lista de precios',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede cambiar la lista de precios en cabecera. Los clientes (C) no pueden modificar lista ni precios.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaListaPrecS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaListaPrecS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar lista de precios',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede cambiar la lista de precios en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaListaPrecV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaListaPrecV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Cliente: modificar nivel',
       [TOOLTIP] = N'Si está activo, el cliente (C) puede modificar el nivel comercial en la cabecera del comprobante.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaNivelC';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaNivelC';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Modificar nivel (ERP desktop)',
       [TOOLTIP] = N'Parámetro reservado al cliente ERP de escritorio. No lo consume el portal web MVP; se mantiene por compatibilidad con la suite Tango/ERP.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaNivelDesktop';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaNivelDesktop';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar nivel',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede modificar el nivel comercial en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaNivelS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaNivelS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar nivel',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede modificar el nivel comercial en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaNivelV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaNivelV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar precio',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede modificar manualmente el precio unitario de los renglones.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaPrecioS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaPrecioS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar precio',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede modificar manualmente el precio unitario de los renglones. Los clientes (C) no pueden editar precios.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaPrecioV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaPrecioV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Cliente: modificar transporte',
       [TOOLTIP] = N'Si está activo, el cliente (C) puede cambiar el transporte en la cabecera del comprobante.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaTranspC';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaTranspC';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Supervisor: modificar transporte',
       [TOOLTIP] = N'Si está activo, el supervisor (S) puede cambiar el transporte en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaTranspS';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaTranspS';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Vendedor: modificar transporte',
       [TOOLTIP] = N'Si está activo, el vendedor (V) puede cambiar el transporte en cabecera.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'ModificaTranspV';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'ModificaTranspV';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Solo niveles 0 y 100',
       [TOOLTIP] = N'Si está activo, el campo nivel de cabecera solo admite los valores 0 y 100. Si está inactivo, se acepta el rango completo definido por el maestro de niveles.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'NivelExtremo';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'NivelExtremo';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Impide eliminar pedidos',
       [TOOLTIP] = N'Si está activo, bloquea la eliminación de pedidos desde el portal aunque el usuario tenga permiso de menú. Si está inactivo, rige la autorización normal del proceso.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'NOeliminaPedido';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'NOeliminaPedido';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Impide modificar pedidos',
       [TOOLTIP] = N'Si está activo, impide abrir pedidos en edición desde el portal. Útil en ventanas de cierre o sincronización con el ERP.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'NOmodificaPedido';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'NOmodificaPedido';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Redistribución manual (ERP)',
       [TOOLTIP] = N'Parámetro utilizado por procesos ERP de redistribución de pedidos. No lo consume el portal web MVP.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'RedistribucionManual';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'RedistribucionManual';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Talonario factura A (ERP)',
       [TOOLTIP] = N'Código o identificador del talonario de factura A usado por integraciones ERP al facturar pedidos originados en web. Consumo principal en ERP.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'TalonarioFacturaA';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'TalonarioFacturaA';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Talonario factura B (ERP)',
       [TOOLTIP] = N'Código o identificador del talonario de factura B para procesos ERP vinculados a pedidos web.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'TalonarioFacturaB';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'TalonarioFacturaB';

UPDATE dbo.PQ_PARAMETROS_GRAL
   SET [CAPTION] = N'Talonario factura E (ERP)',
       [TOOLTIP] = N'Código o identificador del talonario de factura E (exportación u otro régimen) para procesos ERP vinculados a pedidos web.'
 WHERE [Programa] = N'PedidosWeb'
   AND [Clave] = N'TalonarioFacturaE';
IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + N'TalonarioFacturaE';

-- Verificación rápida
SELECT [Clave], [tipo_valor], [CAPTION], LEFT(CAST([TOOLTIP] AS NVARCHAR(120)), 120) AS [TOOLTIP_preview]
  FROM dbo.PQ_PARAMETROS_GRAL
 WHERE [Programa] = N'PedidosWeb'
 ORDER BY [Clave];

COMMIT TRANSACTION;
PRINT N'Update CAPTION/TOOLTIP PedidosWeb OK';

