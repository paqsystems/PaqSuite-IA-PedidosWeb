# Cierre A1 — SPEC-101-22 — APIs importación maestros (ERP → PedidosWeb)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-20 |
| **Épica** | APIs importación maestros ERP → PedidosWeb |
| **Producto** | [APIs-Importacion-datos-maestros.md](../../02-producto/PedidosWeb/APIs-Importacion-datos-maestros.md) |
| **SPEC** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Jira** | [PIAPEDWEB-34](https://paqsystems.atlassian.net/browse/PIAPEDWEB-34) |
| **Método** | Skill `spec-ambiguity-review` |

---

## Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno (tras decisiones A1 abajo) |

---

# Revisión de ambigüedad — SPEC-101-22

## Checklist (10 ejes)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance | OK | 14 recursos; transacciones 101-23 fuera; sin UI |
| 2 | Actores | OK | Token integración + `pw_integracion_importacion`; vendedor portal excluido |
| 3 | Flujo | OK | POST lote → validar tenant/auth → SP por ítem → envelope + log |
| 4 | Reglas | Obs. | Semántica `delta` vs `snapshot` unificada en A1-C01…C03 |
| 5 | Datos | Obs. | Mapeo campo a campo en TR/OpenAPI; modelo §3 es SoT |
| 6 | UI | OK | Sin UI; logs existentes |
| 7 | APIs | OK | Prefijo, envelope, éxito parcial 200; abortos 4xx |
| 8 | Especiales | Obs. | Recursos sin política snapshot — cerrado A1-C04 |
| 9 | CA | OK | CA producto §11 cubiertos; medibles en feature tests |
| 10 | Trazabilidad | OK | Producto §4 SoT transversal; 101-08 logs |

## Ambigüedades críticas

Ninguna bloqueante para Parte B tras cierre A1.

| ID | Tema | Resolución A1 |
|----|------|----------------|
| AMB-C-101-22-01 | Significado de `delta` vs `snapshot` por tipo de maestro | **Cerrado** — ver decisiones A1-C01, A1-C02, A1-C03 en SPEC |
| AMB-C-101-22-02 | `listaprecios-articulos` y `descuentocantidad` sin fila en tabla políticas §6 producto | **Cerrado A1-C04:** solo `delta` en MVP; `modo=snapshot` → lote rechazado (422) |
| AMB-C-101-22-03 | Snapshot catálogo chico «desactivar o borrar» sin columna inhabilitado | **Cerrado A1-C02:** solo **DELETE** físico si sin referencias; no soft-delete inventado; si hay FK → **omitido**, no error de lote |
| AMB-C-101-22-04 | Contadores `omitidos` vs `rechazados` | **Cerrado A1-C05:** `rechazados` = ítem inválido/FK en upsert; `omitidos` = skip intencional (p. ej. snapshot delete bloqueado por referencia, clave fuera de whitelist ignorada en validación previa) |

## Ambigüedades menores (TR / OpenAPI, no bloquean B)

| ID | Tema | Resolución / propuesta A1 |
|----|------|----------------------------|
| AMB-M-101-22-01 | Forma JSON ítem `escalas` | **Propuesta A1:** `{ "codEscala", "descripEs", "nroEscala", "valores": [{ "codValor", "descValor" }] }`; TR alinea nombres a camelCase API |
| AMB-M-101-22-02 | `fechaProceso` en maestros sin `fecha_proceso` en tabla | **Propuesta A1:** persistir en log siempre; en tablas solo donde el modelo ya tenga `uma_fecha` / `fecha_proceso` (ningún maestro §3 salvo uso futuro); no agregar columnas en este SPEC |
| AMB-M-101-22-03 | Tope detalle errores en `resultado.errores` | **Propuesta A1:** máximo **50** ítems; si hay más, `respuesta` indica truncado (clave i18n) |
| AMB-M-101-22-04 | `{recurso}` desconocido en path | **Propuesta A1:** HTTP **404** + envelope `integracion.maestro.recursoDesconocido` |
| AMB-M-101-22-05 | Auth sin token / token sin ability | **Propuesta A1:** **401** sin Bearer válido; **403** con usuario sin `pw_integracion_importacion` |
| AMB-M-101-22-06 | Rate limit | **Propuesta A1:** 60 req/min por token+tenant (ajustable en TR) |
| AMB-M-101-22-07 | Un SP por recurso vs SP genérico | **Propuesta A1:** un SP por recurso (o par cabecera/detalle escalas); firma común documentada en TR |
| AMB-M-101-22-08 | `especial` en JSON import | **Propuesta A1:** boolean (`true`/`false`); persiste `bit` en BD (alineado lookup carga) |
| AMB-M-101-22-09 | Severidad log | **Propuesta A1:** `info` si `rechazados=0`; `warning` si éxito parcial; `error` si lote abortado |

## Supuestos detectados

- El ERP envía lotes respetando orden §7; el API no reordena ni precarga dependencias.
- Gateway/agente invoca los mismos SP que SQL local (regla 74).
- Permiso `pw_integracion_importacion` se asigna solo al usuario técnico; no aparece en menú MVP vendedor.
- Consulta parámetros web sigue solo lectura; el import ERP es vía recurso `parametros`.
- Reenvío del mismo lote es idempotente por upsert, sin tabla de idempotencia HTTP en MVP.

## Preguntas para decisión humana

Ninguna pendiente bloqueante. Las AMB-M quedan como propuestas A1 aceptables en TR salvo reversión explícita en B/C.

## Recomendaciones de ajuste del SPEC

- [x] Incorporar decisiones A1-C01…C05 y cierres AMB-A-101-22-01…05.
- [x] Registrar propuestas AMB-M-101-22-01…09.
- [x] Marcar A1 cerrado / autoriza Parte B.
- [ ] Parte B: sugerir HU separadas — (1) plataforma integración (auth, permiso, rutas, envelope, logs), (2) SP + API por familia de recursos (catálogo chico / artículos-listas / clientes / parámetros).

## Veredicto

**Apto con observaciones** para cierre **A1**. **Autoriza Parte B** (generación de HU).
