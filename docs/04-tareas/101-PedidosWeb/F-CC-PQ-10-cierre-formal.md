# Cierre F — CC PQ #10 (30/07/2026) — `CargaUnidadesVenta` (retrospectivo)

## Alcance

Verificación **F1** (agente) sobre correcciones del Control de Calidad #10.  
**No** sustituye Parte **I** (unificación de `*-update` en SPEC/HU/TR base).

| Capa | Updates (siguen en `docs/.../updates/`, Estado **Pendiente**) |
|------|---------------------------------------------------------------|
| Parámetro | SPEC-001-04 / HU-GEN-04 / TR-GEN-04 |
| Modelos / services | SPEC-101-02 / SPEC-101-04 · TR-SPEC-101-02 / TR-SPEC-101-04 |
| Carga UI | SPEC-101-10 · HU-101-006 · TR-SPEC-101-10 |
| Mail | SPEC-101-13 · HU-101-019 · TR-SPEC-101-13 |
| Detalle pedidos | SPEC-101-07 / SPEC-101-11 · HU-101-028 · TR-SPEC-101-07 / TR-SPEC-101-11 |
| Excel | SPEC-101-16 / SPEC-101-21 · HU-101-029/030/043 · TR-SPEC-101-16 |
| Asistente IA | SPEC-101-19 · HU-101-040 · TR-SPEC-101-19 |

**Fecha F1:** 18/08/2026 (retrospectivo: D+E el 30/07/2026; F no se ejecutó entonces).  
**Parte E:** [E-CC-PQ-10-tests.md](E-CC-PQ-10-tests.md)  
**Commit D:** `56271ef` — `feat(pedidosweb): carga por unidades de venta (CC PQ #10)…` (30/07/2026)  
**Integración:** PR #22 → `v1.1.1`; contenido en `origin/develop` y `origin/main` (PR #29, 05/08/2026)

---

# Verificación del agente - CC PQ #10

## Resultado

**Aprobado con observaciones**

Código y tests del alcance CC #10 están en `v1.1.1` / `main`. El Control de Calidad seguía en **A Programar** porque no hubo Parte F ni Parte I. El QA manual de F **no está documentado** (PQ indica uso en producción).

## Evidencia revisada

- Commit `56271ef` (30/07/2026) + merge a `main`.
- [E-CC-PQ-10-tests.md](E-CC-PQ-10-tests.md) — 15 PHPUnit + 20 Vitest, veredicto E **Aprobado**.
- Código: `CargaUnidadesVentaConverter`, migración `2026_07_30_100000_add_carga_unidades_venta_columns.php`, seed `CargaUnidadesVenta`, UI renglón, Excel, mail, asistente, consulta detalle.
- Producto parcial: `consulta-parametros.md`, `consulta-detalle-pedidos.md`, definición conceptual § parámetro.
- SPEC-001-04 **base** ya cita `CargaUnidadesVenta` (línea suelta en D). HU/TR-GEN-04 **base** y SPEC-101-\* **base** no absorben el delta.

## Hallazgos críticos

Ninguno de implementación que impida el cierre F1 de código.

## Advertencias

1. **Parte F (QA manual) no documentada** el 30/07/2026. Este F1 no inventa escenarios PQ (copia de #9). Producción referida por PQ no deja acta de casos `CargaUnidadesVenta` true/false.
2. **Parte I no hecha.** ~27 archivos `*-update` siguen `Estado: Pendiente`. El índice CC decía «A Programar» con nota D+E; el bloque #10 solo decía Parte G.
3. **Manual de usuario** (`docs/99-manual-usuario/PedidosWeb.md`) **no** menciona unidades de venta / `CargaUnidadesVenta`.
4. **Checklists AC** de TR-update siguen sin tildar (trabajo D no actualizó casillas).
5. No hay `TR-SPEC-101-21-*-update` aparte: masivo cubierto en [TR-SPEC-101-16-importacion-excel-update.md](../updates/101-PedidosWeb/TR-SPEC-101-16-importacion-excel-update.md) (AC-CC10-T-X3).
6. E dejó pendiente PHPUnit con BD (`PedidosWebParameterServiceTest`, mail E2E) y E2E Playwright carga param true/false.

## Sugerencias

- Ejecutar **Parte I** (fusionar updates en base, marcar HU/TR **Finalizado**, actualizar manual § carga).
- Si PQ confirma QA en producción: anotar 2–3 escenarios (param false/true, Excel, detalle) en este F o en un addendum.
- No reabrir D: el hueco es documental (F/I), no de código.

## Tests

- Comandos: los de E-CC-PQ-10 (30/07/2026). Esta F1 **no** re-ejecutó PHPUnit/Vitest (18/08/2026).
- Resultado documentado E: **15 PHPUnit + 20 Vitest passed**.

## Pendientes

| ID | Tema | Bloquea F1 código | Destino |
|----|------|-------------------|---------|
| OBS-01 | Parte I unificación `*-update` | No | Dispatcher I |
| OBS-02 | Manual usuario | No | I / `write-user-manual` |
| OBS-03 | QA F no acta | No (código) | PQ si se quiere paridad con #9 |
| OBS-04 | E2E Playwright / tests BD | No | Opcional |
| OBS-05 | Casillas AC TR-update | No | I (tildar al fusionar) |

## Recomendación final

Tratar el **código** como entregado en `main`. El **circuito OpenSpec** de CC #10 queda **abierto en F/I**. No marcar el control **Finalizado** hasta Parte I (como #9).

---

## F1 — Matriz alcance vs código

| Ítem CC #10 | Evidencia | Estado |
|-------------|-----------|--------|
| Parámetro `CargaUnidadesVenta` (B, seed) | `PQ_PARAMETROS_GRAL.PedidosWeb.seed.json` · `PedidosWebParameterService::getCargaUnidadesVenta` · i18n `pedidosWeb.*.json` | OK |
| `equivalencia_ventas` en artículos (default 1; 0/null → 1) | migración + `CargaUnidadesVentaConverter::resolveEquivalenciaVentas` · modelo `PqPedidoswebArticulo` | OK |
| `cantidad_venta` en `pq_pedidosweb_pedidosdetalle` (backfill = `cantidad`) | misma migración · `PqPedidoswebPedidoDetalle` · `CalculoTotalesService` | OK |
| Modal renglón: un solo campo cantidad según parámetro; importes desde `cantidad` | `PedidosCargaRenglonEditDialog` + `cargaUnidadesVenta.ts` · converter `fromCantidadUsuario` | OK |
| Mail: cantidad visible según parámetro | `ComprobanteMailService` + `cantidadVisibleParaUsuario` | OK |
| Detalle de Pedidos: columna `cantidadVenta` (no listados cabecera) | `DetallePedidosConsultaService` · `DetallePedidosConsultaColumns.tsx` · kardex mobile | OK |
| Excel individual / masivo / host carga | `PedidoIndividualRowResolver` · `PedidoMasivoGroupAssembler` · `ExcelImportHostModal` · `mapExcelImportToCarga` | OK |
| Asistente IA cantidad | `resolveAsistenteCantidadPair` · tools asistente · draft context | OK |
| Mobile carga | `PedidosCargaMobilePage` / `usePedidosCargaMobile` | OK |

## F — QA manual PQ

**No ejecutado / no hay acta** (30/07/2026).  
Declaración 18/08/2026: el cambio **ya estaría en producción**. Sin checklist de escenarios en este archivo.

## F — Verificación documental (TR ↔ SPEC ↔ HU ↔ producto)

**Resultado F documental:** **No aprobado como cierre de ciclo** (falta I + manual + metadatos Pendiente).  
**Resultado F1 código:** **Aprobado con observaciones**.

| Documento | ¿Delta CC #10? |
|-----------|----------------|
| SPEC-001-04 base | Sí (1 fila parámetro); update sigue existiendo |
| HU-GEN-04 / TR-GEN-04 base | No |
| SPEC/HU/TR 101-\* base | No (solo `*-update`) |
| Producto consulta params / detalle | Sí |
| Manual usuario | No |

## Veredicto

| Parte | Veredicto |
|-------|-----------|
| D | Hecho — `56271ef` en `main` |
| E | **Aprobado** — [E-CC-PQ-10-tests.md](E-CC-PQ-10-tests.md) |
| F1 código | **Aprobado con observaciones** |
| F QA | **Pendiente de acta** |
| I | **Pendiente** |

**Estado CC #10 en `00-ControlCalidad-PQ.md`:** **Implementado (D+E; F1 18/08/2026). Pendiente I.**
