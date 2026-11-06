# Cierre C — SPEC-101-24 — Importación asíncrona de medidas ERP

| Campo | Valor |
|---|---|
| **Fecha** | 2026-10-25 |
| **SPEC** | [SPEC-101-24](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **B1** | [F-101-24-cierre-b1](F-101-24-cierre-b1-importacion-medidas-erp.md) |
| **Veredicto C1** | **Apto con observaciones — autoriza D1** |

## TR generadas

| TR | HU | Alcance |
|---|---|---|
| [TR-101-24a](TR-SPEC-101-24-integracion-medidas-plataforma.md) | HU-101-052 | Recepción, consulta, job, estados, lock e idempotencia |
| [TR-101-24b](TR-SPEC-101-24-integracion-medidas-maestro.md) | HU-101-053 | Handler, SP, delta, snapshot y contadores |
| [TR-101-24c](TR-SPEC-101-24-integracion-medidas-postman.md) | HU-101-054 | Collection, requests y pruebas de contrato |

## Orden de implementación propuesto

```text
24a → 24b → 24c
```

`24c` puede preparar la collection en paralelo, pero su ejecución final
depende de que 24a y 24b estén disponibles.

## Cobertura

- Recepción asíncrona HTTP 202.
- Consulta intermedia sin resultados provisorios.
- Resultado final con seis contadores.
- Upsert por `codMedida`.
- Delta y snapshot, incluido snapshot vacío.
- Rechazo de ítems sin código válido.
- Idempotencia y concurrencia.
- Stored procedures, auditoría, OpenAPI y Postman.

## Revisión C1

### Cobertura verificada

- Las tres TR permanecen dentro del alcance del SPEC y sus HU.
- La plataforma documenta rutas, seguridad, tenant, envelope, OpenAPI y tests.
- El dominio documenta upsert por `codMedida`, delta, snapshot, rechazo y
  contadores.
- Postman documenta recepción, consulta intermedia/final, idempotencia y 409.

### Observaciones para D1

1. **Cerrado en D1:** la tabla de medidas no tiene referencias funcionales; las
   medidas ausentes del snapshot se eliminan al finalizar el ciclo.
2. Fijar límites iniciales de bytes y retención de payloads/errores.
3. Confirmar nombres y parámetros finales de SP contra el contrato GEN-36.
4. Confirmar polling acotado para la request GET de Postman.
5. **Cerrado en D1:** el ERP envía `idMedida`; `id_medida` no es identity y se
   actualiza si cambia para un `codMedida` existente.

### Contradicciones corregidas

- Se agregó a TR-24a la política de autorización, `X-Paq-Cliente`, 401/403,
  permiso y envelope requerido por las normas transversales.
- Se agregaron checklist de envelope, seguridad, SP y alcance en las tres TR.

## Veredicto

Las TR cubren el alcance del SPEC y no agregan código ni alcance funcional.
Las observaciones restantes son decisiones de D1/TR técnica y no impiden
elaborar el plan de implementación.

**Siguiente paso: Parte D1.**
