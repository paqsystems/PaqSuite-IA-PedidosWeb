# Cierre F — CC PQ #14 (08/09/2026) — Asistente IA: alias `codigo` + comillas

## Alcance

Verificación **F1 + F** (agent-verification-guide / openspec-05) sobre optimizaciones de parseo del asistente de carga:

| # | Tema | Updates |
|---|------|---------|
| 1 | Alias `codigo`/`código`/`cod.`/`cod`/`cód.`/`cód` (D1-27) | SPEC/HU/TR-101-19-update-01 · HU-040-update-01 |
| 2 | Texto entre comillas = `q` literal con espacios (D1-28) | Idem |
| 3 | Mutate (eliminar/cambiar) con mismos alias | Idem |

**Fecha verificación F1/F:** 08/09/2026  
**Parte E:** [E-CC-PQ-14-tests.md](E-CC-PQ-14-tests.md)  
**Rama:** `fix/carga-unidades-venta-renglones`  
**TR:** [TR-SPEC-101-19-update-01](../updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update-01.md)

---

## F1 — Verificación agente (8 ejes)

**Resultado F1:** **Aprobado**

### 1. Alcance

| Pedido CC #14 | Implementado | Fuera de alcance respetado |
|---------------|--------------|----------------------------|
| Sinónimo `codigo` + abreviaturas | `ARTICULO_KEYWORD_REGEX` + `articuloKeywordList` | Consultas SPEC-101-20 no tocadas |
| Comillas entre artículo y cantidad → búsqueda literal | Preferencia de tramo entre comillas en `extractArticuloParams` (D1-28) | Sin cambio UI panel |
| Mutate con alias | Patrones eliminar/cambiar + lista keywords | Sin prompt LLM / visión |

### 2. Código

| Pieza | Evidencia |
|-------|-----------|
| Detector | `backend/app/Services/PedidosWeb/CargaAsistente/CargaAsistenteIntentDetector.php` |
| Regex | `codigos?\|códigos?` antes de `cod\.?\|cód\.?` |
| Lista keywords | `codigo`/`código`/`cod.`/`cod`/`cód.`/`cód` |
| Mutate | Prefijos `eliminar codigo`, `cambiar codigo`, etc. |
| Comentario D1-28 | Sobre extracción de comillas en `extractArticuloParams` |

Sin FE ni migraciones.

### 3. Datos

N/A — sin DDL, seed ni SP.

### 4. Backend

| RN | Evidencia | Estado |
|----|-----------|--------|
| D1-27 alias dispara `addRenglon` | Unit + regex compartida en composite/sanitize | OK |
| D1-28 `q` literal con espacios | Unit comillas dobles/tipográficas/simples | OK |
| Mutate detalle | Unit `eliminar codigo` / `cambiar … codigo "…"` | OK |
| Permisos / envelope | Sin cambio | N/A |

### 5. Frontend

Sin cambio (TR: FE no obligatorio).

### 6. Tests

Ver [E-CC-PQ-14-tests.md](E-CC-PQ-14-tests.md): **19 tests / 156 assertions OK** (re-ejecutados en F 08/09/2026).

### 7. Documentación

| Doc | Alineado |
|-----|----------|
| SPEC-101-19-update-01 (D1-27, D1-28) | Sí |
| HU-101-040-update-01 | Sí |
| TR-SPEC-101-19-update-01 | Sí — estado Implementado (D) — Pendiente de Revisión |
| CC #14 *Procesado* + Especificado | Sí |
| OpenAPI | N/A (sin contrato HTTP nuevo) |

### 8. Trazabilidad

Updates G en `docs/.../updates/101-PedidosWeb/` (`*-update-01`). CC #14 ciclo G+D+E+F.

---

## F — Smoke / checklist QA

No aplica smoke HTTP dedicado: el cambio es parseo local del IntentDetector (cubierto por unit).

| # | Escenario | Resultado |
|---|-----------|-----------|
| 1 | `codigo ABC-01 cantidad 3` → addRenglon | **OK** (PHPUnit) |
| 2 | `codigo "texto con espacios" cantidad 10` → q literal | **OK** (PHPUnit) |
| 3 | Comillas tipográficas / simples | **OK** (PHPUnit) |
| 4 | Mutate con `codigo` + comillas | **OK** (PHPUnit) |
| 5 | Regresión `art.` / `item` / `it` | **OK** (PHPUnit) |
| 6 | Turno LLM E2E en UI | **No ejecutado** (opcional PQ) |

---

## Observaciones no bloqueantes

| ID | Tema | Destino |
|----|------|---------|
| OBS-F-01 | Smoke manual en panel asistente (frases con `codigo` + comillas) | Checklist PQ opcional post-F |
| OBS-F-02 | Updates CC #13 (`*-update` sin `-01`) siguen abiertos en paralelo | Parte I cuando PQ marque Finalizado |

---

## Veredicto final

| Control | F1 (agente) | F (tests) | F (manual PQ extra) |
|---------|-------------|-----------|---------------------|
| CC #14 (08/09/2026) | **Aprobado** | **Aprobado** | Opcional OBS-F-01 |

**Estado CC #14:** **G+D+E+F 08/09/2026.** Unificación de updates en originales: **Parte I** (tras `Estado: Finalizado` manual en updates).

**Recomendación:** Parte I cuando PQ autorice. No hay migrate/seed en deploy.
