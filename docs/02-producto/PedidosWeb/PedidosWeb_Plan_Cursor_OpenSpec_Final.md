# Portal de Pedidos Web - Plan de trabajo para Cursor / OpenSpec

## 1. Principio de trabajo

No pedir a Cursor “hacer todo el sistema”. El proyecto debe construirse por especificaciones pequeñas, verificables y secuenciales.

Cada paso debe generar:

- código limitado al alcance solicitado,
- tests asociados cuando corresponda,
- sin mezclar capas,
- sin cambiar nombres de tablas existentes,
- sin inventar lógica ERP,
- sin modificar la base heredada salvo indicación expresa.

## 2. Orden sugerido de specs

**Codificación:** slices, HU y TR de PedidosWeb usan prefijo **`SPEC-101-xx`** (alineado a `docs/05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md`). HU: **`HU-101-xxx`**; TR: **`TR-SPEC-101-xx-*.md`** en `docs/04-tareas/101-PedidosWeb/`.

### SPEC-101-01 - Backend base Laravel

Objetivo: crear estructura base backend con arquitectura limpia.

Incluye:

- Controllers.
- Services.
- Repositories.
- DTOs.
- Models.
- Policies.
- Jobs.
- Events.
- SQL Server.
- Middleware tenant MONO: ver `docs/_base/resolucion-host-cliente-sql-mono.md`.

No incluye lógica de pedidos.

### SPEC-101-02 - Modelos Eloquent

Crear modelos para tablas existentes:

- pedidoscabecera.
- pedidosdetalle.
- clientes.
- clientesde.
- vendedores.
- articulos.
- stock.
- listas.
- precios.
- condiciones.
- transportes.

Respetar claves primarias y relaciones.

### SPEC-101-03 - Repositories

Crear repositories de acceso a datos:

- PedidoRepository.
- PedidoDetalleRepository.
- ClienteRepository.
- ArticuloRepository.
- ConsultaRepository.

Sin lógica de negocio.

### SPEC-101-04 - PedidoService

Implementar lógica real:

- crear pedido.
- editar pedido estado 0.
- eliminar pedido estado 0.
- crear presupuesto estado 99.
- editar presupuesto **estado 99** (activo).
- cerrar/rechazar presupuesto **estado 99 → 98** (con motivo y registro en `presupuestos_cierres`).
- convertir presupuesto a pedido (**presupuesto → 98**, pedido nuevo estado 0).
- convertir pedido a presupuesto.
- copiar comprobante.
- calcular totales.
- auditoría.

### SPEC-101-05 - Controllers REST

Crear endpoints JSON:

- POST /pedidos.
- PUT /pedidos/{id}.
- DELETE /pedidos/{id}.
- GET /pedidos.
- GET /pedidos/{id}.
- POST /presupuestos.
- PUT /presupuestos/{id} (solo estado 99).
- POST /presupuestos/{id}/cerrar (cierre negativo/positivo → estado 98).
- POST /presupuestos/{id}/convertir-a-pedido (presupuesto → 98).

Controllers sin lógica de negocio.

### SPEC-101-06 - Autenticación

Implementar:

- login.
- recuperación de contraseña.
- expiración por inactividad.
- asociación usuario-cliente o usuario-vendedor.
- middleware de autenticación.
- policies de visibilidad.

### SPEC-101-07 - Consultas

Endpoints:

- pedidos ingresados.
- pedidos pendientes.
- presupuestos ingresados.
- stock.
- deuda.
- cheques.
- historial ventas.

Con filtros básicos, paginación y preparación para exportación.

### SPEC-101-08 - Logs de integración

Crear:

- tabla/modelo logs_integracion.
- servicio de logging.
- endpoint de consulta.
- filtros por fecha, tipo y severidad.

### SPEC-101-09 - Frontend base

Crear estructura React + DevExtreme:

- layout principal.
- login.
- navegación.
- módulos iniciales.
- cliente API.
- manejo de sesión.

### SPEC-101-10 - Pantalla clave de pedidos

Crear pantalla de carga:

- cabecera.
- renglones dinámicos.
- autocompletar artículos.
- cálculo en tiempo real.
- totales e impuestos.
- guardar pedido.
- guardar presupuesto.

### SPEC-101-11 - Consultas frontend

Crear pantallas con grillas DevExtreme para:

- pedidos ingresados.
- pedidos pendientes.
- presupuestos activos (estado 99).
- presupuestos cerrados (estado 98, solo consulta).
- stock.
- deuda.
- cheques.
- historial.

### SPEC-101-12 - Tratativas y motivos de cierre

Crear:

- tablas nuevas.
- endpoints.
- pantalla simple de tratativas.
- cierre positivo/parcial/negativo (**presupuesto pasa a estado 98**).
- motivo obligatorio cuando corresponda.

### SPEC-101-13 - Mails

Implementar:

- mail al crear/modificar.
- texto simple.
- con/sin detalle según parámetro.
- destinatarios definidos.
- logging de errores de mail.

### SPEC-101-14 - Dashboard

Crear indicadores:

- tasa de cierre por vendedor.
- ranking motivos rechazo.
- artículos CORE sin movimiento.
- pedidos por vendedor.

### SPEC-101-15 - Tests

Agregar o completar:

- unitarios para cálculos.
- integración para repositories/services.
- E2E para login y carga de pedido.

## 3. Prompt maestro para Cursor

```markdown
Estás trabajando en el proyecto Portal de Pedidos Web.

Stack:
- Backend Laravel API REST.
- Frontend React + DevExtreme.
- SQL Server.
- Monorepo /backend y /frontend.

Reglas obligatorias:
- No modificar nombres de tablas heredadas.
- No inventar lógica ERP.
- Usar código en español.
- Controllers sin lógica de negocio.
- Services con reglas de negocio.
- Repositories solo acceso a datos.
- DTOs para entrada/salida.
- Policies para visibilidad por usuario.
- Tenant MONO: `docs/_base/resolucion-host-cliente-sql-mono.md`; constantes en OpenSpec §5.
- Mantener compatibilidad con Tango Gestión.
- Generar tests cuando el alcance lo requiera.

Tipos de usuario:
- Cliente: solo su información.
- Vendedor: solo clientes asignados.
- Supervisor: todos los clientes.

Estados:
- -1 control transitorio.
- 0 pedido ingresado.
- 1 pedido pendiente ERP.
- 2 pedido cerrado/cumplido.
- 98 presupuesto cerrado (conversión, cierre o rechazo).
- 99 presupuesto activo/ingresado.
```

## 4. Criterio para Historias de Usuario

Cada HU debe contener:

- Código HU.
- Título.
- Épica.
- Actor.
- Descripción en formato “Como / quiero / para”.
- Reglas de negocio.
- Criterios de aceptación.
- Casos negativos.
- Dependencias.
- Datos involucrados.
- Prioridad Must/Should/Could.

## 5. Épicas sugeridas

1. Seguridad y acceso.
2. Multiempresa.
3. Clientes y visibilidad.
4. Carga de pedidos/presupuestos.
5. Cálculos comerciales.
6. Conversión y cierre de presupuestos.
7. Tratativas.
8. Consultas comerciales.
9. Stock.
10. Mails y notificaciones.
11. Integración ERP.
12. Logs y monitoreo.
13. Dashboard.
14. Frontend y UX DevExtreme.
15. Calidad y tests.
16. Infraestructura y despliegue.

## 6. Primeras HU Must

Convención de archivo: `HU-101-xxx-nombre.md` en `docs/03-historias-usuario/101-PedidosWeb/`.

- HU-101-001 Login de usuario (`SPEC-101-06`).
- HU-101-002 Recuperación de contraseña (`SPEC-101-06`).
- HU-101-003 Resolución de tenant (`{cliente}.pedidosweb` → `frontend.pedidosweb`, `X-Paq-Cliente`, SQL). Fuente: `docs/_base/resolucion-host-cliente-sql-mono.md` (`SPEC-101-01`).
- HU-101-004 Selección de cliente según tipo de usuario.
- HU-101-005 Inicialización de cabecera desde cliente.
- HU-101-006 Carga de renglones con artículos.
- HU-101-007 Cálculo de bonificación neta.
- HU-101-008 Cálculo de precio neto e importes.
- HU-101-009 Grabación de pedido.
- HU-101-010 Grabación de presupuesto.
- HU-101-011 Edición de pedido ingresado.
- HU-101-012 Eliminación de pedido ingresado.
- HU-101-013 Conversión de presupuesto a pedido (`SPEC-101-12`).
- HU-101-014 Cierre/rechazo de presupuesto (estado 99 → 98 + `presupuestos_cierres`) (`SPEC-101-12`).
- HU-101-015 Consulta de pedidos ingresados.
- HU-101-016 Consulta de presupuestos activos (estado 99) y cerrados (estado 98).
- HU-101-017 Consulta de pedidos pendientes.
- HU-101-018 Consulta de stock.
- HU-101-019 Envío de mail al grabar (`SPEC-101-13`).
- HU-101-020 Log de integración (`SPEC-101-08`).

## 7. Recomendación de ejecución

Primero cargar en Cursor:

1. Definición conceptual final.
2. Modelo de datos final.
3. Este plan Cursor/OpenSpec.

Luego pedir:

```text
Generá las épicas y todas las historias de usuario del MVP respetando este contexto. No generes código todavía.
```

Después revisar manualmente las HU y recién entonces pasar a TR por slice (`TR-SPEC-101-xx-*.md`).
