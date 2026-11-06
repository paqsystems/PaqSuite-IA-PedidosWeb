# Cierre A1 — SPEC-101-23 — APIs importación transacciones (ERP → PedidosWeb)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-21 |
| **Épica** | APIs importación transacciones ERP → PedidosWeb |
| **Producto** | [APIs-Importacion-transacciones.md](../../02-producto/PedidosWeb/APIs-Importacion-transacciones.md) |
| **SPEC** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **Jira** | [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35) |
| **Método** | Skill `spec-ambiguity-review` |
| **Hermano** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) (contrato lote / auth) |

---

## Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno (tras decisiones A1 abajo) |

---

# Revisión de ambigüedad — SPEC-101-23

## Checklist (10 ejes)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance | OK | 4 recursos; maestros 101-22 y `resumen-cuenta` fuera; sin UI |
| 2 | Actores | OK | Mismo token `pw_integracion_importacion`; vendedor 403 |
| 3 | Flujo | Obs. | Snapshot atómico vs éxito parcial de §2 — unificado A1-C01/C02 |
| 4 | Reglas | Obs. | Delta saldos, duplicados, campos extra — A1-C03…C05 |
| 5 | Datos | Obs. | Mínimos JSON vs modelo §4; detalle campo a campo en TR |
| 6 | UI | OK | Sin UI nueva; GET consultas y logs existentes |
| 7 | APIs | OK | Prefijo, envelope, 401/403/404; snapshot 422 si aborta |
| 8 | Especiales | Obs. | `items: []`, historial snapshot, FK, `comprometidoWeb` |
| 9 | CA | OK | 7 CA testeables en feature tests |
| 10 | Trazabilidad | OK | Producto transacciones + maestros §4 + consultas * |

## Ambigüedades críticas

Ninguna bloqueante para Parte B tras cierre A1.

| ID | Tema | Resolución A1 |
|----|------|----------------|
| AMB-C-101-23-01 | Snapshot atómico vs apply-válidos | **Cerrado:** snapshot stock/deuda/cheques es **atómico**. Un ítem inválido o duplicado de clave **no escribe**. HTTP **422**, `error ≠ 0`, log `error`. Ratifica Parte A. |
| AMB-C-101-23-02 | Éxito parcial 200 vs snapshot | **Cerrado:** HTTP **200** + `error: 0` con `rechazados > 0` **solo** en `delta` (historial, o delta opcional de saldos). Snapshot **nunca** responde 200 si no se aplicó el universo. |
| AMB-C-101-23-03 | Delta en stock/deuda/cheques | **Cerrado:** igual merge 101-22: upsert por clave; columnas **omitidas** no pisan; **no** borra claves ausentes. Snapshot sigue siendo el modo de producción. |
| AMB-C-101-23-04 | Duplicados de clave en el mismo `items[]` | **Cerrado:** snapshot → abortar lote (C01). Delta → primer ítem de esa clave se aplica; siguientes de la misma clave → `rechazados` (el resto del lote sigue). |
| AMB-C-101-23-05 | Propiedades JSON de más | **Cerrado:** `comprometidoWeb`, `*Base`, `disponibleNeto*` en stock → ítem **rechazado** (y aborta snapshot). Otras claves **desconocidas** se **ignoran** (forward compatible). |

## Ambigüedades menores (TR / OpenAPI, no bloquean B)

| ID | Tema | Resolución / propuesta A1 |
|----|------|----------------------------|
| AMB-M-101-23-01 | `fecha_proceso` / `uma_fecha` | Carátula `fechaProceso` se escribe en **todas** las filas aplicadas del lote (`uma_fecha` stock; `fecha_proceso` deuda/cheques/historial). GET metadata = `MAX` según docs de consulta. |
| AMB-M-101-23-02 | Historial campos mínimos | MUST: `idGva53`, `codCliente`, `tipo` (`t_comp`), `numero` (`n_comp`), `fechaEmision`. Resto nullable según modelo. Falta MUST → ítem rechazado. |
| AMB-M-101-23-03 | Tipos numéricos | `stock`, `comprometido`, `saldo`, `importe` y cantidades historial: decimal; TR alinea precisión al DDL. Negativos: **permitidos** si el ERP los manda (no inventar rechazo). |
| AMB-M-101-23-04 | `omitidos` en transacciones | Snapshot atómico OK: `omitidos=0`. No hay skip por FK de referencia (saldos se reemplazan). Delta: `omitidos` no se usa salvo TR futura; duplicados posteriores = `rechazados`. |
| AMB-M-101-23-05 | Recurso desconocido | HTTP **404** + `integracion.transaccion.recursoDesconocido` |
| AMB-M-101-23-06 | Severidad log | Snapshot OK → `info`; snapshot abortado → `error`; delta parcial → `warning`; delta limpio → `info` |
| AMB-M-101-23-07 | Nombres SP | Un SP por recurso: `pq_sp_integracion_transacciones_stock` / `_deuda` / `_cheques` / `_historial_ventas` (ajustable en TR) |
| AMB-M-101-23-08 | Cheques `interno` | Tipo según DDL vigente; JSON número o string normalizado en TR. Par `(interno, numero)` es la clave. |
| AMB-M-101-23-09 | Implementar 23 antes que 22 D | Permitido en tests con **fixtures** de artículos/clientes. Producción: maestros primero. No bloquear HU 23 a espera de D 22. |
| AMB-M-101-23-10 | `items: []` + `delta` | No-op **200** `error: 0` (igual 22). Snapshot + vacío → **422**. |

## Supuestos detectados

- El ERP pagina historial; PedidosWeb no recorta por `DiasVentasDetalladas` al importar.
- Replace-conjunto de snapshot se hace **dentro del SP** (temp/MERGE), no `TRUNCATE` desde PHP.
- GET de consultas no cambia contrato; tras snapshot OK el universo visible (con visibilidad de usuario) refleja el lote.
- Columnas legacy de deuda/cheques: el import **solo** escribe canónico; el GET sigue resolviendo aliases si existen.
- Rate limit / tope 1500 / errores[] 50: heredados de 101-22, no se reabren.

## Preguntas para decisión humana

Ninguna bloqueante. Default atómico ya estaba en Parte A; A1 lo confirma.

## Recomendaciones de ajuste del SPEC

- [x] Incorporar A1-C01…C05 y AMB-M-101-23-01…10.
- [x] Aclarar que el párrafo de éxito parcial de maestros **no** aplica a snapshot de saldos.
- [x] Marcar A1 cerrado / autoriza Parte B.
- [ ] Parte B: HU sugeridas — (1) plataforma transacciones (rutas, auth reuso, envelope, logs `transaccion_import`), (2) snapshot stock, (3) snapshot deuda+cheques, (4) delta historial.

## Veredicto

**Apto con observaciones** para cierre **A1**. **Autoriza Parte B** (generación de HU).
