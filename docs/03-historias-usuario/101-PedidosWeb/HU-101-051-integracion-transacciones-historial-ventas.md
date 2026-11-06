# HU-101-051 — Importación de historial de ventas ERP

Jira: [PIAPEDWEB-42](https://paqsystems.atlassian.net/browse/PIAPEDWEB-42)

| Campo | Valor |
|-------|-------|
| **ID** | HU-101-051-integracion-transacciones-historial-ventas |
| **SPEC origen** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Épica** | APIs importación transacciones ERP → PedidosWeb |
| **Prioridad** | **Must** |
| **Estado** | **Finalizado** (Parte I SPEC-101-23, 2026-10-29) |
| **B1** | Cerrado (2026-10-22) |
| **Dependencias** | [HU-101-049](HU-101-049-integracion-transacciones-plataforma.md) · clientes disponibles |

## Trazabilidad SPEC

| Entregable SPEC | Cobertura en esta HU |
|-----------------|----------------------|
| Ruta `historial-ventas` | CA-01 |
| Solo modo `delta` | RN-01, CA-02 |
| Campos obligatorios A1-M02 | RN-02, CA-03 |
| Upsert por `id_gva53` | RN-03, CA-04 |
| FK cliente y campos nullable | RN-04, CA-05 |
| Fecha de proceso | RN-05, CA-06 |
| Sin borrado de ausentes | RN-06, CA-07 |
| GET aplica `DiasVentasDetalladas` | RN-07, CA-08 |
| SP específico | RN-08, CA-09 |

## Narrativa

Como **job ERP**,  
quiero **enviar las ventas detalladas nuevas o modificadas mediante lotes delta**,  
para **que el historial de ventas consultable en el portal se mantenga actualizado sin borrar registros históricos ausentes del lote**.

## Contexto funcional

La HU implementa `POST /api/v1/integracion/transacciones/historial-ventas` sobre `pq_pedidosweb_ventadetallada`. El recurso no admite snapshot: cada lote delta hace upsert por `id_gva53`, valida el cliente y conserva las filas que no aparecen en el lote.

## Alcance incluido

- Modo exclusivamente `delta`.
- Campos obligatorios: `idGva53`, `codCliente`, `tipo`, `numero`, `fechaEmision`.
- Resto de campos definidos por la consulta de historial, nullable según el SPEC.
- `fechaEmision` conserva la fecha del comprobante.
- `fechaProceso` de carátula prevalece para todas las filas → `fecha_proceso`.
- Upsert por `id_gva53`; reenvío actualiza y no duplica.
- Validación de que `codCliente` exista.
- Rechazo por ítem sin impedir el procesamiento del resto del delta.
- Persistencia mediante `pq_sp_integracion_transacciones_historial_ventas`.
- Aplicación de `DiasVentasDetalladas` exclusivamente en el GET de consulta.

## Fuera de alcance

- `modo=snapshot`.
- Borrado de filas ausentes del lote.
- Validación de `codArticu` contra artículos.
- Importación de pedidos, deuda, stock o cheques.
- Cambios en el GET, UI, cron e idempotencia.

## Reglas de negocio

1. **RN-01:** `modo=snapshot` devuelve 422 con `integracion.transaccion.modoNoSoportado`.
2. **RN-02:** Cada ítem exige `idGva53`, `codCliente`, `tipo`, `numero` y `fechaEmision`.
3. **RN-03:** El upsert se identifica por `id_gva53`; reenviar la misma clave actualiza la fila.
4. **RN-04:** `codCliente` inexistente produce rechazo del ítem; no se exige que `codArticu` exista en artículos.
5. **RN-05:** `fechaEmision` es la fecha del comprobante; `fechaProceso` de carátula prevalece y se guarda en `fecha_proceso` para todas las filas.
6. **RN-06:** El delta no borra filas históricas ausentes.
7. **RN-07:** `DiasVentasDetalladas` se aplica al leer, no durante la importación.
8. **RN-08:** La escritura se realiza exclusivamente mediante el SP del recurso.

## Criterios de aceptación

- [ ] **CA-01:** La ruta `POST /api/v1/integracion/transacciones/historial-ventas` existe y devuelve `resultado.recurso=historial-ventas`.
- [ ] **CA-02:** Un request con `modo=snapshot` devuelve HTTP 422 y código `integracion.transaccion.modoNoSoportado`.
- [ ] **CA-03:** Un ítem sin cualquiera de los cinco campos obligatorios queda rechazado con error por ítem.
- [ ] **CA-04:** Importar dos veces el mismo `idGva53` deja una sola fila con los datos del último envío.
- [ ] **CA-05:** Un ítem con cliente inexistente se rechaza y los demás ítems delta válidos se aplican.
- [ ] **CA-06:** La fila aplicada conserva `fecha_proceso` desde la fecha de fila o carátula.
- [ ] **CA-07:** Una fila histórica ausente del lote permanece en la tabla.
- [ ] **CA-08:** El filtro `DiasVentasDetalladas` no altera los datos persistidos y solo afecta el GET.
- [ ] **CA-09:** La escritura utiliza exclusivamente `pq_sp_integracion_transacciones_historial_ventas`.

## Escenarios Gherkin

```gherkin
Feature: Importación delta de historial de ventas

  Scenario: Upsert por idGva53
    Given una venta con idGva53 "1001" ya existente
    When se importa un delta con idGva53 "1001" y datos actualizados
    Then HTTP 200
    And existe una sola fila para idGva53 "1001"
    And contiene los datos del último envío

  Scenario: Snapshot no soportado
    Given un token técnico válido
    When se importa historial-ventas con modo "snapshot"
    Then HTTP 422
    And la respuesta usa "integracion.transaccion.modoNoSoportado"

  Scenario: Cliente inválido no aborta el delta
    Given un lote delta con una venta de cliente inexistente y otra válida
    When se importa el lote
    Then la venta inválida queda rechazada
    And la venta válida queda aplicada
```

## Supuestos explícitos

- Los campos opcionales y su mapeo físico se definirán en la TR a partir del contrato de la consulta existente.
- La consulta existente seguirá controlando el rango temporal mediante `DiasVentasDetalladas`.

## Preguntas abiertas (TR)

- Lista completa de campos opcionales y tipos para OpenAPI.
- Regla de precedencia si una fila trae `fechaProceso` distinta de la carátula.

## Observaciones B1

- La HU deja explícito que historial solo admite delta, usa `idGva53` como clave y no borra ausentes.
- Los campos opcionales y el mapeo completo se mantienen como detalle de TR, tal como establece el SPEC.
- La precedencia entre fecha de fila y fecha de carátula queda identificada como pregunta de TR, sin introducir una decisión no presente en el SPEC.
- No se detectan ambigüedades bloqueantes para generar la TR.

## Veredicto B1

**Lista para TR** — el upsert histórico es independiente de los snapshots de saldos.
