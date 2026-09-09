# SPEC-101-19-update-01 — Asistente IA: alias `codigo` + búsqueda entre comillas

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-19-asistente-carga-ia-mutaciones-update-01 |
| **SPEC base** | [SPEC-101-19-asistente-carga-ia-mutaciones](../../101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #14 · **08/09/2026** |
| **HU relacionadas** | [HU-101-040-update-01](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar-update-01.md) |
| **TR relacionadas** | [TR-SPEC-101-19-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update-01.md) |
| **Última actualización** | 2026-09-08 |

## Objetivo

Optimizar el parseo de frases de alta/mutación de renglón en el asistente de carga (capacidad **D**): ampliar sinónimos de artículo con **`codigo`** (y abreviaturas) y tratar el texto **entre comillas** entre el sinónimo de artículo y el de cantidad como criterio de búsqueda **literal** sobre código o descripción (permite espacios).

## Decisiones (CC PQ #14)

| ID | Tema | Decisión |
|----|------|----------|
| D1-27 | Alias `codigo` | Prefijos de renglón equivalentes a artículo/producto/item: `codigo(s)` / `código(s)`, `cod.` / `cod`, `cód.` / `cód`. Misma semántica que D1-26 (disparan `addRenglon` / parse de renglón). |
| D1-28 | Comillas entre artículo y cantidad | Si entre un sinónimo de artículo (D1-26 + D1-27) y un sinónimo de cantidad (`cantidad` / `canti` / `cant` / `cant.`) aparece un tramo entre comillas dobles o tipográficas (`"…"`, `“…”`) o simples (`'…'`), el contenido **tal cual** (sin tokenizar por espacios) es el `q` de lookup sobre **código o descripción** del maestro. Qty/precio/bonif se extraen fuera de ese tramo (D1-19). |

## In scope

- Capacidad **D** (alta y mutación de renglones vía texto/voz → IntentDetector / tools).
- Pedido compuesto multilínea (D1-25): mismas reglas por línea.
- Extender CA-D01 / prefijos documentados en D1-26 con D1-27 y D1-28.

## Fuera de scope

- Consultas stock/historial (SPEC-101-20): sin cambio de alias de consulta.
- Extracto imagen (K): no redefine OCR; si el texto ya trae comillas, aplica el mismo parser de frase.
- UI del panel (SPEC-101-18).

## Criterios de aceptación medibles

- [ ] **CA-CC14-D01:** Frase con prefijo `codigo` / `cod.` / `cód` dispara alta de renglón igual que `artículo` / `item`.
- [ ] **CA-CC14-D02:** `codigo "almendra carmel 20/22" cantidad 10` → `q` = `almendra carmel 20/22` (espacios conservados); cantidad = 10; lookup sobre código o descripción.
- [ ] **CA-CC14-D03:** Sin comillas, el parseo vigente (tokens / D1-22) no se rompe.

## Definición de listo

- [ ] D1-27 y D1-28 reflejados en HU-101-040-update-01 y TR-SPEC-101-19-update-01
- [ ] Tests de IntentDetector cubren alias `codigo*` y comillas entre keyword y cantidad
