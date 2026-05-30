# TR-GEN-02-politicas-endpoints — Marco transversal de politicas por endpoint

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-politicas-endpoints](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-politicas-endpoints.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generaliddes |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion, TR-GEN-02-autorizacion-menu-api |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-02-politicas-endpoints](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-politicas-endpoints.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Definir marco transversal para autorizar todos los endpoints protegidos del MVP.

### Narrativa
Como arquitectura API, necesitamos que cada endpoint protegido tenga politica en codigo, fila en matriz y contrato OpenAPI coherente.

### In scope / Out of scope
- In scope: estandar de autorizacion para `api/v1/*`, lista blanca publica y checklist transversal de cumplimiento.
- In scope: matriz viva endpoint ↔ permiso y reglas de mantenimiento por cada slice.
- Out of scope: implementar un endpoint funcional especifico de negocio (se ejecuta en TR de cada slice).

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Existe inventario inicial de endpoints MVP con politica asignada.
- **AC-02**: Toda operacion protegida en OpenAPI declara `security`, `X-Paq-Cliente`, 401 y 403.
- **AC-03**: Toda ruta publica listada explicitamente sin `security`.
- **AC-04**: Matriz endpoint ↔ permiso se actualiza en el mismo slice del cambio.
- **AC-05**: Ningun controller de negocio confia solo en ocultamiento UI/menu.

### Escenarios Gherkin

```gherkin
Feature: Politicas transversales por endpoint

  Scenario: Endpoint protegido sin token
    Given un endpoint protegido del inventario
    When se invoca sin Bearer token
    Then responde HTTP 401

  Scenario: Endpoint protegido sin permiso
    Given un usuario autenticado sin permiso requerido
    When invoca endpoint protegido
    Then responde HTTP 403
    And no genera efectos laterales

  Scenario: Coherencia OpenAPI
    Given un endpoint protegido publicado
    When se inspecciona /api/documentation
    Then aparece security, X-Paq-Cliente, 401 y 403

  Scenario: Menu oculto no implica endpoint abierto
    Given un usuario sin permiso de menu para un proceso
    When invoca directamente el endpoint del proceso
    Then recibe HTTP 403

  Scenario: Token valido con permiso
    Given un usuario autenticado con permiso requerido
    When invoca endpoint protegido
    Then recibe HTTP 200 en envelope valido
```

---

## 3) Reglas de Negocio

1. **RN-01**: Politica backend por endpoint es obligatoria para toda ruta protegida.
2. **RN-02**: Menu visible no reemplaza autorizacion de endpoint.
3. **RN-03**: Lista blanca publica inicial: login, recuperacion de contrasena, health.
4. **RN-04**: Cada TR funcional que agregue/edite endpoint debe actualizar matriz y OpenAPI en el mismo slice.
5. **RN-05**: Cualquier discrepancia codigo-matriz-openapi bloquea cierre de slice.

---

## 4) Impacto en Datos

### Tablas afectadas
- `Pq_Permiso` (catalogo de permisos y relaciones)
- `Pq_Rol` (asignaciones de autorizacion)
- `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md` como matriz viva documental

### Seed minimo para tests
- Roles/permisos base del seed MVP.
- Usuarios de prueba por perfil para validar 401/403.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints cubiertos por el marco (no exhaustivo)

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| POST | `/api/v1/auth/login` | No | N/A | Si |
| POST | `/api/v1/auth/password/forgot` | No | N/A | Si |
| GET | `/api/v1/health` | No | N/A | Si |
| GET | `/api/v1/user/menu` | Bearer Sanctum + `X-Paq-Cliente` | Permiso_Repo menu | No |
| GET/POST/PUT/DELETE | `/api/v1/pedidos/*` | Bearer Sanctum + `X-Paq-Cliente` | Segun matriz (`Permiso_Repo/Alta/Modi/Baja`) | No |
| GET/POST/PUT/DELETE | `/api/v1/presupuestos/*` | Bearer Sanctum + `X-Paq-Cliente` | Segun matriz | No |
| GET | `/api/v1/dashboard/*` | Bearer Sanctum + `X-Paq-Cliente` | Segun matriz + visibilidad perfil | No |

### 5.2 Norma de detalle por operacion (obligatoria)

Cada operacion protegida debe documentar:
- Autorizacion (permiso/rol o regla equivalente).
- Request y `X-Paq-Cliente`.
- Response 200 en envelope `error`/`respuesta`/`resultado` (`error` entero; `resultado` objeto, nunca `null`). Ver [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).
- Response 401 (no autenticado).
- Response 403 (autenticado sin permiso).

Cada operacion publica debe documentar:
- Sin bloque `security`.
- Justificacion de lista blanca.
- Envelope de respuestas y errores funcionales.

### 5.3 Checklist transversal de publicacion OpenAPI

- [ ] Anotaciones en controller/DTO del slice.
- [ ] `security` presente en rutas protegidas.
- [ ] Header `X-Paq-Cliente` documentado donde aplique.
- [ ] Respuestas 401 y 403 declaradas por operacion protegida.
- [ ] Permiso requerido visible en `description` (o extension acordada).
- [ ] Verificado spec final en `/api/documentation`.
- [ ] Matriz endpoint ↔ permiso actualizada en `matriz-permisos-mvp.md`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Sin cambios directos en UI; impacto transversal en manejo uniforme de 401/403.
- Definir interceptor comun para forzar login en 401.

### data-testid sugeridos
- `http-401-handler`
- `http-403-handler`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Arquitectura | Publicar inventario endpoint ↔ permiso MVP | Tabla inicial acordada |
| T2 | Backend | Estandarizar middleware/policies por modulo | Todos los modulos usan regla comun |
| T3 | Docs | Definir formato canonicode matriz permisos | Archivo vivo actualizado |
| T4 | OpenAPI | Normalizar anotaciones 401/403 + security | `/api/documentation` consistente |
| T5 | QA | Suite de regression 401/403 transversal | Evidencia por recursos criticos |

---

## 8) Estrategia de Tests

- **Unit:** policies y evaluadores de permiso por accion.
- **Integration:** pruebas 200/401/403 por endpoint critico del MVP.
- **E2E:** intento de acceso directo a rutas no autorizadas sin menu visible.

---

## 9) Riesgos y Edge Cases

- Drift entre matriz documental y autorizacion real en codigo.
- Endpoints nuevos sin actualizacion de OpenAPI en el mismo PR.
- Respuestas 403 inconsistentes entre modulos.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Marco transversal acordado y documentado
- [ ] Checklist operativo reutilizable por cada slice

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
- Middleware/policies transversales (por modulo en implementacion).

### Frontend
- Manejo estandar de errores 401/403.

### OpenAPI
- `backend/OpenApi.php`
- Controllers/DTOs de cada slice funcional.

### Docs
- `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md`
- TR de cada slice con seccion 5 alineada a este marco.
