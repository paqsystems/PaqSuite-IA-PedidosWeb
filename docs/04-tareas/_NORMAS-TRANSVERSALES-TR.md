# Normas transversales TR — PedidosWeb MVP

**Alcance:** toda TR bajo `docs/04-tareas/` (Generalidades `001-*` y slices `101-PedidosWeb`).

**Fuentes:** `SPEC-001-02`, `HU-GEN-02-politicas-endpoints`, `PedidosWeb_SPEC_MVP.md` §6.1, §3 (tenancy), **`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`** (contrato envelope MONO).

---

## 1. OpenAPI y autorización por endpoint (obligatorio)

Toda TR que **exponga o modifique** endpoints `api/v1/*` debe documentar en su sección **5) Contratos de API** la política de acceso **y** exigir su publicación en OpenAPI.

### 1.1 Principio

| Capa | Obligación |
|------|------------|
| **Código** | Middleware/policy alineado a la matriz endpoint ↔ permiso |
| **Matriz** | Fila en `matriz-permisos-mvp.md` o tabla viva del slice |
| **OpenAPI** | Misma política en `/api/documentation` (L5-Swagger) |

**No alcanza** implementar el control solo en código o solo en markdown interno.

### 1.2 Por cada operación documentada en la TR

| Elemento | Requisito |
|----------|-----------|
| **`security`** | En rutas protegidas: esquema Bearer (Sanctum u equivalente acordado) |
| **Header `X-Paq-Cliente`** | Documentado donde aplique tenancy MONO |
| **Respuesta `401`** | Sin token o token inválido |
| **Respuesta `403`** | Token válido sin permiso para la operación |
| **Descripción** | Permiso, rol o atributo requerido (`Permiso_Alta`, `Permiso_Modi`, `Permiso_Baja`, `Permiso_Repo`, `AccesoTotal`, etc.) |
| **Envelope JSON** | Cuerpo con `error`, `respuesta`, `resultado` según §2 y contexto MONO |

### 1.3 Rutas públicas (lista blanca)

Sin bloque `security` en OpenAPI:

- Login / logout (auth)
- Recuperación de contraseña
- Health check
- Otras explícitamente públicas en la TR del slice

### 1.4 Implementación técnica

- Raíz de anotaciones: `backend/OpenApi.php`
- Anotaciones en controllers/DTOs del slice
- Spec generado: **`GET /api/documentation`**
- Actualizar OpenAPI **en el mismo slice/PR** que el código del endpoint

### 1.5 Coherencia

Si la TR introduce `POST /api/v1/pedidos`, el checklist del slice debe incluir:

- [ ] Policy/middleware implementado
- [ ] Fila en matriz endpoint ↔ permiso
- [ ] Operación en OpenAPI con `security`, 401, 403 y permiso en descripción
- [ ] Test integración 401 y al menos un 403 cuando aplique

---

## 2. Envelope JSON (obligatorio)

**Fuente canónica:** [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md)  
**Regla Cursor:** `.cursor/rules/mono/03-api-contract.md`

Todas las respuestas API (éxito y error) usan:

```json
{
  "error": 0,
  "respuesta": "mensaje o clave i18n",
  "resultado": {}
}
```

| Campo | Regla |
|-------|--------|
| **`error`** | Entero. `0` = OK; `≠ 0` = error controlado. Rangos: 1000 validación, 2000 negocio, 3000 autorización, 4000 not found/conflicto, 9000 infraestructura. **No booleano.** |
| **`respuesta`** | String. Clave i18n (`auth.*`, `validation.*`, …) cuando la UI traduce; `"ok"` en éxito silencioso. |
| **`resultado`** | **Siempre objeto JSON.** Nunca `null` ni ausente. `{}` si no hay payload. Listados paginados: `{ items, page, page_size, total, total_pages }` dentro de `resultado`. |

El **status HTTP** (401, 403, 422, …) categoriza el fallo; el cuerpo **siempre** mantiene las tres propiedades.

Al documentar ejemplos en TR y OpenAPI, validar coherencia con el contexto MONO (sin `resultado: null`, sin `error: false`).

---

## 3. Tenancy MONO (obligatorio en TR con API)

- Header **`X-Paq-Cliente: {cliente}`** en requests autenticados (desarrollo: `desarrollo`).
- **Prohibido** documentar `X-Company-Id` ni selector de empresa en UI (`SPEC-001-05`).
- Desarrollo local: fila `EMPRESAS_CONEXION.CODIGO_TENANT = desarrollo` (SPEC MVP §3).

---

## 4. Tests mínimos por slice (SPEC MVP §12)

Toda TR de slice funcional debe planificar:

- Unit tests en services (umbral §12.2 del SPEC MVP)
- Integration tests API (incl. 401/403 en endpoints protegidos)
- **≥ 2 E2E** Playwright por slice cuando haya flujo UI

---

## 5. Checklist transversal (pegar al cierre de cada TR)

```md
### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado (`error` entero, `resultado` objeto, nunca null)
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR
```

---

## 6. Trazabilidad

| Documento | Rol |
|-----------|-----|
| `docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md` | Contrato envelope MONO (contexto compartido) |
| `HU-GEN-02-politicas-endpoints.md` | HU origen de la norma OpenAPI |
| `SPEC-001-02-acceso-y-seguridad.md` | SPEC que exige política por endpoint |
| `_PLANTILLA-TR-SLICE.md` | Plantilla con sección 5 preestructurada |
| `001-Generaliddes/matriz-permisos-mvp.md` | Matriz viva (crear al implementar seed) |
