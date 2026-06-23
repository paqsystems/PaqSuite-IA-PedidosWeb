# Chat Asistente IA — Manual de usuario

| Campo | Valor |
|-------|--------|
| **Versión documento** | 2026-06-22 |
| **Ámbito** | Asistente conversacional de ayuda en PedidosWeb |
| **Manual transversal** | [Generalidades.md](./Generalidades.md) |
| **Operatoria comercial** | [PedidosWeb.md](./PedidosWeb.md) |

---

## 1. Qué es y para qué sirve

El **Chat Asistente IA** es una ayuda conversacional integrada en el portal. Responde preguntas sobre **cómo usar PedidosWeb** apoyándose en la documentación oficial del producto (manuales de usuario y guías operativas).

**Sirve para:**

- orientarse sobre pasos de carga, consultas, conversiones y parámetros;
- interpretar mensajes de error o bloqueos habituales;
- entender permisos, estados de comprobantes y comportamiento de grillas.

**No sirve para:**

- grabar, editar o eliminar comprobantes en su nombre;
- consultar datos reales de su base (clientes, stock, pedidos);
- reemplazar al soporte humano ni al administrador ERP;
- garantizar una respuesta correcta si la documentación no cubre el caso.

---

## 2. Cómo acceder

1. Iniciar sesión en el portal.
2. Abrir el **menú del avatar** (extremo superior derecho).
3. Elegir **Chat Asistente IA**.
4. El chat se abre en una **nueva pestaña**; la pantalla donde estaba trabajando permanece abierta.

Si no tiene configuración válida, verá un mensaje indicando que debe configurar el asistente y un acceso a **Preferencias** (§4).

---

## 3. Primera configuración (BYOK)

El consumo de IA es **Bring Your Own Key (BYOK)**: cada usuario configura su **propia credencial** del proveedor elegido (OpenAI, Ollama u otros del catálogo). El costo de inferencia corre por la cuenta externa configurada.

### Pasos

1. Menú avatar → **Preferencias** (o el enlace desde el chat vacío).
2. Sección **Asistente IA** / configuraciones LLM.
3. **Agregar configuración**:
   - **Nombre** descriptivo (ej. «OpenAI trabajo»).
   - **Proveedor** del catálogo.
   - **Endpoint base** (si el proveedor lo requiere, ej. Ollama local).
   - **Modelo** (sugerido del catálogo o personalizado).
   - **Credencial (API key)** del proveedor.
4. Activar el interruptor **Habilitado**.
5. Guardar y volver al chat.

Puede tener **varias configuraciones**; el chat usa la configuración operativa habilitada según reglas del portal (prioridad de la activa).

### Campos obligatorios habituales

| Campo | Cuándo es obligatorio |
|-------|------------------------|
| Nombre | Siempre |
| Proveedor | Siempre |
| Modelo | Siempre |
| Credencial | Al crear; al editar puede dejarse vacía para **conservar** la actual |
| Endpoint base | Si el proveedor del catálogo lo marca como requerido |

---

## 4. Uso del chat

### Envío de mensajes

- Escriba la consulta en lenguaje natural (recomendado: **español**).
- Pulse enviar.
- La respuesta aparece en la conversación; puede incluir referencias a procedimientos del manual.

### Límites de texto

| Tipo de consulta | Límite |
|------------------|--------|
| Solo texto | **2.000** caracteres |
| Texto con imágenes | **1.000** caracteres de texto |

El contador es visible en pantalla. Si supera el límite, el envío se **bloquea** hasta acortar el mensaje.

### Imágenes (opcional)

- Si su proveedor y modelo soportan visión, puede adjuntar hasta **4 imágenes** por mensaje.
- Formatos y tamaños válidos según validación del portal.
- Las imágenes **no se almacenan** en el sistema tras el análisis.

### Qué preguntar para mejores respuestas

Formule preguntas **concretas** con contexto:

- «¿Qué lookups son obligatorios para grabar un pedido?»
- «Convertí presupuesto a pedido y el presupuesto quedó cerrado, ¿es normal?»
- «No veo Editar en pedidos ingresados, ¿qué reviso?»
- «Importé Excel y dice cabecera incoherente, ¿qué significa?»

Evite preguntas sin contexto del tipo «no funciona» sin indicar pantalla, acción ni mensaje de error.

---

## 5. Documentación que utiliza el asistente (Fase 1)

El asistente busca en documentación **aprobada**, principalmente:

| Carpeta / documento | Contenido |
|---------------------|-----------|
| `99-manual-usuario/` | Manuales de usuario y guías operativas |
| `02-producto/PedidosWeb/` | Documentación operativa estable del módulo |

**No utiliza** (en Fase 1): historias de usuario, tareas técnicas, especificaciones de desarrollo, borradores ni documentación de implementación.

Documentos clave para consultas comerciales:

- [PedidosWeb.md](./PedidosWeb.md) — operatoria general
- [PedidosWeb-validaciones-errores.md](./PedidosWeb-validaciones-errores.md) — errores y validaciones de grabación
- [PedidosWeb-circuito-estados.md](./PedidosWeb-circuito-estados.md) — estados y conversiones
- [Generalidades.md](./Generalidades.md) — login, grillas, pivot, parámetros

---

## 6. Comportamiento esperado y limitaciones

| Comportamiento | Detalle |
|----------------|---------|
| Respuesta orientativa | No es resolución garantizada ni dictamen comercial |
| Sin acceso a su sesión | No ve pantallas ni datos de su comprobante abierto |
| Sin acciones en el ERP | No graba, no cambia parámetros, no desbloquea pedidos |
| Incertidumbre | Si la documentación no alcanza, debe indicarlo y sugerir soporte |
| Idioma | Configurado para responder en español operativo |
| Proveedor externo | Fallos de API, cuota o clave inválida son de su cuenta/configuración |

### Si la respuesta fue pobre o incorrecta

1. Reformule con **más detalle** (pantalla, botón, mensaje exacto).
2. Consulte directamente los manuales enlazados arriba.
3. Para incidentes de **datos o permisos reales**, contacte soporte o administrador ERP.

---

## 7. Problemas frecuentes del chat

| Problema | Causa probable | Acción |
|----------|----------------|--------|
| Chat vacío pide configurar | Sin configuración LLM habilitada | Completar §3 en Preferencias |
| Error al enviar mensaje | Clave inválida, endpoint incorrecto o proveedor caído | Revisar credencial y endpoint en Preferencias |
| «No se pudo cargar el catálogo de proveedores» | Fallo de red o backend | Reintentar; contactar soporte si persiste |
| No admite imágenes | Modelo/proveedor sin visión | Usar solo texto o cambiar modelo |
| Respuesta genérica o vacía | Pregunta muy amplia o tema no documentado | Ser específico; leer manuales §5 |
| Superé caracteres y no envía | Límite 2000/1000 | Acortar mensaje |

---

## 8. Relación con otras ayudas

- **Ayuda externa** (si está habilitada en su entorno): puede coexistir con el chat; son canales distintos.
- **Consulta de parámetros** (menú General): muestra valores reales de su instalación; el chat explica qué significan pero no lee su ERP en vivo.
- **Soporte humano**: para bloqueos de datos, permisos no documentados o errores técnicos persistentes.

---

## 9. Preguntas frecuentes

### ¿El chat ve mis pedidos o clientes?

No. Solo usa documentación estática y el texto que usted escribe.

### ¿Puedo usar el chat sin pagar un proveedor externo?

Sí si configura un proveedor **local** (ej. Ollama en su red) sin costo por token externo; requiere infraestructura disponible.

### ¿Mis mensajes quedan guardados?

La conversación de la sesión se muestra en pantalla según implementación del portal; las **imágenes adjuntas no se persisten** en el servidor.

### ¿Por qué el chat no me dijo el valor de un parámetro?

Debe consultarlo en **General → Consulta de parámetros**. El chat explica el parámetro, no su valor en su empresa.

### ¿Puedo tener varias configuraciones?

Sí. Gestione la lista en Preferencias; habilite la que desee usar operativamente.
