# TR-GEN-02-visibilidad-datos-pedidosweb — Visibilidad de datos por perfil funcional

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-visibilidad-datos-pedidosweb](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-visibilidad-datos-pedidosweb.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generaliddes |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion, TR-GEN-02-politicas-endpoints |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-02-visibilidad-datos-pedidosweb](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-visibilidad-datos-pedidosweb.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Aplicar visibilidad de clientes/comprobantes por perfil (cliente, vendedor, supervisor).

### Narrativa
Como usuario PedidosWeb, quiero ver solo los datos permitidos por mi perfil para proteger cartera y datos comerciales.

### In scope / Out of scope
- In scope: filtros en services/repositorios de consultas de clientes y comprobantes.
- In scope: helper central `visibleClientsForUser` para unificar criterio en backend.
- In scope: alineacion futura con slices funcionales de `SPEC-101`.
- Out of scope: ABM de asignaciones vendedor-cliente desde UI.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Perfil Cliente solo accede a su `codCliente`.
- **AC-02**: Perfil Vendedor solo accede a clientes asignados.
- **AC-03**: Perfil Supervisor accede a todo cliente del tenant.
- **AC-04**: Acceso directo a dato fuera de alcance retorna 403 o 404 sin fuga de datos.
- **AC-05**: `visibleClientsForUser` se reutiliza en consultas de slices `SPEC-101`.
- **AC-06**: Dashboard agrega solo datos del universo visible del perfil.
- **AC-07**: Usuario sin vinculo `cod_login` valido: login rechazado o error controlado (RN-6 HU).

### Escenarios Gherkin

```gherkin
Feature: Visibilidad por perfil funcional

  Scenario: Cliente restringido a su codigo
    Given un usuario perfil cliente con codCliente C1
    When consulta pedidos y comprobantes
    Then solo visualiza datos de C1

  Scenario: Vendedor no ve cartera ajena
    Given un usuario perfil vendedor V1
    When consulta clientes visibles
    Then obtiene solo clientes asignados a V1

  Scenario: Supervisor ve todo el tenant
    Given un usuario perfil supervisor
    When consulta listado de clientes
    Then ve todos los clientes activos del tenant

  Scenario: Intento de acceso directo fuera de alcance
    Given un vendedor autenticado
    When solicita un comprobante de cliente no asignado
    Then recibe 403 o 404 sin datos sensibles
```

---

## 3) Reglas de Negocio

1. **RN-01**: Un login representa exactamente un perfil funcional activo.
2. **RN-02**: Filtro de visibilidad se ejecuta siempre en backend, nunca solo en UI.
3. **RN-03**: `visibleClientsForUser` es la API interna unica para universo de clientes visibles.
4. **RN-04**: Politicas por endpoint y visibilidad por datos son capas complementarias.
5. **RN-05**: Todos los slices `SPEC-101-*` que consulten datos de clientes deben integrar `visibleClientsForUser`.
6. **RN-06**: Perfil funcional se resuelve por `cod_login` (producto §7.2); ver bootstrap [TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md) §3.1.

### 3.1) Resolucion `visibleClientsForUser(user)`

| `functionalProfile` | Universo de `cod_cliente` |
|---------------------|---------------------------|
| `cliente` | Solo el `cod_cliente` del login |
| `vendedor` | Clientes con `cod_vended` = vendedor del login |
| `supervisor` | Todos los clientes activos del tenant |

Fuente vinculo: `pq_pedidosweb_clientes.cod_login` / `pq_pedidosweb_vendedores.cod_login` + flag `supervisor`.

---

## 4) Impacto en Datos

### Tablas afectadas
- Tabla de clientes (ej. `clientes`)
- Vinculo vendedor-cliente (ej. `clientesde` o equivalente real)
- Vinculo user-perfil funcional (segun modelo de autenticacion)

### Seed minimo para tests
- Clientes semilla de al menos dos vendedores.
- Un usuario cliente, un vendedor y un supervisor.
- Casos de datos cruzados para verificar no filtracion.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice (referenciales de visibilidad)

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| GET | `/api/v1/clientes` | Bearer Sanctum + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad perfil | No |
| GET | `/api/v1/comprobantes/{id}` | Bearer Sanctum + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad perfil | No |
| GET | `/api/v1/dashboard/resumen` | Bearer Sanctum + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad perfil | No |

### 5.2 Detalle por operacion

#### GET `/api/v1/clientes`
**Autorizacion:** permiso de consulta + filtro `visibleClientsForUser`.
**Response 200:** envelope con clientes dentro del universo visible.
**Response 401:** no autenticado.
**Response 403:** autenticado sin permiso base de consulta.

#### GET `/api/v1/comprobantes/{id}`
**Autorizacion:** permiso de consulta + validacion de pertenencia al universo visible.
**Response 200:** envelope con comprobante permitido.
**Response 401:** no autenticado.
**Response 403:** sin permiso o acceso fuera de alcance.

#### GET `/api/v1/dashboard/resumen`
**Autorizacion:** permiso de consulta + filtros por perfil.
**Response 200:** envelope con agregados del universo visible.
**Response 401:** no autenticado.
**Response 403:** sin permiso para dashboard.

### 5.3 Actualizacion matriz permisos

- [ ] Agregar regla "aplica visibleClientsForUser" para endpoints de consulta.
- [ ] Referenciar dependencia con TR funcionales `SPEC-101-*`.
- [ ] Validar 401/403 y descripciones en OpenAPI generado.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Selector de cliente condicionado por universo visible.
- Mensajes de "sin datos visibles" para perfil sin cartera.
- Componentes de dashboard/consultas consumen solo resultados filtrados backend.

### data-testid sugeridos
- `clientes-selector`
- `clientes-visible-count`
- `forbidden-resource-message`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Implementar helper `visibleClientsForUser(user, tenant)` | Devuelve universo correcto por perfil |
| T2 | Backend | Integrar helper en repos/services de consultas | Ninguna consulta saltea filtro |
| T3 | Backend | Enforzar 403/404 para acceso por ID fuera de alcance | Sin fuga de datos |
| T4 | Frontend | Adaptar selector y estados vacios | UX coherente por perfil |
| T5 | Tests | Integration por perfil + E2E cartera | 3 perfiles y resultados distintos |
| T6 | Coordinacion | Definir puntos de integracion futura con `SPEC-101` | Checklist de adopcion por slice |

---

## 8) Estrategia de Tests

- **Unit:** comportamiento de `visibleClientsForUser` para cliente/vendedor/supervisor.
- **Integration:** consultas 200/401/403/404 aplicando filtros de perfil.
- **E2E:** vendedor A no visualiza clientes/comprobantes de vendedor B.

---

## 9) Riesgos y Edge Cases

- Usuario mal configurado sin vinculo perfil-cliente/vendedor.
- Endpoints legacy de consulta no migrados al helper central.
- Diferencias entre filtros de listado y filtros por ID puntual.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] `visibleClientsForUser` implementado y reutilizable
- [ ] Estrategia de adopcion definida para `SPEC-101`

### Checklist normas transversales
- [ ] Endpoints nuevos/modificados con policy en codigo
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en `/api/documentation` coherente con codigo y matriz
- [ ] 401/403 documentados por operacion protegida
- [ ] Envelope JSON respetado
- [ ] `X-Paq-Cliente` documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliacion de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

### Backend
- Helper central `visibleClientsForUser`.
- Repos/services de consultas filtradas por perfil.

### Frontend
- Selector de clientes y vistas de consultas/dashboards.

### OpenAPI
- Operaciones de consultas de clientes/comprobantes/dashboard con 401/403 y reglas de visibilidad.

### Docs
- `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md`
- Referencias cruzadas en TR `SPEC-101-*` al helper de visibilidad.
