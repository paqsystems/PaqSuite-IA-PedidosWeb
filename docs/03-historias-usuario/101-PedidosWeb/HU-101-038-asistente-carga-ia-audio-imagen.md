# HU-101-038 — Asistente IA carga: audio (Web Speech) e imagen (entrada)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-038-asistente-carga-ia-audio-imagen |
| **SPEC origen** | [SPEC-101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | Enriquecida (2026-07-13) |
| **TR** | [TR-SPEC-101-18](../../04-tareas/101-PedidosWeb/TR-SPEC-101-18-asistente-carga-ia-shell.md) |
| **Dependencias** | HU-101-037; HU-GEN-10-imagenes-asistente-ia (límites visión); SPEC-001-10 |
| **HUs relacionadas** | HU-101-040 (aplicación extracto imagen K) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Audio L / Web Speech (D1-15, CA-L01) | CA-01 … CA-04 |
| Imagen entrada K (CA-K-IN01) | CA-05 … CA-09 |
| Límites SPEC-001-10 | CA-07 |
| Sin persistir imágenes | CA-08 |
| Gate sin LLM aplica a audio/imagen | CA-10 |

## Narrativa

Como **usuario en la pantalla de carga**,  
quiero **dictar por micrófono o adjuntar una imagen al asistente**,  
para **cargar datos sin tipear todo el texto cuando el proveedor lo permite**.

## Contexto funcional

SPEC-101-18 §5–6: el micrófono usa **Web Speech** → texto → misma pipeline que el chat escrito. Las imágenes se adjuntan desde el panel; con visión habilitada inician extracto; sin visión se informa. La hidratación del borrador es HU-101-040.

## Alcance incluido

- Botón micrófono (`cargaAsistenteIaMic`): Web Speech API; texto resultante al input/pipeline.
- Permiso micrófono denegado → mensaje i18n; sin mutar borrador.
- Fallo de transcripción → mensaje; sin mutar.
- Botón adjuntar (`cargaAsistenteIaAttach`): selección de imagen(es).
- Límites alineados a Asistente IA (p. ej. hasta 4 imágenes, tamaño/formatos SPEC-001-10).
- Sin `supports_vision` → mensaje; pedir texto o audio; no llamar visión.
- Con visión → envío a orquestación modalidad `imagen` (extracto hacia 040).
- No persistir imágenes en BD del portal.
- Modalidad en auditoría: `audio` | `imagen`.

## Fuera de alcance

- STT del proveedor LLM.
- Validación/aplicación de candidatos de imagen al borrador → HU-101-040.
- Lógica de mutaciones/consultas.

## Reglas de negocio

1. Audio = solo Web Speech en MVP (D1-15).
2. Texto dictado usa la misma pipeline y gate que texto tipeado.
3. Imágenes solo si el proveedor/modelo activo soporta visión.
4. Gate sin LLM bloquea audio e imagen igual que texto.
5. Imágenes no se guardan como histórico del portal.

## Criterios de aceptación

- [ ] **CA-01:** Con permiso de micrófono, dictado produce texto que se envía por la misma pipeline que el input.
- [ ] **CA-02:** Permiso denegado → mensaje claro; formulario intacto.
- [ ] **CA-03:** Fallo de transcripción → mensaje; formulario intacto.
- [ ] **CA-04:** Tras dictado exitoso con LLM, el turno se audita con modalidad `audio`.
- [ ] **CA-05:** Con visión habilitada, adjuntar imagen válida inicia extracto (turno modalidad `imagen`).
- [ ] **CA-06:** Sin visión → mensaje de indisponibilidad; no se fuerza extracto.
- [ ] **CA-07:** Se rechazan formatos/tamaño/cantidad fuera de límites SPEC-001-10 con error controlado.
- [ ] **CA-08:** Tras el turno, las imágenes no quedan persistidas en BD del portal.
- [ ] **CA-09:** Adjunto solo desde el panel de carga (no otro menú).
- [ ] **CA-10:** Sin LLM, mic o imagen → mismo mensaje fijo de configuración; sin mutación.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Browser sin Web Speech | Mensaje controlado; no romper pantalla |
| Más de 4 imágenes | Rechazo controlado |
| Imagen > límite MB | Rechazo controlado |

## Escenarios Gherkin

```gherkin
Feature: Audio e imagen en asistente de carga

  Scenario: Dictado a pipeline
    Given un usuario con LLM configurado y permiso de microfono
    When dicta "cliente Acme"
    Then el texto se procesa como un mensaje escrito
    And la modalidad de auditoria es audio

  Scenario: Sin vision
    Given un usuario con LLM sin soporte de vision
    When intenta adjuntar una imagen
    Then ve un mensaje de indisponibilidad
    And el borrador no cambia

  Scenario: Gate sin LLM con imagen
    Given un usuario sin LLM configurado
    When adjunta una imagen
    Then ve el mensaje fijo de configurar Asistente IA
```

## Supuestos explícitos

- Los límites numéricos de imagen se toman de la política vigente SPEC-001-10 / HU-GEN-10-imagenes.
- La calidad del reconocimiento de voz depende del navegador (fuera de control del portal).

## Preguntas abiertas

Ninguna bloqueante.

## Riesgos de ambigüedad

- Capacitor/WebView puede limitar Web Speech; TR debe definir fallback de mensaje en native.

## Veredicto B1

**Lista para TR:** Sí.
