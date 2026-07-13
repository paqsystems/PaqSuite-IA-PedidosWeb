# HU-101-039 — Asistente IA carga: cliente, cabecera y cambio de cliente

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-039-asistente-carga-ia-cliente-cabecera |
| **SPEC origen** | [SPEC-101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | Enriquecida (2026-07-13) |
| **TR** | [TR-SPEC-101-19](../../04-tareas/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Dependencias** | HU-101-037; HU-101-004; HU-101-005; SPEC-101-10 |
| **HUs relacionadas** | HU-101-040 (artículos/grabar) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| A — Selección cliente (CA-A01) | CA-01 … CA-05 |
| B — Cabecera lookups (CA-B01) | CA-06 … CA-08 |
| C — Campos libres (CA-C01) + C ampliado D1-23 | CA-09 … CA-10, CA-16 … CA-17 |
| I — Cambio cliente (CA-I01, D1-18) | CA-11 … CA-14 |
| Moneda (D1-14) | CA-08 |
| Listas 0/1/2–10/>10 | CA-01 … CA-03 |
| Solo lectura / perfil C | CA-05, CA-15 |

## Narrativa

Como **usuario en carga**,  
quiero **elegir o cambiar cliente y ajustar cabecera/leyendas por el asistente**,  
para **armar el comprobante con las mismas reglas que la UI DevExtreme**.

## Contexto funcional

SPEC-101-19 capacidades A, B, C, I: equivalencia con combobox cliente, `CabeceraInicial`, lookups y diálogo de cambio de cliente. El LLM no es fuente de permisos.

## Alcance incluido

- Búsqueda de cliente (código / razón social / nombre / fantasía) con visibilidad `GET /api/v1/clientes`.
- Patrones 0 / 1 / 2–10 / >10 (informar / auto + init cabecera / lista numerada / refinar).
- Tras selección: misma inicialización de cabecera que el combobox.
- Lookups de cabecera (perfil, cond. venta, transporte, dirección, lista precios, bonif. cabecera 1–3, moneda si UI lo permite).
- Campos libres / C ampliado: nivel (`NivelExtremo`), observaciones, leyendas 1…5, expreso, dirección expreso, **fecha de entrega**, **dirección de entrega** (lookup `idDe`).
- Permisos: `ModificaListaPrec*`, `ModificaCondVta*`, `ModificaDirEntr*`, `ModificaExpreso*`, `ModificaBonCli*` (perfil **C** never en bonif/lista).
- Cambio de cliente con datos: advertencia + confirmación (`sí`/`si`, `confirmo`, `aceptado`) o rechazo (`no`, `cancelar`).
- Sync UI sin F5.
- Rechazo en solo lectura; perfil cliente no elige otro cliente.

## Fuera de alcance

- Artículos, grabar, apply imagen → HU-101-040.
- Consultas E–H → HU-101-041/042.
- Panel/gate → HU-101-037.

## Reglas de negocio

1. Paridad con UI: mismos services/guards/mensajes.
2. Sin permiso ERP `Modifica*` → `denied`, no mutar.
3. Perfil **C**: cliente fijo de sesión; no ofrecer otro.
4. Moneda vía IA solo si la UI permite editarla (D1-14).
5. Confirmación I: frases D1-18 (ES) + i18n en TR.
6. Tras confirmar cambio: limpiar + aplicar A.

## Criterios de aceptación

- [ ] **CA-01:** 2–10 clientes → lista numerada; usuario elige por número → init cabecera.
- [ ] **CA-02:** 1 match → auto-selección + init cabecera igual combobox.
- [ ] **CA-03:** >10 matches → pedir refinar; no listar.
- [ ] **CA-04:** 0 matches → informar; pedir otro criterio.
- [ ] **CA-05:** Perfil cliente no puede seleccionar otro cliente vía asistente.
- [ ] **CA-06:** Cambio de lookup de cabecera con permiso aplica y refleja efectos UI (p. ej. lista → precios).
- [ ] **CA-07:** Sin permiso de campo → mensaje; cabecera intacta.
- [ ] **CA-08:** Intento de cambiar moneda sin UI editable → `denied`.
- [ ] **CA-09:** Leyenda N / nivel / observaciones asignan solo ese campo y confirman en el hilo.
- [ ] **CA-10:** Nivel respeta `NivelExtremo` (0/100 si aplica).
- [ ] **CA-16:** Bonif. cabecera 1/2/3 y expreso/dir. expreso con `ModificaBonCli*` / `ModificaExpreso*`; sin permiso → denied.
- [ ] **CA-17:** Transporte / cond. venta / perfil / lista / fecha entrega / dirección entrega vía chat con permisos y lookups (2–10 → lista).
- [ ] **CA-11:** Con cliente/renglones y pedido de otro cliente → pide confirmación antes de cambiar.
- [ ] **CA-12:** Respuesta `confirmo`/`sí`/`aceptado` → limpia y aplica nuevo cliente.
- [ ] **CA-13:** Respuesta `no`/`cancelar` → no cambia cliente ni datos.
- [ ] **CA-14:** Sin confirmar (otro mensaje no afirmativo) → no cambia.
- [ ] **CA-15:** Modo solo lectura → mutaciones A/B/C/I rechazadas.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Lista pending + nuevo “cambiar transporte” | Cancela pending (D1-04 vía 037) |
| Confirmación en otro idioma | Equivalentes i18n D1-18 |

## Escenarios Gherkin

```gherkin
Feature: Cliente y cabecera via asistente

  Scenario: Lista numerada de clientes
    Given un vendedor con LLM en carga nueva
    When pide "cliente San"
    And hay 3 matches
    Then ve una lista numerada 1 a 3
    When responde "2"
    Then el cliente 2 queda seleccionado
    And la cabecera se inicializa como con el combobox

  Scenario: Cambio de cliente con confirmacion
    Given un comprobante con cliente y renglones
    When pide otro cliente
    Then el asistente pide confirmacion
    When responde "confirmo"
    Then el cliente cambia y los datos previos se limpian

  Scenario: Cabecera sin permiso
    Given un usuario sin ModificaListaPrec
    When pide cambiar la lista de precios
    Then recibe denied
    And la lista no cambia
```

## Supuestos explícitos

- Los campos exactos de cabecera editables siguen la matriz vigente de pantalla-carga / parámetros ERP.
- Equivalentes i18n de D1-18 se detallan en TR.

## Preguntas abiertas

Ninguna bloqueante.

## Riesgos de ambigüedad

- Ambigüedad de lookup (varios transportes similares) se resuelve con lista ≤10; si >10 refine.

## Veredicto B1

**Lista para TR:** Sí.
