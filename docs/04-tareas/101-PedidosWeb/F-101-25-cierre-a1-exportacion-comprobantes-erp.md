# Cierre A1 — SPEC-101-25 — API exportación comprobantes (PedidosWeb → ERP)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-11-26 |
| **Épica** | APIs exportación comprobantes ERP ← PedidosWeb |
| **Producto** | [APIs-Exportacion-comprobantes-erp.md](../../02-producto/PedidosWeb/APIs-Exportacion-comprobantes-erp.md) |
| **SPEC** | [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Jira** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) |
| **Método** | Skill `spec-ambiguity-review` |
| **Hermano** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) · [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) (auth integración) |

---

## Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno (tras decisiones A1 abajo) |

---

# Revisión de ambigüedad — SPEC-101-25

## Checklist (10 ejes)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance | OK | Un GET; sin UI; escritura ERP fuera; consultas portal intactas |
| 2 | Actores | OK | Token `pw_integracion_importacion`; tenant completo; operador portal 403 |
| 3 | Flujo | OK | GET síncrono 200; validación query → SP → mapeo anidado |
| 4 | Reglas | OK | RN-01…05 + rangos OR; A1 unifica fechas y filtros combinados |
| 5 | Datos | Obs. | Lista exacta cabecera = superset consulta + TR/OpenAPI; cierre 98 |
| 6 | UI | OK | Sin pantalla |
| 7 | APIs | OK | Contrato query/response; envelope GEN-20; prefijo `integracion.export.*` |
| 8 | Especiales | Obs. | Página vacía, `cierre` null, mezcla CSV/repetido — A1-M |
| 9 | CA | OK | CA-01…CA-11 testeables |
| 10 | Trazabilidad | OK | Producto + consultas cabecera/detalle + 101-22 auth |

## Ambigüedades críticas

Ninguna bloqueante para Parte B tras cierre A1.

| ID | Tema | Resolución A1 |
|----|------|----------------|
| AMB-C-101-25-01 | Claves envelope 422 | **Cerrado:** prefijo dedicado **`integracion.export.*`** para validación de query y export. Auth/tenant reutiliza convención 101-22 (`integracion.*` existente donde aplique). |
| AMB-C-101-25-02 | Rate limit GET | **Cerrado:** **60 req/min** por token+tenant, igual familia POST integración (101-22/23). |
| AMB-C-101-25-03 | Universo pedido vs presupuesto | **Cerrado:** **un solo listado** sobre `pq_pedidosweb_pedidoscabecera`; **sin** query `tipoComprobante`. Discriminación solo por `estado` (p. ej. 98/99). |
| AMB-C-101-25-04 | Semántica filtros combinados | **Cerrado:** predicado **AND** entre `estadoRangos` (OR interno), `codCliente`, `codPedido`, `fechaDesde`/`fechaHasta`. |
| AMB-C-101-25-05 | `estadoRangos` mezclado | **Cerrado:** aceptar repetido **y** CSV en el mismo request; **unir** todos los pares válidos en un solo conjunto OR antes de consultar. |

## Ambigüedades menores (TR / OpenAPI, no bloquean B)

| ID | Tema | Resolución / propuesta A1 |
|----|------|----------------------------|
| AMB-M-101-25-01 | Filtro fecha | `fechaDesde`/`fechaHasta` sobre columna `fecha` de cabecera (instante BD). Entrada: **ISO 8601** o **`YYYY-MM-DD`**. Solo fecha → día **completo** en zona **America/Argentina/Buenos_Aires** (misma convención que consultas en TR). `fechaDesde > fechaHasta` → **422** `integracion.export.rangoFechasInvalido`. |
| AMB-M-101-25-02 | Log por request | **Should** (no Must): una fila `tipo=export_comprobantes` por request exitoso o 422 de validación; **error** en fallos 5xx/tenant; severidad `info` en 200 con ítems, `info` también en 200 con `items: []`. |
| AMB-M-101-25-03 | Campo `cierre` | Siempre presente en cada ítem: **`null`** salvo `estado === 98` **y** exista fila en cierre → objeto mismo shape que `mapPresupuestoCierre`. Si 98 sin fila cierre → **`null`** (no omitir clave). |
| AMB-M-101-25-04 | Página vacía | HTTP **200** `error: 0`; `items: []`, `total: 0`, `totalPages: 0`, `page` refleja el solicitado (≥1). |
| AMB-M-101-25-05 | `page` / `pageSize` inválidos | Entero no numérico, `<1`, o `pageSize` > 1000 → **422** `integracion.export.paginacionInvalida`. |
| AMB-M-101-25-06 | Par `estadoRangos` malformado | Sin `:`, no enteros, vacío, o extremo fuera [-1,99] → **422** `integracion.export.rangoInvalido` (un mensaje por request si varios pares inválidos). |
| AMB-M-101-25-07 | `metadata.fechaProceso` | Instantánea **UTC con offset** al completar lectura (servidor), no la `fecha` de cabecera del comprobante. |
| AMB-M-101-25-08 | Nombre SP | Familia `pq_sp_integracion_export_comprobantes_list` (listado paginado); detalle renglones en mismo SP o `*_detalle` según TR datos. |
| AMB-M-101-25-09 | Mapeo cabecera | **MUST** replicar joins de `mapComprobanteItem` vía SP/adapter; prohibido llamar `ConsultaListadoService` desde ruta integración. |
| AMB-M-101-25-10 | Sin `estadoRangos` | **422** `integracion.export.estadoRangosRequerido` (no default “todo el universo”). |

## Supuestos detectados

- El ERP pagina por `page`/`pageSize`; no hay cursor/opaque token en MVP.
- Renglones de cabeceras fuera del page **no** se precargan ni cuentan en `total`.
- Estados 1–97 no reservados en UI siguen siendo filtrables si existen en BD.
- Confirmación POST descarga (estado 0→1) será otro SPEC (producto Prompt cabecera).

## Preguntas para decisión humana

Ninguna bloqueante.

## Recomendaciones de ajuste del SPEC

- [x] Incorporar AMB-C/M en § Decisiones cerradas en A1.
- [x] Cerrar tabla «Preguntas abiertas» de Parte A.
- [x] Marcar A1 **apto con observaciones** / autoriza Parte B.
- [x] Parte B: HU-101-057 plataforma · HU-101-058 datos (2026-11-26; Jira PIAPEDWEB-56/57). Nota: 055/056 ya usados en el repo (emisión).

## Veredicto

**Apto con observaciones** para cierre **A1**. **Autoriza Parte B** (generación de HU).