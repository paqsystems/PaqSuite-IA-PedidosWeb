# Manual del programador — PaqSuite PedidosWeb

| Campo | Valor |
|-------|--------|
| **Producto** | PaqSuite IA — PedidosWeb (portal MONO) |
| **Público** | Programadores, integradores y soporte técnico que necesitan entender el sistema de punta a punta |
| **Fecha** | 1 de septiembre de 2026 |
| **Complementa** | Este documento enseña. No sustituye SPEC, HU, TR ni el contrato OpenAPI. |

Este texto está pensado para que alguien que entra al repositorio por primera vez pueda responder dos preguntas:

1. **¿Cómo está construido el proyecto y cómo se trabaja en él?** (visión técnica)
2. **¿Qué puede hacer el sistema hoy?** (visión funcional)

Al final hay un **anexo** sobre la integración del aplicativo **GALO**, que genera pedidos en nuestra base usando las mismas APIs de lectura de maestros y de grabación de pedidos.

---

## Cómo leer este documento

Si venís a **programar**, empezá por la Parte 1. Ahí está el mapa del monorepo, las capas, las reglas que no se improvisan y el circuito OpenSpec (cómo nace un cambio: SPEC → HU → TR → código → tests → verificación).

Si venís a **entender qué hace el producto**, andá a la Parte 2. Ahí está el inventario de prestaciones: carga, consultas, Excel, IA, mobile, mails, etc.

Si venís a **integrar un sistema externo**, leé la Parte 1 (auth, tenant, envelope) y el **Anexo A (GALO)**. El contrato vivo de cada endpoint está en Swagger: `http://127.0.0.1:8088/api/documentation` (puerto local habitual del backend).

Los manuales de usuario (operatoria para vendedores y clientes) viven en `docs/99-manual-usuario/`. Este archivo es el equivalente para quien escribe código o consume la API.

---

# Parte 1 — Cómo entender técnicamente el desarrollo

## 1.1 Qué es PedidosWeb, en una frase técnica

PedidosWeb es un **portal comercial conectado al ERP** (en la práctica, Tango Gestión). No es un CRM completo ni un ERP en sí mismo.

El portal vive en una **base SQL Server propia del tenant**, con tablas `pq_pedidosweb_*`. El ERP **empuja maestros y consultas** (clientes, artículos, stock, deuda, cheques, historial, parámetros). El portal **genera pedidos y presupuestos** en esa misma base. Un proceso del lado ERP los descarga cuando corresponde.

Hay tres “caras” del mismo backend:

- la **aplicación web** (React + DevExtreme);
- la **aplicación mobile** (Capacitor, mismo frontend, vistas distintas);
- **consumidores externos de API**, como GALO, que autenticados igual que un usuario del portal leen maestros y graban pedidos.

## 1.2 El monorepo

El repositorio es un monorepo de producto **MONO** (un tenant = una empresa = una base). Estructura:

```text
PaqSuite-IA-PedidosWeb/
├── backend/          Laravel 10 — API REST `/api/v1`
├── frontend/         React + Vite + DevExtreme (+ Capacitor android/ios)
├── docs/             producto, OpenSpec, HU, TR, operación, manuales
├── .cursor/rules/    normas que el asistente y el equipo deben cumplir
└── prompts/          prompts del circuito OpenSpec
```

Herencia compartida de PaqSuite (no reinventar login, envelope, grillas, menú, etc.):

- `docs/_base/` y `docs/00-contexto/_mono/` — arquitectura y patrones MONO
- `.cursor/rules/base/` — reglas transversales (API, DB, frontend, mobile, Git)

El producto **adopta capacidades GEN del Framework** (login, i18n, menú, grillas, pivots, Excel, chat IA, mobile). El host aporta el dominio PedidosWeb: catálogo, menú, permisos, APIs/SP de negocio, pantallas de carga y consultas.

## 1.3 Stack y puertos locales habituales

| Capa | Tecnología | Puerto / URL típica |
|------|------------|---------------------|
| Frontend | React 18, Vite, TypeScript, DevExtreme, i18next | `http://localhost:3010` |
| Backend | Laravel 10, PHP, Sanctum | `http://127.0.0.1:8088` |
| OpenAPI | L5-Swagger | `http://127.0.0.1:8088/api/documentation` |
| Base | SQL Server (no MySQL) | definida en `backend/.env` (`DB_DATABASE`) |

Health: `GET /api/v1/health` — no requiere tenant ni token.

En producción el patrón de URLs es:

- entrada por subdominio: `https://{cliente}.pedidosweb.paqsystems.com`
- API: `https://backend.pedidosweb.paqsystems.com`
- header obligatorio: `X-Paq-Cliente: {cliente}`

En desarrollo local el middleware suele forzar el tenant `desarrollo`.

## 1.4 Arquitectura del backend (cómo pensar un cambio)

La API es REST pura. El contrato de respuesta es siempre el **envelope MONO**:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": { }
}
```

- `error` es un **entero** (`0` = OK; distinto de 0 = error controlado).
- `resultado` **siempre** es un objeto; nunca `null` ni ausente. Si no hay datos, `{}`.
- El status HTTP clasifica (401, 403, 404, 422…); el cuerpo no cambia de forma.

Capas (regla: el controller **no** tiene lógica de negocio):

| Capa | Carpeta típica | Responsabilidad |
|------|----------------|-----------------|
| Routes | `backend/routes/api.php` | Prefijo `v1`, middleware tenant + Sanctum |
| Controllers | `backend/app/Http/Controllers/...` | Validar entrada, llamar service, devolver envelope |
| Services | `backend/app/Services/PedidosWeb/` | Reglas: grabar, convertir, calcular, mail, visibilidad |
| Repositories | `backend/app/Repositories/PedidosWeb/` | Acceso a datos; no calculan totales ni permisos |
| Models | `backend/app/Models/PqPedidosweb*.php` | Mapeo Eloquent a tablas existentes del ERP/portal |
| OpenAPI | anotaciones `@OA` + `backend/app/OpenApi/` | Contrato Swagger |
| Tests | `backend/tests/Unit`, `Feature`, `Integration` | PHPUnit |

Flujo mental de un request autenticado:

1. Header `X-Paq-Cliente` → middleware `paq.tenant` resuelve la conexión SQL del tenant.
2. Bearer Sanctum → usuario de sesión.
3. Policy / guard de visibilidad: el usuario ve **solo su universo** (cliente propio, cartera del vendedor, o todo si es supervisor).
4. Service aplica reglas de negocio (estados, precios, IVA, bloqueo de edición).
5. Repository lee o escribe tablas `pq_pedidosweb_*`.
6. `ApiResponse::success` / `error` arma el envelope.

**Norma de acceso a datos:** las consultas de negocio deberían ir por stored procedures (regla BASE). En este producto hay una mezcla histórica Eloquent/Query Builder documentada en las TR; el código **nuevo** de negocio no debe ampliar SQL ad-hoc “por comodidad”. Lecturas SQL Server usan `NOLOCK` / `READ UNCOMMITTED` de forma controlada (stock informativo puede ver datos no confirmados). Escrituras restauran `READ COMMITTED` en transacción.

**Nunca** ejecutar bootstrap destructivo (`DROP` de tablas `pq_pedidosweb_*`) sobre la base del `.env` (habitualmente `Ankas_del_sur`) sin consentimiento explícito y literal del usuario en el chat.

## 1.5 Arquitectura del frontend (cómo pensar una pantalla)

El frontend es una SPA. Tras el login, el **shell** (layout, menú lateral, avatar) es capacidad GEN. Cada proceso de negocio vive en `frontend/src/features/`.

Convenciones que no se negocian:

- **DevExtreme obligatorio** para controles interactivos (`SelectBox`, `TextBox`, `Button`, `Popup`, `DataGrid`, `List`, etc.). No introducir `<select>` / `<button>` nativos si DX cubre el caso.
- **Todo texto visible sale de i18n** (`frontend/src/locales/es.json` y pares `en`, `pt`, `fr`, `it`). Idioma por defecto: español.
- **`data-testid` estables** vía `inputAttr` / `elementAttr`; los tests no se atan al DOM interno de DevExtreme.
- Variables, métodos y propiedades en **camelCase**.
- En mobile, si la pantalla es de consulta, el patrón es **kardex** (lista de tarjetas), no DataGrid de escritorio. Pivot, importación Excel, ABM de seguridad y “abrir en pestaña nueva” están **excluidos** de native.

Rutas de dominio (alineadas al menú): `frontend/src/features/menu/mvpMenuRoutes.ts`.

Si el cambio toca **web y puede usarse en mobile**, hay que evaluar `isNativeApp()` y la policy `pedidosWebMobilePolicy.ts`.

## 1.6 Tenancy, login y visibilidad

PedidosWeb es **MONO**: no hay selector de empresa ni header `X-Company-Id`. El tenant se resuelve **antes** del login.

Cada usuario de login se asocia a **un** cliente **o** a **un** vendedor; nunca a ambos ni a varios.

| Perfil funcional | Qué ve |
|------------------|--------|
| **Cliente** | Solo su propia información; en carga el cliente está fijo. |
| **Vendedor** | Solo clientes de su cartera. |
| **Supervisor** | Todos los clientes del tenant. |

Los ítems de menú salen de `GET /api/v1/user/menu` según permisos (`pq_menus` + matriz de roles). Un vendedor “acotado” puede tener un subconjunto (carga, ingresados, presupuestos, dashboard).

Auth (Sanctum):

- `POST /api/v1/auth/login` — usuario + contraseña (+ tenant por header).
- Recuperación y reset de contraseña.
- Cambio de contraseña autenticado.
- Expiración de sesión por inactividad (parámetro ERP de minutos).
- Mobile: el campo **tenant (empresa) va primero** en la UI; después usuario y contraseña.

No hay 2FA ni login social en el MVP.

## 1.7 Modelo de datos (lo mínimo para no perderse)

Prefijo de tablas: `pq_pedidosweb_*`. Los nombres de tablas y campos se **conservan** porque la base es capa de integración con Tango.

Comprobante = pedido o presupuesto. Ambos viven en las mismas tablas:

- `pq_pedidosweb_pedidoscabecera` — una fila por comprobante (`cod_pedido` es GUID interno).
- `pq_pedidosweb_pedidosdetalle` — renglones.

El **número visible** es secuencial único por tenant (pedidos y presupuestos comparten secuencia).

Estados de cabecera (hay que memorizarlos; gobiernan casi toda la operatoria):

| Estado | Significado |
|-------:|-------------|
| **-1** | Pedido en modificación web (bloqueo frente a descarga ERP). Vigencia: última actividad + `MinutosWeb`. |
| **0** | Pedido ingresado en el portal, aún no descargado. |
| **1** | Pedido pendiente en ERP. |
| **2** | Pedido cerrado/cumplido en ERP. |
| **3** | Pedido facturado en ERP (aparece en dashboard “mes en curso”). |
| **98** | Presupuesto **cerrado** (conversión, rechazo u otro cierre). No se borra. |
| **99** | Presupuesto **activo**. |

Maestros que alimenta el ERP (lectura en el portal): clientes, direcciones de entrega, contactos, vendedores, artículos, condiciones de venta, transportes, listas de precios, descuentos, stock, deuda, cheques, historial de ventas, parámetros generales (`PQ_parametros_gral`).

El detalle de columnas está en `docs/02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md`. DDL idempotente de ajustes: `backend/scripts/sql/` y `docs/Migraciones-en-forge.md`.

## 1.8 Cómo se desarrolla de verdad: OpenSpec

En este equipo **no se “codea de oído”** una feature de negocio. El circuito es:

```text
Producto (docs/02-producto)
    → A  SPEC (docs/05-open-spec)
    → B  HU  (docs/03-historias-usuario)
    → C  TR  (docs/04-tareas)
    → D  Implementación
    → E  Tests
    → F  Verificación vs documentos
```

Correcciones de control de calidad (PQ) entran por `docs/00-ControlCalidad/` y recorren **G → D → E → F → I** (volcado a updates, código, tests, cierre, unificación).

Convención de IDs:

| Ámbito | Prefijo | Ejemplo |
|--------|---------|---------|
| Generalidades (GEN) | `SPEC-001`, `HU-GEN`, `TR-GEN` | Login, grillas, Excel, mobile scaffold |
| PedidosWeb | `SPEC-101`, `HU-101`, `TR-SPEC-101` | Carga, consultas, asistente, Excel masivo |

Mientras un cambio de alcance está en curso, **no se edita el SPEC/HU/TR base**: se escribe un `*-update.md` en `docs/.../updates/`. Recién en Parte I se unifica.

Si estás por implementar una TR, el orden mental es: leer SPEC (fuente de verdad) → HU (criterios de aceptación) → TR (pasos técnicos) → código. Si el SPEC y el código discrepan, gana el SPEC hasta que haya un update explícito.

## 1.9 Mapa de carpetas que vas a tocar

### Backend

| Ruta | Qué hay |
|------|---------|
| `backend/routes/api.php` | Todas las rutas `/api/v1` |
| `backend/app/Http/Controllers/Api/V1/PedidosWeb/` | Pedidos, presupuestos, consultas, carga IA |
| `backend/app/Services/PedidosWeb/` | Núcleo de negocio |
| `backend/app/Services/ExcelImport/` | Motor GEN-14 + handlers PedidosWeb |
| `backend/app/Services/Visibility/` | Universo visible por perfil |
| `backend/app/Services/ChatAssistant/` | Chat documental BYOK |
| `backend/config/paqsuite_mvp.php` | Menú, roles seed, flags (`PIVOTS_ENABLED`, `EXCEL_IMPORT_ENABLED`, admin) |
| `backend/storage/api-docs/api-docs.json` | Spec OpenAPI generada |

### Frontend

| Ruta | Qué hay |
|------|---------|
| `frontend/src/features/pedidos/` | Carga, importación masiva, asistente IA de carga |
| `frontend/src/features/consultas/` | Deuda, cheques, historial, stock |
| `frontend/src/features/presupuestos/` | Listados y tratativas |
| `frontend/src/features/auth/` | Login, inactividad, password |
| `frontend/src/features/chatAssistant/` | Chat de ayuda |
| `frontend/src/features/excelImport/` | UI GEN-14 |
| `frontend/src/features/admin/security/` | ABM roles/permisos (flag) |
| `frontend/src/features/mobile/` | Policy de rutas native |
| `frontend/src/locales/` | i18n |

### Docs

| Ruta | Qué hay |
|------|---------|
| `docs/02-producto/PedidosWeb/` | Definición funcional y UI |
| `docs/05-open-spec/101-PedidosWeb/` | SPEC de slices |
| `docs/03-historias-usuario/101-PedidosWeb/` | HU |
| `docs/04-tareas/101-PedidosWeb/` | TR y cierres D/E/F |
| `docs/99-manual-usuario/` | Manuales de usuario / corpus del chat |
| `docs/06-operacion/` | OpenAPI, runbooks |

## 1.10 Tests y calidad

- **Backend:** PHPUnit (`backend/tests`). Unitarios de services, Feature de endpoints (envelope, 401/403, happy path), algún Integration contra SQL.
- **Frontend:** Vitest junto a los componentes (`*.test.ts` / `*.test.tsx`).
- Al tocar API: actualizar OpenAPI (estructura de `resultado` tipada; no dejar envelope genérico vacío) y, si existe, el test de cobertura OpenAPI.
- Tras implementación de una TR: Parte E (suite) y Parte F (alineación SPEC/HU/TR ↔ código).

No se hace commit ni push sin autorización explícita. `main` solo se actualiza por PR desde `develop`.

## 1.11 Flags de producto (`.env`)

Algunas capacidades se encienden por tenant/deploy:

| Variable (concepto) | Efecto |
|---------------------|--------|
| `PIVOTS_ENABLED` / `PIVOT_LAYOUTS_ENABLED` | Vista pivot en informes |
| `EXCEL_IMPORT_ENABLED` | Importación Excel (individual en carga + masiva) |
| `ADMIN_SECURITY_UI_ENABLED` | ABM de roles y permisos en web |

Sin el flag, la UI y a veces el menú no exponen el proceso aunque el código exista.

## 1.12 Por dónde empezar si tenés que programar mañana

1. Levantar backend y frontend; abrir OpenAPI y el health.
2. Loguearte y recorrer el menú con un usuario **supervisor** (ves todo) y uno **vendedor** (ves recorte).
3. Leer `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` (qué es el producto) y el SPEC madre `docs/05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md`.
4. Para un bug o mejora: no parchear a ciegas. Si cambia el “qué”, hay SPEC-update (Parte G / L). Si solo es implementación de una TR ya apta, Parte D.
5. Respetar visibilidad: un endpoint nuevo que liste clientes o comprobantes **debe** filtrar por el universo del usuario (JOIN/EXISTS; nunca `whereIn` de miles de códigos).

---

# Parte 2 — Prestaciones del funcionamiento

Esta parte describe **qué puede hacer el sistema hoy**, agrupado como lo ve un usuario o un integrador. El detalle de pantallas está en los manuales de `docs/99-manual-usuario/`.

## 2.1 Acceso, sesión y preferencias

- Login con usuario y contraseña (en mobile: tenant + usuario + contraseña).
- Recuperación de contraseña por mail (mismo canal que el aviso al grabar un pedido).
- Cambio de contraseña desde el menú del avatar.
- Exigencia de cambio en el primer ingreso, si está configurado.
- Cierre automático por **inactividad**.
- Idiomas: español, inglés, portugués, francés, italiano.
- Apariencia (tema claro/oscuro u otros del catálogo GEN): Aplicar previsualiza; Confirmar persiste; Cancelar revierte.
- Preferencia de abrir procesos en **pestaña nueva** (solo web; prohibido en mobile).
- Layouts de grilla por proceso: guardar, compartir, activar. Los diseños propios se marcan con `(*)`.
- Menú lateral armado por el servidor según permisos.

## 2.2 Dashboard operativo

Pantalla habitual post-login. Indicadores del **universo visible**:

- Presupuestos activos (estado 99): cantidad, importe, unidades.
- Pedidos ingresados (estados 0 y -1, con exclusión si hay edición activa por `MinutosWeb`): cantidad, importe, unidades.
- Pedidos pendientes (estado 1): cantidad, importe, unidades.
- Cliente con mayor monto en presupuestos activos y en pedidos ingresados.
- Bloque **mes en curso**: un KPI por estado (99, 98, 0, 1, 2, 3).
- Accesos rápidos a las consultas relacionadas.
- Botón actualizar y marca de última actualización.

## 2.3 Carga de pedidos y presupuestos (pantalla única)

Ruta web: **Pedidos → Carga**. Una sola pantalla sirve para alta, edición, consulta, copia y conversión. Los botones **Grabar pedido** y **Grabar presupuesto** (más Cancelar) definen el destino.

### Cabecera

Al elegir cliente (vendedor/supervisor) o al entrar con cliente de sesión (perfil cliente), el sistema **inicializa** vendedor, perfil de pedido, condición de venta, transporte, lista de precios, dirección de entrega, leyendas 1–5 y bonificaciones desde el maestro y los parámetros.

En cabecera se ve también el **saldo de deuda** del cliente, con color:

- verde si está en cero o a favor del cliente;
- negro si hay saldo sin facturas vencidas;
- rojo si hay saldo vencido.

Si el saldo no es cero, un ícono abre un modal con los comprobantes pendientes (misma información que la consulta de deuda, sin export/pivot).

Leyendas 1 a 5: máximo **60 caracteres** Unicode. Si llega texto más largo (API, Excel o asistente IA), se **recorta**; no se rechaza el comprobante por eso.

### Renglones

- Búsqueda de artículos del catálogo (con stock/disponible según reglas de depósito y artículos no stockeables).
- Precio según lista; bonificación de línea; bonificación neta de cabecera.
- Recálculo de **precio unitario neto**, importes e IVA (el IVA se persiste en renglón y cabecera).
- Parámetro **Carga unidades de venta**: se pueden cargar bultos/unidades de venta y el sistema convierte a unidades de stock; el mail de confirmación muestra ambas columnas.
- Permisos de modificar precio, lista o descuentos: **no están hardcodeados**; salen de parámetros ERP (`ModificaPrecioV/S`, `ModificaBonArtV/S`, etc.). El perfil cliente no edita precio/lista/descuento de artículo.

### Grabación y edición

- Grabar pedido → estado **0**.
- Grabar presupuesto → estado **99**.
- Editar un pedido ingresado: se pasa a **-1** (bloqueo de descarga ERP) mientras hay actividad; al grabar o cancelar se libera. Si vence `MinutosWeb` sin actividad, otro usuario puede retomar.
- Eliminar: **solo pedidos en estado 0**. Los presupuestos **no se borran**; se cierran a 98.
- Al grabar o modificar se puede enviar **mail** de confirmación (parámetros ERP).

### Copia y conversiones

- **Copiar** un comprobante anterior como borrador de uno nuevo (sin persistir hasta grabar). El parámetro `ActualizarPrecioCopia` decide si se refrescan precios al copiar.
- **Presupuesto → pedido:** cierra el presupuesto en 98 con motivo de cierre exitoso (`CodMotivoCierreExitoso`) y genera el pedido trazado (`cod_presupuesto_origen` + registro en cierres).
- **Pedido → presupuesto:** solo si el pedido está en estado **0**; el resultado es un presupuesto 99.

## 2.4 Consultas de comprobantes

| Proceso | Qué lista |
|---------|-----------|
| Pedidos ingresados | Estados 0 y -1 (universo visible) |
| Pedidos pendientes | Estado 1 |
| Detalle de pedidos | Cabecera + renglones en una grilla plana (útil para análisis) |
| Presupuestos ingresados | Activos (99) y cerrados (98), en vistas o filtros separados |

Desde los listados, según permiso, se puede abrir en carga para editar, copiar, convertir o (pedidos estado 0) eliminar.

Las grillas soportan orden, filtros, columnas, agrupación, totalizadores, layouts guardados y **exportación a Excel**. PDF de consultas queda fuera del MVP (emisión GEN-15).

## 2.5 Informes comerciales

Datos que el ERP deposita en el portal; el portal los consulta, no los “calcula de cero”.

| Informe | Contenido |
|---------|-----------|
| **Deuda** | Comprobantes con saldo, vencimiento, acumulado; `fecha_proceso` en carátula |
| **Cheques** | Cheques en cartera o aplicados con fecha posterior al día |
| **Historial de ventas** | Período acotado por `DiasVentasDetalladas`; detalle en modal |
| **Stock** | Disponibilidad; el “disponible base” consolida por código base cuando aplica |

Si el tenant tiene pivots encendidos, Deuda, Cheques, Stock, Historial y Detalle de pedidos ofrecen conmutador **Grilla / Pivot** (diseños guardados y exportación). En mobile estos informes son **kardex**, sin pivot.

## 2.6 Tratativas y cierre de presupuestos

- Sobre un presupuesto **activo (99)** se pueden registrar **tratativas** (seguimiento comercial mínimo; no es un CRM).
- **Cierre/rechazo:** el usuario elige un motivo de catálogo `tipo_cierre = negativo`; el presupuesto pasa a **98**.
- No hay cierre parcial ni “positivo por renglón” en el MVP. El cierre positivo de conversión a pedido usa el motivo parametrizado, sin que el usuario lo elija.

## 2.7 Importación Excel

Capacidad GEN-14, procesos de host:

1. **Pedido individual** (`PEDIDO_INDIVIDUAL`): desde la pantalla de carga (altas nuevas). Se descarga plantilla, se sube el archivo, se validan filas y se hidrata la pantalla para revisar y grabar.
2. **Pedido masivo** (`PEDIDO_MASIVO`): pantalla propia. Agrupa filas en varios pedidos, muestra grilla de staging, permite grabar en lote y consultar un **borrador** en carga solo lectura.
3. **Historial de importaciones** en menú General.

Ambos procesos recortan leyendas a 60 y respetan las mismas validaciones de negocio que la carga manual (cliente visible, artículos, precios, etc.).

## 2.8 Inteligencia artificial

Hay **dos** asistentes distintos; no se mezclan.

### Chat Asistente IA (ayuda)

Se abre desde el avatar, en pestaña nueva (web). Responde sobre **manuales y documentación** (corpus en `docs/99-manual-usuario/`). **No** ejecuta acciones ni consulta el ERP real. El usuario configura proveedor/modelo/clave (**BYOK**, GEN-16).

### Asistente IA de carga (operativo)

Panel colapsable en la pantalla de carga. Requiere BYOK y permiso de carga. Acepta texto, dictado continuo y **imagen**. Puede:

- elegir o cambiar cliente;
- completar cabecera;
- proponer renglones y cantidades;
- consultar stock, deuda, cheques e historial del cliente de la sesión de carga;
- disparar la grabación (el usuario confirma en UI).

Endpoint dedicado: `POST /api/v1/pedidos/carga/asistente/turn`. No usa el corpus del chat documental.

## 2.9 Mail

Al crear o modificar un pedido/presupuesto, si los parámetros lo habilitan, se envía un mail de confirmación (cabecera, renglones, bultos/unidades si aplica). No adjunta PDF en el MVP. El canal es el mismo que “olvidé mi contraseña”.

## 2.10 Logs de integración

Consulta de bitácora de sincronización portal ↔ ERP (`pq_pedidosweb_logs_integracion`). Es una pantalla de soporte, no de operatoria diaria.

## 2.11 Consulta de parámetros

Pantalla de solo lectura (`/general/parametros`) sobre `PQ_parametros_gral` y afines. El ABM de parámetros no es de este portal: se administran en ERP o herramientas internas.

Parámetros que el programador se va a cruzar seguido: `MinutosWeb`, `DiasVentasDetalladas`, `CargaUnidadesVenta`, `ActualizarPrecioCopia`, `CodMotivoCierreExitoso`, flags de modificación de precio/bonificación por perfil, inclusión de no stockeables (informativo en este portal; lo usa quien alimenta el maestro de artículos).

## 2.12 Administración de seguridad (opcional)

Si `ADMIN_SECURITY_UI_ENABLED` está activo, el menú **Seguridad** permite ABM de roles y permisos (GEN-06). Está **excluido de mobile**. En muchos deploys de producción el alta de usuarios sigue siendo interna/ERP.

## 2.13 Mobile (Capacitor)

Misma API, mismo frontend compilado a Android/iOS.

| Release | Qué incluye |
|---------|-------------|
| v1 (`v1.2.0-mobile`) | Scaffold, login tenant-first, consulta stock kardex |
| v2 (`v1.2.1-mobile`) | Listados y consultas en kardex |
| v3 (`v1.2.2-mobile`) | Carga de pedidos/presupuestos adaptada a táctil |

Pantalla de configuración en dispositivo: override de URL de API + test de health. El **tenant no se configura en el engranaje**; se pide en el login.

Excluido en native: pivots, Excel, admin de seguridad, abrir en pestaña nueva.

## 2.14 Circuito con el ERP (visión de sistema)

```text
ERP (Tango)
   │  sincroniza maestros, stock, deuda, cheques, historial, parámetros
   ▼
Base SQL del tenant (pq_pedidosweb_*)
   ▲
   │  el portal (web / mobile / GALO) graba pedidos y presupuestos
   │
PedidosWeb API
   │
   └── proceso ERP descarga pedidos en estado descargable (no -1)
```

El portal no “habla Tango” en cada click: trabaja contra su base. Por eso un pedido en **-1** no debe bajar al ERP, y un pedido ya en 1/2/3 no se edita ni se borra como si fuera un alta web.

---

# Anexo A — Integración del aplicativo GALO

## A.1 Qué es GALO respecto de PedidosWeb

**GALO** es un aplicativo **externo** (no forma parte de este repositorio). No reemplaza al portal: es un **consumidor de la misma API REST** de PedidosWeb.

GALO utiliza:

1. las **API de lectura de maestros y tablas** (catálogo OpenAPI tag **«Maestros y Tablas»**), para conocer clientes, artículos, condiciones comerciales y demás datos de referencia del tenant;
2. la **API de grabación de pedidos**, para **generar pedidos en nuestra base** (`pq_pedidosweb_pedidoscabecera` / `pq_pedidosweb_pedidosdetalle`) desde su propia interfaz.

Los pedidos que origina GALO entran al mismo circuito que los cargados a mano en la web o en mobile: mismos estados, misma visibilidad, misma descarga posterior al ERP, mismos mails si el tenant los tiene activos. No hay una “tabla paralela” ni un modo especial de persistencia para GALO.

## A.2 Cómo se autentica (igual que el portal)

Toda llamada de negocio exige:

| Pieza | Rol |
|-------|-----|
| Header `X-Paq-Cliente` | Identifica el tenant (empresa) y selecciona la base SQL |
| Bearer Sanctum (`Authorization: Bearer …`) | Usuario con perfil comercial y permisos (`pw_cargapedidos`, consulta de maestros, etc.) |
| Envelope `{ error, respuesta, resultado }` | Única forma de respuesta |

Sin tenant válido → 400. Sin token → 401. Sin permiso o fuera de cartera → 403. Cliente/artículo inexistente o no visible → 404.

El login es `POST /api/v1/auth/login`. GALO debe persistir el token y renovar o re-loguear cuando expire (incluida la política de inactividad del tenant).

El contrato canónico —paths, schemas, ejemplos— está en **OpenAPI / Swagger UI**. Cualquier integración nueva debe leer esa spec, no copiar payloads de memoria.

## A.3 APIs de lectura de maestros y tablas

Grupo OpenAPI **Maestros y Tablas**. Sirven para armar un pedido con datos reales del tenant. La visibilidad de clientes (y por tanto de lo que se puede grabar) es la del **usuario autenticado**.

| Método y path | Para qué sirve |
|---------------|----------------|
| `GET /api/v1/clientes` | Listado de clientes visibles. Incluye nodo **`contactos`** (`pq_pedidosweb_clientescontactos`) pensado para consumidores terceros; la UI de PedidosWeb **no** usa ese nodo. |
| `GET /api/v1/clientes/{codCliente}` | Ficha de un cliente visible (también con contactos). |
| `GET /api/v1/clientes/{codCliente}/cabecera-inicial` | Valores por defecto de cabecera + catálogos asociados al elegir ese cliente (equivalente a lo que hace la pantalla de carga al seleccionar cliente). |
| `GET /api/v1/clientes/{codCliente}/direcciones-entrega` | Direcciones de entrega del cliente. |
| `GET /api/v1/articulos` | Catálogo de artículos para renglones (filtros `q`, `lista_precios`, `page_size`; puede pedirse por códigos). |
| `GET /api/v1/perfiles` | Perfiles de pedido. |
| `GET /api/v1/condiciones-venta` | Condiciones de venta. |
| `GET /api/v1/transportes` | Transportes. |
| `GET /api/v1/listas-precios` | Listas de precios. |

Complemento habitual al armar cabecera (tag **Parametros**):

- `GET /api/v1/config/parametros-carga` — flags según perfil comercial (qué se puede editar).
- `GET /api/v1/config/parametros` — consulta informativa de parámetros.

GALO **no necesita** (y no debería reimplementar) las pantallas de consultas, pivots, Excel, asistente IA ni el ABM de seguridad para cumplir su caso de uso de generar pedidos. Esas APIs existen para el portal; el anexo se limita al uso declarado: **leer maestros y grabar pedidos**.

## A.4 API de grabación de pedidos

Hay dos superficies que persisten comprobantes. Ambas escriben las mismas tablas.

| Método y path | Uso |
|---------------|-----|
| `POST /api/v1/pedidos` | Alta de **pedido** (estado 0). Body tipado: cabecera + renglones. Requiere permiso de alta sobre `pw_cargapedidos`. |
| `PUT /api/v1/pedidos/{cod_pedido}` | Modificación de un pedido ya existente (reglas de estado y bloqueo -1). |
| `POST /api/v1/comprobantes/grabar` | Canal **canónico** documentado en SPEC-101-05 para alta, modificación y conversiones (pedido o presupuesto) desde la matriz de la pantalla de carga. |

Para el caso GALO —**generar pedidos nuevos en nuestra base**— el path natural es **`POST /api/v1/pedidos`** (o `comprobantes/grabar` con destino pedido, si el integrador sigue el canal canónico del portal). El resultado exitoso deja el comprobante en estado **0**, listo para el circuito ERP, con el mismo validador de negocio que la web (cliente visible, artículos, importes, leyendas recortadas a 60, IVA, etc.).

Lectura de lo grabado:

- `GET /api/v1/pedidos/{cod_pedido}` — pedido con detalle.

No forma parte del alcance declarado de GALO en este anexo: presupuestos, tratativas, Excel, asistente IA, ni eliminación masiva. Si GALO ampliara su uso, hay que documentarlo y respetar los mismos permisos y estados.

## A.5 Reglas que GALO hereda sí o sí

- Un pedido grabado por GALO **cuenta** en dashboard, listados y descarga ERP igual que uno web.
- El usuario del token **no puede** grabar un cliente fuera de su cartera (vendedor) ni “otro” cliente (perfil cliente).
- Validaciones de negocio (precios, bonificaciones, cantidades, unidades de venta, no stockeables) son las del service de grabación, no un atajo.
- Leyendas 1–5: máximo 60 caracteres; exceso recortado.
- OpenAPI es la fuente de los nombres de campos del JSON (`cabecera`, `renglones`, etc.). Ante duda, Swagger gana sobre este manual.

## A.6 Dónde mirar en el código

| Tema | Dónde |
|------|--------|
| Rutas | `backend/routes/api.php` |
| Tag OpenAPI maestros | `VisibilityDataController`, `CatalogosReferencialesController`, `ArticuloController`, `ClienteCabeceraController`, `PedidosWebOpenApiPaths` |
| Grabación pedido | `PedidoController` + `PedidoService` / canal `ComprobanteController::grabar` |
| Contactos en API clientes (terceros) | `pq_pedidosweb_clientescontactos` — SPEC-101-02 / CC PQ #11 |
| Envelope | `App\Http\Responses\ApiResponse` |

---

## Documentos de cabecera (si hace falta profundizar)

| Necesitás… | Abrí… |
|------------|--------|
| Definición de producto | `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` |
| SPEC madre y slices | `docs/05-open-spec/101-PedidosWeb/` |
| Modelo de datos | `docs/02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md` |
| Operatoria de usuario | `docs/99-manual-usuario/PedidosWeb.md` y `Generalidades.md` |
| Estados y conversiones | `docs/99-manual-usuario/PedidosWeb-circuito-estados.md` |
| Envelope API | `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md` |
| Metodología OpenSpec | `docs/_base/_OPEN-SPEC-METODOLOGIA.md` |
| Contrato vivo de endpoints | `/api/documentation` (Swagger) |

Este manual describe el sistema **tal como está implementado al 1 de septiembre de 2026**. Si un SPEC-update o un control de calidad cambia el alcance, prevalecen el SPEC unificado y OpenAPI.
