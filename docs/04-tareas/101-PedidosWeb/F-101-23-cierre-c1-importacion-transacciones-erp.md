# Cierre C1 — SPEC-101-23 — APIs importación transacciones ERP

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-22 |
| **Épica** | [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35) |
| **C** | [F-101-23-cierre-c](F-101-23-cierre-c-importacion-transacciones-erp.md) — 3 TR generadas |
| **Veredicto C1** | **Apto con observaciones** — autoriza D1 |
| **D1** | [D1-PLAN-101-23](D1-PLAN-101-23-integracion-transacciones-erp.md) — listo para Parte D |

## TR revisadas

| TR | Resultado | Observaciones para D1 |
|----|-----------|-----------------------|
| [TR-23a](TR-SPEC-101-23-integracion-transacciones-plataforma.md) | Apto con observaciones | Confirmar adapter/clases comunes de 101-22 y actualizar matriz/OpenAPI |
| [TR-23b](TR-SPEC-101-23-integracion-transacciones-saldos.md) | Apto con observaciones | Cerrar columnas físicas y mecanismo staging/TVP de snapshot |
| [TR-23c](TR-SPEC-101-23-integracion-transacciones-historial-ventas.md) | Apto con observaciones | Carátula prevalece para `fecha_proceso`; completar mapeo opcional |

## Revisión C1

### Alcance

Las TR permanecen dentro de `SPEC-101-23` y sus HU. No agregan UI, cron, Excel, pedidos, maestros ni idempotencia.

### API y seguridad

- Las cuatro rutas están identificadas.
- Se mantiene Bearer Sanctum, `X-Paq-Cliente` y `pw_integracion_importacion`.
- Se documentan 401, 403, 404, 422 y envelope GEN-20.
- La publicación OpenAPI y la matriz de permisos quedan como entregables verificables de D1/D.

### Datos y SP

- El acceso de negocio queda delegado a stored procedures.
- Stock, deuda y cheques conservan snapshot atómico y delta no destructivo.
- Historial conserva delta exclusivo, upsert por `idGva53` y ausentes.
- No se autoriza SQL ad-hoc desde PHP.

### Tests

Las TR incluyen estrategia para unitarios, feature API, autorización, atomicidad, upsert, FKs, campos prohibidos y OpenAPI. No se ejecutaron tests porque C1 es revisión documental.

## Observaciones no bloqueantes para D1

1. Elegir nombres concretos de clases/adapters sin duplicar el contrato común de 101-22.
2. Confirmar nombres físicos de columnas canónicas de stock y cheques.
3. Definir el mecanismo SP de snapshot aislado por tenant/recurso/batch.
4. La precedencia ya quedó resuelta: `fechaEmision` = comprobante; `fechaProceso` de carátula = `fecha_proceso` para todas las filas.
5. Completar tabla campo a campo de opcionales de historial.

Estas observaciones deben quedar resueltas en el plan D1 o en una actualización de TR antes del código.

## Veredicto

**C1 aprobado con observaciones. Puede pasar a D1; no implica autorización para implementar directamente.**

## Siguiente paso

Implementar según [D1-PLAN-101-23](D1-PLAN-101-23-integracion-transacciones-erp.md), en orden **23a → 23b → 23c**.
