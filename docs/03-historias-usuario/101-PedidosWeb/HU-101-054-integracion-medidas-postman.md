# HU-101-054 — Pruebas Postman de importación de medidas

| Campo | Valor |
|---|---|
| **ID** | HU-101-054-integracion-medidas-postman |
| **SPEC origen** | [SPEC-101-24-importacion-medidas-erp.md](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **Prioridad** | Must |
| **Estado** | B1 cerrado — lista para TR |
| **B1** | Enriquecida desde SPEC-101-24 |
| **Dependencias** | HU-101-052, HU-101-053 |

## Narrativa

Como responsable de integración, quiero contar con requests reproducibles en
Postman, para verificar la recepción, el procesamiento y el resultado final de
la importación de medidas.

## Trazabilidad SPEC

| Entregable / criterio del SPEC | Cobertura |
|---|---|
| Request POST de recepción | Alcance, CA-01…CA-02 |
| Request GET de resultado | Alcance, CA-03…CA-05 |
| Alta, modificación y rechazo | Alcance, CA-06 |
| Idempotencia y concurrencia | Alcance, CA-07…CA-08 |
| Contadores y camelCase | Alcance, CA-04…CA-05, CA-09 |

## Alcance

Actualizar:

```text
backend/storage/app/postman/integracion/PedidosWeb-Integracion.postman_collection.json
```

Debe incluir:

- Request `medidas - recibir lote 1/1`.
- Request `importacion - consultar resultado medidas`.
- Payload con alta, modificación y registro rechazado.
- Reenvío idempotente.
- Verificación de concurrencia HTTP 409.
- Los contadores `agregadosErroneos`, `modificadosErroneos` y
  `eliminadosErroneos` en los ejemplos finales.

## Criterios de aceptación

- [ ] La request de recepción usa la URL y headers del contrato.
- [ ] La recepción valida HTTP 202, `error: 0`, recurso y estado.
- [ ] La consulta valida contadores `null` durante procesamiento.
- [ ] La consulta final valida los seis contadores.
- [ ] Un test verifica la suma de los seis contadores contra `procesados`.
- [ ] Un test verifica que un ítem sin `codMedida` queda rechazado.
- [ ] Un test verifica el reenvío idempotente.
- [ ] Un test verifica HTTP 409 ante ciclo concurrente.
- [ ] Los JSON de ejemplo respetan camelCase.

## Escenarios Gherkin

```gherkin
Feature: Verificación Postman de medidas

  Scenario: Consultar un ciclo en procesamiento
    Given una importación de medidas recibida y aún no finalizada
    When se ejecuta el GET de resultado
    Then los contadores de procesamiento son null

  Scenario: Validar resultado final
    Given una importación de medidas finalizada
    When se ejecuta el GET de resultado
    Then la suma de los seis contadores es igual a "procesados"

  Scenario: Rechazar medida sin código
    Given un payload con un ítem sin "codMedida"
    When se ejecuta la recepción y luego se consulta el resultado final
    Then el ítem aparece como rechazado
    And no incrementa los seis contadores operativos

  Scenario: Rechazar ciclo concurrente
    Given un ciclo de medidas en procesamiento
    When se intenta enviar otro ciclo para el mismo tenant y recurso
    Then la request devuelve HTTP 409
```

## Fuera de alcance

- Publicar la collection en un workspace remoto.
- Crear credenciales reales.
- Ejecutar pruebas contra una base compartida sin autorización.

## Supuestos explícitos

- La collection reutiliza las variables de environment existentes.
- Los tests verifican códigos, estados y propiedades del envelope.
- La consulta se repite hasta observar un estado final cuando el
  procesamiento no sea inmediato.

## Riesgos de ambigüedad

- La TR debe definir cómo esperar el estado final sin convertir Postman en un
  bucle indefinido.
- Los ejemplos deben representar tanto estado intermedio con `null` como
  resultado final con los seis contadores.

## Veredicto B1

**Lista para TR**, con observaciones sobre polling, datos de prueba y
aislamiento del environment.
