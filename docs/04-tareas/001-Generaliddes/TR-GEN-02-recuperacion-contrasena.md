# TR-GEN-02-recuperacion-contrasena — Recuperacion de contrasena

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-02-recuperacion-contrasena](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-recuperacion-contrasena.md) |
| **SPEC relacionada** | [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| **Epica** | 001-Generaliddes |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-login-sesion, [TR-GEN-01-idioma](TR-GEN-01-idioma.md) (locale activo y checklist §4 ítem 13) |
| **Estado** | Pendiente |
| **Ultima actualizacion** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-02-recuperacion-contrasena](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-recuperacion-contrasena.md)  
**Referencia SPEC:** [SPEC-001-02-acceso-y-seguridad](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)  
**Checklist i18n (correo):** [TR-GEN-01-idioma §4 — ítem 13](TR-GEN-01-idioma.md#cobertura-mínima-de-textos-traducibles-checklist)

---

## 1) HU Refinada (resumen)

### Titulo
Implementar flujo de recuperacion por email con token de un solo uso e i18n.

### Narrativa
Como usuario que olvido su contrasena, quiero solicitar un reset y definir nueva clave sin exponer informacion sensible.

### In scope / Out of scope
- In scope: solicitud de recuperacion, envio de correo, validacion de token, seteo de nueva contrasena.
- In scope: plantilla de correo en locale activo con fallback `es`.
- Out of scope: cambio autenticado desde avatar, 2FA y ABM de seguridad.

---

## 2) Criterios de Aceptacion (AC)

- **AC-01**: Solicitud responde mensaje generico para email existente/no existente.
- **AC-02**: Token de recuperacion es temporal y de un solo uso.
- **AC-03**: Restablecimiento exitoso permite login con nueva clave.
- **AC-04**: Correo usa **locale i18n vigente en el sitio** al solicitar (selector login / `users.locale` / header; ver TR-GEN-01-idioma).
- **AC-05**: Fallo de mail registra error sin revelar existencia de cuenta.
- **AC-06**: Plantillas de correo con paridad de claves para `es`, `en` y **`it`** (mínimo validado en tests).

### Escenarios Gherkin

```gherkin
Feature: Recuperacion de contrasena

  Scenario: Solicitud con email registrado
    Given un email existente
    When llama POST /api/v1/auth/password/forgot
    Then recibe mensaje generico
    And se envia correo con token

  Scenario: Token expirado
    Given un token vencido
    When llama POST /api/v1/auth/password/reset
    Then recibe error funcional de token invalido o expirado

  Scenario: Correo con idioma activo
    Given locale actual en "en"
    When solicita recuperacion
    Then el asunto y cuerpo del correo salen en ingles

  Scenario: Correo en italiano
    Given locale actual en "it" en pantalla de login
    When solicita recuperacion con email registrado
    Then el asunto y cuerpo del correo salen en italiano
    And el enlace de restablecimiento es legible en el mismo idioma

  Scenario: Solicitud con email no registrado
    Given un email inexistente
    When llama POST /api/v1/auth/password/forgot
    Then recibe el mismo mensaje generico que email existente

  Scenario: Restablecer con token valido y login
    Given un token de recuperacion valido
    When establece nueva contrasena
    Then puede iniciar sesion con la nueva clave
```

---

## 3) Reglas de Negocio

1. **RN-01**: Misma respuesta funcional para email existente y no existente.
2. **RN-02**: Token de reset con TTL configurable (default documentado).
3. **RN-03**: Token solo puede consumirse una vez.
4. **RN-04**: Asunto y cuerpo del correo **solo** vía claves i18n; locale = idioma activo en el portal al momento del `POST forgot` (coherente con HU-GEN-01-idioma); fallback `es` si locale ausente o no soportado.
5. **RN-05**: El `locale` de la solicitud se toma del cliente (header/cuerpo) alineado al selector de idioma en login; no inferir desde email del usuario.

### Resolución del locale para el correo

| Origen (prioridad) | Uso |
|--------------------|-----|
| Campo `locale` en body de `POST /auth/password/forgot` | Preferido (enviado por frontend según selector) |
| Header `Accept-Language` | Respaldo si no viene body |
| `es` | Fallback final |

**Checklist transversal:** cumplir ítem **13** del [checklist de cobertura i18n](TR-GEN-01-idioma.md#cobertura-mínima-de-textos-traducibles-checklist) (mail “Olvidé mi contraseña”).

---

## 4) Impacto en Datos

### Tablas afectadas
- `password_reset_tokens` (o equivalente del stack)
- `users` (actualizacion de hash de contrasena)
- bitacora de eventos de seguridad (si existe)

### Seed minimo para tests
- Usuario semilla con email valido.
- Tokens de prueba validos y expirados.
- **Plantillas / claves i18n de correo** (backend o recursos compartidos): `mail.passwordReset.subject`, `mail.passwordReset.body`, `mail.passwordReset.linkLabel` (nombres a confirmar en implementación) en **`es`, `en`, `it`** como mínimo.
- Caso de prueba integración: solicitud con `locale: "it"` → asunto/cuerpo en italiano (assert sobre strings o snapshots de plantilla).

### Plantillas de correo (i18n)

| Elemento | Claves sugeridas | Idiomas mínimos MVP |
|----------|------------------|---------------------|
| Asunto | `mail.passwordReset.subject` | `es`, `en`, `it` |
| Saludo / cuerpo | `mail.passwordReset.body` | `es`, `en`, `it` |
| Texto del enlace | `mail.passwordReset.linkLabel` | `es`, `en`, `it` |
| Pie / no responder | `mail.passwordReset.footer` | `es`, `en`, `it` |

- Mismo canal de envío que login (producto PedidosWeb).
- No hardcodear HTML en un solo idioma; parametrizar URL de reset y minutos de validez del token.
- Ver criterio de cierre **italiano** en [TR-GEN-01-idioma](TR-GEN-01-idioma.md#cobertura-mínima-de-textos-traducibles-checklist).

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Metodo | Path | Auth | Permiso / rol | Publico |
|--------|------|------|---------------|---------|
| POST | `/api/v1/auth/password/forgot` | No | N/A | Si |
| POST | `/api/v1/auth/password/reset` | No | N/A | Si |

### 5.2 Detalle por operacion

#### POST `/api/v1/auth/password/forgot`
**Autorizacion:** publica (sin `security`).
**Request:** email + **`locale`** (opcional; recomendado — código del catálogo MVP: `es`, `en`, `pt`, `fr`, `it`) para determinar idioma del correo.
**Response 200:** envelope de confirmacion generica.
**Response 401:** N/A para ruta publica (documentar solo si middleware global aplica).
**Response 403:** N/A para ruta publica.

#### POST `/api/v1/auth/password/reset`
**Autorizacion:** publica con token funcional de recuperacion.
**Request:** token, email, nueva contrasena, confirmacion.
**Response 200:** envelope de reset exitoso.
**Response 401:** token ausente/invalido si se modela como autenticacion fallida.
**Response 403:** token valido pero no apto (usado/expirado) si se modela como autorizacion funcional.

### 5.3 Actualizacion matriz permisos

- [ ] Marcar ambos endpoints como publicos en matriz.
- [ ] Documentar codigos 401/403 segun convencion acordada del slice.
- [ ] Verificar OpenAPI en `/api/documentation`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- Enlace `Olvidaste tu contrasena` en login.
- Pantalla de solicitud y pantalla de nueva contrasena.
- Mensajes i18n coherentes con locale actual.

### data-testid sugeridos
- `forgot-password-link`
- `forgot-password-form`
- `reset-password-form`
- `reset-password-submit`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripcion | DoD |
|----|------|-------------|-----|
| T1 | Backend | Endpoint `forgot` con respuesta generica | No filtra existencia de email |
| T2 | Backend | Endpoint `reset` con token one-time | Respeta TTL y consumo unico |
| T3 | Backend | Envio mail con locale activo | Plantillas i18n `es`/`en`/`it` + fallback `es`; alinea checklist TR-GEN-01-idioma §4 ítem 13 |
| T4 | Frontend | Formularios forgot/reset | UX completa y validada |
| T5 | Tests | Integration + E2E de recuperacion | Cubre token valido/expirado |
| T6 | Docs | OpenAPI y matriz actualizadas | Coherencia transversal |

---

## 8) Estrategia de Tests

- **Unit:** generacion/validacion de token y politicas de password.
- **Integration:** flujos `forgot/reset`, validando envelope y errores; **`forgot` con `locale: it`** → contenido de mail en italiano (mock mailer).
- **E2E:** solicitud, consumo de token de prueba y login con nueva clave; opcional captura de mail en entorno test para `en` e `it`.

---

## 9) Riesgos y Edge Cases

- Retrasos/falla de proveedor de correo.
- Reuso accidental de token por carrera concurrente.
- Diferencias de locale entre UI y backend al generar email (mitigar: frontend envía `locale` explícito en `forgot`).

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Flujo forgot/reset completo
- [ ] i18n de correos verificado (`es`, `en`, **`it`** como mínimo)
- [ ] Checklist TR-GEN-01-idioma ítem 13 marcado para este slice

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
- Endpoints y servicio de recuperacion de contrasena.

### Frontend
- Formularios forgot/reset en modulo de autenticacion.

### OpenAPI
- Anotaciones de auth password endpoints.

### Docs
- `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md` (rutas publicas registradas)
