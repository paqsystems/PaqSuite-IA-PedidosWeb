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

### SPEC-001 - Backend base Laravel

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
- Middleware multiempresa por subdominio.

No incluye lógica de pedidos.

### SPEC-002 - Modelos Eloquent

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

### SPEC-003 - Repositories

Crear repositories de acceso a datos:

- PedidoRepository.
- PedidoDetalleRepository.
- ClienteRepository.
- ArticuloRepository.
- ConsultaRepository.

Sin lógica de negocio.

### SPEC-004 - PedidoService

Implementar lógica real:

- crear pedido.
- editar pedido estado 0.
- eliminar pedido estado 0.
- crear presupuesto estado 99.
- editar presupuesto.
- convertir presupuesto a pedido.
- convertir pedido a presupuesto.
- copiar comprobante.
- calcular totales.
- auditoría.

### SPEC-005 - Controllers REST

Crear endpoints JSON:

- POST /pedidos.
- PUT /pedidos/{id}.
- DELETE /pedidos/{id}.
- GET /pedidos.
- GET /pedidos/{id}.
- POST /presupuestos.
- PUT /presupuestos/{id}.
- DELETE /presupuestos/{id}.
- POST /presupuestos/{id}/convertir-a-pedido.

Controllers sin lógica de negocio.

### SPEC-006 - Autenticación

Implementar:

- login.
- recuperación de contraseña.
- expiración por inactividad.
- asociación usuario-cliente o usuario-vendedor.
- middleware de autenticación.
- policies de visibilidad.

### SPEC-007 - Consultas

Endpoints:

- pedidos ingresados.
- pedidos pendientes.
- presupuestos ingresados.
- stock.
- deuda.
- cheques.
- historial ventas.

Con filtros básicos, paginación y preparación para exportación.

### SPEC-008 - Logs de integración

Crear:

- tabla/modelo logs_integracion.
- servicio de logging.
- endpoint de consulta.
- filtros por fecha, tipo y severidad.

### SPEC-009 - Frontend base

Crear estructura React + DevExtreme:

- layout principal.
- login.
- navegación.
- módulos iniciales.
- cliente API.
- manejo de sesión.

### SPEC-010 - Pantalla clave de pedidos

Crear pantalla de carga:

- cabecera.
- renglones dinámicos.
- autocompletar artículos.
- cálculo en tiempo real.
- totales e impuestos.
- guardar pedido.
- guardar presupuesto.

### SPEC-011 - Consultas frontend

Crear pantallas con grillas DevExtreme para:

- pedidos ingresados.
- pedidos pendientes.
- presupuestos.
- stock.
- deuda.
- cheques.
- historial.

### SPEC-012 - Tratativas y motivos de cierre

Crear:

- tablas nuevas.
- endpoints.
- pantalla simple de tratativas.
- cierre positivo/parcial/negativo.
- motivo obligatorio cuando corresponda.

### SPEC-013 - Mails

Implementar:

- mail al crear/modificar.
- texto simple.
- con/sin detalle según parámetro.
- destinatarios definidos.
- logging de errores de mail.

### SPEC-014 - Dashboard

Crear indicadores:

- tasa de cierre por vendedor.
- ranking motivos rechazo.
- artículos CORE sin movimiento.
- pedidos por vendedor.

### SPEC-015 - Tests

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
- Pensar multiempresa por subdominio.
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
- 99 presupuesto.
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

- HU-001 Login de usuario.
- HU-002 Recuperación de contraseña.
- HU-003 Resolución de empresa por subdominio.
- HU-004 Selección de cliente según tipo de usuario.
- HU-005 Inicialización de cabecera desde cliente.
- HU-006 Carga de renglones con artículos.
- HU-007 Cálculo de bonificación neta.
- HU-008 Cálculo de precio neto e importes.
- HU-009 Grabación de pedido.
- HU-010 Grabación de presupuesto.
- HU-011 Edición de pedido ingresado.
- HU-012 Eliminación de pedido ingresado.
- HU-013 Conversión de presupuesto a pedido.
- HU-014 Cierre negativo de presupuesto.
- HU-015 Consulta de pedidos ingresados.
- HU-016 Consulta de presupuestos ingresados.
- HU-017 Consulta de pedidos pendientes.
- HU-018 Consulta de stock.
- HU-019 Envío de mail al grabar.
- HU-020 Log de integración.

## 7. Recomendación de ejecución

Primero cargar en Cursor:

1. Definición conceptual final.
2. Modelo de datos final.
3. Este plan Cursor/OpenSpec.

Luego pedir:

```text
Generá las épicas y todas las historias de usuario del MVP respetando este contexto. No generes código todavía.
```

Después revisar manualmente las HU y recién entonces pasar a tasks técnicas por SPEC.
