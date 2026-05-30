# TR-GEN-01-apariencia-temas — Apariencia y temas por usuario

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-apariencia-temas](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-apariencia-temas.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout; TR-GEN-01-menu-avatar; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-01-apariencia-temas](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-apariencia-temas.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Apariencia DevExtreme por usuario con fallback seguro.

### Narrativa
Como usuario autenticado quiero seleccionar apariencia global desde el menú avatar para trabajar con un tema persistente entre sesiones.

### In scope / Out of scope
- **In scope:** selector de tema desde avatar, aplicación inmediata, persistencia en `users.theme`, fallback `generic.light`.
- **Out of scope:** temas por empresa, theme builder custom, ajustes visuales pixel-perfect.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Menú avatar expone acción de apariencia.
- **AC-02**: Seleccionar tema aplica cambio sin recarga completa.
- **AC-03**: Tema se persiste en `users.theme` y se recupera al próximo login.
- **AC-04**: Si `users.theme` es nulo/inválido, se usa `generic.light`.
- **AC-05**: Error al guardar preferencia revierte al último tema válido y notifica.
- **AC-06**: E2E valida cambio de tema y persistencia.

### Escenarios Gherkin

```gherkin
Feature: Apariencia y temas

  Scenario: Tema por defecto generic.light
    Given un usuario sin users.theme
    When accede al shell post-login
    Then la interfaz usa el tema generic.light

  Scenario: Cambiar y persistir tema
    Given un usuario autenticado
    When selecciona un tema desde Apariencia en el menú avatar
    Then la UI aplica el tema inmediatamente
    And users.theme se persiste
    And en el próximo login ve el mismo tema

  Scenario: Tema inválido en base de datos
    Given un usuario con users.theme inválido
    When accede al portal
    Then se aplica generic.light sin error fatal
```

---

## 3) Reglas de Negocio

1. **RN-01**: Tema por defecto del producto es `generic.light`.
2. **RN-02**: La preferencia de tema es individual por usuario en MONO.
3. **RN-03**: Solo se aceptan temas del catálogo cerrado MVP.
4. **RN-04**: Debe existir un único tema activo por sesión.
5. **RN-05**: No se presenta configuración de tema por empresa.

---

## 4) Impacto en Datos

### Tablas afectadas
- `users.theme` (lectura/escritura de preferencia de apariencia).

### Seed mínimo para tests
- Usuario con `theme = null`.
- Usuario con `theme = generic.light`.
- Usuario con `theme = generic.dark` (si está en catálogo MVP).

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/users/me/preferences/theme` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### PATCH `/api/v1/users/me/preferences/theme`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "theme": "generic.dark"
}
```

**Response 200:** envelope con `theme` persistido y aplicado.

**Response 401:** no autenticado.

**Response 403:** sin permiso para actualizar preferencias (si aplica policy explícita).

**Response 422:** tema fuera del catálogo permitido.

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controller/DTO del endpoint de tema.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403/422 documentadas.
- [ ] Enumeración de temas permitidos en schema.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar/confirmar fila para `PATCH /api/v1/users/me/preferences/theme`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/theme/model/supportedThemes.ts` (nuevo): catálogo de temas permitidos.
- `frontend/src/features/theme/api/updateThemePreference.ts` (nuevo): persistencia de tema.
- `frontend/src/features/theme/hooks/useThemePreference.ts` (nuevo): carga/aplicación/fallback.
- `frontend/src/features/theme/components/ThemeSelectorModal.tsx` (nuevo): selector invocado desde avatar.
- `frontend/src/features/avatar/components/AvatarMenu.tsx`: entrada "Apariencia".
- `frontend/src/app/App.tsx`: aplicación de clase/atributo de tema en contenedor raíz.

### data-testid sugeridos
- `themeSelectorOpen`
- `themeOption-{themeKey}`
- `themeApplyButton`
- `themeCurrentValue`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer endpoint `PATCH /api/v1/users/me/preferences/theme` con catálogo cerrado | OpenAPI + validaciones |
| T2 | Frontend | Implementar selector de tema desde avatar y aplicación en runtime | Cambio inmediato de UI |
| T3 | Frontend | Implementar fallback `generic.light` para tema nulo/inválido | Arranque robusto |
| T4 | Frontend | Sincronizar tema persistido al iniciar sesión | Persistencia validada |
| T5 | Tests | Integration API + E2E de cambio y persistencia | Casos verdes |
| T6 | Docs | Actualizar matriz endpoint ↔ permiso | Trazabilidad completa |

---

## 8) Estrategia de Tests

- **Unit:** validación de catálogo y lógica fallback de tema.
- **Integration:** endpoint de tema con 200/401/403/422.
- **E2E:**  
  - cambiar tema desde avatar y verificar clase de tema en raíz;  
  - cerrar/abrir sesión y confirmar persistencia.

---

## 9) Riesgos y Edge Cases

- Catálogo de temas no consolidado puede romper compatibilidad entre frontend/backend.
- Tema inválido cargado desde BD puede dejar UI inconsistente si no hay fallback central.
- Cambios de tema simultáneos (multitab) pueden dejar estado desfasado.
- Diferencias de nombre de tema entre DevExtreme y persistencia interna.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Fallback `generic.light` validado
- [ ] Integración operativa con menú avatar

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

