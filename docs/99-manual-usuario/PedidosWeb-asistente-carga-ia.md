# PedidosWeb — Manual de usuario: Asistente IA en carga de pedidos y presupuestos

| Campo | Valor |
|-------|--------|
| **Versión documento** | 2026-07-15 |
| **Ámbito** | Panel Asistente IA embebido en `/pedidos/carga` |
| **Público** | Vendedores, supervisores, perfiles cliente; soporte funcional |
| **Definición de producto (fuente de verdad)** | [asistente-ia-carga-pedidos-presupuestos.md](../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) |
| **Operatoria general de carga** | [PedidosWeb.md](./PedidosWeb.md) §6 |
| **Chat de ayuda por manuales (distinto)** | [Chat-Asistente-IA.md](./Chat-Asistente-IA.md) |
| **Configuración BYOK** | Preferencias → Asistente IA (mismo que el chat documental) |

---

## 1. Qué es y qué no es

### Qué es

El **Asistente IA de carga** es un panel conversacional al **pie del formulario** de pedidos/presupuestos. Permite armar o completar el comprobante con:

- **texto** escrito;
- **dictado** (micrófono del navegador);
- **imágenes** (foto de lista / pedido manuscrito, si el proveedor LLM admite visión).

Cada acción del asistente usa la **misma lógica** que la pantalla: mismos permisos, mismos lookups, mismas validaciones al grabar.

### Qué no es

| Capacidad | Chat Asistente IA (menú avatar) | Asistente IA de carga (este manual) |
|-----------|----------------------------------|--------------------------------------|
| Propósito | Ayuda por **documentación** | **Operar** el comprobante abierto |
| Mutar pedido | No | Sí (si tiene permisos) |
| Datos reales de cartera | No | Sí (cliente, stock, deuda, etc. del contexto) |
| Dónde vive | Nueva pestaña / ruta de chat | Pie de `/pedidos/carga` |

---

## 2. Requisitos previos

1. Estar logueado en PedidosWeb.
2. Tener al menos **una configuración LLM habilitada** en **Preferencias → Asistente IA** (Bring Your Own Key): proveedor, modelo y credencial válidos.
3. Abrir **Pedidos → carga** (alta, edición o vista según permiso).
4. En el panel, expandir **Asistente IA**.

Si no hay configuración válida, cualquier pedido al asistente responde el mensaje fijo de configuración y **no modifica** el comprobante. Use la ruedita del panel o Preferencias.

---

## 3. Recorrido de la interfaz del panel

| Elemento | Función |
|----------|---------|
| Título / expandir–contraer | Muestra u oculta el hilo y el compositor |
| Hilo de mensajes | Conversación usuario ↔ asistente; tablas de consulta cuando corresponde |
| Campo de texto | Escribir instrucciones; Enter envía (si no está dictando) |
| **Enviar** | Manda texto y/o imágenes adjuntas |
| **Dictar** / **Detener dictado** | Inicia o corta el reconocimiento de voz continuo |
| **Adjuntar imagen** | Agrega PNG/JPEG/WebP (límite de cantidad y tamaño del portal) |
| Ruedita / Preferencias | Abre Preferencias del Asistente IA |
| **Proveedor LLM** (combo) | Elige cuál configuración BYOK usar en este turno |

Mientras se dicta, el campo de texto se llena con el reconocimiento parcial; Enviar y Adjuntar quedan deshabilitados hasta detener.

---

## 4. Configurar y elegir el proveedor LLM

1. Preferencias → **Asistente IA** → agregar o editar una configuración (nombre, proveedor, modelo, API key, endpoint si aplica).
2. Dejarla **habilitada**.
3. En el panel de carga, el combo **Proveedor LLM** lista las configuraciones operativas.
4. La elección se recuerda en la sesión (misma idea que el chat documental).

Si necesita visión para fotos, el proveedor/modelo debe soportar imágenes (`supports_vision`). Si no, el asistente pedirá texto o audio.

---

## 5. Cómo pedirle cosas (idioma y estilo)

- Preferir **español** claro, con palabras clave: `cliente`, `artículo` / `art` / `item` / `it`, `cantidad` / `canti` / `cant`, `precio`, `bonificación` / `bonif` / `descuento`.
- Puede usar una sola instrucción corta o un **pedido completo** (varias líneas o un solo párrafo / dictado largo).
- Ante ambigüedad (varios clientes o artículos), el asistente muestra una **lista numerada** (máximo 10). Responda con el número (`1`, `2`…).
- Si hay demasiados matches (>10), pedirá **refinar** la búsqueda.

---

## 6. Cliente

### Ejemplos

- `cliente 101093`
- `cliente Agromenta`
- `cambiar cliente Bernascone`

### Comportamiento

| Resultado búsqueda | Qué ocurre |
|--------------------|------------|
| 1 match | Selecciona el cliente e inicializa cabecera como al elegir en el combobox |
| 2–10 matches | Lista numerada; responda el número. **No** es un “no encontrado” |
| >10 matches | Pide refinar |
| 0 matches | Informa que no encontró; puede reintentar con variantes de dictado (p. ej. B/V) |

Perfil **cliente**: el cliente es fijo de sesión; el asistente no cambia a otro.

### Cambio de cliente con renglones

Si ya hay detalle cargado, pedirá confirmación (`sí` / `confirmo` / `aceptado`). Sin confirmar, no borra el borrador.

### Regla crítica del pedido compuesto

Si en el **mismo mensaje** manda cliente + artículos (y/o cabecera) y el cliente **no queda determinado** (no encontrado o pendiente de elegir en la lista), el asistente **no carga** artículos ni cabecera de ese turno. Primero resuelva el cliente; luego complete el resto (o vuelva a enviar el pedido completo).

---

## 7. Cabecera

Ejemplos (respetan permisos ERP / parámetros `Modifica*`):

| Pedido al asistente | Efecto típico |
|---------------------|---------------|
| `perfil STANDARD` | Lookup de perfil |
| `condición de venta 30 días` | Lookup condición |
| `transporte Pablo` | Lookup transporte |
| `lista de precios 2` / `Lista de Precios: Ankas C` | Lista + efectos de precios |
| `fecha de entrega 15/07/2026` | Fecha |
| `dirección de entrega Mitre` | Dirección del cliente (requiere cliente) |
| `expreso Andreani` | Texto expreso |
| `Direccion: san martin 2470` | Dirección de expreso |
| `Bonificación 1: 3%` / `Bonif 2: 5` / `Descto 3: 4` | Bonificaciones de cabecera |
| `Leyenda 1: entregar lista de precios` | Leyenda N |
| `Observaciones: horario de atención` | Observaciones |
| `Nivel 100` | Nivel (si *Nivel extremo*, solo 0 o 100) |

Sin permiso o en modo solo lectura: informa y no cambia el campo.

---

## 8. Artículos y renglones

### Alta (agregar)

Prefijos válidos: `artículo`, `artículos`, `art.`, `art`, `producto`, `item`, `it` (y plurales).

Ejemplos:

- `artículo ajo en polvo 25 kg cantidad 100`
- `art. "AJO EN POLVO25 kg" canti: 100`
- `item arroz largo fino 5/05 cant: 10 precio: 150 bonif: 3`
- `almendra carmel 10 unidades 120 $` (también puede inferir alta sin prefijo si hay unidades/precio)

Cantidad: `cantidad`, `canti`, `cant`, `x`, o `N unidades`.  
Precio y bonificación de línea: solo si su perfil tiene permiso.

### Modificar / eliminar

Busca en el **detalle del comprobante**, no en el maestro:

- `eliminar artículo almendra`
- `elimina el articulo arroz`
- `cambiar cantidad a 5 del articulo ABC`
- `cambiar cantidad del articulo "almendra tostada" a 150`
- `poner precio 1500 en el ultimo renglon`

Si varios renglones coinciden, elija el número de la lista (código · cantidad · precio · bonif.).

---

## 9. Pedido completo de una vez (texto o dictado)

### Multilínea (pegar)

```
Cliente: 101093
Perfil: aukanes presupuesto
condición de venta: 180
fecha de entrega: 31-07-2026
transporte: retira por deposito
Expreso: la estrella
Direccion: san martin 2470
Lista de Precios: Ankas C
Bonificación 1: 3%
Leyenda 1: entregar lista de precios
Observaciones: horario de atención
articulo "AJO EN POLVO25 kg" canti: 100
articulo almendra ramillada10 kg cant: 120
articulo arroz largo fino 5/05 cant: 10 precio: 150 bonif: 3
```

### Una sola línea / dictado largo

```
cliente Agromenta artículo ajo en polvo 25 kg cantidad 100 artículo almendra ramillada 10 cantidad 120
```

El sistema corta por palabras clave (`cliente`, `artículo`/`art`/`item`/`it`, etiquetas de cabecera…).

### Cómo dictar bien

1. Pulse **Dictar**.
2. Hable con pausas naturales; el micrófono **sigue escuchando**.
3. Pulse **Detener dictado** cuando termine: se envía todo el texto acumulado.
4. Si el nombre del cliente se oye mal (p. ej. `vernasconi` por `bernascone`), el sistema intenta variantes comunes; si aún hay varios matches, elija el número de la lista.

Requisitos técnicos del dictado: navegador con Web Speech (Chrome/Edge), **HTTPS** o `localhost`, permiso de micrófono.

---

## 10. Imágenes

1. **Adjuntar imagen** (una o varias, según límite).
2. Opcionalmente escriba una pista (“extraé cliente y renglones”).
3. **Enviar**.

El asistente intenta leer cliente, cabecera y renglones. Solo carga lo que valida contra maestros y permisos. Si el cliente es ambiguo, pide elegir y **después** aplica el resto diferido. Si el cliente no se encuentra, **no** carga renglones de esa imagen en el mismo turno.

Sin visión en el proveedor: pedirá texto o audio.

---

## 11. Consultas desde el panel

Requieren **cliente en proceso** (salvo stock por artículo, según flujo):

| Intención | Ejemplo |
|-----------|---------|
| Stock | `stock tornillo` / `¿cuánto stock de ajo?` |
| Deuda | `deuda` |
| Cheques | `cheques` |
| Historial de ventas | `historial de ventas` |

Las respuestas de consulta se muestran en **tabla** dentro del hilo. Tope habitual de filas visibles: 10; si hay más, pide refinar o abrir la consulta del menú.

---

## 12. Grabar

- `grabar pedido`
- `grabar presupuesto`

Equivale a los botones de la toolbar: mismas validaciones. Si faltan datos obligatorios, verá el mismo tipo de errores que al grabar a mano ([PedidosWeb-validaciones-errores.md](./PedidosWeb-validaciones-errores.md)).

---

## 13. Modo Ver / solo lectura

En comprobantes de solo lectura el asistente puede responder consultas; **no** debe mutar cabecera, renglones ni grabar. Si lo intenta, rechaza con el mismo criterio que la UI.

---

## 14. Mensajes frecuentes y qué hacer

| Situación | Qué significa | Qué hacer |
|-----------|---------------|-----------|
| Debe configurar el proveedor LLM | Sin BYOK operativo | Preferencias → Asistente IA |
| No encontré ese cliente | 0 matches (tras fallbacks) | Código exacto, otra grafía, o nombre más corto |
| Seleccione un cliente (1–N) | Hay varios matches | Responder el número |
| Seleccione un artículo (1–N) | Varios artículos matchean la búsqueda | Responder el número (no se elige solo el “más corto”) |
| Primero seleccioná un cliente | Consulta/acción sin cliente en curso | Elegir cliente (UI o asistente) |
| No tenés permiso… | Flag `Modifica*` / perfil (también en alta con precio/bonif) | Pedir habilitación o no forzar el campo |
| Hay demasiados resultados | >10 matches | Refinar búsqueda |
| El proveedor no admite imágenes | Sin visión | Texto/dictado, u otro modelo con visión |
| Dictado no disponible / HTTPS | Web Speech bloqueado | Chrome/Edge + HTTPS o localhost |
| Artículo agregado sin cliente | *(corregido)* Ya no debe pasar en pedidos compuestos | Actualizar backend; reenviar el pedido completo |

---

## 15. Buenas prácticas

1. Configure BYOK una sola vez y elija el modelo en el combo del panel.
2. Para pedidos largos: dictar completo → **Detener** → revisar cabecera y grilla antes de grabar.
3. Use comillas en descripciones con números (`"almendra carmel 20/2210"`).
4. Resuelva siempre el **cliente** antes de asumir que los renglones se cargaron.
5. No confunda este panel con el **Chat Asistente IA** del menú avatar.
6. Tras un mensaje del asistente, mire la **pantalla de carga**: es la fuente visible del borrador.

---

## 16. Permisos y seguridad (resumen)

- El asistente **no** eleva privilegios: el servidor revalida permisos y el **perfil comercial** (V/S/C) sale del usuario autenticado.
- Visibilidad de clientes = misma cartera que el combobox de carga.
- Auditoría: el portal registra modalidad (texto/audio/imagen), intención y resultado a nivel de aplicación.

---

## 17. Relación con otros documentos

| Documento | Uso |
|-----------|-----|
| [PedidosWeb.md](./PedidosWeb.md) §6 | Operatoria completa de la pantalla de carga |
| [PedidosWeb.md](./PedidosWeb.md) §6.17 | Resumen corto; apunta a este manual |
| [Chat-Asistente-IA.md](./Chat-Asistente-IA.md) | Chat de ayuda documental + BYOK |
| [asistente-ia-carga-pedidos-presupuestos.md](../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) | Contrato de producto / criterios de aceptación |
| [patron-asistente-operativo-embebido.md](../02-producto/_patrones/patron-asistente-operativo-embebido.md) | Patrón reusable para otros módulos PaqSuite |

---

## 18. Preguntas frecuentes

### ¿El dictado corta solo?

No. Escucha hasta que pulse **Detener dictado**.

### ¿Puedo pegar un pedido de Excel/nota en varias líneas?

Sí. Use etiquetas (`Cliente:`, `artículo`, etc.).

### ¿Por qué dijo “no encontré” si hay dos Bernascone?

“No encontré” = cero coincidencias. Dos matches muestran lista 1…N. Revise cómo quedó escrito el nombre tras el dictado.

### ¿Las imágenes usan mi API key?

Sí (BYOK). El costo de visión corre por su cuenta del proveedor.

### ¿Puedo usar un LLM distinto al del chat de ayuda?

Son las mismas configuraciones. Puede tener varias y elegir cuál está activa en el combo del panel o en el chat documental.
