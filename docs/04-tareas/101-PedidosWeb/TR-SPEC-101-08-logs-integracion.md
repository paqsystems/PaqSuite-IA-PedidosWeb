# TR-SPEC-101-08 — Logs de integración ERP

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-020-logs-integracion](../../03-historias-usuario/101-PedidosWeb/HU-101-020-logs-integracion.md) |
| **SPEC relacionada** | [SPEC-101-08-logs-integracion](../../05-open-spec/101-PedidosWeb/SPEC-101-08-logs-integracion.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | **Should** (AMB-C02) |
| **Dependencias** | TR-SPEC-101-02-modelos; TR-SPEC-101-09-frontend-base (menú ítem 11) |
| **Estado** | Pendiente de Revisión — **Bloque 4** |
| **Última actualización** | 2026-06-02 |

**Origen:** [HU-101-020-logs-integracion](../../03-historias-usuario/101-PedidosWeb/HU-101-020-logs-integracion.md)  
**Referencia SPEC:** [SPEC-101-08-logs-integracion](../../05-open-spec/101-PedidosWeb/SPEC-101-08-logs-integracion.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Persistir y consultar logs de integración ERP/portal para soporte operativo.

### Narrativa
Como **usuario de soporte o supervisor**, quiero **consultar logs de integración con filtros**, para **diagnosticar fallas de sincronización sin acceder al servidor**.

### In scope / Out of scope
- **In scope:** tabla `pq_pedidosweb_logs_integracion`; servicio de escritura en puntos acordados (jobs ERP, errores API integración); **GET** consulta paginada con filtros fecha / tipo / severidad; grilla UI menú ítem 11 (producto §20).
- **Out of scope:** No bloquea cierre E2E §9 si se difiere a iteración posterior (CA-02 HU); reintentos automáticos ERP; ABM de logs.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** `GET /api/v1/integracion/logs` devuelve listado paginado con filtros `fecha_desde`, `fecha_hasta`, `tipo`, `severidad`, `origen`, `procesado`.
- **AC-02:** Persistencia en `pq_pedidosweb_logs_integracion` según modelo §7.5 (`id_log`, `fecha`, `tipo`, `severidad`, `origen`, `mensaje`, `payload`, `procesado`).
- **AC-03:** Grilla `DataGridDx` con filtros básicos y solo lectura (GEN-03 layouts si aplica; export Excel opcional Should).
- **AC-04:** Usuario sin `Permiso_Repo` (o permiso acordado supervisor) → 403.
- **AC-05:** Feature tests: 200 con filtros, 401 sin token, 403 sin permiso.
- **AC-06:** Slice **Should**: puede entregarse post E2E §9 sin bloquear release MVP.

### Escenarios Gherkin

```gherkin
Feature: Consulta de logs de integración

  Scenario: Listado filtrado por severidad error
    Given logs seed con severidad "error" y "info"
    When consulta GET /api/v1/integracion/logs?severidad=error
    Then solo recibe registros con severidad error
    And el envelope resultado contiene items paginados

  Scenario: Sin permiso de consulta
    Given un usuario autenticado sin Permiso_Repo en el proceso
    When consulta GET /api/v1/integracion/logs
    Then recibe 403

  Scenario: Grilla en menú ítem 11
    Given supervisor autenticado
    When navega a Logs de integración
    Then ve DataGridDx con columnas fecha, tipo, severidad, origen, mensaje
```

---

## 3) Reglas de Negocio

1. **RN-01:** Severidades permitidas: `info`, `warning`, `error` (validar en escritura).
2. **RN-02:** `payload` JSON opcional; no exponer secretos ni credenciales completas (producto §20).
3. **RN-03:** Orden por defecto: `fecha` descendente.
4. **RN-04:** Paginación estándar MONO en `resultado`: `{ items, page, page_size, total, total_pages }`.
5. **RN-05:** Escritura de logs desde services de integración; no desde controllers directamente.

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_pedidosweb_logs_integracion` (DDL según `PedidosWeb_Modelo_Datos_Final.md` §7.5)

### Seed mínimo para tests
- ≥ 3 filas: `info`, `warning`, `error`; fechas distintas; al menos un `procesado = 0`.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/integracion/logs` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` (supervisor/soporte; cerrar en matriz) | No |

### 5.2 Detalle por operación

#### GET `/api/v1/integracion/logs`

**Autorización:** `Permiso_Repo` + menú ítem 11 visible para rol

**Request (query):**

| Parámetro | Tipo | Obligatorio |
|-----------|------|-------------|
| `fecha_desde` | date/datetime | No |
| `fecha_hasta` | date/datetime | No |
| `tipo` | string | No |
| `severidad` | string | No |
| `origen` | string | No |
| `procesado` | boolean | No |
| `page` | int | No (default 1) |
| `page_size` | int | No (default acordado) |

**Response 200:** envelope; `resultado` con paginación y `items[]` (`id_log`, `fecha`, `tipo`, `severidad`, `origen`, `mensaje`, `procesado`; `payload` omitido o truncado en listado)

**Response 401:** no autenticado

**Response 403:** sin permiso

**Response 422:** filtros inválidos (rango fechas incoherente)

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controller/DTO
- [ ] `security` Bearer
- [ ] Header `X-Paq-Cliente` documentado
- [ ] Respuestas 401, 403, 422
- [ ] Permiso en `description`
- [ ] Verificado en `/api/documentation`

### 5.3 Actualización matriz permisos

- [ ] Fila `GET /api/v1/integracion/logs` → `Permiso_Repo`

---

## 6) Cambios Frontend

### Pantallas / componentes
- Ruta menú ítem 11: `LogsIntegracionPage` con `DataGridDx`
- Filtros: `DateBox` rango, `SelectBox` severidad/tipo
- Sin acciones de alta/edición en grilla

### data-testid sugeridos
- `logsIntegracionGrid`
- `logsIntegracionFilterDesde`
- `logsIntegracionFilterHasta`
- `logsIntegracionFilterSeveridad`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | DB | Migración `pq_pedidosweb_logs_integracion` | Modelo §7.5 |
| T2 | Backend | `LogIntegracionService` write + list | AC-02 |
| T3 | Backend | Controller GET + policy | OpenAPI + tests 401/403 |
| T4 | Frontend | Grilla + filtros DX | AC-03 |
| T5 | Tests | Feature GET + seed | AC-05 |
| T6 | Docs | Matriz permisos | Checklist §5 |

---

## 8) Estrategia de Tests

- **Unit:** Filtros query builder; validación severidad.
- **Integration:** 200 paginado; 401; 403; filtro severidad.
- **E2E:** ≥ 2 si se implementa UI en mismo release (feliz listado + 403); si slice difiere, documentar en TR-SPEC-101-15.

---

## 9) Riesgos y Edge Cases

- Volumen alto de logs → definir `page_size` máximo y índice por `fecha`.
- `payload` grande → truncar en API listado.
- Puntos de escritura dispersos → documentar en TR-SPEC-101-04 qué operaciones loguean.

---

## 10) Checklist final

### Checklist del slice
- [x] AC-01, AC-02 — GET logs + persistencia service
- [x] AC-03 — Grilla UI `IntegracionLogsPage` + filtros DX
- [ ] AC-05 feature 200/403 (BLOQUEADO_ENV)

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 y 403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 y 403
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- Model/migration logs integración
- `IntegracionLogController`, service, repository

### Frontend
- Página logs integración + ruta menú

### OpenAPI
- Controller anotado GET logs

### Docs
- Matriz permisos — fila integracion/logs
