# TR-GEN-01-idioma — Selector de idioma e i18n base

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-01-idioma](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-idioma.md) |
| **SPEC relacionada** | [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md) |
| **Épica** | 001-Generaliddes |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout (header/login); TR-GEN-02-login-sesion (estado autenticado); coordina con TR-GEN-01-menu-avatar |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-28 (resincronizada con HU) |

**Origen:** [HU-GEN-01-idioma](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-idioma.md)  
**Referencia SPEC:** [SPEC-001-01-experiencia-base](../../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md)  
**Normas transversales:** [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Selector de idioma en login y header post-login.

### Narrativa
Como usuario quiero elegir idioma de interfaz en login y header para leer labels/mensajes en mi preferencia y conservarla entre sesiones.

### In scope / Out of scope
- **In scope:** selector en login/header, resolución de idioma inicial (`users.locale` -> `navigator.language` -> `es`), persistencia por usuario autenticado.
- **Out of scope:** traducción de datos de negocio, internacionalización avanzada fuera de shell base.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Selector visible en login y en header del shell.
- **AC-02**: Cambio de idioma actualiza textos de shell/login sin recarga completa.
- **AC-03**: Usuario autenticado persiste idioma en `users.locale`.
- **AC-04**: Si `users.locale` está vacío, se usa `navigator.language`; si no soportado, fallback `es`.
- **AC-05**: Error al guardar preferencia no rompe UI y mantiene idioma actual en cliente.
- **AC-06**: E2E valida cambio de idioma y persistencia.

### Escenarios Gherkin

```gherkin
Feature: Selector de idioma

  Scenario: Idioma por defecto es español
    Given un usuario nuevo sin users.locale
    And navigator.language no está soportado
    When accede al portal
    Then la interfaz se muestra en español (es)

  Scenario: Persistir idioma tras login
    Given un usuario autenticado
    When cambia el idioma desde el header
    Then users.locale se actualiza
    And en el próximo login ve el mismo idioma

  Scenario: Selector en login
    Given un visitante en pantalla de login
    Then ve selector de idioma
    And puede cambiar idioma antes de autenticarse

  Scenario: Interfaz en italiano
    Given el visitante selecciona italiano (it) en login
    When visualiza la pantalla de login y una grilla de prueba
    Then los labels de login y al menos un caption de grilla están en italiano
    And los textos de filtro DevExtreme del grid están en italiano
```

---

## 3) Reglas de Negocio

1. **RN-01**: Idioma por defecto del producto es `es`.
2. **RN-02**: Precedencia de resolución: `users.locale` > `navigator.language` soportado > `es`.
3. **RN-03**: Selector de idioma post-login vive en header (no en menú avatar).
4. **RN-04**: Preferencia de idioma es por usuario, no por tenant.
5. **RN-05**: Catálogo MVP cerrado: `es`, `en`, `pt`, `fr`, `it` (archivos de recursos completos por idioma antes de habilitar en selector).

---

## 4) Impacto en Datos

### Tablas afectadas
- `users.locale` (lectura y escritura de preferencia de idioma).

### Catálogo de idiomas MVP

| Código | Uso en tests / producto |
|--------|------------------------|
| `es` | Idioma por defecto (SPEC §8.1) |
| `en` | Segundo idioma obligatorio en suite |
| `pt` | Catálogo cerrado MVP |
| `fr` | Catálogo cerrado MVP |
| `it` | Catálogo cerrado MVP — **incluir en seed y E2E de cambio de idioma** |

Un idioma solo se habilita en el selector cuando su archivo de recursos (`frontend/src/locales/{locale}.json` o convención del proyecto) está **completo** para los ítems del checklist de cobertura (sección siguiente).

### Seed mínimo para tests

**Usuarios (`users.locale`):**

| Caso | `locale` | Objetivo del test |
|------|----------|-------------------|
| Sin preferencia | `null` | Fallback `navigator.language` → `es` |
| Español | `es` | Idioma por defecto |
| Inglés | `en` | Persistencia y cambio en header |
| Italiano | `it` | Validar catálogo completo y E2E con textos en italiano |
| Portugués | `pt` | Smoke de catálogo (opcional en CI si recursos listos) |
| Francés | `fr` | Smoke de catálogo (opcional en CI si recursos listos) |
| Inválido en BD | `xx` | Fallback a `es` sin error fatal |

**Recursos i18n (frontend):**

- Archivos base por idioma: mínimo `es.json`, `en.json`, `it.json` con **mismas claves** (paridad de claves validada en test unitario).
- Claves de prueba representativas para italiano: login, un ítem de menú, caption de grilla, mensaje de carga, tooltip de filtro (ver checklist).
- **Claves de autenticación** (coord. [TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md) D-01) — obligatorias en todos los locales MVP:

| Clave | Uso | Tono |
|-------|-----|------|
| `auth.invalidCredentials` | Login 401 — credenciales incorrectas | **Genérico** (no revelar si existe la cuenta) |
| `auth.noPermission` | Login 403 — sin `Pq_Permiso` válido | Explícito |
| `auth.noCommercialProfile` | Login 403 — sin `cod_login` comercial | Explícito (contacte administración) |
| `tenant.invalid` | Request sin `X-Paq-Cliente` válido (400) | Explícito |

**Convención envelope:** el backend devuelve la **clave** en `respuesta`; la UI traduce con i18n. Ver [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md) §4.

### Cobertura mínima de textos traducibles (checklist)

Lista de **ámbitos** que deben usar claves i18n (no texto fijo en código) en el MVP. Marcar por idioma al cerrar traducciones; usar como guía de QA y de revisión de PRs que toquen UI.

| # | Ámbito | Ejemplos / notas | ¿Obligatorio MVP? |
|---|--------|------------------|-------------------|
| 1 | **Labels y títulos de pantalla** | Títulos de páginas, subtítulos, textos de formulario estáticos | Sí |
| 2 | **Botones y acciones** | Guardar, Cancelar, Buscar, Aplicar filtros, Exportar | Sí |
| 3 | **Controles DevExtreme genéricos** | Textos de botones integrados, placeholders por defecto del tema | Sí |
| 4 | **Tooltips** | Iconos de ayuda, acciones de toolbar, botones solo icono | Sí |
| 5 | **Mensajes del sistema** | Toasts, confirmaciones, alertas de error/éxito, validaciones de formulario | Sí |
| 6 | **Estados de carga** | “Cargando…”, “Procesando…”, overlay al cargar datos o guardar | Sí |
| 7 | **Estado vacío / sin datos** | “No hay registros”, mensajes de grilla vacía | Sí |
| 8 | **Captions de columnas (DataGrid / TreeList)** | Cabeceras de grillas de consultas y ABM | Sí |
| 9 | **Opciones de filtro en grilla DevExtreme** | Textos del filtro de encabezado, operadores (contiene, empieza con, etc.) vía `dx.messages` / locale DevExtreme | Sí |
| 10 | **Paginación y selector de página** | “Página”, “de”, tamaños de página si se personalizan | Sí |
| 11 | **Menú lateral y menú avatar** | Textos de ítems de `pq_menus` si son claves; etiquetas fijas del shell (Perfil, Cerrar sesión, Apariencia) | Sí |
| 12 | **Pantalla de login y recuperación** | Labels de usuario/contraseña, enlaces; `auth.invalidCredentials` (401 genérico), `auth.noPermission` y `auth.noCommercialProfile` (403 explícitos) | Sí |
| 13 | **Mail “Olvidé mi contraseña”** | Asunto y cuerpo según locale activo al solicitar | Sí — implementación: [TR-GEN-02-recuperacion-contrasena](TR-GEN-02-recuperacion-contrasena.md) |
| 14 | **Wizard / UI de pivot** | Pasos, botones y mensajes del asistente pivot (referencia documental SPEC-001-08; si hay pivot en pantalla, mismas reglas) | Sí si hay pivot visible |
| 15 | **Carga de parámetros** | Captions y tooltips en pantallas que lean parámetros globales (SPEC-001-04) | Sí cuando exista UI |
| 16 | **Footer y versión** | Leyendas institucionales configurables vía i18n si no vienen de backend | Sí |
| 17 | **Selector de idioma** | Nombres de idiomas en autodenominación (Español, English, Italiano, …) | Sí |
| 18 | **Fechas y números** | Formato vía `Intl` / locale activo (no traducción literal, pero coherencia regional) | Sí |
| 19 | **Mensajes HTTP / envelope** | Claves i18n en `respuesta` del envelope; UI traduce (`t(respuesta)`). Ver contexto MONO `envelope-respuestas.md` §4 | Sí |
| 20 | **Datos de negocio** | Nombres de clientes, artículos, descripciones ERP | **No** (fuera de alcance HU) |

**Criterio de cierre por idioma (ej. italiano):**

- [ ] Paridad de claves respecto a `es.json`
- [ ] E2E: login con selector en `it` → al menos un label de login en italiano
- [ ] E2E o smoke: grilla con caption y mensaje de carga en italiano
- [ ] DevExtreme: locale cargado (`it`) para textos de filtro/paginación del grid
- [ ] Mail de recuperación probado con locale `it` (mock o entorno test) — ver [TR-GEN-02-recuperacion-contrasena](TR-GEN-02-recuperacion-contrasena.md)

**Implementación DevExtreme (recordatorio técnico):**

- Cargar mensajes localizados del paquete DevExtreme para cada código del catálogo (`es`, `en`, `pt`, `fr`, `it`) además de los JSON propios de la app.
- Al cambiar `locale` de la app, sincronizar `locale()` de DevExtreme en el mismo ciclo (sin recarga completa de página).

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** cumplir [`_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1. Código, matriz y OpenAPI deben coincidir.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/users/me/preferences` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |
| PATCH | `/api/v1/users/me/preferences/locale` | Bearer + `X-Paq-Cliente` | Usuario autenticado | No |

### 5.2 Detalle por operación

#### PATCH `/api/v1/users/me/preferences/locale`

**Autorización:** usuario autenticado.

**Request:**

```json
{
  "locale": "es"
}
```

**Response 200:** envelope con locale persistido.

**Response 401:** no autenticado.

**Response 403:** sin permiso de auto-gestión (si se define policy explícita).

**Response 422:** locale fuera del catálogo permitido.

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controller/DTO de preferencias.
- [ ] `security` declarado.
- [ ] Header `X-Paq-Cliente` documentado.
- [ ] Respuestas 401/403/422 documentadas.
- [ ] Permisos/restricciones de locale en descripción.
- [ ] Verificado en `/api/documentation`.

### 5.3 Actualización matriz permisos

- [ ] Agregar/confirmar fila para `PATCH /api/v1/users/me/preferences/locale`.

---

## 6) Cambios Frontend

### Pantallas / componentes
- `frontend/src/features/i18n/model/supportedLocales.ts` (nuevo): catálogo cerrado.
- `frontend/src/features/i18n/api/updateLocalePreference.ts` (nuevo): persistencia server-side.
- `frontend/src/features/i18n/hooks/useCurrentLocale.ts` (nuevo): resolución de idioma inicial.
- `frontend/src/features/i18n/components/LocaleSelector.tsx` (nuevo): selector reutilizable.
- `frontend/src/app/layout/ShellHeader.tsx`: ubicar selector post-login.
- `frontend/src/app/auth/LoginPage.tsx` (nuevo o ajuste): selector visible pre-login.

### data-testid sugeridos
- `localeSelectorLogin`
- `localeSelectorHeader`
- `localeOption-{localeCode}`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Crear endpoint `PATCH /api/v1/users/me/preferences/locale` con validación de catálogo | OpenAPI + 401/403/422 |
| T2 | Frontend | Implementar resolución inicial de idioma y fallback (`users.locale`, `navigator.language`, `es`) | Reglas aplicadas en arranque |
| T3 | Frontend | Implementar selector reutilizable en login y header | Cambio de idioma en vivo |
| T4 | Frontend | Persistir locale para usuario autenticado y fallback cliente en no autenticado | Preferencia consistente |
| T5 | Tests | Unit i18n (paridad claves `es`/`en`/`it`) + Integration API + E2E cambio/persistencia + smoke italiano | Suite en verde |
| T6 | Docs | Actualizar matriz endpoint ↔ permiso para locale | Trazabilidad completa |
| T7 | Frontend | Integrar `dx.messages` / locale DevExtreme para `es`, `en`, `pt`, `fr`, `it` | Filtros y paginación de grillas traducidos |
| T8 | i18n | Completar checklist de cobertura por idioma (prioridad `it` para validación E2E) | Checklist §4 marcado |

---

## 8) Estrategia de Tests

- **Unit:** algoritmo de resolución de idioma y catálogo soportado.
- **Integration:** `PATCH /api/v1/users/me/preferences/locale` con 200/401/403/422.
- **E2E:**  
  - cambiar idioma en header y verificar textos;  
  - iniciar sesión nuevamente y validar persistencia;  
  - **italiano (`it`):** selector en login → textos clave (login + caption grilla o menú) en italiano;  
  - validar que filtro de grilla DevExtreme muestra operadores en idioma activo.

---

## 9) Riesgos y Edge Cases

- Claves i18n faltantes pueden dejar textos sin traducir.
- Catálogo de idiomas no consensuado puede generar cambios de contrato tardíos.
- Diferencias entre `navigator.language` (ej. `es-AR`) y código interno (`es`) requieren normalización.
- Guardado fallido de locale puede dejar estado divergente entre cliente y backend.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC cumplidos
- [ ] Backend + frontend + tests según plan
- [ ] Resolución de fallback sin ambigüedad validada
- [ ] Selector en login y header operativo
- [ ] Catálogo `it` con paridad de claves y smoke/E2E en italiano
- [ ] Checklist de cobertura §4 revisado en PRs de UI (muestreo por ámbito)

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401/403 documentados por operación protegida
- [ ] Envelope JSON respetado
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

