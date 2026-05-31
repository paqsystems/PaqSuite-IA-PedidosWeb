# TR-GEN-10-imagenes-asistente-ia — Adjuntos de imágenes en el chat

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-10-imagenes-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-imagenes-asistente-ia.md) |
| **SPEC relacionada** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Should |
| **Dependencias** | TR-GEN-10-configuracion-asistente-ia; TR-GEN-10-chat-documental |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-30 |

**Origen:** [HU-GEN-10-imagenes-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-imagenes-asistente-ia.md)  
**Referencia SPEC:** [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Adjuntos de imágenes en el chat.

### Narrativa
Como usuario autenticado con un proveedor compatible quiero adjuntar imágenes a mi consulta del chat para explicar mejor errores, pantallas o documentos relevantes del sistema.

### In scope / Out of scope
- **In scope:** adjuntar imágenes a una consulta, interacciones con texto/solo imágenes/combinadas, validación de formatos `png`, `jpg`, `jpeg`, `webp`, máximo `5 MB` por archivo, máximo `4` imágenes por interacción, mensajes controlados cuando la capacidad no esté disponible.
- **Out of scope:** persistencia de imágenes en el portal, edición de imágenes en UI, garantías de OCR o interpretación perfecta, tipos de archivo distintos a los definidos.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Un usuario con proveedor compatible puede adjuntar imágenes válidas.
- **AC-02**: El sistema admite interacciones con texto, solo imágenes o combinación de ambos.
- **AC-03**: El sistema rechaza formatos no admitidos con error controlado.
- **AC-04**: El sistema rechaza imágenes que exceden `5 MB` con error controlado.
- **AC-05**: El sistema rechaza más de `4` imágenes por interacción.
- **AC-06**: Si el proveedor/modelo no soporta visión, la UI informa indisponibilidad.
- **AC-07**: Las imágenes no se persisten como histórico del portal.

### Escenarios Gherkin

```gherkin
Feature: Adjuntos de imágenes en el chat

  Scenario: Enviar consulta con texto e imágenes
    Given un usuario autenticado con proveedor compatible con imágenes
    When adjunta una o más imágenes válidas
    And envía una consulta
    Then la consulta es procesada correctamente

  Scenario: Proveedor sin soporte de visión
    Given un usuario autenticado con proveedor sin soporte de imágenes
    When intenta adjuntar una imagen
    Then ve un mensaje de indisponibilidad
    And el chat no se rompe

  Scenario: Exceder límites de imágenes
    Given un usuario autenticado
    When intenta adjuntar un archivo inválido o exceder los límites
    Then recibe un error controlado
    And la interacción no se envía
```

---

## 3) Reglas de Negocio

1. **RN-01**: Solo se permiten imágenes si el proveedor/modelo activo soporta visión.
2. **RN-02**: La interacción puede tener texto, imágenes o ambos.
3. **RN-03**: Cada archivo no puede superar `5 MB`.
4. **RN-04**: El máximo por interacción es `4` imágenes.
5. **RN-05**: Las imágenes se envían al proveedor externo configurado y se descartan tras el análisis.
6. **RN-06**: Un formato inválido o un exceso de tamaño no debe romper la aplicación.

---

## 4) Impacto en Datos

### Tablas afectadas
- Sin tabla nueva obligatoria en este slice.
- Lectura indirecta de `pq_pedidosweb_asistente_ia_credenciales` y/o catálogo para resolver soporte de visión.

### Seed mínimo para tests
- Usuario autenticado con configuración válida y proveedor/modelo con soporte de visión.
- Usuario autenticado con configuración válida y proveedor/modelo sin soporte de visión.
- Archivos de prueba válidos en formatos admitidos.
- Archivo de prueba que exceda `5 MB`.

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

No introduce endpoint exclusivo si los adjuntos se resuelven dentro de `POST /api/v1/chat-assistant/messages`.

Si la implementación necesitara multipart/form-data, endpoint separado o storage temporal explícito, deberá abrirse ajuste controlado del slice sin ampliar alcance funcional.

### 5.2 Integración con el endpoint del chat

El request del chat debe poder transportar una estructura equivalente a:

```json
{
  "message": "Necesito ayuda con esta pantalla",
  "images": [
    {
      "fileName": "captura.png",
      "mimeType": "image/png",
      "contentBase64": "..."
    }
  ]
}
```

Validaciones funcionales mínimas:

- hasta `4` imágenes por interacción;
- formatos `png`, `jpg`, `jpeg`, `webp`;
- máximo `5 MB` por archivo;
- rechazo controlado si `supportsVision` no aplica para la configuración activa.

**OpenAPI (L5-Swagger):**

- [ ] Payload documentado en controller/DTO del slice de chat.
- [ ] Restricciones de cantidad, formato y tamaño documentadas.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403/422 documentadas.
- [ ] Envelope documentado.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Si no hay endpoint nuevo, confirmar que la fila de `POST /api/v1/chat-assistant/messages` cubre adjuntos de imágenes.
- [ ] Si hubiera endpoint separado, agregar fila correspondiente en `matriz-permisos-mvp.md`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/chatAssistant/components/ChatAssistantImagePicker.tsx` (nuevo): selección y previsualización básica.
- `frontend/src/features/chatAssistant/components/ChatAssistantImageValidationMessage.tsx` (nuevo o ajuste): errores de formato/tamaño/límite.
- `frontend/src/features/chatAssistant/components/ChatAssistantComposer.tsx` (ajuste): soporte texto + imágenes o solo imágenes.
- `frontend/src/features/chatAssistant/utils/validateChatAssistantImages.ts` (nuevo): validación de cantidad, formatos y tamaño.
- `frontend/src/features/chatAssistant/model/chatAssistantImage.ts` (nuevo): tipado del adjunto.
- `frontend/src/features/chatAssistant/api/sendChatAssistantMessage.ts` (ajuste): payload con imágenes cuando aplique.

### data-testid sugeridos
- `chatAssistantImagePicker`
- `chatAssistantImagePreview`
- `chatAssistantImageValidationMessage`
- `chatAssistantImageUnsupportedProviderMessage`
- `chatAssistantImageRemoveButton`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Añadir selección y remoción de imágenes en el composer | Hasta 4 imágenes visibles |
| T2 | Frontend | Validar formato, tamaño y límite de cantidad | Mensajes de error controlados |
| T3 | Frontend | Bloquear adjuntos cuando proveedor/modelo no soporte visión | Mensaje claro de indisponibilidad |
| T4 | Backend | Aceptar adjuntos de imágenes dentro del flujo del chat y validarlos | Sin persistencia local |
| T5 | Tests | Unit validadores + E2E formatos/límites/indisponibilidad | AC verdes |
| T6 | Docs | OpenAPI y trazabilidad de restricciones de imágenes | Coherencia documental |

---

## 8) Estrategia de Tests

- **Unit:** validador de formatos, tamaño máximo, cantidad máxima y disponibilidad por soporte de visión.
- **Integration:** validación de request del chat con imágenes válidas e inválidas; 401/403/422 donde aplique.
- **E2E:**  
  - adjuntar imagen válida y enviar consulta;  
  - intentar adjuntar formato inválido;  
  - intentar exceder `5 MB`;  
  - intentar exceder `4` imágenes;  
  - proveedor sin soporte de visión muestra indisponibilidad.

---

## 9) Riesgos y Edge Cases

- Detección de soporte de visión inconsistente entre proveedor y modelo activo.
- Archivos grandes o múltiples que degraden la UX antes de validar.
- Diferencias de MIME real vs extensión del archivo.
- Navegadores con comportamientos distintos al seleccionar múltiples imágenes.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Imágenes no persistidas como histórico del portal
- [ ] Validaciones de formato/tamaño/cantidad verificadas

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR
