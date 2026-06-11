# SPEC-001-08 - Pivots

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | Ninguna en MVP (referencia en `SPEC-001-03`) |
| **Estado** | Documental |
| **Revisión A1** | Apto con observaciones (2026-06-10) — sin HU MVP; decisiones §6 cerradas parcialmente |

## Objetivo

Definir la base conceptual y técnica de pivots (consultas pivotables, plantillas y motor de validaciones) para adopción progresiva.

## Estado de ejecución

Documental para este bloque inicial (base de diseño para ejecución posterior).

## Entradas requeridas

- Documentación de pivots en `docs/00-contexto/_mono/pivots/`.
- Paridad UI transversal: `SPEC-001-03`, `HU-GEN-03-layouts-grilla`.

## Fuentes

Subcarpeta: `docs/00-contexto/_mono/pivots/`

- `arquitectura_motor_pivots_y_flujo.md`
- `catalogo_campos_pivotables.md`
- `catalogo_plantillas_globales_pivots.md`
- `catalogo_validaciones_pivots.md`
- `especificacion_tecnica_consultas_pivotables.md`
- `frontend-pivotgrid-devextreme-agregaciones-y-menu.md`
- `patron-i18n-pivot-devextreme.md`
- `modelo_datos_pivots_personalizados.md`
- `modelo_datos_pivots_y_catalogo.md`
- `norma_conceptual_pivots.md`

Transversal: `docs/00-contexto/_mono/03-ui-transversal/pivots.md`

## Decisiones humanas (cerradas parcialmente)

| ID | Tema | Decisión |
|----|------|----------|
| **AMB-P08-01** | Diseños propios en selector | Sufijo visual **` (*)`** vía i18n `pivotLayout.ownerMarker`; no persiste en `nombre` |
| **AMB-P08-02** | Plantilla inicial | `configId: null` → **pivot vacía** (field panel sin campos asignados); i18n `pivotLayout.initialTemplate` |
| **AMB-P08-03** | Guardar con plantilla inicial | **Guardar** abre flujo **Guardar como** (POST); no altera pivot base ni diseños ajenos |
| **AMB-P08-04** | Actualizar datos | Ícono `refresh` en toolbar; tooltip `pivot.refresh`; `data-testid="pivotRefresh"`; re-fetch servidor |
| **AMB-P08-05** | i18n PivotGrid | Patrón obligatorio en `patron-i18n-pivot-devextreme.md` (5 idiomas) |
| **AMB-P08-06** | Primera apertura sin último diseño | Aplicar **pivot base** de metadata (consulta útil); plantilla inicial vacía solo al elegirla en selector |
| **AMB-P08-07** | Unicidad de nombre guardado | **Paridad grilla:** único por `consulta_id` (un registro por nombre en la consulta); error i18n si duplicado en Guardar como |

Detalle normativo: `especificacion_tecnica_consultas_pivotables.md` §17–17.1, `frontend-pivotgrid-devextreme-agregaciones-y-menu.md` §6–8.

## Alcance

- Arquitectura del motor de pivots.
- Catálogos necesarios (campos, plantillas, validaciones).
- Reglas frontend base para pivotgrid.
- Paridad UI con grillas (GEN-03): diseños guardados, plantilla inicial, Actualizar, i18n.

## Fuera de alcance

- Implementación completa de pivots avanzados en el MVP core.
- HU/TR de ejecución en release MVP portal (`PedidosWeb_SPEC_MVP.md`).

## Entregables verificables

- Definición técnica de catálogo de campos, plantillas y validaciones.
- Criterios frontend para pivotgrid documentados (agregaciones, toolbar, i18n).
- Inventario de fuentes en `docs/00-contexto/_mono/pivots/` trazable desde este SPEC.

## Criterios de aceptación medibles

- Existen lineamientos únicos para modelado de pivots.
- Se identifican claramente las etapas para SPEC funcional de pivots.
- Queda documentada la paridad con mejoras de grillas:
  1. Diseños propios identificados con ` (*)` (`pivotLayout.ownerMarker`).
  2. Plantilla inicial restablece pivot vacía (`configId: null`).
  3. Guardar desde plantilla inicial equivale a Guardar como.
  4. Ícono Actualizar con re-fetch (`pivot.refresh`, `pivotRefresh`).
  5. i18n DevExtreme del pivot en 5 idiomas (`patron-i18n-pivot-devextreme.md`).

---

## Revisión A1 — cierre (2026-06-10)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a HU (MVP portal)** | **No** — bloque documental; sin HU en primer release |
| **Puede abrir epic / HU futura** | **Sí** — tras cerrar pendientes §6.1 en TR dedicada |

### Checklist A1 (resumen)

| Área | Estado | Notas |
|------|--------|-------|
| Alcance / fuera de alcance | OK | Documental MVP; motor completo fuera de portal MVP |
| Actores / permisos | OK | Creador modifica/elimina; visibilidad global por consulta |
| Flujo guardado | OK | Guardar / Guardar como / Cargar / Eliminar + plantilla inicial |
| Reglas de negocio | OK | Paridad GEN-03 cerrada en §6 |
| Datos | Obs. | Tabla canónica `pq_pivots_config`; alias legacy `PQ_PIVOTS` en doc antiguo |
| UI / i18n | OK | Patrón pivot + 5 mejoras grillas documentadas |
| APIs | Pendiente TR | Contrato CRUD no definido a nivel SPEC (vive en arquitectura + TR futura) |
| Criterios aceptación | OK | Medibles y trazables a contexto `_mono/pivots/` |

### Ambigüedades críticas

Ninguna bloqueante para el **estado documental** del SPEC (no hay HU MVP que implementar).

### Ambigüedades menores

| ID | Tema | Resolución |
|----|------|------------|
| AMB-M-P08-01 | `PQ_PIVOTS` vs `pq_pivots_config` | **Canónico:** `pq_pivots_config` (`modelo_datos_pivots_y_catalogo.md`). `modelo_datos_pivots_personalizados.md` es alias conceptual legacy — alinear en TR |
| AMB-M-P08-02 | Tabla `pq_pivots_config_last_used` | Mencionada en arquitectura; **definir DDL en TR** al implementar persistencia |
| AMB-M-P08-03 | `configId` (frontend) vs `pivot_id` (BD) | Mapeo 1:1; documentar en contrato API de la TR |
| AMB-M-P08-04 | `pivots.md` § capacidades vs plantilla inicial | **Alineado** en A1: primera apertura → pivot base; plantilla inicial → vacía bajo demanda (§6 AMB-P08-06) |

### Supuestos detectados

- DevExtreme `PivotGridBlock` será el wrapper transversal (referencia en `frontend-pivotgrid-devextreme-agregaciones-y-menu.md`).
- Dictionary DB con prefijo `pq_pivots_*` según catálogos.
- En MVP portal solo referencia desde `SPEC-001-03`; implementación en módulo Informes u otro epic posterior.

### Preguntas para decisión humana (no bloqueantes MVP)

| ID | Pregunta | Propuesta default |
|----|----------|-------------------|
| AMB-Q-P08-01 | ¿Drill-down obligatorio en todas las consultas pivotables? | Según flag `admite_drilldown` por consulta (ya en arquitectura) |
| AMB-Q-P08-02 | ¿Límite de diseños guardados por consulta? | Sin límite en MVP (paridad layouts grilla) |

### Recomendaciones de ajuste del SPEC

- [x] Incorporar §6 decisiones humanas (paridad grillas).
- [x] Alinear `pivots.md` transversal con plantilla inicial y toolbar.
- [ ] Al generar HU futura: referenciar este SPEC + `especificacion_tecnica` §17.1 como CA.
- [ ] Unificar nomenclatura `PQ_PIVOTS` → `pq_pivots_config` en `modelo_datos_pivots_personalizados.md` (tarea documental opcional).

### Veredicto

**Apto con observaciones** para cierre **A1 documental**. No avanzar a **B (HU)** en MVP portal. Cuando se priorice el epic pivots, usar este SPEC como fuente única y resolver AMB-M-P08-* en la TR correspondiente.
