# TR-GEN-01-menu-avatar — Menú avatar y preferencias personales

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-menu-avatar](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-menu-avatar.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout; TR-GEN-02-login-sesion; TR-GEN-02-cambio-contrasena; integra TR-GEN-01-idioma, TR-GEN-01-apariencia-temas y TR-GEN-01-ayuda-externa |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-01-menu-avatar](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-menu-avatar.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Menú avatar con acciones de sesión y preferencias.

### Narrativa
Como usuario autenticado quiero acceder a un menú personal bajo el avatar para gestionar apariencia, apertura en nueva pestaña y acciones de sesión (idioma en header, no en avatar).

### In scope / Out of scope
- **In scope:** desplegable avatar post-login, acciones personales, persistencia de preferencia de nueva pestaña, enlaces a slices hermanos.
- **Out of scope:** selector de empresa (MULTI), contenido del menú de procesos, implementación interna de cambio de contraseña.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Clic en avatar abre/cierra menú desplegable.
- **AC-02**: Toggle "abrir en nueva pestaña" persiste entre sesiones.
- **AC-03**: Acción "cerrar sesión" invalida sesión y redirige a login.
- **AC-04**: Acción "cambiar contraseña" enruta al flujo de seguridad correspondiente.
- **AC-05**: Acción "apariencia" enlaza al selector de temas.
- **AC-06**: Acción "asistente IA" se muestra solo si hay URL configurada.
- **AC-07**: No existe opción "cambiar empresa".
- **AC-08**: Avatar genérico sin foto de usuario (iniciales o icono).
- **AC-09**: E2E: logout y persistencia toggle nueva pestaña.

### Escenarios Gherkin

```gherkin
Feature: Menú avatar

  Scenario: Abrir menú avatar post-login
    Given un usuario autenticado en el shell
    When hace clic en su avatar
    Then ve opciones de apariencia, seguridad y sesión
    And no ve opción de cambiar empresa

  Scenario: Persistir preferencia abrir en nueva pestaña
    Given un usuario autenticado
    When activa "Abrir en nueva pestaña" en el menú avatar
    And recarga la sesión
    Then la preferencia sigue activa
    And afecta la navegación del sidebar

  Scenario: Cerrar sesión desde avatar
    Given un usuario autenticado
    When selecciona "Cerrar sesión"
    Then la sesión se invalida
    And es redirigido al login

  Scenario: Acceso a apariencia desde avatar
    Given un usuario autenticado
    When abre el menú avatar y elige apariencia
    Then puede cambiar el tema DevExtreme
    And la UI refleja el tema elegido
```

---

## 3) Reglas de Negocio

1. **RN-01**: Menú avatar solo visible en estado post-login.
2. **RN-02**: Las acciones del avatar son personales/de sesión, no de navegación de procesos.
3. **RN-03**: La preferencia `openInNewTab` se guarda por usuario (server-side).
4. **RN-04**: En MONO no se permite "cambiar empresa".
5. **RN-05**: Si no existe avatar/foto, se usa ícono genérico sin bloquear acciones.

---

## 4) Impacto en Datos

### Tablas afectadas
- `users` (columna de preferencia `openInNewTab` o equivalente acordado en backend).
- Reuso de `users.locale` y `users.theme` por navegación a slices hermanos.

### Seed mínimo para tests
- Usuario autenticado con y sin foto de perfil.
- Usuarios con valores `openInNewTab = true/false` para validar persistencia.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| POST | `/api/v1/auth/logout` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### PATCH `/api/v1/users/me/preferences`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "openInNewTab": true
}
```

**Response 200:** envelope con preferencias actualizadas del usuario.

**Response 401:** no autenticado.

**Response 403:** token válido sin permiso de auto-gestión (si aplica política explícita).

**Response 4xx/5xx:** validación de tipo de dato o error interno.

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controller/DTO de preferencias.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403 documentadas.
- [ ] Envelope validado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar/confirmar filas para `GET/PATCH /api/v1/users/me/preferences`.
- [ ] Confirmar fila de `POST /api/v1/auth/logout` en matriz de autenticación.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/app/layout/ShellHeader.tsx`: avatar y trigger del menú.
- `frontend/src/features/avatar/components/AvatarMenu.tsx` (nuevo): opciones personales.
- `frontend/src/features/avatar/model/avatarMenuAction.ts` (nuevo): modelo tipado de opciones.
- `frontend/src/features/preferences/api/updatePreferences.ts` (nuevo): patch de preferencias.
- `frontend/src/features/auth/api/logout.ts` (nuevo o ajuste): cierre de sesión.
- `frontend/src/features/avatar/hooks/useAvatarMenu.ts` (nuevo): estado de apertura/cierre.

### data-testid sugeridos
- `avatarMenuTrigger`
- `avatarMenuPanel`
- `avatarMenuItemAppearance`
- `avatarMenuItemOpenInNewTab`
- `avatarMenuItemLogout`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer `GET/PATCH /api/v1/users/me/preferences` con campo `openInNewTab` | OpenAPI + 401/403 + tests |
| T2 | Backend | Asegurar `POST /api/v1/auth/logout` idempotente y documentado | Sesión invalidada correctamente |
| T3 | Frontend | Implementar `AvatarMenu` con acciones y estados de visibilidad | Menú operativo y accesible |
| T4 | Frontend | Integrar persistencia `openInNewTab` y conexión con sidebar | Navegación respeta preferencia |
| T5 | Tests | Integration preferencias/logout + E2E persistencia y logout | Casos verdes en CI |
| T6 | Docs | Actualizar matriz de permisos y trazabilidad con slices hermanos | Documentación alineada |

---

## 8) Estrategia de Tests

- **Unit:** reducer/hook de estado de menú avatar y parseo de preferencias.
- **Integration:** `PATCH /api/v1/users/me/preferences` (200/401/403) y `POST /api/v1/auth/logout`.
- **E2E:**  
  - activar `openInNewTab`, refrescar sesión y verificar persistencia;  
  - cerrar sesión desde avatar y validar redirección.

---

## 9) Riesgos y Edge Cases

- Desacople entre preferencias en cliente y server si falla sincronización.
- Popup blockers del navegador pueden interferir con navegación en nueva pestaña.
- Orden inconsistente de ítems del menú avatar entre entornos puede afectar UX.
- Duplicar selector de idioma dentro de avatar contradice flujo definido.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Integración validada con shell/menu/login
- [ ] Sin opción de cambiar empresa (MONO)

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

