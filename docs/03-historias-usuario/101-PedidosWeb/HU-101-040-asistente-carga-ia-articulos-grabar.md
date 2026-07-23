# HU-101-040 — Asistente IA carga: artículos, grabar y extracto imagen

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-040-asistente-carga-ia-articulos-grabar |
| **SPEC origen** | [SPEC-101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | Enriquecida (2026-07-13) |
| **TR** | [TR-SPEC-101-19](../../04-tareas/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Dependencias** | HU-101-037; HU-101-038 (entrada imagen); HU-101-006…010; SPEC-101-10 |
| **HUs relacionadas** | HU-101-039 (cliente/cabecera) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| D — Artículos (CA-D01, CA-D02, D1-06, D1-13, D1-24) | CA-01 … CA-09, CA-17 … CA-21 |
| J — Grabar (CA-J01) | CA-10 … CA-13 |
| K — Apply extracto (CA-K01, D1-05) | CA-14 … CA-16 |
| Permisos precio/bonif; perfil C | CA-06, CA-07 |
| Duplicados / importes | CA-08, CA-09 |

## Narrativa

Como **usuario en carga**,  
quiero **agregar o ajustar renglones, grabar el comprobante y aplicar datos válidos de una imagen**,  
para **completar el pedido/presupuesto con paridad a los botones y grilla de la pantalla**.

## Contexto funcional

SPEC-101-19 D, J, K: lookup de artículos igual que carga; cantidad omitida = **1**; precio/bonif. con `Modifica*`; grabar = mismos flujos HU-101-009/010; imagen aplica solo candidatos válidos sin grabar automático.

## Alcance incluido

- Buscar/agregar artículos (excluir `usa_esc = 'B'`); listas 0/1/2–10/>10.
- Prefijos de renglón (D1-26): `artículo(s)`, `art.`/`art`, `producto(s)`, `prod.`/`prod`, `item(s)`, `it.`/`it`.
- Cantidad > 0; si omitida → **asumir 1** (D1-06); alias `cantidad` / `canti` / `cant`.
- Bonificación inicial como UI (maestro / descuento por cantidad).
- Precio / bonif. línea solo con permiso; perfil **C** nunca.
- Renglón ambiguo para precio/eliminar/modificar: 1 renglón → ese; varios → lista **código — desc · cant · precio · bonif%** (D1-13/D1-24).
- Eliminar/modificar: solo renglones del **borrador**; comillas o descripción al final; conjugados `elimina`/`borra`/`quita`/`saca`; 0 match → mostrar texto buscado.
- Pedido multilínea (D1-25): varios `articulo`/`item`/`it` en el mismo mensaje → varios `addRenglon` (tras cabecera).
- Un código de artículo por comprobante (regla UI).
- Recálculo importes/totales (`renglonesCarga` / services vigentes).
- Grabar pedido / grabar presupuesto vía asistente = mismos botones (validaciones, errores, post-éxito, mail).
- Apply imagen: auto-hidratizar solo válidos (cabecera ampliada + renglones); dudosos/inválidos → lista/errores; no grabar hasta J.
- Sync UI sin F5; solo lectura → denied.

## Fuera de alcance

- Entrada de audio/adjunto UI → HU-101-038.
- Cliente/cabecera → HU-101-039.
- Consultas → HU-101-041/042.

## Reglas de negocio

1. Equivalencia total con UI/services de carga y grabación.
2. Cantidad omitida → 1.
3. Extracto: solo válidos al borrador; nunca grabación implícita.
4. Respetar `NOmodificaPedido`, estados no editables, CC PQ de grabado vigentes.
5. Duplicado de artículo → misma regla que grilla (no segundo renglón del mismo código).

## Criterios de aceptación

- [ ] **CA-01:** 2–10 artículos → lista numerada; elección agrega renglón.
- [ ] **CA-02:** 1 match → agrega renglón; >10 → refine; 0 → informar.
- [ ] **CA-03:** Sin cantidad en el pedido → renglón con cantidad **1**.
- [ ] **CA-04:** Con cantidad explícita > 0 → usa esa cantidad (`cant`/`canti` válidos).
- [ ] **CA-04b:** Prefijos `art.` / `item` / `it` disparan alta de renglón igual que `artículo`.
- [ ] **CA-05:** Cantidad ≤ 0 → validationError; no agrega.
- [ ] **CA-06:** Cambio de precio/bonif. sin `ModificaPrecio*`/`ModificaBonArt*` → denied.
- [ ] **CA-07:** Perfil C no modifica precio/bonif. vía asistente.
- [ ] **CA-08:** Artículo ya en comprobante → misma regla UI de duplicados (mensaje / no segundo renglón indebido).
- [ ] **CA-09:** Tras agregar/cambiar, importes y totales UI coinciden con carga manual equivalente.
- [ ] **CA-10:** “Grabar pedido” vía asistente ejecuta el mismo flujo que el botón (estado 0).
- [ ] **CA-11:** “Grabar presupuesto” vía asistente = botón presupuesto (estado 99).
- [ ] **CA-12:** Errores de grabación = misma lista/modal que UI.
- [ ] **CA-13:** Éxito = mismo post-grabación (número/GUID, recurrente, mail si aplica).
- [ ] **CA-14:** Extracto con candidatos válidos hidrata borrador automáticamente.
- [ ] **CA-15:** Candidatos dudosos/inválidos no se cargan; se listan o informan.
- [ ] **CA-16:** Apply imagen **no** graba hasta intención J explícita.
- [ ] **CA-17:** “Elimina el artículo arroz” con 2 renglones de arroz en el detalle → lista de elección (no “demasiados resultados” del maestro).
- [ ] **CA-18:** “Cambiar cantidad del artículo \"almendra\" a 150” actualiza el renglón match; sin comillas ni desc. al final puede fallar y debe mostrar el `q` buscado.
- [ ] **CA-19:** “Cambiar cantidad a 5 del artículo ABC” (desc. al final) actualiza cantidad a 5.
- [ ] **CA-20:** “Quitar el último renglón” elimina el último del borrador.
- [ ] **CA-21:** Update precio/bonif. de renglón existente respeta `ModificaPrecio*` / `ModificaBonArt*`.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Grabar sin cliente/renglones | Mismas validaciones HU-101-009/010 |
| Solo lectura + agregar artículo | denied |
| Imagen sin visión | No aplica (bloqueado en 038) |
| Eliminar sin match en detalle | Mensaje con descripción buscada; no muta |
| “elimina …” mal clasificado como alta | No permitido: debe ser mutate sobre detalle |

## Escenarios Gherkin

```gherkin
Feature: Articulos y grabar via asistente

  Scenario: Agregar sin cantidad
    Given un comprobante con cliente y LLM
    When pide "agregar articulo ABC-01"
    And ABC-01 es match unico
    Then se agrega un renglon con cantidad 1
    And los totales se recalculan

  Scenario: Grabar pedido
    Given un borrador valido
    When pide "grabar pedido"
    Then se ejecuta el mismo flujo que el boton Grabar pedido
    And en exito ve el mismo resultado post-grabacion

  Scenario: Extracto imagen parcial
    Given una imagen con un articulo valido y uno invalido
    When el extracto se aplica
    Then solo el valido entra al borrador
    And el invalido se informa
    And el comprobante no queda grabado

  Scenario: Eliminar articulo ambiguo en detalle
    Given un borrador con dos renglones cuyo descripcion contiene "arroz"
    When pide "elimina el articulo arroz"
    Then ve una lista numerada de esos renglones con cant precio y bonif
    And no recibe el mensaje de demasiados resultados del maestro
```

## Supuestos explícitos

- Lookup de artículos = mismo endpoint/universo que la grilla de carga.
- Mensajes de error de grabación se reutilizan (no set paralelo).

## Preguntas abiertas

Ninguna bloqueante.

## Riesgos de ambigüedad

- Facade única vs N llamadas FE queda a TR; el contrato `action`/`resultado` del SPEC orienta la implementación.

## Veredicto B1

**Lista para TR:** Sí.
