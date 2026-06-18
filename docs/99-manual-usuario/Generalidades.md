# Generalidades

## 1. Introducción

Este manual describe el funcionamiento general de PedidosWeb desde la mirada de uso diario. Reúne en un solo documento la experiencia base del portal y las reglas visibles de acceso y seguridad.

Está pensado para dos públicos:

- usuarios finales que necesitan saber cómo ingresar, navegar y resolver acciones habituales;
- soporte funcional que necesita identificar el comportamiento esperado, interpretar bloqueos y orientar al usuario.

El contenido cubre el uso general del portal en su modalidad monoempresa: ingreso al sistema, sesión de trabajo e **inactividad**, navegación principal, idioma, apariencia, ayuda global, cambio de contraseña, recuperación de acceso, **grillas de listados** (consultas y procesos tabulares), **vista pivot** en informes analíticos (cuando el tenant la tiene habilitada) y **consulta de parámetros** (solo lectura).

## 2. Alcance

Este documento incluye:

- la pantalla de acceso;
- la estructura general del portal luego del login;
- el menú principal y el menú del avatar;
- la selección de idioma;
- la apariencia visual del sistema;
- el inicio y cierre de sesión;
- el cambio de contraseña;
- la recuperación de contraseña;
- los comportamientos esperados ante bloqueos de acceso;
- el uso estándar de **grillas y listados** (orden, filtros, columnas, agrupación, totalizadores, layouts, exportación y acciones por fila);
- la **vista pivot** en informes que la admiten (alternar con grilla, diseños guardados y exportación).

Este documento no incluye:

- la operatoria detallada de cada proceso de negocio;
- configuraciones avanzadas de seguridad;
- administración completa de usuarios, perfiles o permisos;
- métodos especiales de autenticación ajenos al flujo estándar del portal.

## 3. Conceptos clave

### Sesión

Es el estado de acceso del usuario dentro del portal. Mientras la sesión está activa, el usuario puede navegar por las opciones habilitadas para su perfil.

### Menú principal

Es el panel lateral que contiene los procesos a los que el usuario puede acceder.

### Menú del avatar

Es el menú personal ubicado en el extremo derecho del encabezado. Desde allí se accede a preferencias y acciones de sesión.

### Idioma

Es la preferencia que define en qué idioma se muestran títulos, botones, mensajes y textos generales del sistema.

### Apariencia

Es la preferencia visual que modifica el estilo general del portal para ese usuario.

### Recuperación de contraseña

Es el mecanismo que permite restablecer el acceso cuando el usuario no recuerda su contraseña.

### Primer ingreso

Es la situación en la que el sistema puede exigir al usuario cambiar su contraseña antes de continuar operando.

### Grilla o listado

Es la presentación tabular de datos dentro del área principal de un proceso. En PedidosWeb las grillas comparten la misma experiencia en todo el portal: mismos controles para ordenar, filtrar, personalizar columnas, agrupar, totalizar, exportar y —cuando el proceso lo permite— operar sobre cada fila.

### Layout de grilla

Es un formato guardado de una grilla (columnas visibles, orden, filtros, agrupaciones, totalizadores y demás preferencias de vista). Los layouts se identifican por proceso y por grilla; pueden compartirse entre usuarios, pero solo quien creó un layout puede modificarlo o eliminarlo. En el selector, los diseños **propios** del usuario se distinguen con el sufijo **` (*)`**.

### Vista pivot

Es una presentación analítica alternativa a la grilla: permite arrastrar campos a filas, columnas y valores para agrupar y totalizar datos. Algunos informes comerciales ofrecen un conmutador **Grilla / Pivot** cuando la funcionalidad está habilitada en el tenant.

### Inactividad de sesión

Es el cierre automático de la sesión tras un período sin interacción del usuario (duración configurable en parámetros ERP). Cada acción en el portal —navegar, escribir, pulsar botones o completar operaciones— renueva el contador.

## 4. Objetivo operativo

El objetivo de estas generalidades es asegurar que el usuario pueda:

- ingresar al sistema con sus credenciales;
- reconocer fácilmente la estructura de trabajo;
- acceder solo a las opciones que le corresponden;
- personalizar idioma y apariencia;
- mantener su cuenta segura;
- recuperar el acceso cuando olvida su contraseña;
- cerrar sesión correctamente al finalizar su trabajo;
- consultar y trabajar con listados tabulares usando una grilla homogénea en todos los procesos;
- analizar informes con vista pivot cuando el tenant la tenga activa.

El resultado esperado es una experiencia ordenada, segura y estable, con criterios de uso consistentes para todas las pantallas del portal.

## 5. Cuándo se utiliza

Este manual se utiliza:

- al comenzar a trabajar con el portal;
- cuando un usuario necesita entender cómo está organizada la pantalla;
- cuando soporte debe asistir problemas de acceso;
- cuando se requiere cambiar idioma o apariencia;
- cuando el usuario necesita cambiar su contraseña;
- cuando se perdió el acceso y debe iniciarse la recuperación;
- cuando un proceso muestra datos en forma de **grilla o listado** y el usuario necesita ordenar, filtrar, personalizar la vista o exportar;
- cuando se necesita consultar la **configuración de parámetros** del módulo (menú General, sección 18);
- cuando un informe ofrece **vista pivot** y el usuario debe alternar entre grilla y análisis dinámico (sección 19).

Como condición previa, el usuario debe contar con una cuenta habilitada para operar en el portal.

## 6. Cómo funciona

El uso general del sistema se organiza en dos momentos:

### Antes del ingreso

El usuario visualiza la pantalla de acceso con:

- identidad visual del portal;
- formulario de inicio de sesión;
- selector de idioma;
- acceso a recuperación de contraseña, si corresponde.

En esta instancia todavía no aparece el menú lateral de procesos.

### Después del ingreso

Una vez iniciada la sesión, el sistema muestra un entorno de trabajo estable compuesto por:

- encabezado superior;
- menú lateral de navegación;
- área principal de trabajo;
- pie de página.

El usuario accede directamente al entorno principal. No debe atravesar una selección adicional de empresa.

El contenido visible del menú puede variar según el perfil y la habilitación del usuario. Por ese motivo, no todos los usuarios ven las mismas opciones.

Dentro del área principal, muchos procesos muestran una **grilla**: una tabla con filas de datos y herramientas comunes para consultar, ordenar, filtrar y —según permisos— ejecutar acciones sobre cada registro. El detalle de uso de la grilla se describe en la sección 16.

## 7. Paso a paso de uso

### Ingreso al sistema

1. Abrir la pantalla de acceso.
2. Seleccionar el idioma deseado, si se quiere operar en un idioma distinto al predeterminado.
3. Ingresar las credenciales solicitadas.
4. Confirmar el acceso.
5. Si las credenciales y habilitaciones son correctas, el sistema abre el entorno principal.

### Navegación general

1. Revisar el encabezado superior para ubicar controles globales.
2. Usar el menú lateral para ingresar al proceso deseado.
3. Trabajar dentro del área principal.
4. Cambiar de proceso sin perder el marco general de navegación.

### Uso de los controles del menú principal

1. Utilizar el control para mostrar u ocultar el menú lateral cuando se necesita más espacio de trabajo.
2. Utilizar el control para expandir o contraer ramas cuando el menú tiene varias opciones agrupadas.
3. Utilizar el control de vista para alternar entre una vista completa y una centrada en opciones operativas.

### Cambio de idioma

1. Ubicar el selector de idioma en login o en el encabezado principal.
2. Elegir el idioma deseado.
3. Verificar que los textos de la interfaz se actualicen inmediatamente.

### Cambio de apariencia

1. Abrir el menú del avatar.
2. Ingresar a la opción de apariencia.
3. Seleccionar la apariencia deseada.
4. Confirmar el cambio si la pantalla lo solicita.
5. Verificar que el nuevo estilo se aplique a toda la interfaz.

### Cambio de contraseña

1. Abrir el menú del avatar.
2. Ingresar a la opción de cambio de contraseña.
3. Completar la contraseña actual.
4. Ingresar la nueva contraseña.
5. Repetir la nueva contraseña en el campo de confirmación.
6. Confirmar la operación.

### Recuperación de contraseña

1. En la pantalla de acceso, seleccionar la opción de recuperación.
2. Ingresar el correo asociado a la cuenta.
3. Confirmar la solicitud.
4. Revisar el correo recibido.
5. Abrir el enlace de restablecimiento.
6. Ingresar la nueva contraseña y su confirmación.
7. Confirmar el cambio y volver a iniciar sesión.

### Cierre de sesión

1. Abrir el menú del avatar.
2. Seleccionar la opción de cierre de sesión.
3. Verificar el retorno a la pantalla de acceso.

## 8. Campos, opciones y datos relevantes

### En la pantalla de acceso

- **Usuario o credencial de acceso:** identifica a la persona que intenta ingresar.
- **Contraseña:** valida la identidad del usuario.
- **Selector de idioma:** permite definir el idioma visible antes de ingresar.
- **Opción de recuperación:** inicia el proceso de restablecimiento de contraseña.

### En el encabezado superior

- **Controles del menú lateral:** modifican la presentación del menú.
- **Selector de idioma:** permite cambiar el idioma durante la sesión.
- **Avatar:** concentra acciones personales y de seguridad.

### En el menú del avatar

- **Perfil:** acceso a información personal, si el producto lo habilita.
- **Apariencia:** cambia el estilo visual del portal.
- **Apertura en nueva pestaña:** ajusta cómo se abren los procesos, si la opción está disponible.
- **Asistente IA:** abre la ayuda global en una nueva pestaña o ventana.
- **Cambiar contraseña:** permite actualizar la clave.
- **Cerrar sesión:** finaliza el acceso actual.

### En el cambio de contraseña

- **Contraseña actual:** valida que el cambio sea realizado por el usuario correcto.
- **Nueva contraseña:** define la nueva clave.
- **Confirmación de la nueva contraseña:** evita errores de tipeo.

### En la recuperación de contraseña

- **Correo de recuperación:** debe corresponder a una cuenta válida del usuario.
- **Nueva contraseña:** reemplaza la contraseña anterior.
- **Confirmación de la nueva contraseña:** valida que ambas coincidan.

## 9. Validaciones

### Validaciones de acceso

- El usuario debe ingresar credenciales válidas.
- El usuario debe tener acceso habilitado para operar en el portal.
- El usuario debe contar con el perfil funcional necesario para acceder a procesos del sistema.
- Si la cuenta requiere cambio obligatorio de contraseña, el sistema debe dirigir al usuario a ese paso antes de permitir la operatoria normal.

Qué debe revisar el usuario:

- que el usuario y la contraseña sean correctos;
- que no haya errores de tipeo;
- que esté utilizando la cuenta correcta.

Qué debe revisar soporte:

- que la cuenta esté habilitada;
- que el usuario tenga acceso autorizado;
- que no exista una restricción pendiente de primer ingreso o configuración incompleta.

### Validaciones del menú y navegación

- El sistema solo debe mostrar opciones permitidas para el perfil del usuario.
- Los cambios de vista del menú no deben habilitar opciones no autorizadas.
- La navegación debe mantenerse estable al cambiar de proceso.

Qué debe revisar soporte:

- si el usuario espera ver una opción que no aparece;
- si el perfil asignado corresponde con la operatoria solicitada;
- si la consulta es un problema de permisos o una expectativa funcional equivocada.

### Validaciones del idioma

- El idioma elegido debe aplicarse inmediatamente en la interfaz.
- Si el sistema no puede usar el idioma solicitado, debe utilizar el idioma por defecto.

Qué debe revisar soporte:

- si el cambio se realizó desde el selector correcto;
- si la sesión estaba activa al momento de guardar la preferencia;
- si el comportamiento observado corresponde al idioma activo o a un contenido no traducible.

### Validaciones de apariencia

- La apariencia elegida debe impactar en toda la interfaz.
- Si no existe una preferencia guardada o la preferencia no es válida, el sistema debe usar la apariencia por defecto.

Qué debe revisar soporte:

- si el cambio se confirmó correctamente;
- si la apariencia quedó aplicada solo parcialmente o si el usuario espera un comportamiento distinto del previsto.

### Validaciones del cambio de contraseña

- La contraseña actual debe ser correcta.
- La nueva contraseña debe cumplir las reglas de aceptación vigentes del sistema.
- La confirmación debe coincidir con la nueva contraseña.
- Si la sesión se interrumpe durante el proceso, el usuario debe volver a ingresar.

Qué debe revisar el usuario:

- que haya ingresado correctamente la contraseña actual;
- que la nueva contraseña y la confirmación sean iguales.

Qué debe revisar soporte:

- si el usuario estaba en un flujo de cambio obligatorio;
- si el rechazo responde a datos mal ingresados o a una restricción de seguridad.

### Validaciones de recuperación de contraseña

- La solicitud debe aceptar el correo informado sin revelar si existe o no una cuenta asociada.
- El enlace de recuperación debe ser válido durante su período de uso.
- Un enlace vencido o ya utilizado no debe permitir restablecer la contraseña.
- La nueva contraseña y su confirmación deben coincidir.

Qué debe revisar el usuario:

- si ingresó correctamente su correo;
- si abrió el último correo recibido;
- si el enlace todavía está vigente.

Qué debe revisar soporte:

- si el correo esperado es el correcto para la cuenta;
- si el usuario utilizó un enlace viejo;
- si la consulta corresponde a recepción de correo, expiración del enlace o error en la nueva contraseña.

## 10. Mensajes de error y advertencia

Este manual no fija textos literales obligatorios para todos los mensajes, pero sí define su interpretación funcional.

### Acceso rechazado por credenciales incorrectas

Causa probable:

- usuario o contraseña incorrectos.

Interpretación funcional:

- el sistema no pudo validar la identidad del usuario.

Acción recomendada para el usuario:

- volver a ingresar los datos con cuidado;
- revisar mayúsculas, minúsculas y errores de tipeo;
- usar la recuperación de contraseña si no recuerda la clave.

Control sugerido para soporte:

- confirmar que el usuario esté intentando ingresar con la cuenta correcta.

### Acceso rechazado por falta de habilitación

Causa probable:

- la cuenta no tiene permisos suficientes o no está lista para operar.

Interpretación funcional:

- el usuario fue identificado, pero no cumple las condiciones necesarias para ingresar al portal o utilizar determinadas opciones.

Acción recomendada para el usuario:

- contactar a soporte o a la administración funcional.

Control sugerido para soporte:

- revisar la habilitación del usuario y su perfil operativo.

### Bloqueo por cambio obligatorio de contraseña

Causa probable:

- el usuario debe actualizar su contraseña antes de continuar.

Interpretación funcional:

- el sistema exige reforzar la seguridad de la cuenta antes de habilitar el uso normal.

Acción recomendada para el usuario:

- completar el cambio de contraseña y luego continuar.

Control sugerido para soporte:

- verificar si la cuenta está marcada para primer ingreso o cambio obligatorio.

### Error al cambiar contraseña

Causa probable:

- contraseña actual incorrecta;
- nueva contraseña no aceptada;
- confirmación distinta;
- sesión interrumpida durante el proceso.

Interpretación funcional:

- el sistema rechazó el cambio por inconsistencia o por falta de validación.

Acción recomendada para el usuario:

- revisar cada dato ingresado;
- volver a iniciar sesión si fue redirigido al acceso.

Control sugerido para soporte:

- identificar si el error es de ingreso de datos o de vigencia de la sesión.

### Recuperación de contraseña sin correo recibido

Causa probable:

- demora en la recepción;
- dirección de correo distinta de la esperada;
- consulta realizada sobre una cuenta que no corresponde.

Interpretación funcional:

- la solicitud fue tomada, pero el usuario no dispone todavía del mensaje de recuperación.

Acción recomendada para el usuario:

- revisar nuevamente la casilla;
- confirmar el correo que usa la cuenta;
- repetir la operación solo si corresponde.

Control sugerido para soporte:

- validar el correo asociado a la cuenta consultada.

### Enlace de recuperación inválido o vencido

Causa probable:

- el enlace ya fue usado;
- el enlace dejó de estar vigente;
- el usuario abrió un correo anterior.

Interpretación funcional:

- el sistema no permite reutilizar o extender un enlace de recuperación.

Acción recomendada para el usuario:

- generar una nueva solicitud de recuperación.

Control sugerido para soporte:

- confirmar que el usuario esté usando el último correo recibido.

### Ayuda global no disponible

Causa probable:

- la opción de ayuda no está habilitada o no está disponible en ese entorno.

Interpretación funcional:

- el recurso de asistencia no puede abrirse en ese momento.

Acción recomendada para el usuario:

- continuar operando en el portal y contactar soporte si necesita asistencia.

Control sugerido para soporte:

- verificar si la ayuda global está prevista para ese entorno o situación.

## 11. Comportamientos esperados del sistema

- El usuario ingresa directamente al entorno principal cuando el acceso es válido.
- El encabezado, el menú lateral, el área principal y el pie de página forman un marco estable durante la navegación.
- El menú lateral muestra únicamente opciones compatibles con el perfil del usuario.
- El idioma cambia sin necesidad de recargar la página.
- La apariencia impacta en toda la interfaz y se conserva para el usuario.
- El cambio de contraseña actualiza la cuenta cuando los datos son correctos.
- La recuperación de contraseña permite volver a acceder sin intervención manual cuando el usuario dispone del correo correspondiente.
- El cierre de sesión devuelve al usuario a la pantalla de acceso y finaliza el uso actual del portal.
- Tras un período de **inactividad** (según parámetro ERP *MinutosWeb*), la sesión expira y el usuario debe volver a ingresar; cada interacción renueva el contador.
- Las grillas de listados ofrecen la misma experiencia transversal: filtros, orden, columnas, agrupación, totalizadores, layouts, exportación y acciones por fila según permisos.

## 12. Casos habituales

### Caso 1. Ingreso normal al portal

El usuario abre la pantalla de acceso, ingresa sus credenciales, accede al entorno principal y utiliza el menú lateral para abrir sus procesos habituales.

### Caso 2. Cambio de idioma antes del ingreso

El usuario selecciona otro idioma en la pantalla de acceso y luego inicia sesión. Desde ese momento, la interfaz aparece en el idioma elegido.

### Caso 3. Cambio de apariencia durante la sesión

El usuario abre el menú del avatar, cambia la apariencia y continúa operando con el nuevo estilo visual.

### Caso 4. Cambio obligatorio de contraseña

El usuario ingresa correctamente, pero el sistema le exige actualizar su contraseña antes de permitir el acceso normal al resto del portal.

### Caso 5. Recuperación por olvido de contraseña

El usuario no recuerda su clave, solicita la recuperación, recibe el correo, define una nueva contraseña y vuelve a ingresar al portal.

## 13. Problemas frecuentes

- El usuario espera ver opciones en el menú que no aparecen.
- El usuario cambia el idioma y espera que se traduzcan datos propios del negocio.
- El usuario interpreta que ocultar el menú lateral cambia sus permisos.
- El usuario intenta recuperar la contraseña con un correo distinto del asociado a su cuenta.
- El usuario utiliza un enlace viejo.
- El usuario intenta cambiar la contraseña, pero no completa correctamente la confirmación.
- El usuario cierra la pantalla sin cerrar sesión y luego tiene dudas sobre el estado de acceso.
- El usuario espera ver el botón **Agregar (+)** en una grilla de solo consulta.
- El usuario no encuentra una columna porque la ocultó o aplicó un layout distinto.
- El usuario agrupa o filtra y interpreta que «desaparecieron» registros.
- El usuario espera exportar una grilla vacía o sin datos visibles.

Qué debería revisar soporte en consultas repetidas:

- si el problema es de acceso, perfil o expectativa funcional;
- si el usuario comprendió correctamente la diferencia entre menú visible y autorización real;
- si la cuenta requiere cambio obligatorio de contraseña;
- si el correo usado para la recuperación es el correcto;
- si la grilla está filtrada, agrupada o usando un layout que oculta columnas;
- si el proceso es de consulta o de ABM (alta/modificación/baja).

## 14. Recomendaciones de uso

- Seleccionar el idioma antes del login si se desea iniciar directamente en otro idioma.
- Mantener actualizada la contraseña y no compartirla con terceros.
- Usar el menú lateral como referencia principal de procesos habilitados.
- Aprovechar los controles del encabezado para ordenar mejor la navegación.
- Confirmar visualmente los cambios de apariencia antes de continuar trabajando.
- Utilizar la recuperación de contraseña solo sobre la cuenta correcta.
- Cerrar sesión al finalizar la tarea, especialmente en equipos compartidos.
- Antes de consultar por «registros faltantes» en una grilla, revisar filtros, agrupación y layout activo.
- Guardar layouts propios con **Guardar como** cuando se personaliza una vista que se usará a menudo.
- Pasar el mouse sobre los íconos de **Acciones** para confirmar qué operación ejecuta cada uno.

Para soporte:

- orientar primero sobre el flujo correcto antes de escalar un incidente;
- diferenciar entre bloqueo de acceso, falta de permiso, error de ingreso y problema de recuperación;
- validar siempre la cuenta involucrada antes de sugerir una nueva recuperación.

## 15. Preguntas frecuentes

### ¿Por qué no veo las mismas opciones que otro usuario?

Porque el menú se ajusta al perfil y a las habilitaciones de cada usuario.

### ¿Cambiar el idioma traduce también los datos cargados por clientes, artículos o registros?

No. El idioma cambia los textos del sistema, no el contenido propio del negocio.

### ¿Cambiar la apariencia modifica solo una pantalla?

No. La apariencia está pensada para impactar en toda la interfaz del portal para ese usuario.

### ¿Puedo usar el portal sin cambiar la contraseña en el primer ingreso?

No, si el sistema exige el cambio obligatorio, primero debe completarse ese paso.

### ¿Qué hago si no me llega el correo de recuperación?

Conviene revisar que se esté usando el correo correcto para la cuenta y verificar nuevamente la casilla antes de repetir la solicitud.

### ¿Por qué el enlace de recuperación ya no funciona?

Porque puede haber vencido o ya haber sido usado. En ese caso debe generarse una nueva solicitud.

### ¿Cerrar la sesión es obligatorio?

Es recomendable, especialmente si se trabaja en un equipo compartido o de uso común.

## 16. Grillas y listados

Esta sección describe el comportamiento **estándar** de las grillas en PedidosWeb. Aplica a consultas, dashboards con listados y procesos operativos que muestran datos tabulares. Algunos procesos pueden ocultar una función concreta por reglas de negocio; en ese caso, el control simplemente no aparece o permanece deshabilitado.

### 16.1 Qué es y dónde aparece

Una grilla muestra registros en filas y columnas dentro del área principal del proceso. Suele incluir:

- una **barra de herramientas** superior (layouts, exportación y acciones propias del listado, cuando corresponda);
- un **panel de agrupación** en la parte superior de la tabla;
- **cabeceras de columna** con título traducido según el idioma activo;
- una **fila de filtros** debajo de las cabeceras;
- el **cuerpo** con los datos;
- una **fila de pie** para totalizadores por columna;
- un **paginador** cuando hay muchos registros;
- una columna de **acciones** al extremo derecho (íconos con ayuda al pasar el mouse), si el proceso lo habilita.

Las grillas **no reemplazan** la navegación del portal: el menú lateral y el encabezado siguen disponibles mientras se trabaja en el listado.

### 16.2 Elementos de la barra superior de la grilla

Según el proceso, la barra superior puede incluir:

| Elemento | Para qué sirve |
|----------|----------------|
| **Layout de grilla** | Elegir un formato guardado o volver a la **plantilla del sistema** |
| **Guardar** | Actualiza el layout seleccionado (si el usuario es el creador y la vista lo permite) |
| **Guardar como** | Crea un layout nuevo con el formato actual |
| **Eliminar** | Quita un layout propio |
| **Exportar** | Descarga la vista actual a Excel |
| **Actualizar** | Recarga los datos desde el servidor (habitual en informes y consultas) |
| Acciones del proceso | Botones adicionales definidos por cada pantalla |

**Layouts**

- Cada layout guarda, entre otras cosas: columnas visibles y su orden, filtros, agrupaciones, ordenamiento y **totalizadores del pie** configurados.
- Todos los usuarios pueden **ver y aplicar** layouts existentes de la misma grilla.
- Solo el **creador** puede **modificar o eliminar** un layout que guardó.
- Los diseños **propios** aparecen en el selector con el sufijo **` (*)`** para distinguirlos de layouts de otros usuarios o de la plantilla del sistema.
- Si partís de un layout de otro usuario, podés crear uno propio con **Guardar como**.
- Si estás en la **plantilla del sistema**, **Guardar** se interpreta como **Guardar como** (crea un layout nuevo).
- Elegir **Plantilla del sistema** restablece la vista base de la grilla (columnas, filtros y totalizadores por defecto del proceso).
- Al volver a abrir la pantalla, el sistema intenta restaurar el **último layout usado** por el usuario.

**Exportación a Excel**

- Disponible cuando hay datos exportables en la grilla.
- Si no hay filas, el botón queda deshabilitado y se informa que no hay datos para exportar.
- La exportación respeta la **vista vigente**: columnas visibles, filtros, orden y agrupaciones activas al momento de exportar.
- Modalidades habituales:
  - **Básica:** datos tal como se ven, sin formato avanzado de Excel (útil para procesamiento externo).
  - **Formateada:** encabezados con fondo gris y negrita; fechas según el idioma activo; enteros sin decimales forzados; decimales con el formato de la columna; valores Sí/No traducidos; **totales del pie** incluidos cuando están visibles en pantalla (útil para uso de negocio).

### 16.3 Ordenar registros

1. Hacer clic en el **título de una columna** para ordenar ascendente o descendente.
2. Repetir el clic invierte el sentido del orden.
3. Clic derecho sobre la cabecera de columna → elegir **Orden ascendente**, **Orden descendente** o **Limpiar orden**.

Por defecto, el orden principal es por **una sola columna** a la vez (salvo que un proceso específico habilite orden múltiple).

### 16.4 Filtrar registros

1. Ubicar la **fila de filtros** debajo de los títulos de columna.
2. Escribir o elegir el criterio en la columna deseada.
3. Usar el operador disponible (igual, contiene, mayor que, entre, etc.) según el tipo de dato.
4. Combinar filtros en varias columnas para acotar el resultado.

Los filtros **no modifican** los datos en el sistema: solo reducen lo que se muestra en pantalla. Si parece que «faltan» registros, conviene revisar filtros activos y el layout aplicado.

### 16.5 Mostrar, ocultar y reordenar columnas

**Selector de columnas**

1. Abrir el **selector de columnas** desde el icono correspondiente en la grilla.
2. Arrastrar columnas hacia el panel para **ocultarlas**, o desde el panel hacia la grilla para **mostrarlas**.

**Reordenar columnas**

- Arrastrar el encabezado de una columna hacia la izquierda o derecha, o
- Clic derecho en la cabecera → **Mover a la izquierda** / **Mover a la derecha**.

### 16.6 Agrupar registros

1. Arrastrar el **título de una columna** al **panel de agrupación** superior (texto orientativo: arrastrar un encabezado para agrupar).
2. La grilla reorganiza las filas en grupos.
3. Para quitar una agrupación: clic derecho en la cabecera → **Desagrupar**, o usar **Desagrupar todo** desde el menú contextual.

La agrupación es una vista; no altera los datos almacenados.

### 16.7 Totalizadores en el pie de la grilla

Cada columna puede tener su **propio totalizador** en la fila inferior del pie.

1. Clic derecho sobre la **celda del pie** de la columna deseada.
2. Elegir el tipo según el dato:
   - **Numéricos:** contar, sumar, promedio, mínimo, máximo.
   - **Texto o fecha:** contar, mínimo, máximo (según corresponda).
3. Para quitar un totalizador: clic derecho en el pie de esa columna → **Quitar totalizador**.

Dos columnas pueden mostrar totalizadores distintos al mismo tiempo (por ejemplo, suma en importes y conteo en códigos).

### 16.8 Paginación

Cuando el listado supera la cantidad de filas por página:

- usar el **paginador** inferior para avanzar, retroceder o ir a una página concreta;
- cambiar el tamaño de página si el control lo permite.

Orden y filtros se mantienen al cambiar de página.

### 16.9 Acciones por fila

En procesos que lo permiten, la columna **Acciones** (extremo derecho) muestra **íconos sin texto visible**.

- Pasar el mouse sobre un ícono muestra el **nombre de la acción** (editar, eliminar, ver detalle, etc.).
- Las acciones visibles dependen del **permiso** y de las reglas del proceso.
- En grillas de **solo consulta**, no aparece el alta ABM ni acciones de modificación no autorizadas.

**Procesos ABM (alta, modificación, eliminación)**

- El **alta** de un registro se realiza con el botón **+** integrado en la grilla (toolbar nativa del listado), no con un botón suelto fuera de la tabla.
- **Editar** y **eliminar** suelen estar en la columna de acciones de cada fila, según permisos.

### 16.10 Estados de la grilla

| Estado | Qué ve el usuario | Interpretación |
|--------|-------------------|----------------|
| **Cargando** | Mensaje de carga del listado | El sistema está obteniendo datos |
| **Vacío** | Mensaje de sin registros | No hay filas para mostrar con los criterios actuales |
| **Error** | Mensaje de error de carga | No se pudo completar la consulta; reintentar o contactar soporte |
| **Listo** | Datos visibles | Operación normal |

### 16.11 Idioma y textos de la grilla

- Títulos de columnas, filtros, menús contextuales, paginador, totalizadores y mensajes de la grilla siguen el **idioma activo** del portal (español, inglés, francés, portugués o italiano).
- Los **datos de negocio** (nombres de clientes, descripciones de artículos, observaciones cargadas por usuarios, etc.) **no se traducen** al cambiar idioma: se muestran tal como fueron registrados.

### 16.12 Validaciones y permisos en grillas

Qué debe tener en cuenta el usuario:

- Si no ve una acción (editar, eliminar, agregar), probablemente su perfil **no tiene permiso** para esa operación en ese proceso.
- Ocultar columnas o aplicar filtros **no cambia** permisos ni datos reales.
- Exportar requiere datos visibles/exportables; una grilla vacía no permite exportación.
- Solo el creador puede modificar o eliminar un layout propio.

Qué debe revisar soporte:

- si el usuario confunde una grilla de **consulta** con un ABM;
- si hay filtros, agrupaciones o un layout que oculta información;
- si el permiso del rol cubre la acción esperada;
- si el error es de carga (conectividad, backend) o de criterios de búsqueda sin resultados.

### 16.13 Casos habituales con grillas

**Caso A. Consulta simple**

El usuario abre un listado, ordena por fecha descendente, filtra por cliente y exporta la vista formateada a Excel.

**Caso B. Personalización recurrente**

El usuario oculta columnas que no usa, define un totalizador de suma en importes, guarda el formato con **Guardar como** y lo reutiliza en visitas posteriores.

**Caso C. Agrupación para revisión**

El usuario arrastra la columna «Vendedor» al panel de agrupación para revisar totales por grupo y luego desagrupa para volver a la vista plana.

**Caso D. Solo lectura**

El usuario abre una consulta, ve íconos de acción limitados o ausentes, y no encuentra el botón **+**: el proceso es de consulta, no de ABM.

### 16.14 Preguntas frecuentes sobre grillas

**¿Por qué no veo el botón Agregar (+)?**

Porque el proceso es de consulta o su usuario no tiene permiso de alta en ese listado.

**¿Por qué desaparecieron registros?**

Lo más habitual es que haya **filtros activos**, **agrupación** aplicada o un **layout** que cambia la vista. Revisar la fila de filtros, el panel de agrupación y el layout seleccionado; volver a la **plantilla del sistema** ayuda a restablecer la vista base.

**¿Puedo guardar mi forma de ver la grilla?**

Sí, con **Guardar** o **Guardar como** en la barra de layouts. El formato queda asociado a esa grilla del proceso.

**¿Puedo modificar un layout que creó otro usuario?**

No directamente. Podés **aplicarlo** y, si te sirve como base, crear uno propio con **Guardar como**.

**¿Qué significa el sufijo ` (*)` en un layout?**

Indica que el diseño es **propio** del usuario conectado.

**¿Qué hace la plantilla del sistema?**

Restablece la grilla a la vista base del proceso, sin filtros ni personalizaciones del layout guardado.

**¿La exportación incluye columnas ocultas?**

No. Exporta la **vista vigente**: columnas visibles, filtros, orden y agrupaciones activas al exportar.

**¿Cambiar el idioma traduce los datos de la grilla?**

No. Traduce los textos del sistema (cabeceras, menús, mensajes). Los valores de negocio se muestran en su idioma original.

**¿Qué significa el ícono en Acciones si no tiene texto?**

Pasá el mouse sobre el ícono: aparece el nombre de la acción (tooltip).

## 17. Resumen operativo

PedidosWeb ofrece una experiencia base organizada y segura: el usuario ingresa desde una pantalla simple, accede a un entorno principal estable y opera solo dentro de las opciones habilitadas para su perfil.

Las preferencias personales, como idioma y apariencia, ayudan a adaptar la experiencia sin alterar la seguridad. Al mismo tiempo, las funciones de cambio y recuperación de contraseña permiten mantener el acceso bajo control y resolver incidentes habituales sin salir del circuito previsto.

Las **grillas** unifican la consulta de listados: orden, filtros, columnas, agrupación, totalizadores por columna, layouts reutilizables (propios marcados con **` (*)`**), exportación a Excel formateada y acciones por fila según permisos. La **vista pivot** (sección 19) extiende el análisis en informes habilitados.

Los puntos que usuario y soporte deben recordar son:

- el acceso válido habilita el entorno principal sin pasos intermedios;
- el menú visible depende del perfil del usuario;
- idioma y apariencia son preferencias personales;
- el cambio de contraseña puede ser obligatorio;
- la recuperación de contraseña debe hacerse sobre la cuenta y el correo correctos;
- cerrar sesión sigue siendo la forma recomendada de finalizar el uso del portal;
- la sesión **expira por inactividad** según parámetro ERP; conviene grabar el trabajo antes de ausentarse;
- en grillas, filtros y layouts modifican la **vista**, no los permisos ni los datos;
- exportar y totalizar respetan lo visible en pantalla al momento de la acción;
- el botón **+** de alta aparece solo en procesos ABM autorizados;
- la **Consulta de parámetros** (menú General) es solo lectura: muestra descripción, valor y ayuda de la configuración ERP, sin clave técnica visible;
- la **vista pivot** en informes requiere habilitación del tenant; consultas de cabecera de comprobantes usan solo grilla.

## 18. Consulta de parámetros (menú General)

Proceso transversal del grupo **General**, ubicado al **final** del menú lateral. Permite consultar la configuración vigente del módulo PedidosWeb según los parámetros cargados desde el ERP.

### 18.1 Qué muestra

| Columna | Contenido |
|---------|-----------|
| **Descripción** | Texto legible del parámetro (`CAPTION` en ERP) |
| **Valor** | Valor efectivo según su tipo (número, texto, fecha, Sí/No, etc.), **centrado** en la columna |
| **Tooltip** | Ayuda contextual cuando existe en ERP |

La **clave técnica** del parámetro (identificador interno) **no se muestra** en la grilla.

El listado se ordena por **descripción** ascendente.

### 18.2 Qué no permite

- **No** editar valores desde el portal.
- **No** agregar ni eliminar parámetros.
- **No** exportar a Excel en el MVP (salvo que el proceso lo habilite en una versión posterior).

La administración de parámetros corresponde al **ERP** o herramientas internas de la empresa.

### 18.3 Permisos

- Requiere permiso de **consulta** (`Permiso_Repo`) sobre el procedimiento *Consulta de parámetros*.
- Usuarios sin permiso no ven el ítem en el menú General.

### 18.4 Caso habitual

Un supervisor abre **General → Consulta de parámetros**, revisa valores como minutos de edición web, flags de mail o permisos de modificación, y utiliza la información para interpretar el comportamiento del resto del portal **sin modificar** la configuración.

Para el detalle de grillas (filtros, layouts), aplicar también la sección 16 de este manual.

## 19. Vista pivot (PivotGrid)

Esta sección describe el comportamiento **estándar** de la vista pivot en informes analíticos. No todos los procesos la ofrecen: depende de la consulta y de que el **tenant** tenga la funcionalidad habilitada por administración.

### 19.1 Dónde aparece

- Informes comerciales de PedidosWeb que admiten análisis pivot (deuda, cheques, stock, detalle de pedidos, historial de ventas, según configuración).
- **No** aparece en consultas de comprobantes por cabecera (pedidos ingresados, pendientes, presupuestos): esas pantallas usan solo grilla.

Si la funcionalidad no está habilitada en el tenant, el informe se muestra **únicamente en grilla** (comportamiento habitual del MVP).

### 19.2 Conmutador Grilla / Pivot

En informes habilitados, la barra superior incluye un conmutador para alternar entre:

| Vista | Uso |
|-------|-----|
| **Grilla** | Listado tabular estándar (sección 16): filtros, layouts de grilla, exportación Excel de grilla |
| **Pivot** | Tabla dinámica: arrastrar campos a filas, columnas y valores para agrupar y totalizar |

La vista inicial suele ser **Grilla**. Al pasar a **Pivot** por primera vez, el sistema carga los datos analíticos del mismo informe.

### 19.3 Elementos de la vista pivot

| Elemento | Para qué sirve |
|----------|----------------|
| **Panel de campos** | Lista de dimensiones y medidas disponibles; arrastrar hacia filas, columnas o valores |
| **Área pivot** | Muestra la tabla dinámica con totales y subtotales |
| **Actualizar** | Recarga datos del servidor |
| **Diseño pivot** | Selector de layouts guardados, **Guardar**, **Guardar como**, **Eliminar**, **Plantilla inicial** |
| **Exportar** | Descarga la vista pivot a Excel (básica o tabla dinámica, según opciones del proceso) |

**Diseños pivot**

- Misma lógica que los layouts de grilla: todos pueden **ver y aplicar** diseños; solo el **creador** modifica o elimina el suyo.
- **Plantilla inicial** restablece la pivot vacía sin borrar diseños de otros usuarios.
- Los diseños propios pueden distinguirse con el sufijo **` (*)`** en el selector.

### 19.4 Valores numéricos

Los importes, cantidades y saldos en pivot usan formato decimal **`#,##0.00`** (dos decimales, separador de miles según locale del portal).

### 19.5 Idioma

Títulos de campos, botones y menús del pivot siguen el **idioma activo** del usuario. Los **datos de negocio** (nombres de clientes, descripciones, etc.) no se traducen.

### 19.6 Casos habituales

**Caso A.** Revisar deuda por cliente y vendedor: abrir informe Deuda, conmutar a Pivot, arrastrar *Cliente* a filas y *Saldo* a valores con suma.

**Caso B.** Guardar diseño recurrente: configurar filas/columnas/valores, **Guardar como** con un nombre descriptivo y reutilizarlo en visitas posteriores.

**Caso C.** Tenant sin pivot habilitado: el informe muestra solo grilla; exportar y filtrar con las herramientas de la sección 16.

### 19.7 Preguntas frecuentes sobre pivot

**¿Por qué no veo el conmutador Grilla / Pivot?**

La consulta no lo admite o el tenant no tiene pivots activos. Contactar soporte o administración.

**¿El pivot modifica datos en el ERP?**

No. Es una vista analítica de solo lectura sobre los mismos datos del informe.

**¿Puedo exportar la pivot?**

Sí, con el botón **Exportar** del bloque pivot cuando hay datos cargados.

**¿Los filtros de la grilla se aplican al pivot?**

Los criterios de negocio del informe (cliente, fechas, etc.) se respetan; al cambiar de vista conviene pulsar **Actualizar** si se modificaron filtros en pantalla.
