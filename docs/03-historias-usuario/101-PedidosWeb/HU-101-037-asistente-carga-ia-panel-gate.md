# HU-101-037 — Asistente IA carga: panel, gate BYOK y orquestación

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-037-asistente-carga-ia-panel-gate |
| **SPEC origen** | [SPEC-101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | Enriquecida (2026-07-13) |
| **TR** | [TR-SPEC-101-18](../../04-tareas/101-PedidosWeb/TR-SPEC-101-18-asistente-carga-ia-shell.md) |
| **Dependencias** | HU-101-004…010 (pantalla carga); HU-GEN-10-configuracion-asistente-ia (BYOK); SPEC-001-10 |
| **HUs relacionadas** | HU-101-038 (audio/imagen); HU-101-039/040 (mutaciones); HU-101-041/042 (consultas) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Panel pie, colapsable, testids, i18n | CA-01 … CA-04 |
| Ruedita → Preferencias Asistente IA (M) | CA-05 |
| Gate sin LLM (CA-UX02) | CA-06, CA-07 |
| API dedicada / pipeline (D1-01) | CA-08 |
| Hilo efímero (D1-03) | CA-09 |
| Pending lista + nueva intención (D1-04) | CA-10 |
| Auditoría log (D1-17) | CA-11 |
| Altura hilo mín. 270px / hasta 33vh (D1-16) | CA-03 |
| CA-SYNC01 (hook mutación) | CA-12 (contrato; implementación mutaciones en 039/040) |

## Narrativa

Como **usuario que carga un pedido o presupuesto**,  
quiero **un asistente conversacional al pie del formulario, con la misma configuración LLM de Preferencias**,  
para **operar la carga por texto sin salir de la pantalla y sin usar el chat documental**.

## Contexto funcional

SPEC-101-18 define el canal operativo embebido en `/pedidos/carga`: panel UI, reuso BYOK (`pq_asistente_ia_*`), gate sin configuración, API dedicada (no corpus documental), hilo efímero y auditoría en log. Las acciones de negocio y consultas viven en HU-101-039…042; esta HU entrega el **shell** y el **contrato de turno**.

## Actores

| Actor | Comportamiento |
|-------|----------------|
| Vendedor / Supervisor / Cliente | Usa el panel en carga (alta o edición según reglas 101-19) |
| Sin config LLM | Solo recibe mensaje fijo + CTA Preferencias |

## Alcance incluido

- Panel al pie de `PedidosCargaPage` / carga mobile equivalente (`cargaAsistenteIaPanel`).
- Default **colapsado**; expandido con hilo **mín. 270px** (hasta **33vh**) + scroll interno.
- Toolbar: input texto, enviar, micrófono, adjuntar imagen, ruedita config (`cargaAsistenteIaInput|Send|Mic|Attach|Config`).
- Ruedita → misma ruta Preferencias → Asistente IA que el chat documental.
- Gate FE+BE: sin LLM habilitado → mensaje fijo i18n; sin llamar proveedor; sin mutar/consultar vía asistente.
- Endpoint(s) dedicados de orquestación de carga (p. ej. `POST /api/v1/pedidos/carga/asistente/*`); credencial = misma resolución que chat documental (D1-02).
- Hilo conversacional **efímero** (se pierde al salir/cancelar la pantalla).
- Cancelación de elección numerada pendiente ante nueva intención (D1-04).
- Log de auditoría: usuario, timestamp, modalidad, intención, acción, resultado.
- i18n 5 locales; DevExtreme donde corresponda.
- Contrato de respuesta hacia UI: texto + opcional `action`/`resultado` para sync (CA-SYNC).

## Fuera de alcance

- Transcripción audio y adjunto imagen → HU-101-038.
- Mutaciones A–D, I, J, K apply → HU-101-039/040.
- Consultas E–H → HU-101-041/042.
- Chat documental / nuevas tablas BYOK.
- Tabla BD de auditoría.

## Reglas de negocio

1. El asistente de carga **no** usa el corpus del Chat Asistente IA documental.
2. Sin configuración LLM válida: mensaje fijo ES ref. *Debe configurar primero el proveedor LLM. Ir a **Asistente IA** (Preferencias).* + CTA.
3. Hilo no se persiste en BD.
4. Nueva intención cancela pending de lista numerada.
5. Backend revalida permisos; no confiar solo en el LLM.
6. Mobile: pie/sheet sin salir del flujo de carga; exclusiones mobile del producto.

## Criterios de aceptación

- [ ] **CA-01:** En `/pedidos/carga` el panel aparece al pie (debajo de observaciones/totales/toolbar grabación).
- [ ] **CA-02:** Panel inicia **colapsado**; al expandir permite colapsar de nuevo.
- [ ] **CA-03:** Hilo expandido nunca por debajo de 270px; altura `max(270px, 33vh)`; scroll interno.
- [ ] **CA-04:** Existen `data-testid` estables del panel, input, send, mic, attach, config; textos vía i18n.
- [ ] **CA-05:** Ruedita navega/abre Preferencias → Asistente IA (mismo destino que chat documental).
- [ ] **CA-06:** Sin LLM: enviar texto muestra mensaje fijo; no hay llamada al proveedor; formulario intacto.
- [ ] **CA-07:** Gate también se aplica en servidor (request sin credencial válida → mismo comportamiento).
- [ ] **CA-08:** El turno usa API dedicada de carga (no el endpoint de chat documental).
- [ ] **CA-09:** Al salir de la pantalla de carga, el hilo no reaparece al volver (efímero).
- [ ] **CA-10:** Con lista numerada pendiente, un nuevo prompt de otra intención cancela la pending.
- [ ] **CA-11:** Cada turno deja traza en `storage/logs` (o canal LOG configurado) con modalidad/intención/acción/resultado.
- [ ] **CA-12:** Contrato de sync documentado: mutación `ok` actualiza estado visible de carga sin F5 (integrado con 039/040).

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Mic/imagen usados sin LLM | Mismo gate CA-06 (detalle UX en 038) |
| Prompt injection pidiendo saltar permisos | Backend deniega; sin mutación indebida |
| Credencial deshabilitada | Gate como sin LLM |

## Escenarios Gherkin

```gherkin
Feature: Panel asistente IA en carga

  Scenario: Panel al pie colapsado
    Given un usuario autenticado en carga de pedido
    When abre la pantalla
    Then ve el panel del asistente al pie colapsado
    And puede expandirlo y colapsarlo

  Scenario: Gate sin LLM
    Given un usuario sin configuracion LLM habilitada
    When envia un mensaje en el asistente de carga
    Then ve el mensaje fijo para configurar Asistente IA
    And el formulario de carga no cambia
    And no se invoca al proveedor LLM

  Scenario: Hilo efimero
    Given un usuario con LLM configurado
    And un intercambio visible en el hilo
    When sale de la pantalla de carga y vuelve
    Then el hilo anterior no esta presente
```

## Supuestos explícitos

- La resolución de “credencial activa” reutiliza la ya implementada para el chat documental.
- El path exacto del endpoint se fija en TR bajo el prefijo acordado en D1-01.

## Preguntas abiertas

Ninguna bloqueante.

## Riesgos de ambigüedad

- Si mobile carga aún no tiene pie estable, TR debe definir sheet/drawer equivalente sin salir del flujo.

## Veredicto B1

**Lista para TR:** Sí.
