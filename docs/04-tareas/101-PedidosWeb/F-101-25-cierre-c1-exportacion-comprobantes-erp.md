# Cierre C1 — SPEC-101-25 — API exportación comprobantes ERP

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-11-26 |
| **Épica** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) |
| **C** | [F-101-25-cierre-c](F-101-25-cierre-c-exportacion-comprobantes-erp.md) |
| **Veredicto C1** | **Apto con observaciones** — autoriza **D1** |
| **Orden D1** | **25a → 25b** |
| **D1** | [D1-PLAN-101-25](D1-PLAN-101-25-exportacion-comprobantes-erp.md) |

## TR revisadas

| TR | Jira | Resultado |
|----|------|-----------|
| [TR-SPEC-101-25-integracion-export-plataforma](TR-SPEC-101-25-integracion-export-plataforma.md) | [PIAPEDWEB-58](https://paqsystems.atlassian.net/browse/PIAPEDWEB-58) | Apto con observaciones |
| [TR-SPEC-101-25-integracion-export-datos](TR-SPEC-101-25-integracion-export-datos.md) | [PIAPEDWEB-59](https://paqsystems.atlassian.net/browse/PIAPEDWEB-59) | Apto con observaciones |

---

## Resultado general

Las TR 25a/25b están **alineadas** con SPEC-101-25, HU-057/058 y el documento producto. El contrato HTTP, auth integración, envelope GEN-20, lectura por SP, forma `items[].cabecera/renglones/cierre` y estrategia de tests son **suficientemente deterministas** para planificar e implementar sin bifurcaciones funcionales graves.

**No se ejecutó código ni tests** (C1 es revisión documental).

---

## Ambigüedades críticas

Ninguna bloqueante para D1.

---

## Decisiones C1 (temas §9 TR — cerrados)

| ID | Tema | Decisión |
|----|------|----------|
| C1-D-101-25-01 | Throttle | **`integracion-export`** nuevo en `RouteServiceProvider`; misma regla que `integracion-maestros` (60/min por `userId\|tenant`). No reutilizar el nombre del limiter maestros. |
| C1-D-101-25-02 | Alias `page_size` | `IntegracionExportComprobantesRequest::prepareForValidation` mapea `page_size` → `pageSize`. Defaults/máximos vía constantes **`ConsultaPaginacion`** (20 / 1000). |
| C1-D-101-25-03 | `metadata.fechaProceso` | **`ConsultaFechaProcesoFormatter::now()`**; clave JSON **`fechaProceso`** (camelCase) en `resultado.metadata` (diferente de consultas `fecha_proceso`). |
| C1-D-101-25-04 | Rango fechas ART | Clase **`App\Support\PedidosWeb\IntegracionExportFechaRange`**: `America/Argentina/Buenos_Aires`; `Y-m-d` → inicio/fin de día inclusive; SP recibe `datetime2` ya normalizado. |
| C1-D-101-25-05 | SP list vs detalle | **Un solo** `pq_sp_integracion_export_comprobantes_list` con **dos result sets** (cabeceras page + renglones del page). **No** implementar `pq_sp_integracion_export_comprobantes_detalle` en MVP. |
| C1-D-101-25-06 | Rangos estado en SP | Parámetro **`@estado_rangos_json`** (`nvarchar(max)`), array `[{"desde":0,"hasta":0},…]`. TVP: diferido; no usar en D1 salvo impedimento SQL documentado en PR. |
| C1-D-101-25-07 | Tabla cierre presupuesto | **`pq_pedidosweb_presupuestos_cierres`** (+ join motivo según modelo `PqPedidoswebPresupuestoCierre`). |
| C1-D-101-25-08 | Shape `cierre` | Solo si **`estado === 98`** y existe fila de cierre; shape = `mapPresupuestoCierre` (tipoCierre, idMotivo, motivoDescripcion, fechaCierre, codPedidoGenerado, observacion). Resto: **`cierre: null`**. |
| C1-D-101-25-09 | Mapper cabecera | **`IntegracionExportComprobanteMapper`** nuevo: copiar contrato de **`mapComprobanteItem`** + MUST integración (`talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb`) + SHOULD orígenes; **sin** `puede*` ni `guidSufijo`; clave siempre **`codPedido`**. **No** llamar `ConsultaListadoService` ni extraer mapper compartido en D1 salvo refactor trivial sin visibilidad. |
| C1-D-101-25-10 | OpenAPI cabecera | Lista de propiedades = [consulta-comprobantes-cabecera.md](../../02-producto/PedidosWeb/consulta-comprobantes-cabecera.md) §2 (sin flags UI) + campos MUST integración SPEC § Cabecera; completar en D1 con checklist en PR. |
| C1-D-101-25-11 | Log export | **Should** — fallo de log no altera HTTP 200; test en `@group optional` o unit mock. |
| C1-D-101-25-12 | Tenant inválido HTTP | Copiar códigos/envelope del stack integración maestros (`EnsureIntegracionImportacion` + `paq.tenant`). |

---

## Ambigüedades menores (D1, no bloquean)

| ID | Tema | Resolución D1 |
|----|------|----------------|
| C1-M-101-25-01 | Columnas SHOULD orígenes (`codPedidoOrigen`, …) | Confirmar nombres físicos en `pq_pedidosweb_pedidoscabecera` al armar SELECT del SP; omitir clave JSON si columna null. |
| C1-M-101-25-02 | `nroPedidoTango` tipo/string | Mapear como string en JSON (coherente con prompts integración); vacío `""` si null. |
| C1-M-101-25-03 | Matriz permisos OpenAPI | Entregable D1 junto con path Swagger (patrón 101-22/101-23). |
| C1-M-101-25-04 | Filtro fecha en SP | Predicado sobre **`c.fecha`** (cabecera), mismo criterio inclusivo que rango PHP. |
| C1-M-101-25-05 | Dedup pares `estadoRangos` | Parser puede colapsar pares idénticos antes del OR; **RN-01** exige dedup por `cod_pedido` en SQL. |

---

## Contradicciones TR ↔ HU ↔ SPEC

| Tema | Hallazgo | Estado |
|------|----------|--------|
| SPEC AMB-M-101-25-08 «+ detalle en TR» | C1 fija un SP, dos result sets | **Resuelto** — SPEC/TR alineados en decisión C1-D-101-25-05 |
| `codPresupuesto` en consultas vs export | SPEC exige siempre `codPedido` en integración | **Coherente** |
| Visibilidad vendedor | Consultas filtran; export no | **Coherente** (HU/SPEC) |
| `pageSize` vs `page_size` en consultas | Export usa camelCase en respuesta; acepta ambos en query | **Coherente** |

---

## Supuestos detectados

- Tests `@group integracion` requieren SQL Server + deploy script `integracion-export` en BD de prueba.
- 25a puede avanzar con **repositorio mock** antes de SP 25b (plan §8 TR 25a).
- Usuario de prueba integración: mismo patrón que maestros (`pw_integracion_importacion`).
- CA-08 (403 vendedor portal): usuario **sin** ability integración aunque tenga permisos portal.

---

## Cobertura tests ↔ AC

| Capa | Cobertura |
|------|-----------|
| 25a | CA-01, 06–09, 11–17 HU-057 vía feature + unit parser; CA-02–05, 10, 12–13, 15 requieren 25b o mock con datos fijos |
| 25b | CA-01…CA-14 HU-058 vía fixture + feature end-to-end |

Estrategia **testable**; no gaps bloqueantes.

---

## Veredicto

**C1 aprobado con observaciones. Puede pasar a D1; no autoriza implementar sin plan D1 explícito.**

**Puede pasar a D1/D: Sí**

---

## Siguiente paso

1. Parte **D** — seguir [D1-PLAN-101-25](D1-PLAN-101-25-exportacion-comprobantes-erp.md) (Fase 1 → Fase 2).