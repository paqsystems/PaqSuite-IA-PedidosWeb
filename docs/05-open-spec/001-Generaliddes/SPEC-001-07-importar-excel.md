# SPEC-001-07 - Importar Excel

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | Ninguna — **fuera de alcance MVP** (`PedidosWeb_SPEC_MVP.md`) |
| **Estado** | Documental |
| **Revisión A1** | Apto con observaciones (2026-06-09) — decisiones §6.1 parcial cerradas |

## Objetivo

Dejar especificado el marco funcional/técnico de importación por Excel como capacidad prevista (fuera de implementación MVP inicial).

## Estado de ejecución

Documental para este bloque inicial.

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

- Desarrollo completo de importación Excel en este bloque inicial.

## Entregables verificables

- Resumen funcional/técnico para abrir SPEC dedicado de importación Excel.
- Inventario de tablas y entidades involucradas.

## Criterios de aceptación medibles

- Flujo de importación identificado de extremo a extremo en forma documental.
- Datos requeridos y restricciones técnicas listados explícitamente.
