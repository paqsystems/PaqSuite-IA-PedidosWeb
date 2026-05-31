# TR-GEN-10-mensajes-asistente-ia — Mensajes editables del Chat Asistente IA

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-mensajes-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-mensajes-asistente-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-chat-documental; TR-GEN-10-configuracion-asistente-ia |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-mensajes-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-mensajes-asistente-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Mensajes editables del Chat Asistente IA.

### Narrativa
Como usuario del Chat Asistente IA quiero ver un mensaje inicial claro y un mensaje final de derivación a soporte cuando la IA no tenga suficiente confianza para entender qué esperar del chat y cómo continuar si la respuesta no alcanza.

### In scope / Out of scope
- **In scope:** mensaje inicial editable, mensaje final editable de derivación a soporte, lectura desde archivos Markdown, placeholder de contacto, regla de visualización del cierre solo ante baja confianza.
- **Out of scope:** personalización por usuario, edición de mensajes desde UI, envío automático de correo o ticket a soporte.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Al abrir una conversación nueva se muestra el mensaje inicial.
- **AC-02**: El mensaje inicial proviene del archivo Markdown definido.
- **AC-03**: El mensaje de cierre a soporte proviene del archivo Markdown definido.
- **AC-04**: El cierre a soporte aparece solo en respuestas con baja confianza o insuficiencia de orientación.
- **AC-05**: El contenido admite un placeholder para el dato real de soporte.
- **AC-06**: Si la respuesta es suficientemente confiable, el cierre a soporte no se muestra.

### Escenarios Gherkin

```gherkin
Feature: Mensajes editables del Chat Asistente IA

  Scenario: Mostrar mensaje inicial
    Given un usuario abre una conversación nueva del chat
    Then ve el mensaje inicial definido para la experiencia

  Scenario: Mostrar cierre por baja confianza
    Given un usuario realiza una consulta
    And la IA no tiene confianza suficiente en la respuesta
    Then la respuesta incluye el mensaje final de derivación a soporte

  Scenario: No mostrar cierre cuando la respuesta es suficiente
    Given un usuario realiza una consulta
    And la IA responde con confianza suficiente
    Then la respuesta no incluye el mensaje final de derivación a soporte
```

---

## 3) Reglas de Negocio

1. **RN-01**: El mensaje inicial se resuelve desde archivo Markdown editable del proyecto.
2. **RN-02**: El mensaje de cierre a soporte se resuelve desde archivo Markdown editable del proyecto.
3. **RN-03**: El mensaje final no debe aparecer en todas las respuestas; solo aplica cuando la orientación resulte insuficiente o la IA exprese baja confianza.
4. **RN-04**: El dato de contacto a soporte debe poder completarse sin hardcodear el valor final en la lógica.
5. **RN-05**: La regla concreta para detectar baja confianza debe implementarse como criterio técnico controlado dentro del slice, sin alterar el alcance funcional.

---

## 4) Impacto en Datos

### Tablas afectadas
- Sin tablas nuevas obligatorias en este slice.

### Seed mínimo para tests
- Archivo Markdown de mensaje inicial con contenido válido.
- Archivo Markdown de mensaje de cierre con placeholder de contacto.
- Caso de respuesta con `requiresSupportFollowup = true`.
- Caso de respuesta con `requiresSupportFollowup = false`.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

No introduce endpoint exclusivo si el comportamiento se resuelve como parte de `POST /api/v1/chat-assistant/messages`.

Si la implementación necesitara un endpoint dedicado de assets o contenido, deberá abrirse actualización explícita del slice sin ampliar alcance tácitamente.

### 5.2 Integración con respuesta del chat

El slice requiere que la respuesta del chat pueda transportar una señal técnica equivalente a:

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "reply": "Texto orientativo",
    "references": [],
    "requiresSupportFollowup": true
  }
}
```

Regla:

- `requiresSupportFollowup = true` → UI agrega mensaje final de soporte;
- `requiresSupportFollowup = false` → UI no agrega el mensaje final.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/content/loadInitialMessage.ts` (nuevo o ajuste): carga del mensaje inicial desde el asset/documento definido.
- `frontend/src/features/chatAssistant/content/loadSupportFollowupMessage.ts` (nuevo o ajuste): carga del mensaje final desde el asset/documento definido.
- `frontend/src/features/chatAssistant/components/ChatAssistantInitialMessage.tsx` (nuevo o ajuste): render del mensaje inicial.
- `frontend/src/features/chatAssistant/components/ChatAssistantSupportFollowup.tsx` (nuevo o ajuste): render condicional del cierre de soporte.
- `frontend/src/features/chatAssistant/utils/replaceSupportPlaceholders.ts` (nuevo o ajuste): reemplazo del placeholder de contacto.
- `frontend/src/features/chatAssistant/model/chatAssistantMessage.ts` (ajuste): incluir `requiresSupportFollowup`.

### data-testid sugeridos
- `chatAssistantInitialMessage`
- `chatAssistantSupportFollowup`
- `chatAssistantSupportFollowupHidden`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Cargar mensaje inicial desde archivo Markdown definido | Visible al iniciar conversación |
| T2 | Frontend | Cargar mensaje final de soporte desde archivo Markdown definido | Render correcto |
| T3 | Frontend | Implementar reemplazo de placeholder de contacto | Valor visible sin hardcode |
| T4 | Frontend | Renderizar cierre solo cuando `requiresSupportFollowup = true` | Sin ruido en respuestas confiables |
| T5 | Tests | Unit de carga/render + E2E con y sin cierre de soporte | AC verdes |
| T6 | Docs | Mantener referencia explícita a archivos Markdown y contrato de señal técnica | Trazabilidad consistente |

---

## 8) Estrategia de Tests

- **Unit:** carga de Markdown, reemplazo de placeholder, render condicional según `requiresSupportFollowup`.
- **Integration:** no aplica endpoint exclusivo en esta TR; validar contrato integrado del campo `requiresSupportFollowup` en el slice del chat.
- **E2E:**  
  - abrir conversación nueva y ver mensaje inicial;  
  - respuesta con baja confianza muestra cierre;  
  - respuesta suficiente no muestra cierre.

---

## 9) Riesgos y Edge Cases

- Placeholder sin reemplazar visible en producción.
- Diferencias de formato entre Markdown y render esperado en UI.
- Regla de baja confianza demasiado sensible o demasiado laxa.
- Contenido de mensajes editado con estructura inesperada que degrade la presentación.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Frontend + tests según plan
- [ ] Mensajes cargados desde archivos editables y no hardcodeados
- [ ] Render condicional del cierre validado

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR
