# Presupuestos — Tratativas, motivos de cierre y estadísticas

| Campo | Valor |
|-------|--------|
| **Estado** | Borrador funcional — guía de desarrollo |
| **Ámbito** | PedidosWeb — circuito comercial de presupuestos (estados **99** activo / **98** cerrado) |
| **Prioridad épica** | **Must** (cierre/rechazo + conversión) · **Should** (tratativas + estadísticas) |
| **Referencias** | [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md) §15–§16, §19 · [PedidosWeb_Modelo_Datos_Final.md](PedidosWeb_Modelo_Datos_Final.md) §7 · [TR-SPEC-101-12](../../04-tareas/101-PedidosWeb/TR-SPEC-101-12-tratativas-cierre.md) |
| **Implementación actual (referencia)** | Backend: `PresupuestoCierreService`, `TratativaService`, `MotivoCierreController` · Frontend: `PresupuestoCierreDialog`, `TratativasPage` (placeholder) |

---

## 1) Objetivo

Completar el **circuito comercial mínimo** sobre presupuestos sin convertir PedidosWeb en un CRM:

- **Cerrar** presupuestos (rechazo, conversión a pedido) con trazabilidad en `pq_pedidosweb_presupuestos_cierres`.
- **Seguir** presupuestos activos con **tratativas** (contactos, próximos pasos, resultado).
- **Administrar** catálogos (motivos de cierre, tipos/resultados de tratativa) y parámetros ERP necesarios.
- **Medir** desempeño comercial con indicadores y gráficos acotados.

> **Regla de producto (no negociable):** no existe **DELETE** físico de presupuestos. «Eliminar» en lenguaje de usuario = **cerrar/rechazar** → estado **98** + registro de cierre.

---

## 2) Estado actual (baseline en repo)

| Capacidad | Backend | Frontend | Observación |
|-----------|---------|----------|-------------|
| Catálogo motivos cierre (lectura) | `GET /api/v1/motivos-cierre` | Usado en `PresupuestoCierreDialog` | Solo **consulta**; sin ABM |
| Cerrar/rechazar presupuesto 99→98 | `POST /api/v1/presupuestos/{cod}/cerrar` | Popup en grilla activos | Motivo **negativo** obligatorio |
| Conversión presupuesto→pedido | `PedidoService` + `CodMotivoCierreExitoso` | Flujo carga «Grabar pedido» | Motivo **positivo** paramétrico; sin UI |
| Alta/listado tratativas por presupuesto | `GET/POST .../tratativas` | **No** (página placeholder) | Historial append-only; sin «tratativa en curso» |
| ABM motivos de cierre | — | — | Pendiente |
| ABM tipos/resultados tratativa | — | — | Pendiente |
| Gestión tratativas (panel operativo) | — | `TratativasPage` vacía | Pendiente |
| Dashboard estadísticas presupuesto | KPIs operativos §19 parcial | `DashboardPage` | Sin gráficos motivos/tratativas/tiempo de vida |
| Esquema BD cliente | `PedidosWebSchemaBootstrap` (auto `CREATE`/`ALTER` no destructivo) | — | Ver script manual: `backend/scripts/sql/alter-pq-pedidosweb-pedidosdetalle-portal.sql` |

---

## 3) Modelo de datos (resumen)

Tablas involucradas — detalle en [PedidosWeb_Modelo_Datos_Final.md](PedidosWeb_Modelo_Datos_Final.md) §7.

| Tabla | Rol |
|-------|-----|
| `pq_pedidosweb_motivos_cierre` | Catálogo motivos (`tipo_cierre`: `positivo` \| `negativo`; MVP sin `parcial`) |
| `pq_pedidosweb_presupuestos_cierres` | Registro formal de cada cierre (rechazo o conversión) |
| `pq_pedidosweb_tratativas` | Historial de seguimiento por presupuesto activo |
| `pq_pedidosweb_tratativas_resultados` | Catálogo de tipos/resultados de tratativa |
| `pq_pedidosweb_pedidoscabecera` | `estado` 99/98; trazabilidad conversión vía `cod_presupuesto_origen` en pedido nuevo |
| `PQ_parametros_gral` | `CodMotivoCierreExitoso` (y futuros parámetros de tratativa) |

### 3.1 Ampliación recomendada del modelo (tratativa «en curso»)

El modelo MVP guarda tratativas como **filas históricas** sin estado. Para la gestión operativa propuesta (lista con tratativa vigente + cierre al abrir una nueva), conviene **una** de estas opciones (elegir en TR antes de codificar):

| Opción | Cambio | Pros | Contras |
|--------|--------|------|---------|
| **A (recomendada)** | Columna `cerrada bit` + `fecha_cierre datetime` en `pq_pedidosweb_tratativas` | Consulta simple de «activa»; historial intacto | Migración en N bases cliente |
| **B** | Convención: la última por `fecha_hora` es la vigente | Sin DDL | Ambigua si hay empates; difícil auditar cierre explícito |
| **C** | Tabla `pq_pedidosweb_tratativas_activas` (1:1 con presupuesto) | Separación clara | Más tablas y sincronización |

**Recomendación:** opción **A** — al crear tratativa nueva, el servicio cierra la anterior (`cerrada = 1`, `fecha_cierre = now()`) en la misma transacción.

---

## 4) Parámetros generales (ERP)

| Clave (`PQ_parametros_gral`) | Tipo | Uso | Estado |
|------------------------------|------|-----|--------|
| **`CodMotivoCierreExitoso`** | `I` | `id_motivo` con `tipo_cierre = positivo` y `activo = 1`. Se aplica **automáticamente** al convertir presupuesto→pedido. | Implementado en backend; **sin pantalla de administración web** |
| **`CodResultadoTratativaConversion`** *(propuesto)* | `I` | `id_resultado` por defecto al cerrar tratativa por conversión a pedido (opcional) | No implementado |
| **`DiasAlertaTratativaVencida`** *(propuesto)* | `I` | Días sin actividad / con `proxima_fecha` vencida para resaltar en gestión | No implementado |

**Pantalla de parámetros:** hoy existe consulta de solo lectura (`consulta-parametros.md`). Para editar `CodMotivoCierreExitoso` en web haría falta un slice admin (ver §8) o mantener edición solo en ERP/SQL Server.

---

## 5) Módulos funcionales — checklist de desarrollo

### 5.1 ABM de tipos / resultados de tratativa

**Tabla:** `pq_pedidosweb_tratativas_resultados`

| Ítem | Detalle |
|------|---------|
| **Objetivo** | Mantener catálogo de resultados (ej. «Llamado», «Visita», «Enviado mail», «Sin respuesta») usados al registrar tratativas. |
| **Pantalla** | Grilla DevExtreme + popup alta/edición; ruta sugerida: `/admin/tratativas-resultados` o submódulo en «Gestión presupuestos». |
| **Campos** | `descripcion` (obligatorio), `activo` (switch). |
| **Reglas** | No borrar físico si hay tratativas referenciando el `id_resultado` → **baja lógica** (`activo = 0`). Descripción única case-insensitive recomendada. |
| **API** | `GET/POST/PUT/PATCH` bajo `/api/v1/admin/tratativas-resultados` (o reutilizar patrón admin seguridad si aplica). |
| **Permisos** | Rol **supervisor/admin**; no expuesto a perfil **cliente**. |
| **i18n / testid** | `tratativasResultados.*`, `data-testid="tratativasResultadosGrid"`, etc. |
| **Seed mínimo** | 3–5 resultados activos por base cliente al deploy. |

**Estado:** pendiente (tabla existe en bootstrap; sin CRUD ni UI).

---

### 5.2 ABM de motivos de cierre

**Tabla:** `pq_pedidosweb_motivos_cierre`

| Ítem | Detalle |
|------|---------|
| **Objetivo** | Administrar motivos de cierre **positivos** (conversión automática) y **negativos** (rechazo manual). |
| **Pantalla** | Grilla con columnas: descripción, tipo (`positivo`/`negativo`), activo. Popup alta/edición. |
| **Reglas** | Debe existir **al menos un** motivo positivo activo si se usa conversión. No desactivar el motivo referenciado por `CodMotivoCierreExitoso` sin actualizar el parámetro. No DELETE si hay filas en `presupuestos_cierres`. |
| **API** | CRUD admin + mantener `GET /motivos-cierre` para uso comercial (solo activos, filtros `tipo_cierre`, `activo`). |
| **Validación deploy** | Script/check: parámetro `CodMotivoCierreExitoso` apunta a motivo positivo activo. |

**Estado:** solo `GET` comercial implementado.

---

### 5.3 Parámetro `CodMotivoCierreExitoso`

| Ítem | Detalle |
|------|---------|
| **Objetivo** | Garantizar conversión presupuesto→pedido sin pedir motivo al usuario. |
| **UI mínima** | En ABM motivos o pantalla «Parámetros PedidosWeb»: selector de motivo positivo activo; persiste en `PQ_parametros_gral`. |
| **Alternativa MVP** | Documentar edición SQL/ERP; validación en smoke post-deploy. |
| **Error de negocio** | Si parámetro inválido → `business.invalidMotivoCierre` (mensaje claro en UI). |

**Estado:** lógica backend OK; administración web pendiente.

---

### 5.4 «Eliminar» presupuesto → cierre con motivo (rechazo)

> Alineado a HU-101-027. No es DELETE.

| Ítem | Detalle |
|------|---------|
| **Disparadores UI** | Acción **Cerrar/Rechazar** en grilla presupuestos activos (estado 99); opcional desde detalle/carga en modo solo lectura si aplica. |
| **Modal** | `Popup` DevExtreme: presupuesto (número/cliente), `SelectBox` motivos **negativos** activos, `TextArea` observación opcional, Confirmar/Cancelar. |
| **Backend** | `POST /presupuestos/{cod}/cerrar` — ya implementado. |
| **Post-condición** | Presupuesto **98**; fila en `presupuestos_cierres`; desaparece de activos; visible en cerrados con detalle de cierre. |
| **Mobile** | Misma acción en kardex presupuestos si el proceso está en menú MVP mobile. |

**Estado:** implementado en web (grilla activos). Revisar copy: evitar la palabra «Eliminar» en UI; usar **Cerrar presupuesto** / **Rechazar**.

---

### 5.5 Gestión de tratativas (propuesta operativa)

Pantalla menú: **Gestión presupuestos → Tratativas** (`/presupuestos/tratativas` — hoy placeholder).

#### 5.5.a) Lista de presupuestos con tratativa en curso

| Elemento | Propuesta |
|----------|-----------|
| **Fuente** | Presupuestos **estado 99** visibles para el usuario + join tratativa vigente (`cerrada = 0` o última abierta). |
| **Columnas sugeridas** | Nº presupuesto, cliente, fecha presupuesto, importe total, **resultado tratativa** (tipo), **comentario resumen** (truncado), **próxima fecha**, **próxima acción**, vendedor. |
| **Orden default** | `proxima_fecha` ASC (vencidas primero), luego fecha presupuesto DESC. |
| **Filtros mínimos** | Cliente, rango fechas próxima acción, solo vencidas / sin tratativa. |
| **Indicadores** | Badge «Sin tratativa», «Vencida», «Hoy». |
| **Patrón UI** | `DataGrid` desktop; **kardex** en mobile (`ConsultaKardexMobileView`). |

**API nueva sugerida:** `GET /api/v1/presupuestos/tratativas/resumen` (lista agregada; evita N+1 por presupuesto).

#### 5.5.b) Nueva tratativa

| Elemento | Propuesta |
|----------|-----------|
| **Disparador** | Ícono/botón en fila de la grilla o desde detalle del presupuesto. |
| **Modal campos** | `SelectBox` resultado (catálogo §5.1), `TextArea` comentario (obligatorio), `DateBox` próxima fecha (opcional), `TextBox` próxima acción (opcional). |
| **Regla de negocio** | Si existe tratativa vigente → marcarla **cerrada** y crear nueva con `fecha_hora = now()`, `cod_usuario_web` sesión. |
| **API** | Extender `POST .../tratativas` o `POST .../tratativas/nueva` con transacción cerrar-anterior + insertar. |

#### 5.5.c) Historial de tratativas del presupuesto

| Elemento | Propuesta |
|----------|-----------|
| **Disparador** | Ícono historial en grilla gestión y en **Presupuestos ingresados** (activos). |
| **UI** | `Popup` o drawer con `List`/timeline: fecha-hora, usuario, resultado, comentario, próxima fecha/acción, estado (abierta/cerrada). |
| **API** | `GET .../tratativas` (existente); incluir flags `cerrada`, `fecha_cierre` si se adopta §3.1.A. |

#### 5.5.d) Accesos desde otros procesos

| Origen | Acción |
|--------|--------|
| Presupuestos ingresados (activos) | Ver / Editar / Convertir / **Cerrar** / **Tratativas** / Copiar |
| Carga comprobante (presupuesto 99) | Panel lateral o pestaña **Tratativas** (solo lectura + alta) |
| Presupuestos cerrados | Historial tratativas **solo lectura** |

---

### 5.6 Dashboard / tablero estadísticas de presupuestos

Extensión del [patron-dashboard-operativo-ui.md](patron-dashboard-operativo-ui.md) o nueva sección `/dashboard/presupuestos`.

#### 5.6.a) Gráfico motivos de cierre

| Ítem | Detalle |
|------|---------|
| **Fuente** | `pq_pedidosweb_presupuestos_cierres` + `motivos_cierre` |
| **Métrica** | Cantidad y % por `id_motivo` / descripción |
| **Filtros** | Rango fechas cierre, vendedor, cliente (según visibilidad) |
| **Desglose** | Toggle positivo / negativo / todos |
| **Visual** | `PieChart` o `BarChart` DevExtreme |
| **API** | `GET /api/v1/dashboard/presupuestos/motivos-cierre` |

#### 5.6.b) Gráfico tratativas realizadas

| Ítem | Detalle |
|------|---------|
| **Fuente** | `pq_pedidosweb_tratativas` + `tratativas_resultados` |
| **Métrica** | Cantidad y % por `id_resultado` |
| **Filtros** | Rango `fecha_hora`, vendedor del presupuesto |
| **API** | `GET /api/v1/dashboard/presupuestos/tratativas` |

#### 5.6.c) Tiempo promedio de vida del presupuesto

| Ítem | Detalle |
|------|---------|
| **Definición** | Para presupuestos ya en **98**: `fecha_cierre` − `fecha_creacion` (cabecera). Promedio en días (o horas). |
| **Segmentos** | Por vendedor; opcional por cliente top N |
| **Indicador** | KPI numérico + sparkline mensual opcional |
| **API** | `GET /api/v1/dashboard/presupuestos/tiempo-vida` |
| **Nota** | Requiere `fecha_creacion` poblada en cabecera; validar en bases ERP. |

#### 5.6.d) KPIs complementarios (producto §19)

| KPI | Descripción |
|-----|-------------|
| Tasa de conversión | % presupuestos cerrados con `cod_pedido_generado` vs rechazados |
| Tasa de cierre por vendedor | Ranking operativo |
| Presupuestos sin tratativa | Cantidad en 99 sin ninguna tratativa registrada |
| Tratativas vencidas | `proxima_fecha < hoy` y presupuesto aún 99 |

---

## 6) Temas adicionales propuestos

| # | Tema | Motivo |
|---|------|--------|
| 1 | **Scripts SQL idempotentes de despliegue** | Tablas `motivos_cierre`, columnas detalle portal, seeds mínimos — replicar en cada base cliente (ver `backend/scripts/sql/`). |
| 2 | **Permisos y menú** | Procedimientos `pw_tratativaspresup`, ABM catálogos bajo grupo admin; matriz `Permiso_Alta/Modi/Baja/Repo`. |
| 3 | **Visibilidad comercial** | Estadísticas y listados filtrados por universo cliente/vendedor (`PedidosWebVisibilityGuard`). |
| 4 | **i18n y mensajes de error** | Claves `business.invalidMotivoCierre`, `presupuestos.tratativas.*`, `dashboard.presupuestos.*`. |
| 5 | **OpenAPI** | Documentar endpoints nuevos en L5-Swagger. |
| 6 | **Mobile** | Gestión tratativas en kardex; ABM catálogos **excluido** de native (alineado a exclusiones admin). |
| 7 | **Cierre automático de tratativas** | Al convertir o rechazar presupuesto, cerrar tratativa vigente (si existe). |
| 8 | **Consulta presupuestos cerrados** | Ya muestra motivo; agregar enlace a historial tratativas. |
| 9 | **Auditoría** | Usuario y fecha en cierre y en cada tratativa (ya en modelo; asegurar UI). |
| 10 | **Tests** | Feature: cerrar, convertir con motivo paramétrico, alta tratativa cierra anterior; E2E popup cierre. |
| 11 | **Manual de usuario** | Actualizar `docs/99-manual-usuario/PedidosWeb.md` § presupuestos. |
| 12 | **Exportación** | Export Excel de listado gestión tratativas (Could — patrón exportaciones transversal). |

---

## 7) Matriz de priorización sugerida

| Slice | Contenido | Prioridad | Dependencias |
|-------|-----------|-----------|--------------|
| **S1** | ABM motivos cierre + seed + validación `CodMotivoCierreExitoso` | **Must** | SQL deploy bases |
| **S2** | Ajuste copy UI «Cerrar» vs «Eliminar» + mensajes error | **Must** | — |
| **S3** | ABM resultados tratativa + seed | **Should** | S1 opcional |
| **S4** | Gestión tratativas (lista + alta + historial) + DDL `cerrada` | **Should** | S3 |
| **S5** | Tratativas desde presupuestos ingresados / carga | **Should** | S4 |
| **S6** | Dashboard gráficos motivos + tratativas + tiempo vida | **Should** | S1, S4 |
| **S7** | Admin parámetro `CodMotivoCierreExitoso` en web | **Could** | S1 |
| **S8** | Export Excel + alertas vencidas | **Could** | S4, S6 |

---

## 8) Contratos API (objetivo consolidado)

| Método | Path | Uso |
|--------|------|-----|
| GET | `/api/v1/motivos-cierre` | Catálogo comercial (filtros) |
| GET/POST/PUT | `/api/v1/admin/motivos-cierre` | ABM |
| GET/POST/PUT | `/api/v1/admin/tratativas-resultados` | ABM tipos tratativa |
| POST | `/api/v1/presupuestos/{cod}/cerrar` | Rechazo 99→98 |
| GET | `/api/v1/presupuestos/{cod}/tratativas` | Historial |
| POST | `/api/v1/presupuestos/{cod}/tratativas` | Nueva tratativa (cierra anterior) |
| GET | `/api/v1/presupuestos/tratativas/resumen` | Lista gestión |
| GET | `/api/v1/dashboard/presupuestos/motivos-cierre` | Estadísticas |
| GET | `/api/v1/dashboard/presupuestos/tratativas` | Estadísticas |
| GET | `/api/v1/dashboard/presupuestos/tiempo-vida` | Estadísticas |

*Conversión a pedido: sin endpoint dedicado; flujo grabación en `POST /api/v1/pedidos` con `cod_presupuesto_origen` (TR-SPEC-101-04).*

---

## 9) Criterios de aceptación transversales

1. Ningún flujo **borra** filas de `pq_pedidosweb_pedidoscabecera` por cierre comercial.
2. Solo presupuesto **99** admite alta de tratativas y cierre manual.
3. Presupuesto **98** es solo lectura en consultas comerciales.
4. Conversión usa **`CodMotivoCierreExitoso`** sin selector en pantalla.
5. Rechazo exige motivo **negativo** elegido por el usuario.
6. Catálogos administrables sin desplegar código (tras ABM implementado).
7. Estadísticas respetan **visibilidad** del usuario logueado.
8. UI con **DevExtreme** + **i18n** + `data-testid` estables.
9. Deploy en base cliente nueva: tablas/columnas/seeds documentados y verificables.

---

## 10) Checklist de implementación (para TR/HU)

### Catálogos y parámetros
- [ ] ABM `pq_pedidosweb_tratativas_resultados`
- [ ] ABM `pq_pedidosweb_motivos_cierre`
- [ ] UI o procedimiento documentado para `CodMotivoCierreExitoso`
- [ ] Seed mínimo por base + smoke conversión y rechazo

### Cierre de presupuesto
- [ ] Modal rechazo (motivo + observación) — revisar textos
- [ ] Detalle cierre en presupuestos cerrados
- [ ] Cierre tratativa vigente al convertir/rechazar

### Gestión de tratativas
- [ ] DDL tratativa vigente (`cerrada`, `fecha_cierre`) si se aprueba §3.1.A
- [ ] Lista resumen presupuestos + tratativa en curso
- [ ] Alta tratativa (cierra anterior)
- [ ] Historial por presupuesto
- [ ] Integración en presupuestos ingresados y/o carga

### Dashboard
- [ ] Gráfico motivos de cierre (% y cantidad)
- [ ] Gráfico tratativas por resultado (% y cantidad)
- [ ] Indicador tiempo promedio de vida
- [ ] Filtros período y visibilidad

### Transversal
- [ ] Permisos menú y API
- [ ] OpenAPI + tests Feature/E2E
- [ ] Mobile kardex (si aplica menú)
- [ ] Manual usuario + script SQL deploy

---

## 11) Referencias cruzadas

| Documento | Contenido |
|-----------|-----------|
| [HU-101-014](../../03-historias-usuario/101-PedidosWeb/HU-101-014-tratativas-presupuesto.md) | Tratativas Should |
| [HU-101-027](../../03-historias-usuario/101-PedidosWeb/HU-101-027-cierre-rechazo-presupuesto.md) | Cierre/rechazo Must |
| [HU-101-013](../../03-historias-usuario/101-PedidosWeb/HU-101-013-conversion-presupuesto-pedido.md) | Conversión + motivo paramétrico |
| [TR-SPEC-101-12](../../04-tareas/101-PedidosWeb/TR-SPEC-101-12-tratativas-cierre.md) | TR técnica slice |
| [TR-SPEC-101-11](../../04-tareas/101-PedidosWeb/TR-SPEC-101-11-consultas-ui.md) | Acciones grilla presupuestos |
| [TR-SPEC-101-14](../../04-tareas/101-PedidosWeb/TR-SPEC-101-14-dashboard.md) | Dashboard operativo |
| [consulta-comprobantes-cabecera.md](consulta-comprobantes-cabecera.md) | Columnas y acciones consultas |
| `backend/scripts/sql/alter-pq-pedidosweb-pedidosdetalle-portal.sql` | Columnas detalle en bases ERP |

---

## 12) Notas para despliegue multi-cliente

Al habilitar PedidosWeb en una base ERP existente, verificar como mínimo:

1. Tabla `pq_pedidosweb_motivos_cierre` con al menos 1 motivo **positivo** y 1 **negativo** activos.
2. Parámetro `CodMotivoCierreExitoso` en `PQ_parametros_gral` apuntando al motivo positivo.
3. Tablas `pq_pedidosweb_tratativas`, `pq_pedidosweb_tratativas_resultados`, `pq_pedidosweb_presupuestos_cierres`.
4. Columnas portal en `pq_pedidosweb_pedidosdetalle` (descripción e importes — script SQL del repo).

Sin (1) y (2), la **conversión presupuesto→pedido falla** aunque el resto del portal funcione.
