# Cierre F — CC PQ #9 (02/07/2026) — `ActualizarPrecioCopia` y copia paramétrica

## Alcance

Verificación **F1 + F** (openspec-05) sobre correcciones derivadas del Control de Calidad #9:

| TR | HU | SPEC |
|----|-----|------|
| [TR-GEN-04-consulta-parametros](../001-Generaliddes/TR-GEN-04-consulta-parametros.md) | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) | [SPEC-001-04-configuracion-global](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) |
| [TR-SPEC-101-04-services-pedidos](TR-SPEC-101-04-services-pedidos.md) | [HU-101-026-copiar-comprobante](../../03-historias-usuario/101-PedidosWeb/HU-101-026-copiar-comprobante.md) | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |

> *En Parte F los deltas vivían en archivos `*-update`; unificados en Parte I — ver [I-CC-PQ-9-cierre-formal.md](I-CC-PQ-9-cierre-formal.md).*

**Fecha verificación:** 02/07/2026  
**Parte E:** [E-CC-PQ-9-tests.md](E-CC-PQ-9-tests.md)  
**Rama / build:** `v1.1.0-paq` @ working tree local (commit base `29d2ce3` + fixes sesión F)

---

## F1 — Verificación agente (código + tests)

**Resultado F1:** **Aprobado con observaciones**

### HU-GEN-04 / TR-GEN-04 — Consulta parámetros

| AC / ítem | Evidencia código | Estado |
|-----------|------------------|--------|
| AC-P01 seed `ActualizarPrecioCopia` | `PQ_PARAMETROS_GRAL.PedidosWeb.seed.json` | OK |
| AC-P02 listado API dinámico | `ParametrosConsultaService::listarPorPrograma` | OK |
| AC-P03 i18n fallback 5 locales | `pedidosWeb.{es,en,pt,fr,it}.json` + `resolveParametroConsultaTexts.ts` | OK |
| AC-P06 SQL caption/tooltip | `Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql` | OK |
| Fila en tenant dev | INSERT idempotente en `Ankas_del_sur` (script `insert-actualizar-precio-copia-param.php`) | OK (deploy manual) |

### HU-101-026 / TR-SPEC-101-04 — Copia paramétrica

| AC / RN | Evidencia código | Estado |
|---------|------------------|--------|
| RN-C01 / AC-C01 `false` conserva origen | `ComprobanteCopiaService::copiarBorrador` rama `!getActualizarPrecioCopia()` | OK |
| RN-C02 / AC-C07 validar origen vs params vigentes | `assertPreciosOrigenRenglonesPermitidos` | OK |
| RN-C03 / AC-C02 precios desde lista | `aplicarPreciosLista` + `ArticuloRepository::findPrecioLista` | OK |
| RN-C04 / AC-C03 rechazo sin precio / precio cero en lista | `assertPreciosListaRenglonesPermitidos` (validación **separada** por tipo) | OK |
| RN-C05 recálculo importes | `CalculoTotalesService::calcular` post-lookup | OK |
| AC-C04 modal error 422 (no abrir carga) | `PedidosCargaPage.tsx` / `usePedidosCargaMobile.ts` → `PedidosCargaErroresGrabacionDialog` contexto `copia` | OK |
| Lectura parámetro runtime | `PedidosWebParameterService::getActualizarPrecioCopia` | OK |
| Claves legacy ERP | `getBoolConAliasCanonico` — prioriza `ArticulosPrecioCero` / `ArticulosSinPrecio` | OK |
| FE `POST copiar` | `comprobanteApi.ts` + hidratación `modo=copia` | OK |
| AC-C05 copia P/Pend/Presup | Misma ruta `copiarComprobante`; acción en consultas (sin regresión arquitectura) | OK (manual smoke) |
| AC-C06 sin regresión grabación | Flujo grabación sin cambio de contrato | OK (sin E2E dedicado) |
| AC-C05 conversión P→Ped | `PresupuestoCierreService` / tests existentes sin uso de `ActualizarPrecioCopia` | OK (unit regresión implícita) |

### Correcciones detectadas en sesión F (post-D)

| Tema | Fix | Estado |
|------|-----|--------|
| Parámetro ausente en `Ankas_del_sur` | INSERT fila `ActualizarPrecioCopia` | OK |
| Validación OR global precio cero/sin precio | Validación granular en rama lista | OK |
| Legacy `Articulossinprecio` anulaba canónico | Preferencia clave canónica en `PedidosWebParameterService` | OK |
| Error copia como texto pequeño | Modal DevExtreme + `navigate(-1)` al cerrar | OK |

---

## F — QA manual PQ

**Responsable:** Pablo Quarracino (PQ)  
**Entorno:** Local — `http://localhost:3010` → backend `:8088` — `Ankas_del_sur`

| Escenario | Resultado PQ |
|-----------|--------------|
| Consulta parámetros muestra `ActualizarPrecioCopia` | OK (tras INSERT tenant) |
| Copia con `ActualizarPrecioCopia = true` y artículo sin precio en lista + params restrictivos | OK — modal «No se pudo copiar el comprobante» + mensaje `business.precioCeroNoPermitido`; no abre carga |
| Cierre modal copia rechazada | OK — vuelve al listado anterior |

**Resultado QA manual:** **Aprobado**

---

## F — Verificación documental (TR ↔ SPEC ↔ HU ↔ producto)

**Resultado F:** **Aprobado con observaciones**

| Documento | Alineado | Nota |
|-----------|----------|------|
| TR base (2 familias) | Sí | D+E+F cerrados; Parte I unificada |
| SPEC base (2) | Sí | RN-C04 granular documentada |
| HU base (2) | Sí | CA marcados en Parte I |
| `consulta-parametros.md` | Sí | Entrada `ActualizarPrecioCopia` |
| `PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` §10.6 | Sí | Semántica parámetro |
| `00-ControlCalidad-PQ.md` #9 | Sí | Finalizado Parte I |
| `docs/99-manual-usuario/PedidosWeb.md` §6.9 | Sí | Parte I — copia paramétrica + modal |

### Observaciones no bloqueantes

| ID | Tema | Destino |
|----|------|---------|
| OBS-01 | E2E Playwright copia `false` vs `true` | Opcional; smoke manual PQ OK |
| OBS-02 | INSERT parámetro en tenants productivos | Runbook deploy: seed JSON + script INSERT/SQL |
| OBS-03 | Limpiar fila legacy `Articulossinprecio` duplicada en ERP | Operación cliente si aplica |
| OBS-04 | Metadatos `Finalizado` en HU/TR base | Parte I — [I-CC-PQ-9-cierre-formal.md](I-CC-PQ-9-cierre-formal.md) |

---

## Veredicto final

| Control | F1 | F (agente) | F (manual PQ) |
|---------|----|------------|---------------|
| CC #9 (02/07/2026) | Aprobado con observaciones | Aprobado con observaciones | **Aprobado** |

**Estado CC #9:** **Finalizado (Parte I 02/07/2026)** — ver [I-CC-PQ-9-cierre-formal.md](I-CC-PQ-9-cierre-formal.md).
