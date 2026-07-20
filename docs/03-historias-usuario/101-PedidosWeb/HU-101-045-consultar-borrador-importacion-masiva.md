# HU-101-045 — Consultar borrador de importación masiva (carga solo lectura)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-045-consultar-borrador-importacion-masiva |
| **SPEC origen** | [SPEC-101-21-importacion-masiva-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Épica** | 101 — PedidosWeb / Importación masiva |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | **Cerrado** (2026-07-19) |
| **TR** | [TR-SPEC-101-21-consultar-borrador-importacion-masiva](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) |
| **Dependencias** | [HU-101-044](HU-101-044-pantalla-importacion-masiva.md); [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **HUs relacionadas** | [HU-101-043](HU-101-043-proceso-excel-pedido-masivo.md) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| SPEC CA-07 Consultar + Volver | RN-01 … RN-04, CA-01 … CA-04 |
| AMB-02 host `/pedidos/carga` readonly | RN-01, CA-01 |
| AMB-C-01 hidratación estado navegación | RN-02, CA-02, CA-05 |
| §7 solo lectura total; sin BD | RN-03, RN-05, CA-03, CA-05 |
| Fuera de alcance: edición / grabar desde Consultar | RN-03, CA-03 |
| Entregable tests Vitest/E2E | CA-06 |

## Narrativa

Como **usuario que revisa un comprobante aún no grabado en la grilla masiva**,  
quiero **abrirlo en la pantalla tradicional de carga en solo lectura y volver sin perder el lote**,  
para **verificar cabecera y renglones antes de Grabar**.

## Contexto funcional

SPEC-101-21 §7 / AMB-02 / AMB-C-01: desde una fila de la grilla masiva, **Consultar** abre `/pedidos/carga` en `mode=readonly` con origen importación masiva. Hidrata cabecera+renglones del borrador vía **estado de navegación** / store de sesión de pantalla. No lee ni escribe BD. **Volver** restaura la grilla masiva con el borrador intacto.

## Actores

| Actor | Uso |
|-------|-----|
| Usuario con `pw_importacionmasiva` (en flujo 044) | Consulta filas de su borrador de sesión |

## Alcance incluido

- Navegación Consultar con payload del `idInterno` (cabecera + renglones + tipo visible si aplica en UI carga).
- Modo readonly en carga: sin edición; sin Grabar pedido/presupuesto; sin import Excel usable en ese modo.
- Acción **Volver** a importación masiva.
- Conservación del resto de filas del borrador de sesión.
- Tests de no-persistencia y Volver.

## Fuera de alcance

- Edición o grabación desde Consultar.
- Persistencia servidor del borrador.
- Cambios al flujo normal alta/edición de carga fuera de este origen.
- Import/grabar lote (043/044).
- Mobile.

## Datos involucrados

| Objeto | Rol |
|--------|-----|
| Location state / store de sesión de pantalla | Payload del borrador consultado |
| `PedidosCargaPage` (modo readonly) | Presentación |
| Borrador grilla 044 | Resto de filas al Volver |

## Reglas de negocio

1. **RN-01:** Host = `/pedidos/carga` con `mode=readonly` y origen `from=importacionMasiva` (o equivalente TR).
2. **RN-02:** Datos desde estado de navegación / store; **no** `GET` de comprobante grabado.
3. **RN-03:** Solo lectura total: sin mutar cabecera/renglones; sin Grabar; sin import que altere el formulario.
4. **RN-04:** Volver → pantalla masiva con borrador de sesión preservado (todas las filas previas).
5. **RN-05:** Consultar no crea ni actualiza `pq_pedidosweb_pedidoscabecera` / detalle.
6. **RN-06:** El tipo Pedido/Presupuesto del borrador no habilita botones de grabación en este modo.

## Criterios de aceptación

- [ ] **CA-01:** Consultar abre `/pedidos/carga` en solo lectura con los datos del borrador (SPEC CA-07).
- [ ] **CA-02:** Cabecera/renglones/totales coinciden con el grupo del `idInterno` (hidratación AMB-C-01).
- [ ] **CA-03:** No es posible editar ni Grabar pedido/presupuesto.
- [ ] **CA-04:** Volver regresa a la grilla masiva con el mismo conjunto de filas de borrador (SPEC CA-07).
- [ ] **CA-05:** No queda comprobante nuevo en BD por Consultar.
- [ ] **CA-06:** Vitest/E2E: Consultar → Volver conserva N filas; sin persistencia.
- [ ] **CA-07:** Import Excel en esa vista readonly no está disponible o no muta el borrador consultado.

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Refresh de navegador en carga readonly | Puede perder el state (borrador solo memoria) — coherente con SPEC CA-13 / sin server draft |
| Intentar Grabar si UI residual | Debe estar oculto/deshabilitado; backend no es el foco de esta HU |

## Escenarios Gherkin

```gherkin
Feature: Consultar borrador importación masiva
  Scenario: Revisar y volver
    Given una grilla masiva con 2 borradores
    When el usuario Consulta el primero
    Then ve /pedidos/carga en solo lectura con esos datos
    And no puede Grabar
    When vuelve a importación masiva
    Then la grilla sigue mostrando 2 filas

  Scenario: No persiste en BD
    Given un borrador solo en memoria
    When el usuario lo Consulta y vuelve
    Then no existe comprobante nuevo persistido por esa acción

  Scenario: Sin edición
    Given el usuario está en Consultar readonly
    When intenta modificar un campo de cabecera o renglón
    Then la UI no permite el cambio
```

## Supuestos explícitos

- Reutilizar `PedidosCargaPage` con flag/modo es aceptable; alternativa de ruta dedicada queda fuera (SPEC eligió reutilizar `/pedidos/carga`).
- Presentación visual de renglones/totales alineada a carga (SPEC §3/§7).

## Preguntas abiertas

Ninguna bloqueante. Forma exacta del location state / store en TR.

## Riesgos de ambigüedad

Bajo. Residual: regresiones en modo alta de carga al introducir readonly (mitigar con tests).

## Veredicto B1

**Lista para TR: Sí** — cubre SPEC CA-07 + AMB-02/C-01.
