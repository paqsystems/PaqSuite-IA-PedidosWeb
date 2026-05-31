# TR-GEN-10-chat-documental — Chat documental del asistente IA

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-chat-documental](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-chat-documental.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-configuracion-asistente-ia; TR-GEN-10-catalogo-proveedores-ia; TR-GEN-01-menu-avatar; TR-GEN-10-mensajes-asistente-ia |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-chat-documental](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-chat-documental.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Chat documental del asistente IA.

### Narrativa
Como usuario autenticado quiero abrir un chat asistente IA desde el menú avatar y hacer consultas sobre el sistema para recibir orientación operativa apoyada en la documentación aprobada sin perder mi pantalla actual.

### In scope / Out of scope
- **In scope:** ítem `Chat Asistente IA` en avatar, apertura en nueva pestaña del portal, ruta interna del chat, estado vacío con CTA si falta configuración, consultas textuales, corpus documental Fase 1, límites de longitud y UX asociada, respuestas orientativas con referencia documental.
- **Out of scope:** ejecución de acciones dentro del sistema, uso de corpus técnico/metodológico excluido, envío automático a soporte, persistencia de adjuntos.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: El usuario puede abrir `Chat Asistente IA` desde el menú avatar.
- **AC-02**: El chat se abre en nueva pestaña del portal y la pestaña original permanece disponible.
- **AC-03**: La opción de chat puede coexistir con la ayuda externa simple sin reemplazarla.
- **AC-04**: Si el usuario tiene configuración válida, puede enviar consultas textuales y recibir respuesta.
- **AC-05**: Si falta configuración, ve un estado vacío con mensaje claro y CTA al perfil.
- **AC-06**: Una consulta de texto solo mayor a `2.000` caracteres no se envía.
- **AC-07**: Una consulta con texto e imágenes mayor a `1.000` caracteres no se envía.
- **AC-08**: El usuario ve contador visible y mensaje claro cuando supera el máximo permitido.
- **AC-09**: El chat utiliza solo el corpus documental definido para Fase 1.
- **AC-10**: Las respuestas se presentan como orientación operativa.

### Escenarios Gherkin

```gherkin
Feature: Chat documental del asistente IA

  Scenario: Abrir chat desde avatar
    Given un usuario autenticado
    When selecciona "Chat Asistente IA" en el menú avatar
    Then se abre una nueva pestaña del portal con el chat
    And la pestaña original permanece disponible

  Scenario: Consultar con configuración válida
    Given un usuario autenticado con configuración válida del chat
    When envía una consulta textual sobre el sistema
    Then recibe una respuesta orientativa
    And la respuesta se apoya en el corpus documental aprobado

  Scenario: Exceder límite de texto solo
    Given un usuario autenticado con configuración válida del chat
    When intenta enviar una consulta de texto solo mayor a 2000 caracteres
    Then el envío es bloqueado
    And ve un mensaje indicando el máximo permitido

  Scenario: Abrir chat sin configuración
    Given un usuario autenticado sin configuración válida
    When abre el chat
    Then ve un mensaje indicando que falta configuración
    And ve una CTA para ir a su perfil
```

---

## 3) Reglas de Negocio

1. **RN-01**: El chat se abre desde el menú avatar en una nueva pestaña del portal y no embebido en la pantalla actual.
2. **RN-02**: Si el usuario no tiene configuración válida, el chat no falla: informa el faltante y muestra CTA a configuración.
3. **RN-03**: La Fase 1 consulta solo `99-manual-usuario` y documentación operativa funcional estable aprobada.
4. **RN-04**: La respuesta debe posicionarse como orientación útil y no como resolución garantizada.
5. **RN-05**: Si hay referencia documental disponible, debe priorizarse sobre una respuesta puramente generativa.
6. **RN-06**: Una consulta de texto solo no puede superar `2.000` caracteres.
7. **RN-07**: Una consulta que incluya texto e imágenes no puede superar `1.000` caracteres de texto.
8. **RN-08**: La UI debe mostrar contador de caracteres y bloquear el envío cuando se exceda el límite.

---

## 4) Impacto en Datos

### Tablas afectadas
- Sin tabla nueva obligatoria en este slice para Fase 1 del chat documental.
- Lectura indirecta de `pq_pedidosweb_asistente_ia_credenciales` para resolver si el usuario tiene configuración válida.

### Seed mínimo para tests
- Usuario autenticado con configuración válida del chat.
- Usuario autenticado sin configuración válida.
- Corpus documental mínimo de prueba derivado de `99-manual-usuario` / documentación operativa aprobada para validar respuesta no vacía.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| POST | `/api/v1/chat-assistant/messages` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### POST `/api/v1/chat-assistant/messages`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "message": "Necesito ayuda para entender por qué no puedo guardar un pedido."
}
```

**Validaciones funcionales mínimas:**

- `message` obligatorio.
- máximo `2.000` caracteres si la interacción es de texto solo.
- máximo `1.000` caracteres de texto si la interacción incluye imágenes.

**Response 200:** envelope con respuesta orientativa del asistente.

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "reply": "Texto orientativo de la respuesta",
    "references": [
      {
        "title": "Manual de usuario",
        "path": "99-manual-usuario/..."
      }
    ],
    "requiresSupportFollowup": false
  }
}
```

**Response 401:** no autenticado.

**Response 403:** sin permiso para usar el chat (si aplica policy explícita).

**Response 422:** consulta vacía o excede límites de longitud.

**OpenAPI (L5-Swagger):**

- [ ] Endpoint documentado en controller/DTO.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403 documentadas.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar fila para `POST /api/v1/chat-assistant/messages`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/pages/ChatAssistantPage.tsx` (nuevo): página del chat en nueva pestaña.
- `frontend/src/features/chatAssistant/api/sendChatAssistantMessage.ts` (nuevo): envío de consulta textual.
- `frontend/src/features/chatAssistant/model/chatAssistantMessage.ts` (nuevo): tipado de request/response.
- `frontend/src/features/chatAssistant/components/ChatAssistantComposer.tsx` (nuevo): caja de texto con contador y bloqueo.
- `frontend/src/features/chatAssistant/components/ChatAssistantEmptyState.tsx` (nuevo): estado vacío por falta de configuración.
- `frontend/src/features/chatAssistant/components/ChatAssistantReferences.tsx` (nuevo o ajuste): referencias documentales.
- `frontend/src/features/avatar/components/AvatarMenu.tsx` (ajuste): ítem `Chat Asistente IA`.

### data-testid sugeridos
- `avatarMenuItemChatAssistant`
- `chatAssistantPage`
- `chatAssistantComposerInput`
- `chatAssistantCharacterCounter`
- `chatAssistantSubmitButton`
- `chatAssistantEmptyState`
- `chatAssistantEmptyStateConfigurationCta`
- `chatAssistantResponse`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Exponer `POST /api/v1/chat-assistant/messages` con validación de longitud y envelope | OpenAPI + 401/403/422 |
| T2 | Backend | Resolver lectura de configuración válida y estado vacío cuando no exista | Sin crash; contrato consistente |
| T3 | Backend | Integrar consulta al corpus documental Fase 1 y devolver referencias | Respuesta orientativa con trazabilidad |
| T4 | Frontend | Agregar ítem `Chat Asistente IA` al menú avatar y abrir nueva pestaña del portal | UX completa |
| T5 | Frontend | Implementar página de chat, composer con contador y validación de límites | AC visibles |
| T6 | Frontend | Implementar estado vacío con CTA al perfil cuando falte configuración | Mensaje claro y navegación |
| T7 | Tests | Integration API + unit contador/validación + E2E apertura/estado vacío/consulta válida | Casos verdes |
| T8 | Docs | Matriz endpoint ↔ permiso y trazabilidad del corpus Fase 1 | Coherencia documental |

---

## 8) Estrategia de Tests

- **Unit:** contador de caracteres, bloqueo por límite, resolución de estado vacío.
- **Integration:** `POST /api/v1/chat-assistant/messages` con 200/401/403/422.
- **E2E:**  
  - abrir chat desde avatar en nueva pestaña;  
  - abrir chat sin configuración y ver CTA;  
  - enviar consulta válida y ver respuesta;  
  - exceder límite y ver bloqueo/mensaje.

---

## 9) Riesgos y Edge Cases

- Corpus documental incompleto o mal delimitado que genere respuestas fuera de alcance.
- Respuesta sin referencias cuando la documentación sí existe.
- Diferencias entre límite de texto solo y texto + imágenes que confundan la UX si no se comunica bien.
- Apertura en nueva pestaña afectada por políticas del navegador o interacción no considerada como gesto del usuario.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Integración validada con menú avatar y configuración personal
- [ ] Corpus Fase 1 acotado a documentación aprobada

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR
