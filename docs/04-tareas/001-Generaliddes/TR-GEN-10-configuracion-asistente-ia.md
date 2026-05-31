# TR-GEN-10-configuracion-asistente-ia — Configuración personal del Chat Asistente IA

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-configuracion-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-configuracion-asistente-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-catalogo-proveedores-ia; TR-GEN-01-menu-avatar; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-configuracion-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-configuracion-asistente-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Configuración personal del Chat Asistente IA.

### Narrativa
Como usuario autenticado quiero configurar mi proveedor, credencial y modelo para el Chat Asistente IA desde mi perfil para habilitar el uso del chat con consumo asociado a mi propia configuración.

### In scope / Out of scope
- **In scope:** sección de configuración en perfil, lectura de catálogo soportado, persistencia cifrada por usuario, validación de campos obligatorios por proveedor, actualización y deshabilitación de configuración.
- **Out of scope:** configuración compartida por tenant, administración centralizada por otro actor, edición del catálogo desde UI, envío automático a soporte.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede abrir la configuración del chat desde su perfil.
- **AC-02**: Puede seleccionar un proveedor soportado y ver su ayuda de onboarding.
- **AC-03**: Puede guardar `providerId`, `apiKey`, `modelId` y `baseUrl` cuando corresponda.
- **AC-04**: Si el proveedor no requiere `baseUrl`, ese campo no bloquea el guardado.
- **AC-05**: Si faltan datos obligatorios, el guardado es rechazado con mensaje controlado.
- **AC-06**: La configuración guardada queda asociada al usuario actual y no a `users`.
- **AC-07**: El usuario puede actualizar o deshabilitar su configuración.

### Escenarios Gherkin

```gherkin
Feature: Configuración personal del Chat Asistente IA

  Scenario: Guardar configuración válida
    Given un usuario autenticado en su perfil
    When selecciona un proveedor soportado
    And completa credencial y modelo requeridos
    Then la configuración se guarda correctamente
    And queda asociada solo a ese usuario

  Scenario: Proveedor con baseUrl obligatoria
    Given un usuario autenticado
    And selecciona un proveedor que requiere baseUrl
    When intenta guardar sin baseUrl
    Then ve un mensaje de validación
    And la configuración no se guarda

  Scenario: Consultar ayuda de onboarding
    Given un usuario autenticado en la configuración del chat
    When selecciona un proveedor
    Then ve un acceso a la URL de onboarding del proveedor
```

---

## 3) Reglas de Negocio

1. **RN-01**: La primera versión permite una sola configuración activa por usuario.
2. **RN-02**: `providerId` debe existir en el catálogo de proveedores soportados.
3. **RN-03**: `supportUrl` se obtiene desde el catálogo de proveedores y no desde la configuración sensible del usuario.
4. **RN-04**: La credencial debe persistirse cifrada fuera de `users`.
5. **RN-05**: Si el proveedor requiere `baseUrl`, el campo es obligatorio para guardar.
6. **RN-06**: El usuario puede editar su configuración sin afectar la de otros usuarios.
7. **RN-07**: Deshabilitar una configuración no implica necesariamente eliminar la credencial; la definición exacta se cierra en implementación del slice.

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_pedidosweb_asistente_ia_credenciales` (nueva / recomendada): configuración sensible del chat por usuario.
- `pq_pedidosweb_asistente_ia_proveedores` (lectura): fuente de proveedores soportados y `supportUrl`.

### Seed mínimo para tests
- Usuario autenticado sin configuración previa.
- Usuario autenticado con configuración válida activa.
- Usuario autenticado con configuración deshabilitada.
- Proveedor con `requiere_base_url_editable = true`.
- Proveedor con `requiere_base_url_editable = false`.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/chat-assistant/me/configuration` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PUT | `/api/v1/chat-assistant/me/configuration` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/chat-assistant/me/configuration/status` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### GET `/api/v1/chat-assistant/me/configuration`

**Autorización:** usuario autenticado.

**Request:** sin body.

**Response 200:** envelope con la configuración actual del usuario o estado vacío si no existe.

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "providerId": "ollama",
    "modelId": "llama3.1",
    "baseUrl": "http://localhost:11434",
    "isEnabled": true
  }
}
```

**Response 401:** no autenticado.

**Response 403:** sin permiso para consultar su configuración (si aplica policy explícita).

#### PUT `/api/v1/chat-assistant/me/configuration`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "providerId": "ollama",
  "apiKey": "secret-value",
  "modelId": "llama3.1",
  "baseUrl": "http://localhost:11434"
}
```

**Response 200:** envelope con resumen de configuración guardada, sin exponer credencial completa.

```json
{
  "error": 0,
  "respuesta": "chatAssistant.configurationSaved",
  "resultado": {
    "providerId": "ollama",
    "modelId": "llama3.1",
    "baseUrl": "http://localhost:11434",
    "isEnabled": true
  }
}
```

**Response 401:** no autenticado.

**Response 403:** sin permiso para gestionar su configuración (si aplica policy explícita).

**Response 422:** datos obligatorios ausentes o `baseUrl` faltante cuando el proveedor lo requiera.

#### PATCH `/api/v1/chat-assistant/me/configuration/status`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "isEnabled": false
}
```

**Response 200:** envelope con estado actualizado.

**Response 401:** no autenticado.

**Response 403:** sin permiso para gestionar su configuración (si aplica policy explícita).

**OpenAPI (L5-Swagger):**

- [ ] Endpoints documentados en controller/DTO.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403 documentadas.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar filas para `GET /api/v1/chat-assistant/me/configuration`, `PUT /api/v1/chat-assistant/me/configuration` y `PATCH /api/v1/chat-assistant/me/configuration/status`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/profile/components/ChatAssistantSettingsSection.tsx`: formulario principal de configuración personal.
- `frontend/src/features/chatAssistant/api/getMyConfiguration.ts` (nuevo): lectura de configuración actual.
- `frontend/src/features/chatAssistant/api/saveMyConfiguration.ts` (nuevo): guardado de configuración.
- `frontend/src/features/chatAssistant/api/updateMyConfigurationStatus.ts` (nuevo): habilitar/deshabilitar configuración.
- `frontend/src/features/chatAssistant/model/myChatAssistantConfiguration.ts` (nuevo): tipado.
- `frontend/src/features/profile/components/ChatAssistantProviderFields.tsx`: visibilidad y validación de `baseUrl`.
- `frontend/src/features/profile/components/ChatAssistantProviderHelpLink.tsx`: acceso a `supportUrl`.

### data-testid sugeridos
- `chatAssistantSettingsSection`
- `chatAssistantConfigurationProviderSelect`
- `chatAssistantConfigurationApiKeyInput`
- `chatAssistantConfigurationModelIdInput`
- `chatAssistantConfigurationBaseUrlInput`
- `chatAssistantConfigurationSaveButton`
- `chatAssistantConfigurationStatusToggle`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Crear modelo/repository de configuración personal y persistencia cifrada | Sin uso de `users`; sin exponer credencial |
| T2 | Backend | Exponer endpoints GET/PUT/PATCH de configuración personal | OpenAPI + 401/403/422 |
| T3 | Frontend | Implementar sección de configuración del chat dentro del perfil | AC funcionales visibles |
| T4 | Frontend | Integrar selector de proveedor, `supportUrl` y lógica de `baseUrl` requerida | Validaciones correctas |
| T5 | Tests | Integration API + unit de validación + E2E del formulario | Casos verdes |
| T6 | Docs | Matriz endpoint ↔ permiso y trazabilidad con TR catálogo | Coherencia completa |

---

## 8) Estrategia de Tests

- **Unit:** validación de formulario según proveedor; regla `baseUrl` requerida/no requerida.
- **Integration:** GET/PUT/PATCH configuración personal con 200, 401, 403 y 422.
- **E2E:**  
  - guardar configuración válida;  
  - intentar guardar proveedor que requiere `baseUrl` sin completarla.

---

## 9) Riesgos y Edge Cases

- Exposición accidental de la credencial en responses, logs o errores.
- Divergencia entre regla de `baseUrl` del catálogo y validación del formulario.
- Ambigüedad entre “deshabilitar” y “eliminar” configuración si no se cierra contractualmente.
- Inconsistencia si el catálogo cambia y la configuración del usuario referencia un proveedor inactivo.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Persistencia cifrada fuera de `users`
- [ ] Integración validada con perfil de usuario y catálogo de proveedores

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR
