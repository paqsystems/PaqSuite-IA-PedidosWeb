# HU-101-040-update-01 — Asistente IA: alias `codigo` + búsqueda entre comillas

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-040-asistente-carga-ia-articulos-grabar-update-01 |
| **HU base** | [HU-101-040-asistente-carga-ia-articulos-grabar](../../101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) |
| **SPEC origen** | [SPEC-101-19-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #14 · **08/09/2026** |
| **TR** | [TR-SPEC-101-19-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update-01.md) |
| **Última actualización** | 2026-09-08 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario en carga con el asistente IA**,  
quiero **decir “codigo …” igual que “artículo …” y poner descripciones con espacios entre comillas**,  
para **agregar renglones más rápido sin que el parseo parta el texto de búsqueda**.

## Reglas de negocio

1. **RN-CC14-D01 (D1-27):** Los prefijos `codigo(s)` / `código(s)`, `cod.` / `cod`, `cód.` / `cód` son sinónimos de artículo/producto/item para disparar alta o mutación de renglón.
2. **RN-CC14-D02 (D1-28):** Si entre un sinónimo de artículo (incl. los de RN-CC14-D01) y un sinónimo de cantidad (`cantidad` / `canti` / `cant` / `cant.`) hay texto entre comillas, ese contenido **tal cual** es el criterio de búsqueda sobre **código o descripción** del artículo (espacios incluidos). Cantidad, precio y bonificación se interpretan fuera de las comillas.

## Criterios de aceptación

- [ ] **CA-CC14-D01:** `codigo ABC-01 cantidad 3` agrega el renglón igual que `artículo ABC-01 cantidad 3` (match único).
- [ ] **CA-CC14-D02:** `cod. "ajo en polvo 25 kg" cant: 100` → `q` literal `ajo en polvo 25 kg`; cantidad 100.
- [ ] **CA-CC14-D03:** `código "almendra carmel 20/22" canti 10` busca en código o descripción con los espacios; no tokeniza el tramo entre comillas.
- [ ] **CA-CC14-D04:** Prefijos `art.` / `item` / `producto` con el mismo patrón de comillas + cantidad siguen comportándose igual (regresión).

## Escenarios Gherkin

```gherkin
Feature: Alias codigo y comillas en asistente

  Scenario: Prefijo codigo dispara alta
    Given un comprobante con cliente y LLM
    When pide "codigo ABC-01 cantidad 2"
    And ABC-01 es match unico
    Then se agrega un renglon con cantidad 2

  Scenario: Comillas conservan espacios
    Given un comprobante con cliente y LLM
    When pide "articulo \"ajo en polvo 25 kg\" cantidad 100"
    Then el texto de busqueda q es exactamente "ajo en polvo 25 kg"
    And la cantidad es 100
```

## Fuera de alcance

- Consultas de stock/historial por chat.
- Cambios de UI del panel del asistente.
