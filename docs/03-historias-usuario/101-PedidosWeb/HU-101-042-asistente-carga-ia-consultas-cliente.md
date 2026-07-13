# HU-101-042 — Asistente IA carga: deuda, cheques e historial

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-042-asistente-carga-ia-consultas-cliente |
| **SPEC origen** | [SPEC-101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | Enriquecida (2026-07-13) |
| **TR** | [TR-SPEC-101-20](../../04-tareas/101-PedidosWeb/TR-SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Dependencias** | HU-101-037; HU-101-039 (cliente en proceso); HU-101-021…023; SPEC-101-07 |
| **HUs relacionadas** | HU-101-041 (stock) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| F — Deuda (CA-F01, D1-21) | CA-01 … CA-04 |
| G — Cheques (CA-G01, D1-19) | CA-05 … CA-07 |
| H — Historial (CA-H01, D1-20) | CA-08 … CA-10 |
| Precondición cliente / tope 10 (D1-11) | CA-01, CA-04, CA-07, CA-10 |
| TZ / fechas F–G = `YYYY-MM-DD` sin hora; totales si >1 | CA-03, CA-04b, CA-06, CA-06b |
| Tabla HTML panel (D1-25) | CA-13 |
| Solo lectura / API real (D1-12) | CA-11, CA-12 |

## Narrativa

Como **usuario con un cliente ya elegido en la carga**,  
quiero **consultar deuda, cheques e historial de ventas en el asistente**,  
para **decidir sin abandonar el comprobante**.

## Contexto funcional

SPEC-101-20 F–H: APIs `consultas/deuda`, `consultas/cheques`, `consultas/historial-ventas` filtradas por cliente en proceso y visibilidad; máx. 10 filas + `total`; columnas chat fijadas en A1 (D1-19…21). Sin cliente → guiar a selección (A).

## Alcance incluido

- Deuda: columnas **tipo/nro · fecha · vencimiento · saldo**.
- Cheques: **nro · fecha · importe**.
- Historial: **descripción artículo · cantidad · precio unitario neto · importe**.
- Sin cliente → pedir selección (flujo 039 A); no inventar datos.
- Permisos iguales a cada consulta de menú.
- Fechas/TZ iguales a las consultas actuales.
- Si hay más de 10: mostrar 10 + indicar total + sugerir consulta de menú / refinar si aplica.
- Solo lectura sobre el borrador.

## Fuera de alcance

- Stock → HU-101-041.
- Mutaciones / grabar.
- Export Excel / pivot desde el chat.

## Reglas de negocio

1. Precondición: cliente en proceso para F–H.
2. Datos solo desde APIs de consulta.
3. Tope 10 filas visibles + total (D1-11).
4. Columnas chat = D1-19/20/21.
5. No mutar cabecera/renglones.

## Criterios de aceptación

- [ ] **CA-01:** Sin cliente, pedido de deuda/cheques/historial → pide seleccionar cliente; no llama datos de otro cliente.
- [ ] **CA-02:** Con cliente y permiso, deuda usa `GET /api/v1/consultas/deuda` filtrada al cliente en proceso.
- [ ] **CA-03:** Filas de deuda muestran tipo/nro, fecha, vencimiento, saldo; fechas solo `YYYY-MM-DD` (sin hora).
- [ ] **CA-04:** Si hay más de 10 deudas → muestra 10 + total + hint a consulta/refinar.
- [ ] **CA-04b:** Si hay más de un ítem listado → pie con suma de **saldo**.
- [ ] **CA-05:** Cheques usan `GET /api/v1/consultas/cheques` del cliente en proceso.
- [ ] **CA-06:** Filas de cheques muestran nro, fecha, importe; fechas solo `YYYY-MM-DD`.
- [ ] **CA-06b:** Si hay más de un cheque listado → pie con suma de **importe**.
- [ ] **CA-07:** Tope 10 + total en cheques; sin permiso → mensaje.
- [ ] **CA-08:** Historial usa `GET /api/v1/consultas/historial-ventas` del cliente en proceso (`DiasVentasDetalladas`).
- [ ] **CA-09:** Filas de historial muestran descripción artículo, cantidad, PU neto, importe.
- [ ] **CA-10:** Tope 10 + total en historial; sin permiso → mensaje.
- [ ] **CA-11:** Ninguna de estas consultas modifica el borrador.
- [ ] **CA-12:** Sin permiso o error API → mensaje controlado; sin cifras inventadas.
- [ ] **CA-13:** Presentación F/G/H (y E) como **tabla HTML** del panel (`cargaAsistenteIaConsultaTable`), no texto con `|`.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Cliente sin deuda | Mensaje vacío / sin filas, no error |
| Visibilidad restringe cliente | No ve cheques/deuda de clientes fuera de scope |

## Escenarios Gherkin

```gherkin
Feature: Consultas cliente via asistente de carga

  Scenario: Deuda requiere cliente
    Given carga sin cliente seleccionado
    When pide "mostrar deuda"
    Then el asistente pide seleccionar cliente primero

  Scenario: Cheques del cliente en proceso
    Given un cliente seleccionado y permiso de cheques
    When pide "cheques en cartera"
    Then ve hasta 10 filas con nro fecha e importe
    And el borrador no cambia

  Scenario: Historial con columnas acordadas
    Given un cliente seleccionado y permiso de historial
    When pide "ultimas ventas"
    Then ve descripcion cantidad precio unitario neto e importe
```

## Supuestos explícitos

- Nombres JSON exactos de columnas se mapean en TR desde las APIs existentes.
- “Refinar” en F–H puede ser hint a la consulta de menú si la API no admite `q` libre.

## Preguntas abiertas

Ninguna bloqueante.

## Riesgos de ambigüedad

- Si historial API pagina distinto, el tope 10 debe aplicarse sobre el resultado presentado al usuario según D1-11.

## Veredicto B1

**Lista para TR:** Sí.
