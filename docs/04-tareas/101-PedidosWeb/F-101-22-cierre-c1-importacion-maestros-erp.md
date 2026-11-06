# Cierre C1 — SPEC-101-22 — Revisión ambigüedad TR (importación maestros ERP)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-10-21 |
| **Épica** | APIs importación maestros (PIAPEDWEB-34) |
| **SPEC** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Cierre C** | [F-101-22-cierre-c](F-101-22-cierre-c-importacion-maestros-erp.md) |
| **Método** | Skill `tr-ambiguity-review` · normas [\_NORMAS-TRANSVERSALES-TR.md](../_NORMAS-TRANSVERSALES-TR.md) |

---

## Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a D1/D** | **Sí** (orden **22a → 22b → 22c**) |
| **Bloqueantes documentales** | Ninguno |

## TR revisadas

| TR | Archivo | HU | Veredicto slice |
|----|---------|-----|-----------------|
| 22a | [TR-SPEC-101-22-integracion-maestros-plataforma](TR-SPEC-101-22-integracion-maestros-plataforma.md) | 046 | Apto |
| 22b | [TR-SPEC-101-22-integracion-maestros-catalogo-articulos](TR-SPEC-101-22-integracion-maestros-catalogo-articulos.md) | 047 | Apto |
| 22c | [TR-SPEC-101-22-integracion-maestros-clientes-parametros](TR-SPEC-101-22-integracion-maestros-clientes-parametros.md) | 048 | Apto |

---

# Revisión de ambigüedad — TR SPEC-101-22 (conjunto)

## Checklist C1 (ejes regla 16)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance TR ⊆ HU ⊆ SPEC | OK | 22a plataforma; 22b 10 recursos; 22c 4 recursos = 14 total SPEC §1 |
| 2 | API paths / envelope | OK | GEN-20; POST `/api/v1/integracion/maestros/{recurso}`; matriz permiso en 22a §5.6 |
| 3 | Seguridad / tenant | Obs. | 22a: tenant inválido **400 vs 404** delegado a middleware existente — fijar en D1 alineado a `ValidatePaqTenant` |
| 4 | Datos / seed / SP | Obs. | Firmas SP y parámetros explícitos **en D1** (scripts); contrato lógico y nombres `pq_sp_integracion_maestros_*` coherentes |
| 5 | UI / flujo | OK | Sin UI; logs vía `LogIntegracionService` existente |
| 6 | Tests ↔ AC | OK | CA HU mapeados en §2 de cada TR; `@group integracion` + skip CI sin SQL Server documentado en C |
| 7 | Coherencia interna | Obs. | Ver menores abajo (`modoNoSoportado`, tabla AC 22c) |
| 8 | NORMAS transversales | OK | OpenAPI, 401/403, envelope, matriz permiso citados en 22a |
| 9 | Dependencias orden D1 | OK | 22a → 22b → 22c explícito; FK clientes vs catálogo |
| 10 | Contradicciones SPEC | OK | Políticas A1-C01…C05 reflejadas en 22b §3.1 y 22c §3 |

---

## Ambigüedades críticas

Ninguna bloqueante para D1.

---

## Ambigüedades menores (resolver en D1, no bloquean)

| ID | Tema | TR | Resolución recomendada D1 |
|----|------|-----|---------------------------|
| C1-M-101-22-01 | Clave i18n `modo=snapshot` en recurso sin snapshot | 22a vs SPEC AMB-C-101-22-04 | Orchestrator o handler: **422** + `integracion.maestro.modoNoSoportado` (distinto de `modoInvalido` para enum inválido). Añadido en 22a §5.4 |
| C1-M-101-22-02 | Finalize snapshot catálogo chico | 22b §3 | Elegir **un** patrón por recurso: SP `*_finalize_snapshot` post-loop **o** lógica única en `*_apply`; documentar en script SQL |
| C1-M-101-22-03 | Mapeo JSON ↔ columnas completo | 22b §3.2 | Anexo en D1 (tabla por recurso) + ejemplos OpenAPI; modelo §3 es SoT |
| C1-M-101-22-04 | Whitelist parámetros | 22c §3.4 | **Array PHP versionado** en repo generado desde seed (script one-shot en TR ops); no depender de leer JSON en runtime prod |
| C1-M-101-22-05 | Anti-injection RN-06 (22a) | 22a | Regex defensiva puede false-positive en descripciones; aplicar solo a strings sospechosos o documentar excepción en D1 |
| C1-M-101-22-06 | HTTP tenant inválido | 22a §5.4 | Unificar con comportamiento actual `paq.tenant` (documentar código exacto en runbook) |
| C1-M-101-22-07 | Tabla AC §2 TR 22c | 22c | Corregida redacción CA-01…CA-04 vs CA-02 leyendas (sin duplicar fila ambigua) |

---

## Contradicciones TR ↔ HU ↔ SPEC

| Tema | Hallazgo | Estado |
|------|----------|--------|
| Recursos solo delta | SPEC/HU-047/048 listan 4 recursos; 22b/22c `supportsSnapshot=false` | **Coherente** |
| `parametros` snapshot | SPEC no exige snapshot; 22c solo delta/upsert | **Coherente** |
| 10 vs 14 recursos | 22b incluye `vendedores` (SPEC §1); 22c asume vendedores cargados | **Coherente** (orden ERP §7) |
| OpenAPI enum 14 vs paths literales | 22a enum + 22b/22c paths literales | **Coherente** (preferencia Swagger 22b) |

---

## Supuestos detectados

- Tests feature con SP reales requieren SQL Server + scripts desplegados; CI puede skip `@group integracion`.
- Stub handler en 22a permite CA plataforma antes de SP 22b.
- Usuario técnico: comando ops documentado, no seed automático en deploy (AMB-A-101-22-04).
- Gateway agente invoca mismos SP; fuera alcance D1 PedidosWeb salvo prueba local gateway.

---

## Preguntas para decisión humana

Ninguna pendiente bloqueante.

---

## Recomendaciones antes / durante D1

1. Invocar **`ai-planning-mode`** por TR en orden 22a → 22b → 22c.
2. En D1 de **22a**: registrar `modoNoSoportado` en lang + tests con handler `supportsSnapshot=false`.
3. En D1 de **22b**: primer smoke `provincias` + `condventa` snapshot; luego escalas anidadas.
4. En D1 de **22c**: generar whitelist desde seed y test `ParametrosWhitelist`.
5. PR: sección deploy **solo scripts SP** (sin DROP masivo en BD compartida).

---

## Siguiente paso SDD

**D1** — plan de implementación ([`ai-planning-mode`](../../.cursor/skills/ai-planning-mode/SKILL.md)) empezando por **TR-22a**.
