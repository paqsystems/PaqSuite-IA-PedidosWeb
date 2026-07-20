# Cierre C1 — SPEC-101-21 — Importación masiva

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-19 |
| **Método** | Skill `tr-ambiguity-review` + normas `_NORMAS-TRANSVERSALES-TR.md` |
| **TRs** | [21a](TR-SPEC-101-21-proceso-excel-pedido-masivo.md) · [21b](TR-SPEC-101-21-pantalla-importacion-masiva.md) · [21c](TR-SPEC-101-21-consultar-borrador-importacion-masiva.md) |
| **Parte C** | [F-101-21-cierre-c](F-101-21-cierre-c-importacion-masiva.md) |

---

## Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto** |
| **Puede pasar a D1/D** | **Sí** |
| **Bloqueantes** | Ninguno (tras decisiones C1 abajo) |

---

## Checklist C1 (épica)

| Eje | 21a | 21b | 21c | Notas |
|-----|-----|-----|-----|-------|
| Alcance ⊆ HU ⊆ SPEC | OK | OK | OK | Sin scope creep |
| API path + envelope | OK | OK | OK | GEN-07 + store existentes; `grupos[]` C1 |
| Seguridad / permisos | OK | OK | OK | OR store; Excel host `pw_importacionmasiva` |
| Datos / seed | OK | OK | OK | Catálogo Excel + menú; sin DROP |
| UI / testids / i18n | N/A | OK | OK | Prefijo `pedidos.importacionMasiva.*` |
| Tests ↔ AC | OK | OK | OK | §8 cada TR |
| Coherencia entre TRs | OK | OK | OK | Payload grabar único; sessionStorage 21c |

---

## Ambigüedades críticas

Ninguna pendiente.

| ID | Tema | Resolución C1 |
|----|------|----------------|
| C1-21a-01 | Contrato grupos al host | **`resultado.grupos[]`** (Opción A); FE no reagrupa |
| C1-21b-01 | Grabación | Mismas `POST /pedidos` y `POST /presupuestos` + **mismo JSON** que carga individual |
| C1-21b-02 | Permiso store (AMB-C-03) | Gate alta = `pw_cargapedidos` **OR** `pw_importacionmasiva` |
| C1-21c-01 | Borrador al Consultar | Snapshot lote en **sessionStorage**; rehidratar al Volver |

---

## Ambigüedades menores (no bloquean D1)

| ID | Tema | Guía |
|----|------|------|
| AMB-M-C1-01 | Enganche GEN-07 exacto para `grupos` | D1 elige endpoint/campo sin cambiar contrato |
| AMB-M-C1-02 | Helper OR solo en `store` vs método shared | Preferir OR acotado a store pedido/presupuesto |
| AMB-M-C1-03 | TTL / limpieza sessionStorage | Limpiar al Grabar 100% OK, Cancelar proceso o logout |

---

## Contradicciones TR ↔ HU ↔ SPEC

Ninguna detectada tras cierre C1. Coherente con:

- FE secuencial + progreso x/N  
- Sin endpoint de lote de grabación  
- Sin borrador servidor  
- Permiso menú propio  

---

## Supuestos

1. El body de alta individual (pedido/presupuesto) es reutilizable 1:1 desde el mapper del borrador masivo.
2. Ampliar solo `store` con OR no habilita edición de pedidos ajenos vía otras acciones.
3. `sessionStorage` por pestaña cumple SPEC CA-13 (no sobrevive logout/nueva sesión).

---

## Preguntas abiertas

Ninguna bloqueante para D1.

---

## Orden D1 sugerido

1. **TR-21a** — catálogo, handler, `grupos[]`  
2. **TR-21b** — menú, grilla, import, auth OR, grabación FE  
3. **TR-21c** — Consultar readonly + sessionStorage  

Plan formal: invocar `ai-planning-mode` (D1) sobre 21a → 21b → 21c.

---

## Veredicto

**C1 cerrado — Apto.** Autoriza **Parte D1**.
