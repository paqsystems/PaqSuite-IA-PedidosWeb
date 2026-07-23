# HU-101-041 — Asistente IA carga: consulta de stock

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-041-asistente-carga-ia-consulta-stock |
| **SPEC origen** | [SPEC-101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | Enriquecida (2026-07-13) |
| **TR** | [TR-SPEC-101-20](../../04-tareas/101-PedidosWeb/TR-SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Dependencias** | HU-101-037; HU-101-018; SPEC-101-07 |
| **HUs relacionadas** | HU-101-042 (F–H) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| E — Stock (CA-E01, CA-E02) | CA-01 … CA-07 |
| Mapping exacto consulta-stock | CA-03, CA-04 |
| `total > 10` → refine (D1-07) | CA-02 |
| Solo lectura / sin inventar | CA-06, CA-07 |
| Datos desde API (D1-12) | CA-01, CA-06 |

## Narrativa

Como **usuario en la pantalla de carga**,  
quiero **consultar stock por código o descripción en el chat del asistente**,  
para **ver disponibilidad sin abrir la consulta de menú**.

## Contexto funcional

SPEC-101-20 E: `GET /api/v1/consultas/stock` con filtro `q`; mapping obligatorio de métricas; tope 10 por `total`; totales de pie sobre filas listadas; decimales 2. No muta el borrador.

## Alcance incluido

- Intención de stock → tool/API stock (nunca inventar números).
- Permiso igual `pw_consultastock` / Permiso_Repo vigente.
- Si `total > 10` → pedir refinar; no listar.
- Si `total` 1–10 → listar con mapping SPEC (código, descripción, stock, comprometido, comprometido web, disponible neto, campos *Base si no null).
- Totales pie: suma de stock, comprometido, comprometidoWeb, disponibleNeto de filas listadas.
- Solo lectura: no cambia cabecera/renglones.

## Fuera de alcance

- Deuda/cheques/historial → HU-101-042.
- Mutaciones → HU-101-039/040.
- Export Excel / pivot desde el chat.

## Reglas de negocio

1. Datos únicamente desde API stock.
2. Contador de refine = `total` del filtro `q` (D1-07).
3. Decimales: 2.
4. Sin permiso → mensaje; sin datos inventados.
5. No muta el comprobante.

## Criterios de aceptación

- [ ] **CA-01:** Pedido de stock con permiso llama `GET /api/v1/consultas/stock` con `q` adecuado.
- [ ] **CA-02:** Si `total > 10` → mensaje de refinar; no lista filas.
- [ ] **CA-03:** Si `total` ≤ 10 → lista con todas las propiedades del mapping SPEC-101-20 E.
- [ ] **CA-04:** Pie muestra sumas de stock, comprometido, comprometidoWeb, disponibleNeto de las filas listadas.
- [ ] **CA-05:** Campos *Base se muestran por fila si no son null; no se totalizan en pie (salvo cambio TR explícito).
- [ ] **CA-06:** Sin permiso stock → mensaje; respuesta sin cifras inventadas.
- [ ] **CA-07:** Tras la consulta, el borrador de carga permanece igual.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| `q` vacío / sin matches | Informar 0; pedir otro criterio |
| Gate sin LLM | Mensaje fijo HU-101-037 |

## Escenarios Gherkin

```gherkin
Feature: Stock via asistente de carga

  Scenario: Listar hasta 10
    Given un usuario con permiso de stock y LLM
    When pregunta stock de "tornillo"
    And total es 4
    Then ve hasta 4 filas con stock comprometido y disponible neto
    And ve totales al pie
    And el formulario de carga no cambia

  Scenario: Refine por total
    Given total de matches mayor a 10
    When pregunta stock con ese criterio
    Then se le pide refinar
    And no se listan las filas

  Scenario: Sin permiso
    Given un usuario sin permiso de consulta stock
    When pregunta stock
    Then recibe mensaje de permiso
    And no ve datos inventados
```

## Supuestos explícitos

- El formato visual del listado en chat (tabla vs líneas) lo define TR; el mapping de campos es obligatorio.

## Preguntas abiertas

Ninguna bloqueante.

## Riesgos de ambigüedad

- Paginación de la API stock (page size) no debe usarse en lugar de `total` para el umbral 10.

## Veredicto B1

**Lista para TR:** Sí.
