# TR-SPEC-101-01 — Backend base y tenancy MONO

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-003-resolucion-tenant](../../03-historias-usuario/101-PedidosWeb/HU-101-003-resolucion-tenant.md) |
| **SPEC relacionada** | [SPEC-101-01-backend-base](../../05-open-spec/101-PedidosWeb/SPEC-101-01-backend-base.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Diferida — etapa posterior (`EMPRESAS_CONEXION`; AMB-C07) |
| **Dependencias** | `SPEC-001-05`; scaffold `paq.tenant` en `v1.1.0-paq`; `docs/_base/resolucion-host-cliente-sql-mono.md` |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** [HU-101-003-resolucion-tenant](../../03-historias-usuario/101-PedidosWeb/HU-101-003-resolucion-tenant.md)  
**Referencia SPEC:** [SPEC-101-01-backend-base](../../05-open-spec/101-PedidosWeb/SPEC-101-01-backend-base.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Completar resolución de tenant MONO vía `EMPRESAS_CONEXION` y conexión SQL Server dinámica por `{cliente}`.

### Narrativa
Como **operador del deploy MONO**, quiero **resolver el tenant `{cliente}` a la base SQL correcta**, para **aislar datos por cliente sin `X-Company-Id` ni selector de empresa en UI**.

### In scope / Out of scope
- **In scope:** middleware tenant alineado a resolución host/cliente; fila `CODIGO_TENANT = desarrollo`; validación `X-Paq-Cliente` + registro activo en `EMPRESAS_CONEXION` (`proyecto = pedidosweb`); healthcheck con conexión tenant; estructura base Services/Repositories/DTOs/Policies si faltara.
- **Out of scope:** CRUD pedidos/presupuestos; lógica comercial; reemplazo del stub en slices 02–15 ya desplegados hasta activar esta etapa.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Tenant `desarrollo` resuelve la base de trabajo local documentada en seed.
- **AC-02:** Tenant inexistente, inactivo o sin fila en `EMPRESAS_CONEXION` → error controlado (`400` / clave `tenant.invalid`) **antes** de ejecutar lógica de negocio.
- **AC-03:** `GET /api/v1/health` (o health dedicado tenant) opera con la conexión del tenant solicitado.
- **AC-04:** Login y sesión registran el tenant usado; requests autenticados exigen coherencia `X-Paq-Cliente` ↔ tenant de sesión (definir regla en implementación).
- **AC-05:** Tests feature de resolución tenant (feliz + tenant inválido).
- **AC-06:** Slices de dominio 02–15 siguen operativos con stub hasta merge de esta TR; al activar, mismos tests de dominio pasan contra conexión real.

### Escenarios Gherkin

```gherkin
Feature: Resolución de tenant MONO

  Scenario: Tenant desarrollo válido
    Given una fila activa EMPRESAS_CONEXION con CODIGO_TENANT "desarrollo"
    When una petición API incluye header X-Paq-Cliente "desarrollo"
    Then el middleware resuelve la conexión SQL del tenant
    And la operación de negocio puede ejecutarse

  Scenario: Tenant inexistente
    Given no existe fila para el tenant solicitado
    When una petición API incluye X-Paq-Cliente "inexistente"
    Then recibe error controlado antes de negocio
    And el envelope mantiene error distinto de cero

  Scenario: Health con tenant correcto
    Given tenant "desarrollo" configurado
    When llama GET /api/v1/health con X-Paq-Cliente "desarrollo"
    Then la respuesta indica servicio operativo para ese tenant
```

---

## 3) Reglas de Negocio

1. **RN-01:** Entrada `{cliente}.pedidosweb` → contexto frontend/API con `X-Paq-Cliente: {cliente}`.
2. **RN-02:** `proyecto = pedidosweb` en `EMPRESAS_CONEXION` para filas válidas del módulo.
3. **RN-03:** Prohibido inferir tenant **solo** desde JWT sin registro activo en `EMPRESAS_CONEXION`.
4. **RN-04:** Prohibido `X-Company-Id` y selector de empresa (SPEC-001-05).
5. **RN-05:** Desarrollo local exige fila `CODIGO_TENANT = desarrollo` operativa.

---

## 4) Impacto en Datos

### Tablas afectadas
- `EMPRESAS_CONEXION` (lectura; sin DDL en portal salvo migración acordada en repo infra)
- Conexión dinámica SQL Server por tenant (config runtime)

### Seed mínimo para tests
- Fila `EMPRESAS_CONEXION`: `CODIGO_TENANT = desarrollo`, `proyecto = pedidosweb`, activa, apuntando a base QA/local documentada.

---

## 5) Contratos de API y OpenAPI

> Cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/health` | Opcional Bearer | N/A | Sí (health) |
| * | `/api/v1/*` (middleware) | Bearer + `X-Paq-Cliente` donde aplique | Según endpoint | No |

*Esta TR no introduce endpoints de negocio; documenta comportamiento transversal del middleware `paq.tenant`.*

### 5.2 Detalle por operación

#### GET `/api/v1/health`

**Autorización:** Pública; documentar uso de `X-Paq-Cliente` para verificar conectividad tenant.

**Request:** Header opcional `X-Paq-Cliente`.

**Response 200:** envelope con `resultado` indicando estado (`{}` mínimo o `{ status, tenant }` según implementación).

**Response 400:** `tenant.invalid` si se exige tenant y falta o es inválido (cerrar en D1 si health sin tenant sigue siendo global).

**OpenAPI (L5-Swagger):**

- [ ] Documentar header `X-Paq-Cliente` en operaciones afectadas por middleware
- [ ] Respuesta 400 tenant documentada donde aplique
- [ ] Verificado en `/api/documentation`

### 5.3 Actualización matriz permisos

- [ ] Nota en `matriz-permisos-mvp.md`: middleware tenant obligatorio en rutas protegidas; health según decisión D1

---

## 6) Cambios Frontend

### Pantallas / componentes
- Sin cambios de UI de negocio; confirmar que el host `{cliente}.pedidosweb` propaga `X-Paq-Cliente` en cliente HTTP (interceptor existente o a crear en TR-SPEC-101-09).

### data-testid sugeridos
- N/A (infraestructura)

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Middleware `paq.tenant` real vs stub | Resolución `EMPRESAS_CONEXION` |
| T2 | Backend | Connection factory SQL Server por tenant | AC-01, AC-02 |
| T3 | Backend | Health tenant-aware | AC-03 |
| T4 | Tests | Feature resolución + health | AC-05 |
| T5 | Docs | Runbook desarrollo `desarrollo` + OpenAPI headers | Checklist §5 |

---

## 8) Estrategia de Tests

- **Unit:** Resolver tenant desde header/host; casos fila inactiva.
- **Integration:** Request con/sin `X-Paq-Cliente`; tenant inválido → 400 antes de controller.
- **E2E:** No obligatorio en este slice (infra); validar flujo §9 al integrar con dominio.

---

## 9) Riesgos y Edge Cases

- Activar conexión real en CI sin `EMPRESAS_CONEXION` → fallo masivo; documentar variable/seed QA.
- Divergencia stub vs real en tests existentes de slices 02–15.
- Rotación de credenciales por tenant fuera de alcance MVP.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Stub reemplazado o conmutado por flag documentado
- [ ] Fila `desarrollo` operativa en entornos dev/QA

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida (N/A health; middleware documentado)
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- Middleware tenant / `EMPRESAS_CONEXION` resolver
- Config conexión dinámica SQL Server

### Frontend
- Interceptor `X-Paq-Cliente` (si no existiera)

### OpenAPI
- `backend/OpenApi.php` — headers tenant
- Health controller anotado

### Docs
- Matriz permisos — nota middleware
- Runbook tenant `desarrollo`
