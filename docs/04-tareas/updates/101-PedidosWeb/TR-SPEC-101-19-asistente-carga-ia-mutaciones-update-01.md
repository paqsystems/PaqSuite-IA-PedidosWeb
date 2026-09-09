# TR-SPEC-101-19-update-01 — Asistente IA: alias `codigo` + comillas en parseo

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-19-asistente-carga-ia-mutaciones](../../101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **SPEC relacionada** | [SPEC-101-19-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones-update-01.md) |
| **HU relacionada** | [HU-101-040-update-01](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar-update-01.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #14 · **08/09/2026** |
| **Última actualización** | 2026-09-08 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Ajustar `CargaAsistenteIntentDetector` (y tests unitarios) para:

1. **D1-27:** Incluir sinónimos `codigo` / `código` y abreviaturas `cod.` / `cod` / `cód.` / `cód` en la misma familia que artículo/producto/item (`ARTICULO_KEYWORD_REGEX`, `articuloKeywordList`, patrones de mutate/composite).
2. **D1-28:** Si hay texto entre comillas entre keyword de artículo (familia ampliada) y keyword de cantidad, usar ese contenido **literal** como `q` (sin tokenizar espacios); lookup sigue siendo código **o** descripción.

## 2) Criterios de aceptación

- **AC-CC14-T-D1:** `codigo` / `cod.` / `cód` disparan `addRenglon` con el mismo extract de qty/precio/bonif que `articulo`.
- **AC-CC14-T-D2:** `codigo "texto con espacios" cantidad 10` → `params.q === "texto con espacios"` y `cantidad === 10`.
- **AC-CC14-T-D3:** Comillas tipográficas `“…”` y simples `'…'` aceptadas.
- **AC-CC14-T-D4:** Regresión: casos existentes `art.` / `item` / `it` / comillas actuales en `CargaAsistenteIntentDetectorTest` siguen verdes.
- **AC-CC14-T-D5:** En mutate (eliminar/cambiar), los mismos alias `codigo*` resuelven el `q` sobre el **detalle** del borrador (paridad D1-24).

## 3) Implementación

| Área | Cambio |
|------|--------|
| `CargaAsistenteIntentDetector.php` | Extender `ARTICULO_KEYWORD_REGEX` y `articuloKeywordList()` con formas `codigo`/`código`/`cod.`/`cod`/`cód.`/`cód` (formas largas antes que `cod` para no partir mal). |
| Parseo `q` | Mantener/ reforzar preferencia de tramo entre comillas **después** de quitar qty/precio/bonif del working string; documentar contrato D1-28 en comentario breve. |
| Tests | Ampliar `CargaAsistenteIntentDetectorTest` con casos CC #14 (alias + comillas + espacios). |
| FE | Sin cambio de UI obligatorio; i18n no requiere claves nuevas. |

## 4) Tests

- Unit PHP: IntentDetector — alias `codigo`/`cod.`/`cód` + comillas con espacios + cantidad.
- Regresión suite IntentDetector existente.

## 5) Fuera de alcance

- Tools de consulta (stock/deuda/historial).
- Prompt LLM / visión imagen salvo que reutilicen el mismo detector de frase.
- SPEC-101-18 shell UI.
