# TR-SPEC-XXX-xx — [Título del slice]

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-XXX — enlace](docs/03-historias-usuario/...) |
| **SPEC relacionada** | [SPEC-XXX — enlace](docs/05-open-spec/...) |
| **Épica** | [001-Generaliddes / 101-PedidosWeb] |
| **Prioridad** | Must / Should |
| **Dependencias** | TR/HU previas |
| **Estado** | Pendiente |
| **Última actualización** | YYYY-MM-DD |

**Origen:** [HU](...)  
**Referencia SPEC:** [SPEC](...)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
[Copiar o resumir desde HU]

### Narrativa
Como … quiero … para …

### In scope / Out of scope
- …

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: …
- **AC-02**: …

### Escenarios Gherkin

```gherkin
Feature: [Nombre]

  Scenario: …
    Given …
    When …
    Then …
```

---

## 3) Reglas de Negocio

1. **RN-01**: …
2. **RN-02**: …

---

## 4) Impacto en Datos

### Tablas afectadas
- …

### Seed mínimo para tests
- …

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](_NORMAS-TRANSVERSALES-TR.md) §1–§2. Código, matriz y OpenAPI deben coincidir. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/...` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` | No |
| POST | `/api/v1/...` | Bearer + `X-Paq-Cliente` | `Permiso_Alta` | No |

### 5.2 Detalle por operación

#### [Método] `[path]`

**Autorización:** [permiso, rol o AccesoTotal]

**Request:** (schema JSON o referencia DTO)

**Response 200:** envelope `error` / `respuesta` / `resultado` (`error`: entero `0`; `resultado`: objeto, nunca `null`)

**Response 401:** no autenticado

**Response 403:** sin permiso

**Response 4xx/5xx:** según validaciones del slice

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controller/DTO
- [ ] `security` declarado (si no es ruta pública)
- [ ] Header `X-Paq-Cliente` documentado
- [ ] Respuestas 401 y 403 en spec generado
- [ ] Permiso requerido en `description` u extensión acordada
- [ ] Verificado en `/api/documentation`

### 5.3 Actualización matriz permisos

- [ ] Fila agregada en `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md` (o anexo del slice)

---

## 6) Cambios Frontend

### Pantallas / componentes
- …

### data-testid sugeridos
- …

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Endpoint + policy | OpenAPI + tests 401/403 |
| T2 | Frontend | … | AC cumplidos |
| T3 | Tests | Integration + E2E | §12 SPEC MVP |
| T4 | Docs | OpenAPI + matriz permisos | Checklist §5 transversal |

---

## 8) Estrategia de Tests

- **Unit:** …
- **Integration:** 200, 401, 403 por endpoint protegido
- **E2E:** ≥ 2 escenarios (SPEC MVP §12)

---

## 9) Riesgos y Edge Cases

- …

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan

### Checklist normas transversales

(Copiar desde [`_NORMAS-TRANSVERSALES-TR.md`](_NORMAS-TRANSVERSALES-TR.md) §5)

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- …

### Frontend
- …

### OpenAPI
- `backend/OpenApi.php` (si aplica esquema global)
- Controllers anotados del slice

### Docs
- Matriz permisos actualizada
