# HU-101-053 — Sincronización del maestro de medidas

| Campo | Valor |
|---|---|
| **ID** | HU-101-053-integracion-medidas-maestro |
| **SPEC origen** | [SPEC-101-24-importacion-medidas-erp.md](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **Prioridad** | Must |
| **Estado** | B1 cerrado — lista para TR |
| **B1** | Enriquecida desde SPEC-101-24 |
| **Dependencia** | HU-101-052 |

## Narrativa

Como responsable de datos del ERP, quiero sincronizar el maestro `MEDIDA` por
delta o snapshot, para que `pq_pedidosweb_medidas` refleje el catálogo vigente.

## Trazabilidad SPEC

| Entregable / criterio del SPEC | Cobertura |
|---|---|
| Clave `codMedida` | Contrato del ítem, RN-01, CA-01…CA-02 |
| Delta sin bajas | RN-02, CA-07 |
| Snapshot y snapshot vacío | RN-03…RN-05, CA-03, CA-06 |
| Ítem rechazado sin código | RN-06, CA-05 |
| Seis contadores y suma | RN-07…RN-08, CA-08…CA-09 |
| Persistencia por SP y auditoría | CA-10 |

## Contrato del ítem

| Campo | Regla |
|---|---|
| `codMedida` | Obligatorio, hasta 10 caracteres y clave de upsert |
| `descMedida` | Opcional, hasta 40 caracteres |
| `idMedida` | Obligatorio; lo informa el ERP y no es identity |

## Reglas de negocio

1. El upsert se resuelve por `codMedida`.
2. Delta no elimina medidas ausentes.
3. Snapshot representa el universo completo de medidas de todos sus lotes.
4. Snapshot vacío está permitido.
5. Las ausencias del snapshot se eliminan físicamente solo al finalizar un
   ciclo íntegro.
6. Un ítem sin `codMedida` válido es `rechazado`, no integra `procesados` ni
   los seis contadores.
7. Si `codMedida` existe, la operación se clasifica como alta o modificación.
8. Los seis contadores cumplen:

```text
procesados =
  agregados + modificados + eliminados +
  agregadosErroneos + modificadosErroneos + eliminadosErroneos
```

## Criterios de aceptación

- [ ] Un código nuevo se cuenta como `agregados`.
- [ ] Un código existente se cuenta como `modificados`.
- [ ] Una ausencia válida en snapshot se cuenta como `eliminados`.
- [ ] Un alta o modificación que falla se cuenta en el contador de error
  correspondiente.
- [ ] Un ítem sin código válido queda en `rechazados` y en `errores`.
- [ ] Snapshot vacío deja el universo de medidas vacío.
- [ ] Delta conserva las medidas ausentes.
- [ ] El resultado final expone los seis contadores.
- [ ] La suma de los seis contadores coincide con `procesados`.
- [ ] La escritura utiliza stored procedures y queda auditada.

## Escenarios Gherkin

```gherkin
Feature: Sincronización del maestro de medidas

  Scenario: Alta de una medida por código
    Given no existe una medida con código "UN"
    When se procesa un lote delta con "codMedida" igual a "UN"
    Then se crea una medida
    And el contador "agregados" aumenta en 1

  Scenario: Código de medida inválido
    Given un lote con una medida sin "codMedida"
    When finaliza el procesamiento
    Then el ítem queda rechazado
    And no se incrementan los seis contadores operativos

  Scenario: Snapshot vacío
    Given un ciclo snapshot válido sin medidas
    When se recibe el último lote
    Then se elimina físicamente el universo vigente
    And el ciclo finaliza con resultado

  Scenario: Modificación de una medida existente
    Given existe una medida con código "UN"
    When se procesa un lote delta con código "UN"
    Then se actualiza la medida
    And el contador "modificados" aumenta en 1

  Scenario: Bajas solo al finalizar snapshot
    Given un snapshot dividido en varios lotes
    When se procesa un lote que no es el último
    Then no se elimina ninguna medida ausente de ese lote
```

## Fuera de alcance

- Condiciones de venta, artículos, clientes y otros maestros.
- Direcciones y contactos de clientes.
- UI de administración.

## Preguntas para TR

- Validación exacta de `codMedida` contra el esquema instalado.
- Política de errores de respuesta completa y eventual paginación.

## Supuestos explícitos

- `codMedida` es estable y único dentro del tenant.
- `idMedida` forma parte del payload; si cambia para un `codMedida` existente,
  el backend actualiza `id_medida`.
- Las bajas físicas solo se ejecutan cuando el ciclo snapshot está completo.
- Los registros rechazados no forman parte de `procesados`.

## Riesgos de ambigüedad

- La tabla de medidas no tiene referencias funcionales en el sistema; las
  ausencias del snapshot pueden eliminarse físicamente.
- La TR debe confirmar las longitudes y nulabilidad contra el esquema real.
- La suma de contadores debe mantenerse consistente aun con errores parciales.

## Veredicto B1

**Lista para TR**, con observaciones sobre referencias, esquema físico y
clasificación de errores.
