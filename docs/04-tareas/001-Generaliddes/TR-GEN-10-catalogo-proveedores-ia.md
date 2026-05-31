# TR-GEN-10-catalogo-proveedores-ia — Catálogo inicial de proveedores IA

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-catalogo-proveedores-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-catalogo-proveedores-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Should |
| **Dependencias** | HU-GEN-10-configuracion-asistente-ia; SPEC-001-04-configuracion-global; TR-GEN-01-menu-avatar; TR-GEN-02-login-sesion |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-catalogo-proveedores-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-catalogo-proveedores-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Catálogo inicial de proveedores IA.

### Narrativa
Como usuario autenticado que configura el chat quiero elegir entre proveedores IA soportados y ver su documentación de onboarding para configurar mi integración personal con criterios claros y consistentes.

### In scope / Out of scope
- **In scope:** tabla dedicada de catálogo, seed inicial, endpoint protegido de lectura, exposición de `providerId`, nombre visible, capacidades declaradas, requisito de `baseUrl` y `supportUrl`, consumo desde la configuración personal del chat.
- **Out of scope:** ABM de proveedores en UI, inclusión de proveedores fuera del catálogo inicial, validación exhaustiva de modelos en tiempo real.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario ve todos los proveedores del catálogo inicial al configurar el chat.
- **AC-02**: Cada proveedor expone nombre visible y datos suficientes para selección.
- **AC-03**: El usuario puede acceder a la URL de onboarding (`supportUrl`) de cada proveedor.
- **AC-04**: La UI distingue si un proveedor requiere `baseUrl` editable.
- **AC-05**: Todos los proveedores del catálogo inicial están disponibles como opciones soportadas en la primera HU.
- **AC-06**: El catálogo visible en frontend es consistente con el seed inicial y el catálogo documental.

### Escenarios Gherkin

```gherkin
Feature: Catálogo inicial de proveedores IA

  Scenario: Visualizar proveedores soportados
    Given un usuario autenticado en la configuración del chat
    When abre el selector de proveedor
    Then ve todos los proveedores del catálogo inicial

  Scenario: Ver ayuda del proveedor
    Given un usuario autenticado
    When selecciona un proveedor del catálogo
    Then puede acceder a la URL de onboarding asociada

  Scenario: Identificar proveedor con baseUrl editable
    Given un usuario autenticado
    When selecciona un proveedor que requiere endpoint propio
    Then la UI le informa que debe completar baseUrl
```

---

## 3) Reglas de Negocio

1. **RN-01**: El catálogo inicial incluye `ollama`, `openai`, `anthropic`, `googleGemini`, `azureOpenAi`, `openRouter`, `groq` y `mistral`.
2. **RN-02**: Todos los proveedores del catálogo inicial se consideran soportados en la primera HU.
3. **RN-03**: `providerId` es la clave lógica estable usada por frontend y backend.
4. **RN-04**: `supportUrl` es un dato del catálogo de proveedores y no de la configuración sensible del usuario.
5. **RN-05**: Si un proveedor requiere `baseUrl`, el catálogo debe exponer esa condición para que la UI la comunique claramente.
6. **RN-06**: El catálogo visible al usuario se obtiene desde backend y no debe quedar hardcodeado en frontend.

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_pedidosweb_asistente_ia_proveedores` (nueva / recomendada en producto): catálogo funcional de proveedores IA.

### Seed mínimo para tests
- Fila activa por cada proveedor inicial: `ollama`, `openai`, `anthropic`, `googleGemini`, `azureOpenAi`, `openRouter`, `groq`, `mistral`.
- Al menos un proveedor con `requiere_base_url_editable = true`.
- Al menos un proveedor con `soporta_imagenes = false` o `false` en seed de prueba si se quiere validar degradación visual del selector.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/chat-assistant/providers` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### GET `/api/v1/chat-assistant/providers`

**Autorización:** usuario autenticado.

**Request:** sin body.

**Response 200:** envelope con catálogo inicial de proveedores activos.

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [
      {
        "providerId": "ollama",
        "displayName": "Ollama",
        "supportsVision": true,
        "requiresBaseUrl": true,
        "supportUrl": "https://ollama.com/download"
      }
    ]
  }
}
```

**Response 401:** no autenticado.

**Response 403:** sin permiso para leer catálogo (si aplica policy explícita).

**OpenAPI (L5-Swagger):**

- [ ] Endpoint documentado en controller/DTO.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403 documentadas.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar fila para `GET /api/v1/chat-assistant/providers`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/api/getProviderCatalog.ts` (nuevo): lectura del catálogo desde backend.
- `frontend/src/features/chatAssistant/model/providerCatalog.ts` (nuevo): tipado de items del catálogo.
- `frontend/src/features/profile/components/ChatAssistantSettingsSection.tsx` (nuevo o ajuste): selector de proveedor con datos del catálogo.
- `frontend/src/features/profile/components/ChatAssistantProviderHelpLink.tsx` (nuevo o ajuste): acceso a `supportUrl`.
- `frontend/src/features/profile/components/ChatAssistantProviderFields.tsx` (nuevo o ajuste): indicación visual de si `baseUrl` es requerido.

### data-testid sugeridos
- `chatAssistantProviderSelect`
- `chatAssistantProviderOptionOllama`
- `chatAssistantProviderSupportLink`
- `chatAssistantProviderRequiresBaseUrlHint`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Crear modelo/repository del catálogo y seed inicial de proveedores | Seed consistente con catálogo documental |
| T2 | Backend | Exponer `GET /api/v1/chat-assistant/providers` | OpenAPI + 401/403 |
| T3 | Frontend | Consumir el catálogo en la configuración personal del chat | Selector visible con todos los proveedores |
| T4 | Frontend | Exponer `supportUrl` e indicador de `baseUrl` requerida | UX clara de onboarding |
| T5 | Tests | Integration endpoint + unit mapping catálogo + E2E selector/ayuda | AC verdes |
| T6 | Docs | Matriz endpoint ↔ permiso y trazabilidad con catálogo documental | Coherencia visible |

---

## 8) Estrategia de Tests

- **Unit:** mapeo `providerId` → modelo frontend; interpretación de `requiresBaseUrl` y `supportUrl`.
- **Integration:** `GET /api/v1/chat-assistant/providers` con 200/401/403.
- **E2E:**  
  - abrir configuración y ver todos los proveedores del catálogo;  
  - seleccionar proveedor con `supportUrl` y verificar acceso visible a onboarding.

---

## 9) Riesgos y Edge Cases

- Desincronización entre catálogo documental y seed técnico.
- Provider marcado como soportado pero con metadatos incompletos para la UI.
- `supportUrl` desactualizada o rota.
- Cambios futuros en el catálogo pueden romper orden o snapshots visuales si no se estabiliza la presentación.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Seed inicial consistente con catálogo funcional aprobado
- [ ] Integración validada con configuración personal del chat

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR
