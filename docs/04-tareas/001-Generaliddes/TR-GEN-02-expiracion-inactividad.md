# TR-GEN-02-expiracion-inactividad — Expiracion de sesion por inactividad

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-expiracion-inactividad](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-expiracion-inactividad.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generaliddes |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-02-expiracion-inactividad](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-expiracion-inactividad.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Titulo
Aplicar expiracion de sesion por inactividad usando parametro `MinutosWeb`.

### Narrativa
Como usuario autenticado, quiero que la sesion cierre tras inactividad configurable para reducir uso no autorizado.

### In scope / Out of scope
- In scope: lectura de `MinutosWeb`, detector de inactividad frontend y rechazo backend de sesion expirada.
- In scope: limpieza de estado local y redireccion a login.
- Out of scope: SSO, revocacion global de sesiones y politicas avanzadas de refresh.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Superado `MinutosWeb`, la sesion se cierra automaticamente.
- **AC-02**: Actividad del usuario reinicia contador de inactividad.
- **AC-03**: API protegida con sesion expirada responde 401.
- **AC-04**: Se documenta default cuando `MinutosWeb` no exista.
- **AC-05**: Flujo queda cubierto con pruebas E2E de timeout reducido.
- **AC-06**: Tras expiración, shell y procesos quedan inaccesibles hasta nuevo login.

### Escenarios Gherkin

```gherkin
Feature: Expiracion por inactividad

  Scenario: Expiracion automatica
    Given un usuario autenticado
    And MinutosWeb configurado en N
    When no registra actividad durante N minutos
    Then se redirige al login con mensaje de sesion expirada

  Scenario: Actividad renueva sesion
    Given un usuario autenticado
    When registra actividad antes del timeout
    Then la sesion continua activa

  Scenario: API rechaza token expirado
    Given un usuario con sesion expirada por inactividad
    When llama un endpoint protegido
    Then recibe HTTP 401

  Scenario: Parametro MinutosWeb ausente
    Given MinutosWeb no configurado en parametros
    When el usuario inicia sesion
    Then se aplica un default documentado en TR
```

---

## 3) Reglas de Negocio

1. **RN-01**: `MinutosWeb` se obtiene desde configuracion global (SPEC-001-04).
2. **RN-02**: Eventos de actividad validos: navegacion, interaccion y/o request API segun definicion del slice.
3. **RN-03**: Sesion expirada fuerza 401 en endpoints protegidos.
4. **RN-04**: Si falta `MinutosWeb`, usar default trazable en documentacion.

---

## 4) Impacto en Datos

### Tablas afectadas
- Configuracion global (origen de `MinutosWeb`).
- `personal_access_tokens` o sesion equivalente para invalidacion.

### Seed minimo para tests
- Valor de `MinutosWeb` configurable por ambiente de test.
- Usuario autenticado con token valido.

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| GET | `/api/v1/auth/me` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |
| POST | `/api/v1/auth/logout` | Bearer Sanctum + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operacion

#### GET `/api/v1/auth/me`
**Autorizacion:** usuario autenticado.
**Response 200:** envelope con sesion vigente.
**Response 401:** sesion expirada/invalida.
**Response 403:** autenticado sin permisos de contexto.

#### POST `/api/v1/auth/logout`
**Autorizacion:** usuario autenticado.
**Response 200:** cierre manual exitoso.
**Response 401:** token invalido/expirado.
**Response 403:** restriccion de politica (si aplica).

### 5.3 Actualizacion matriz permisos

- [ ] Confirmar que endpoints de sesion contemplan expiracion por 401.
- [ ] Documentar en descripcion OpenAPI la regla de inactividad.
- [ ] Verificar reflejo en `/api/documentation`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Hook/global guard de inactividad en shell.
- Mensaje de sesion expirada y redireccion a login.
- Reinicio de contador en eventos de actividad definidos.

### data-testid sugeridos
- `session-timeout-banner`
- `session-timeout-redirect`
- `session-activity-ping`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer/leer `MinutosWeb` para autenticacion | Valor disponible y con default |
| T2 | Backend | Invalidar sesion por inactividad | 401 consistente en endpoints protegidos |
| T3 | Frontend | Implementar detector de actividad/inactividad | Redireccion al login al vencer timeout |
| T4 | Tests | Integration + E2E de expiracion | Cobertura 401 y flujo UX |
| T5 | Docs | OpenAPI + matriz + default documentado | Trazabilidad completa |

---

## 8) Estrategia de Tests

- **Unit:** logica de calculo de timeout y default de `MinutosWeb`.
- **Integration:** respuestas 401 por sesion vencida.
- **E2E:** inactividad simulada con timeout corto en ambiente de prueba.

---

## 9) Riesgos y Edge Cases

- Diferencias de reloj entre cliente y servidor.
- Pestanas multiples reactivando sesion de forma inconsistente.
- Parametro ausente o mal configurado en despliegues iniciales.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Inactividad aplica cierre real de sesion
- [ ] Default `MinutosWeb` documentado

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
- Middleware/servicio de expiracion por inactividad.

### Frontend
- Guard de sesion inactiva y notificacion de expiracion.

### OpenAPI
- Descripciones de respuestas 401/403 en endpoints de sesion.

### Docs
- `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md`
