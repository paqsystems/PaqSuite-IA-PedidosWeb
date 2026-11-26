# HU-101-057 — Plataforma GET exportación comprobantes (PedidosWeb → ERP)

Jira: [PIAPEDWEB-56](https://paqsystems.atlassian.net/browse/PIAPEDWEB-56)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-057-integracion-export-comprobantes-plataforma |
| **SPEC origen** | [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Épica** | APIs exportación comprobantes PedidosWeb → ERP |
| **Prioridad** | **Must** |
| **Estado** | **Finalizado** (2026-11-26) |
| **B1** | Cerrado (2026-11-26) — [F-101-25-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-25-cierre-b1-exportacion-comprobantes-erp.md) |
| **TR** | [TR-SPEC-101-25-integracion-export-plataforma](../../04-tareas/101-PedidosWeb/TR-SPEC-101-25-integracion-export-plataforma.md) |
| **Hermano HU** | [HU-101-058](HU-101-058-integracion-export-comprobantes-datos.md) (SP y mapeo) |
| **Dependencias** | GEN-19 tenancy · GEN-20 envelope · [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) · [SPEC-101-08](../../05-open-spec/101-PedidosWeb/SPEC-101-08-logs-integracion.md) (log Should) |

## Trazabilidad SPEC

| ID SPEC | Criterio / entregable | Cobertura en esta HU |
|---------|----------------------|----------------------|
| In scope §1 | Controller + ruta integración | RN-01, CA-01, CA-09 |
| In scope §2 | Validación query | RN-03…RN-08, CA-02…CA-07, CA-11, CA-13…CA-15 |
| In scope §3 | Servicio → SP (orquestación) | RN-14, CA-01 |
| In scope §4 | OpenAPI tag **Integración exportación** | CA-09 |
| In scope §5 | Feature tests auth/rangos/422 | CA-01, CA-06…CA-08, CA-12 |
| In scope §6 | Postman ejemplo | Pregunta abierta → TR |
| In scope §7 | Log `export_comprobantes` (Should) | RN-12, CA-16 |
| CA-01 | Token + tenant → 200 anidado | CA-01 |
| CA-02 | `estadoRangos=0:0` | CA-02 |
| CA-03 | OR sin duplicados | CA-03, CA-10 |
| CA-04 | Paginación cabecera + renglones completos | CA-04 |
| CA-05 | Extremos 99 / -1 | CA-05 |
| CA-06 | Falta `estadoRangos` | CA-06 |
| CA-07 | `1:0` inválido | CA-07 |
| CA-08 | Portal sin ability → 403 | CA-08 |
| CA-09 | OpenAPI camelCase | CA-09 |
| CA-10 | Dedup por `codPedido` | CA-10 |
| CA-11 | Alias `page_size` | CA-11 |
| AMB-C-101-25-02 | Rate limit 60/min | RN-11, CA-17 |
| AMB-M-101-25-04 | Página vacía | CA-12 |
| RN-SPEC 09 | Sin 202 / síncrono 200 | RN-09 |
| § Response | `metadata.fechaProceso` | CA-01, RN-09 |
| § Response | `cierre` siempre en ítem | CA-01 (contrato; contenido → HU-058) |
| Persistencia SP | — | Fuera → HU-058 |

## Narrativa

Como **operador de integración o job ERP (Tango / PaqSuite ERP)**,  
quiero **invocar un GET paginado con token técnico y filtro obligatorio por rangos de estado**,  
para **obtener comprobantes web del tenant en envelope GEN-20 sin credenciales de vendedor ni visibilidad de cartera del portal**.

## Contexto funcional

Primera capa de **SPEC-101-25**: expone `GET /api/v1/integracion/comprobantes`, reutiliza el patrón de autenticación de [SPEC-101-22](SPEC-101-22-importacion-maestros-erp.md) (`Bearer` + `X-Paq-Cliente` + `pw_integracion_importacion`), valida query según decisiones A1 y delega la lectura a un **ExportComprobantesService** que llama SP (HU-058). Respuesta **200** síncrona con `resultado.items[]` donde cada elemento **debe** incluir las claves `cabecera`, `renglones` y `cierre` (valor de `cierre` definido en HU-058). **No** GEN-36. **No** cambios en `/consultas/*`.

## Alcance incluido

### Ruta y seguridad

- Una ruta: `GET /api/v1/integracion/comprobantes`.
- Middleware de integración alineado a maestros/transacciones (misma familia de tenant + Sanctum + gate `pw_integracion_importacion`).
- Visibilidad **tenant completa**; **sin** `VisibleClientsResolver`.

### Validación de query

| Parámetro | Regla verificable |
|-----------|-------------------|
| `estadoRangos` | **Obligatorio**; ≥1 par `desde:hasta`; enteros ∈ [-1, 99]; inclusivo; `desde ≤ hasta`; repetido **y** CSV en el mismo request → **unir** pares (OR). |
| `page` | Entero ≥ 1; default **1**. |
| `pageSize` / `page_size` | Entero 1…1000; default **20**; alias equivalentes; respuesta usa `pageSize`. |
| `codCliente` | Opcional; igualdad exacta (AND con estado). |
| `codPedido` | Opcional; igualdad exacta (AND). |
| `fechaDesde` / `fechaHasta` | Opcional; ISO 8601 o `YYYY-MM-DD` (día completo **ART**); inclusivos sobre `cabecera.fecha`; `fechaDesde > fechaHasta` → 422. |

Claves 422 (prefijo **`integracion.export.*`**): `estadoRangosRequerido`, `rangoInvalido`, `rangoFechasInvalido`, `paginacionInvalida`.

### Respuesta exitosa (GEN-20)

- HTTP **200**, **`error: 0`**.
- `resultado`: `items`, `page`, `pageSize`, `total` (cabeceras), `totalPages`, `metadata.fechaProceso` (ISO con offset, marca de lectura servidor).
- Sin coincidencias: `items: []`, `total: 0`, `totalPages: 0`, `page` = solicitado.
- Orden del listado delegado al servicio/SP (MUST global: `fecha` desc, `codPedido` asc).

### Plataforma transversal

- Rate limit **60 req/min** por token+tenant.
- Log **Should** en `pq_pedidosweb_logs_integracion`, `tipo=export_comprobantes`.
- OpenAPI: tag **Integración exportación**, query, envelope, schema de `resultado` (referencia a ítem anidado).
- Feature tests PHPUnit: 401, 403, tenant inválido, validaciones 422, 200 vacío, alias paginación, muestra con datos HU-058.

## Fuera de alcance

- Scripts SP, mapeo cabecera/renglón/cierre, joins de datos → [HU-101-058](HU-101-058-integracion-export-comprobantes-datos.md).
- POST/PATCH confirmación descarga o cambio de estado en ERP.
- Modificar `ConsultaListadoService` o consultas UI.
- UI, mobile, importación GEN-36, Postman completo (solo entrada ejemplo en TR).

## Actores

| Actor | Uso |
|-------|-----|
| **Usuario técnico integración** | Único consumidor autorizado del GET. |
| **Vendedor / cliente portal** | Autenticado sin `pw_integracion_importacion` → **403**. |
| **Anónimo** | Sin Bearer → **401**. |

## Reglas de negocio

1. **RN-01:** Ruta canónica única bajo `/api/v1/integracion/comprobantes`.
2. **RN-02:** Solo `pw_integracion_importacion` + tenant válido habilita lectura.
3. **RN-03:** Ausencia de `estadoRangos` → 422 `integracion.export.estadoRangosRequerido`.
4. **RN-04:** Par inválido, extremo fuera de dominio o `desde > hasta` → 422 `integracion.export.rangoInvalido`.
5. **RN-05:** Unión de todos los pares `estadoRangos` del request antes de consultar (OR).
6. **RN-06:** Filtros opcionales en **AND** con el predicado de estado.
7. **RN-07:** Paginación inválida → 422 `integracion.export.paginacionInvalida`.
8. **RN-08:** Rango de fechas invertido o parse inválido → 422 (`rangoFechasInvalido` / `rangoInvalido`).
9. **RN-09:** Éxito = HTTP 200 + `error: 0`; **prohibido** 202 en este endpoint.
10. **RN-10:** 401 sin Bearer; 403 sin ability; tenant inválido según convención integración (400/404).
11. **RN-11:** Rate limit 60 req/min por token+tenant documentado y aplicado.
12. **RN-12:** Log Should `export_comprobantes` por request procesado (no bloquea 200 si falla log).
13. **RN-13:** Sin filtro de visibilidad de vendedor/cliente del portal.
14. **RN-14:** Controller **no** ejecuta SQL; delega en servicio que invoca SP (regla 74).

## Criterios de aceptación

- [ ] **CA-01:** Token integración + `X-Paq-Cliente` válidos → HTTP 200, `error: 0`, `resultado` con `items[]` (cada ítem con `cabecera`, `renglones`, `cierre`), `metadata.fechaProceso` presente.
- [ ] **CA-02:** Con fixture HU-058, `estadoRangos=0:0` solo devuelve cabeceras con `estado === 0`.
- [ ] **CA-03:** `estadoRangos=-1:-1&estadoRangos=0:0&estadoRangos=98:99` (o CSV equivalente) aplica OR sin duplicar `codPedido` en la página.
- [ ] **CA-04:** `page=2` y `pageSize` fijo no repiten cabeceras de `page=1`; cada ítem trae `renglones` completos de esa cabecera.
- [ ] **CA-05:** Con datos en BD, `estadoRangos=99:99` y `-1:-1` responden 200 (pueden devolver `items` vacíos si no hay filas).
- [ ] **CA-06:** GET sin `estadoRangos` → HTTP 422 y clave `integracion.export.estadoRangosRequerido`.
- [ ] **CA-07:** `estadoRangos=1:0` → HTTP 422.
- [ ] **CA-08:** Usuario portal con consulta permitida pero **sin** ability integración → HTTP 403.
- [ ] **CA-09:** OpenAPI lista la ruta, parámetros query, tag **Integración exportación** y ejemplo camelCase.
- [ ] **CA-10:** Dos rangos que cubren el mismo `estado` no duplican el mismo `codPedido` en un page.
- [ ] **CA-11:** Misma petición con `pageSize=10` y con `page_size=10` devuelve el mismo `pageSize` en `resultado`.
- [ ] **CA-12:** Filtro sin match → 200, `items: []`, `total: 0`, `totalPages: 0`.
- [ ] **CA-13:** Mezcla en un request de `estadoRangos=0:0,1:2` y `estadoRangos=98:99` une los tres pares.
- [ ] **CA-14:** `fechaDesde` posterior a `fechaHasta` → 422.
- [ ] **CA-15:** `codCliente` + `estadoRangos` acotan en AND (verificado con fixture).
- [ ] **CA-16:** Request exitoso persiste log Should `export_comprobantes` (o queda documentada excepción explícita en TR).
- [ ] **CA-17:** Al superar 60 req/min (test o config), el endpoint responde según política de rate limit de integración.

## Escenarios Gherkin

```gherkin
Feature: Plataforma GET exportación comprobantes integración

  Scenario: Lectura autorizada con envelope
    Given un token técnico con "pw_integracion_importacion"
    And header "X-Paq-Cliente" resuelve un tenant válido
    When realiza GET "/api/v1/integracion/comprobantes?estadoRangos=0:0&page=1&pageSize=20"
    Then la respuesta HTTP es 200
    And el cuerpo tiene "error" 0
    And "resultado.items" es un array
    And "resultado.metadata.fechaProceso" está definido

  Scenario: Sin token
    When realiza GET "/api/v1/integracion/comprobantes?estadoRangos=0:0" sin Authorization
    Then la respuesta HTTP es 401

  Scenario: Vendedor del portal
    Given un usuario autenticado del portal sin ability de integración
    When realiza GET "/api/v1/integracion/comprobantes?estadoRangos=0:0"
    Then la respuesta HTTP es 403

  Scenario: Filtro de estado obligatorio
    Given un token técnico válido
    When realiza GET "/api/v1/integracion/comprobantes?page=1&pageSize=20"
    Then la respuesta HTTP es 422

  Scenario: Formato CSV de rangos
    Given un token técnico válido
    When realiza GET con query "estadoRangos=0:0,1:2,98:99"
    Then la respuesta HTTP es 200
    And el envelope tiene "error" 0

  Scenario: Página sin resultados
    Given un token técnico válido
    And no existen cabeceras con estado 77
    When realiza GET con "estadoRangos=77:77"
    Then la respuesta HTTP es 200
    And "resultado.items" está vacío
    And "resultado.total" es 0
    And "resultado.totalPages" es 0
```

## Supuestos explícitos

- El adapter de export invoca únicamente SP autorizados (HU-058); el controller no conoce tablas.
- Los mensajes de error usan claves i18n en `respuesta`/`codigo`, sin SQL ni stack en producción.
- El job ERP pagina incrementando `page` hasta agotar `totalPages`.

## Preguntas abiertas (resolver en TR)

| # | Tema | Notas |
|---|------|--------|
| Q1 | Middleware | Extender grupo `integracion` existente vs alias dedicado GET export. |
| Q2 | Log JSON | Campos mínimos en payload (`page`, `total`, resumen de `estadoRangos`). |
| Q3 | Postman | Carpeta/colección integración — nombre del request ejemplo. |
| Q4 | Tenant error | Código HTTP exacto 400 vs 404 (copiar handler 101-22). |

## Riesgos de ambigüedad

- Si el rate limit no se cablea en la misma TR que maestros, el export podría quedar sin límite operativo.
- Validación de fechas ART debe compartir helper con consultas para no divergir límites de día.
- OpenAPI podría documentar solo plataforma y posponer lista completa de campos cabecera (acordar con TR datos).

## Plantilla SDD

```text
exportación comprobantes ERP: adoptar GEN-19/GEN-20. UI/motor = GET integración + validación plataforma (HU-101-057).
No reimplementar consultas /consultas/*. SoT: SPEC-101-25.
```