# HU-101-050 — Importación de stock, deuda y cheques

Jira: [PIAPEDWEB-41](https://paqsystems.atlassian.net/browse/PIAPEDWEB-41)

| Campo | Valor |
|-------|-------|
| **ID** | HU-101-050-integracion-transacciones-saldos |
| **SPEC origen** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Épica** | APIs importación transacciones ERP → PedidosWeb |
| **Prioridad** | **Must** |
| **Estado** | Especificado |
| **B1** | Cerrado (2026-10-22) |
| **Dependencias** | [HU-101-049](HU-101-049-integracion-transacciones-plataforma.md) · maestros de artículos/clientes disponibles |

## Trazabilidad SPEC

| Recurso / entregable SPEC | Cobertura en esta HU |
|---------------------------|----------------------|
| `stock` y clave `cod_articulo` | RN-01, CA-01…CA-04 |
| `deuda` y clave compuesta | RN-02, CA-05…CA-07 |
| `cheques` y clave `(interno, numero)` | RN-03, CA-08…CA-10 |
| Snapshot atómico | RN-04, CA-02, CA-06, CA-09 |
| Delta merge upsert | RN-05, CA-03, CA-07, CA-10 |
| Fecha de proceso | RN-06, CA-01, CA-05, CA-08 |
| Persistencia por SP | RN-07, CA-11 |
| Consultas portal reflejan snapshot | CA-12 |

## Narrativa

Como **job ERP**,  
quiero **importar los universos vigentes de stock, deuda y cheques**,  
para **que las consultas operativas del portal reflejen los saldos y la cartera actualizados**.

## Contexto funcional

La HU implementa los handlers y stored procedures para `stock`, `deuda` y `cheques`. En `snapshot`, el lote válido reemplaza atómicamente el universo del recurso. En `delta`, se actualizan solo las claves enviadas y los registros ausentes no se eliminan.

## Alcance incluido

### Stock

- Tabla `pq_pedidosweb_stock`, clave `cod_articulo`.
- Ítem mínimo: `codArticulo`, `stock`, `comprometido`.
- Validación de artículo existente.
- `uma_fecha = fechaProceso`.
- Snapshot reemplaza el conjunto de stock del tenant.
- `stockeable=0` se acepta.
- No se persisten `comprometidoWeb`, `stockBase`, `comprometidoBase` ni `disponibleNeto*`.

### Deuda

- Tabla `pq_pedidosweb_deuda`, clave `(cod_cliente, t_comp, n_comp, fecha_vto)`.
- Ítem mínimo: `codCliente`, `tComp`, `nComp`, `fechaEmis`, `fechaVto`, `saldo`.
- Validación de cliente existente.
- Escritura en columnas canónicas, sin columnas legacy.
- Snapshot representa el universo vigente; delta hace merge upsert.

### Cheques

- Tabla `pq_pedidosweb_cheques`, clave `(interno, numero)`.
- Ítem mínimo: `interno`, `numero`, `codCliente`, `banco`, `fecha`, `importe`, `origen`, `estado`.
- Validación de cliente existente.
- Mapeo camelCase a columnas de escritura `Banco`, `Importe`, `Origen`, `Estado`.
- El filtro `fecha >= hoy` sigue perteneciendo a la consulta, no al import.

## Fuera de alcance

- Historial de ventas → HU-101-051.
- Maestros, pedidos y presupuestos.
- Filtro de visibilidad de cartera en el POST.
- Cálculo de comprometido web o netos.
- `resumen-cuenta`, UI nueva, cron e idempotencia.

## Reglas de negocio

1. **RN-01:** Stock exige que `codArticulo` exista en artículos; si no, el ítem se rechaza.
2. **RN-02:** Deuda exige que `codCliente` exista; si no, el ítem se rechaza.
3. **RN-03:** Cheques exige que `codCliente` exista; si no, el ítem se rechaza.
4. **RN-04:** Snapshot de stock, deuda y cheques es atómico: un ítem inválido o una clave duplicada impide toda escritura y devuelve 422.
5. **RN-05:** Delta aplica los ítems válidos, rechaza duplicados posteriores de la misma clave y no borra ausentes.
6. **RN-06:** La fecha de proceso de carátula se persiste en la metadata/campo de proceso definido para cada recurso.
7. **RN-07:** Toda escritura de negocio se realiza mediante el SP de su recurso, sin SQL ad-hoc desde PHP.
8. **RN-08:** Los negativos son valores permitidos por el ERP.
9. **RN-09:** El snapshot vacío de cualquiera de estos recursos se rechaza con 422.

## Criterios de aceptación

- [ ] **CA-01:** Stock delta válido persiste `codArticulo`, `stock`, `comprometido` y `uma_fecha=fechaProceso`.
- [ ] **CA-02:** Stock snapshot válido deja el universo igual al lote; snapshot vacío o con un artículo inválido no escribe.
- [ ] **CA-03:** Stock delta con un artículo inválido aplica los demás y lo informa como rechazado.
- [ ] **CA-04:** Payload stock con `comprometidoWeb` o campos `*Base`/`disponibleNeto*` rechaza el ítem y no persiste esos campos.
- [ ] **CA-05:** Deuda delta válida persiste en el esquema canónico y no exige enviar razón social.
- [ ] **CA-06:** Deuda snapshot atómico no modifica la tabla si falta un cliente o existe una clave duplicada.
- [ ] **CA-07:** Deuda delta reenvía la misma clave sin duplicarla y no borra comprobantes ausentes.
- [ ] **CA-08:** Cheques delta válido persiste la clave `(interno, numero)` y el mapeo de campos acordado.
- [ ] **CA-09:** Cheques snapshot reemplaza atómicamente el universo; un cliente inválido deja el lote sin cambios.
- [ ] **CA-10:** Cheques delta con claves duplicadas aplica la primera y reporta las siguientes como rechazadas.
- [ ] **CA-11:** Las operaciones llaman exclusivamente a `pq_sp_integracion_transacciones_stock`, `_deuda` y `_cheques`.
- [ ] **CA-12:** Después de un snapshot válido, las consultas GET del portal reflejan el lote y mantienen su propia visibilidad.

## Escenarios Gherkin

```gherkin
Feature: Importación de stock, deuda y cheques

  Scenario: Snapshot stock atómico
    Given artículos existentes A1 y A2
    And un lote snapshot de stock para A1 y A2
    When se importa el lote de stock
    Then HTTP 200
    And el universo de stock del tenant coincide con el lote

  Scenario: Snapshot deuda con cliente inválido
    Given un lote snapshot de deuda con un cliente inexistente
    When se importa el lote de deuda
    Then HTTP 422
    And ninguna deuda del lote queda aplicada

  Scenario: Delta de cheques con duplicado
    Given un lote delta con dos ítems de la misma clave "(interno, numero)"
    When se importa el lote de cheques
    Then el primer ítem puede aplicarse
    And el siguiente queda rechazado
```

## Supuestos explícitos

- El origen carga primero los maestros requeridos, pero el API valida igualmente las FKs.
- Las tablas y columnas canónicas descritas en el SPEC existen o su eventual gap se documentará en TR.

## Preguntas abiertas (TR)

- Detalle final de columnas auxiliares de fecha/metadata de cada tabla.
- Mapeo completo de campos camelCase a columnas físicas y nombres definitivos de SP.

## Observaciones B1

- La HU separa los tres recursos con sus claves, FKs, modos y reglas de atomicidad definidas en el SPEC.
- El mapeo físico completo y los nombres finales de SP quedan explícitamente como detalle de TR, sin bloquear la derivación.
- Los tests con fixtures de maestros respetan la decisión A1 de no bloquear HU-101-23 por el orden de implementación de 101-22.
- No se detectan ambigüedades bloqueantes para generar la TR.

## Veredicto B1

**Lista para TR** — debe mantener snapshots atómicos por recurso y deltas no destructivos.
