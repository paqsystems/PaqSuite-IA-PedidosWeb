# SPEC-001-07 - Importar Excel

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-GEN-07-plantilla-excel](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-plantilla-excel.md), [HU-GEN-07-carga-staging-excel](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-carga-staging-excel.md), [HU-GEN-07-grilla-procesamiento-excel](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-grilla-procesamiento-excel.md), [HU-GEN-07-historial-importaciones](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-historial-importaciones.md) |
| **Estado** | Documental — **B1 cerrado** (2026-06-11) |
| **Revisión A1** | Apto con observaciones (2026-06-09) — decisiones §6.1 parcial cerradas |

## Objetivo

Dejar especificado el marco funcional/técnico de importación por Excel como capacidad prevista (fuera de implementación MVP inicial).

## Estado de ejecución

Documental — **4 HU enriquecidas (parte B)**; sin TR ni implementación en MVP portal.

## Entradas requeridas

- Documentación de importar-excel en `docs/00-contexto/_mono/importar-excel/`.

## Fuentes

Subcarpeta: `docs/00-contexto/_mono/importar-excel/`

- `PQ_EXCEL_Diagrama_Mermaid.md`
- `PQ_EXCEL_Documento_Conceptual_Funcional_v3.md`
- `PQ_EXCEL_Modelo_de_Datos_v3.md`
- `PQ_EXCEL_SQL_Server_Tablas_y_Create.md`

## Decisiones humanas (cerradas parcialmente)

| Tema | Decisión |
|------|----------|
| `PermiteProcesamientoParcial = true` y **todas** las filas con error | **No** se habilita el procesamiento. |
| `PermiteProcesamientoParcial = true` y **cero** filas con error | Se procesa **todo** el conjunto; estado **`procesada`**. |
| `PermiteProcesamientoParcial = true` y mezcla error + válidas | Solo filas válidas; estado **`procesada_parcial`**. |
| **Fila ajustada** (`FilaAjustadaAutomaticamente`) | Trim o limpieza de caracteres no imprimibles (contexto §7); **solo auditoría** en staging; **sin mostrar al usuario** en esta etapa. |

Detalle y casos borde: `PQ_EXCEL_Documento_Conceptual_Funcional_v3.md` §6.1 y §7.

## Alcance

- Modelo conceptual de importación.
- Estructura de datos y tablas asociadas.
- Flujo general de procesamiento.
- **Por proceso:** política `PermiteProcesamientoParcial` — si ≥ 1 fila con error en staging, permite o bloquea el procesamiento del resto (contexto `importar-excel` §6.1).

## Fuera de alcance

- Desarrollo completo de importación Excel en MVP portal (`PedidosWeb_SPEC_MVP.md`).
- TR e implementación (partes C y D) hasta priorizar epic importar Excel.

## Inventario de tablas y entidades

| Objeto | Rol |
|--------|-----|
| `PQ_EXCEL_PROCESOS` | Configuración fija por proceso (`PermiteProcesamientoParcial`, `HandlerBackend`, flags normalización) |
| `PQ_EXCEL_PROCESOS_CAMPOS` | Definición de columnas Excel vs campos internos |
| `PQ_EXCEL_IMPORTACIONES` | Cabecera de lote / sesión de importación |
| `PQ_EXCEL_IMPORTACIONES_FILAS` | Staging persistente por fila |
| `PQ_EXCEL_IMPORTACIONES_FILAS_ERRORES` | Errores detallados por fila (auditoría) |
| `PQ_EXCEL_IMPORTACIONES_NOTIFICACIONES` | Toast / bandeja / resultado (procesos asíncronos) |
| `PQ_EXCEL_VW_HISTORIAL_IMPORTACIONES` | Vista consulta de historial |

Diagrama ER: `PQ_EXCEL_Diagrama_Mermaid.md`.

## Flujo extremo a extremo (documental)

1. Usuario entra al **proceso** de importación (menú / pantalla host).
2. Opcional: **descarga plantilla** (`HU-GEN-07-plantilla-excel`).
3. Selecciona archivo **`.xlsx`** y **una hoja** (`HU-GEN-07-carga-staging-excel`).
4. Validación **estructural** (encabezado fila 1, columnas, duplicados, combinadas).
5. Creación de **lote** y volcado a **staging** con normalizaciones §7.
6. Validación **formato** (FE) y **negocio** (BE / `HandlerBackend`).
7. **Grilla** DevExtreme con errores por fila (`HU-GEN-07-grilla-procesamiento-excel`).
8. Usuario **confirma procesamiento** según `PermiteProcesamientoParcial` → destino vía handler.
9. **Auditoría** de lote (contadores, estado `procesada` / `procesada_parcial`).
10. Consulta en **historial** (`HU-GEN-07-historial-importaciones`).

## Trazabilidad HU (parte B)

| HU | Foco | Orden TR sugerido |
|----|------|-------------------|
| [HU-GEN-07-plantilla-excel](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-plantilla-excel.md) | Exportar plantilla modelo `.xlsx` | 1 |
| [HU-GEN-07-carga-staging-excel](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-carga-staging-excel.md) | Carga, validación estructural, staging | 2 |
| [HU-GEN-07-grilla-procesamiento-excel](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-grilla-procesamiento-excel.md) | Grilla resultados, política parcial, procesar | 3 |
| [HU-GEN-07-historial-importaciones](../../03-historias-usuario/001-Generaliddes/HU-GEN-07-historial-importaciones.md) | Historial consultable | 4 |

## Entregables verificables

- Resumen funcional/técnico para abrir SPEC dedicado de importación Excel.
- Inventario de tablas y entidades involucradas (§ arriba).
- **4 HU** enriquecidas en `docs/03-historias-usuario/001-Generaliddes/`.

## Criterios de aceptación medibles

- Flujo de importación identificado de extremo a extremo en forma documental (§ Flujo e2e).
- Datos requeridos y restricciones técnicas listados explícitamente (fuentes `_mono/importar-excel/`).
- HU derivadas con criterios de aceptación y Gherkin trazables al contexto.

---

## Parte B — cierre (2026-06-11)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto B1** | **Cerrado** — 4 HU enriquecidas |
| **¿Puede pasar a parte C (TR) en MVP portal?** | **No** — epic posterior; sin prioridad en release MVP |
| **¿Listo para parte C cuando se priorice epic?** | **Sí** — resolver AMB-Q-07-* en TR |

### Entregables parte B

| Entregable | Estado |
|------------|--------|
| `HU-GEN-07-plantilla-excel` | Enriquecida |
| `HU-GEN-07-carga-staging-excel` | Enriquecida |
| `HU-GEN-07-grilla-procesamiento-excel` | Enriquecida |
| `HU-GEN-07-historial-importaciones` | Enriquecida |
| Índice HU README 001-Generaliddes | Actualizado |

### Preguntas abiertas (no bloqueantes MVP)

| ID | Tema | HU | Propuesta default |
|----|------|-----|-------------------|
| AMB-Q-07-01 | Permiso dedicado vs permiso del proceso host | plantilla | Mismo permiso que pantalla host |
| AMB-Q-07-02 | Override flags normalización por lote | carga-staging | Defaults del proceso en v1 |
| AMB-Q-07-03 | Reproceso en mismo lote tras corregir Excel | grilla-procesamiento | Nueva importación en v1 |

### Veredicto

**B1 cerrado** para SPEC-001-07. **No avanzar a parte C** en MVP portal. Al priorizar el epic importar Excel, generar TR en orden plantilla → carga → grilla/procesamiento → historial.
