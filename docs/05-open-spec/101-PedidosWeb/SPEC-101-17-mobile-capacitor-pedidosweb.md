# SPEC-101-17 — Mobile Capacitor PedidosWeb

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **SPEC transversal** | [SPEC-001-11-mobile-capacitor.md](../001-Generaliddes/SPEC-001-11-mobile-capacitor.md) |
| **Estado** | **A1 + B1 + C1 cerrados** (2026-06-30) |
| **Revisión A1** | Apto con observaciones |
| **Parte B** | **Cerrada** — HU-101-031…036 (+ transversal HU-GEN-11) |
| **Parte C v1** | **Cerrada** — 3 TR v1 — [F-101-17-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-17-cierre-c1.md) |
| **Prioridad épica** | **Must** (release `v1.2.0-mobile` fase v1) |
| **Release** | Tag **`v1.2.0-mobile`** (fase v1); v2/v3 en tags posteriores |

## Objetivo

Entregar la app mobile PedidosWeb (Capacitor) en **tres fases incrementales**: v1 con una consulta kardex; v2 con todos los listados/consultas; v3 con carga de pedidos/presupuestos.

## Decisiones humanas (2026-06-30)

| Tema | Decisión |
|------|----------|
| Plataformas | Android + iOS desde v1 |
| Login | Tenant + usuario + contraseña — [`04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md) |
| Consulta MVP v1 | **Consulta de stock** (`/consultas/stock`) en **vista kardex** |
| Post-login v1 | Redirigir a **`/consultas/stock`** (no dashboard) |
| Tenant dev/smoke | **`desarrollo`** (alineado backend/tests); ejemplos prod: `demo`, `ankasdelsur`, `quento` |
| v2 | Todos listados + consultas MVP web (kardex; sin pivot) |
| v3 | Pantalla carga pedidos/presupuestos (UX mobile dedicada) |
| Exclusiones | Sin pivot, Excel import, admin seguridad, pestañas separadas |

## Dependencias

| SPEC / artefacto | Notas |
|------------------|-------|
| SPEC-001-11 | Plataforma Capacitor + login tenant |
| SPEC-001-02 | Auth |
| SPEC-101-07 | API consulta stock |
| SPEC-101-09 | Rutas / cliente HTTP base |
| SPEC-101-11 | Referencia web consultas ( **no** reutilizar DataGrid en mobile) |

## Roadmap funcional

### Mobile v1 — `v1.2.0-mobile` (Must)

**In scope:**

- Capacitor Android + iOS operativos (debug).
- Login PedidosWeb: tenant (`demo`, `ankasdelsur`, `quento`, …) + usuario + contraseña.
- Config avanzada: override URL API (opcional dev/staging).
- Shell mobile mínimo post-login (drawer; menú filtrado).
- **Una consulta kardex: Stock** — misma API que web; UI tarjetas verticales.
- Filtros básicos stock (según API 101-07); pull-to-refresh o botón Actualizar.
- i18n ES mínimo + testids kardex.
- Smoke: login → stock kardex en dispositivo Android **y** simulador/dispositivo iOS.

**Fuera de scope v1:**

- Resto de consultas/listados.
- Carga pedidos.
- Dashboard completo, chat, Excel, pivot, admin.
- Play Store / TestFlight producción (opcional TR aparte).

### Mobile v2 — `v1.2.1-mobile` (Should → Must según plan release)

**In scope:**

- Todas las consultas MVP en **kardex**: deuda, cheques, historial, detalle pedidos, parámetros (consulta).
- Listados: pedidos ingresados, pendientes, presupuestos ingresados/tratativas (kardex; acciones reducidas vs web).
- Export: evaluar en D1 (Excel en mobile puede quedar fuera — decisión TR v2).

**Fuera de scope v2:**

- Carga/edición comprobantes.
- Pivot, Excel import, admin.

### Mobile v3 — `v1.2.2-mobile`

**In scope:**

- Carga pedidos/presupuestos — UX mobile (wizard/cards; no paridad desktop SPEC-101-10).
- Integración menú y permisos existentes.

**Fuera de scope v3:**

- Importación Excel masiva.
- Paridad total con pantalla web de 1300+ líneas sin rediseño.

## Consulta kardex — patrón UI (todas las fases)

Aplica desde v1; reutilizar componente `ConsultaKardexList` (nombre orientativo):

| Elemento | Regla |
|----------|--------|
| Layout | Lista vertical de tarjetas; en **Capacitor v1** preferir **scroll de página** (`shellMain`) con lista HTML o cards — el `List` DevExtreme con scroll interno puede fallar en WebView Android |
| Tarjeta | Código artículo, descripción, stock/disponible, depósito (campos según API stock v1) |
| Acción | Tap → detalle modal o pantalla secundaria (solo lectura v1) |
| Filtros | Campo `q`; aplicar con **Enter** en v1 mobile (sin debounce obligatorio) |
| Actualizar | Botón/icono i18n `grid.refresh`; re-fetch servidor |
| Resumen | Opcional v1: «Mostrando {shown} de {total}» (`consultas.resultSummary`) |
| API | Endpoints existentes SPEC-101-07 — **sin cambio backend v1** |

### Stock v1 — campos tarjeta (cerrado A1 — D1-11)

Tarjeta kardex (resumen):

| Campo | Fuente API |
|-------|------------|
| Código artículo | `codArticulo` |
| Descripción | `descripcion` |
| Disponible neto | `disponibleNeto` |
| Stock | `stock` |

**Detalle al tap (v1):** `Popup` solo lectura con el resto de propiedades del ítem (`comprometido`, `comprometidoWeb`, métricas `*Base` si no null). Sin edición.

**Paginación:** botón «Cargar más» o scroll infinito según API (`page`, `page_size` máx. 100) — detallar en TR.

**Filtro:** query `q` (código/descripción) — mismo contrato que web.

**Export Excel:** **fuera** v1 mobile.

Contrato API: `docs/02-producto/PedidosWeb/consulta-stock.md` §6.

## Menú mobile v1

Mostrar solo ítems permitidos en v1:

| Ítem | v1 | v2 | v3 |
|------|----|----|-----|
| Consulta stock | ✅ | ✅ | ✅ |
| Otras consultas | ❌ | ✅ | ✅ |
| Listados pedidos/presupuestos | ❌ | ✅ | ✅ |
| Carga pedidos | ❌ | ❌ | ✅ |
| Dashboard | ❌ | opcional | opcional |
| Excel / pivot / admin | ❌ | ❌ | ❌ |

Implementación: filtro client-side `mobileMenuPolicy` + permisos backend.

**UX native v1 (PedidosWeb smoke 2026-06-30):**

- Menú drawer overlay; ítem operativo **Stock** en vista `operationalOnly`.
- Controles web de expandir/contraer árbol y vista ramas **no** se muestran en native v1.
- Botón menú (☰) en **header derecho** + safe area (`StatusBar`, inset superior) para no solapar barra de estado del SO.

## Login PedidosWeb (tenant)

| Campo | Ejemplo | Notas |
|-------|---------|-------|
| Tenant / Empresa | `demo`, `ankasdelsur`, `quento` | Slug `CODIGO_TENANT` |
| Usuario | login ERP | Igual web |
| Contraseña | — | Igual web |

API producción PedidosWeb: `https://backend.pedidosweb.paqsystems.com/api/v1`

Flujo: ver [`04-patron-login-tenant-mobile-mono.md`](../../_base/01-mobile/04-patron-login-tenant-mobile-mono.md).

## HU relacionadas (a generar — parte B)

> **Numeración:** `HU-101-030` está ocupada por Excel (SPEC-101-16). Mobile v1 usa **031–033**.

| HU propuesta | Fase | Tema |
|--------------|------|------|
| HU-101-031-mobile-v1-scaffold | v1 | Capacitor + shell |
| HU-101-032-mobile-login-tenant | v1 | Login tenant PedidosWeb |
| HU-101-033-mobile-consulta-stock-kardex | v1 | Stock kardex |
| HU-101-034-mobile-v2-consultas-kardex | v2 | Resto consultas |
| HU-101-035-mobile-v2-listados-kardex | v2 | Listados |
| HU-101-036-mobile-v3-carga-pedidos | v3 | Carga mobile |

## TR relacionadas (a generar — parte C)

| TR propuesta | Fase | HU |
|--------------|------|-----|
| TR-SPEC-101-17-mobile-v1-scaffold | v1 | HU-101-031 |
| TR-SPEC-101-17-mobile-v1-login-tenant | v1 | HU-101-032 |
| TR-SPEC-101-17-mobile-v1-stock-kardex | v1 | HU-101-033 |
| TR-SPEC-101-17-mobile-v2-consultas | v2 | HU-101-034 |
| TR-SPEC-101-17-mobile-v2-listados | v2 | HU-101-035 |
| TR-SPEC-101-17-mobile-v3-carga | v3 | HU-101-036 |

## Criterios de aceptación — Mobile v1 (`v1.2.0-mobile`)

- [x] App instala y abre en Android (debug) — smoke emulador 2026-06-30.
- [ ] App instala y abre en iOS (debug) — pendiente Mac/CI.
- [x] Login con tenant `desarrollo` + usuario seed autentica (Android emulador).
- [x] Tras login, landing en **`/consultas/stock`** (menú solo stock visible).
- [x] Consulta stock en kardex con tarjetas en datos reales.
- [x] Actualizar recarga datos; filtro `q` con Enter.
- [x] No acceso a rutas `/excel-import`, `/admin`, pivot (menú + guard).
- [x] Sin opción pestañas separadas en UI mobile.
- [ ] E2E o smoke documentado iOS; regresión web manual pendiente.

## Definición de listo por fase

| Fase | Listo cuando |
|------|--------------|
| v1 | AC § anterior + tag `v1.2.0-mobile` + TR v1 en estado F |
| v2 | Tag `v1.2.1-mobile` + todas consultas/listados kardex + TR v2 F |
| v3 | Tag `v1.2.2-mobile` + carga pedidos smoke + TR v3 F |

---

## Revisión A1 — cierre (2026-06-30)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno |

### Checklist A1 (resumen)

| Área | Estado | Notas |
|------|--------|-------|
| Trazabilidad SPEC-001-11 | OK | Plataforma + patrón login §04 |
| Roadmap v1/v2/v3 | OK | Alineado decisión stakeholder |
| Consulta v1 stock | OK | API §6 `consulta-stock.md`; kardex D1-11 |
| Menú filtrado v1 | OK | Solo stock; permiso `pw_consultastock` |
| Login tenant | OK | Conflictos numeración HU resueltos |
| Auth flujos auxiliares | Obs. | firstLogin sí; forgot/reset v1 no (D1-12) |
| Criterios aceptación v1 | OK | Android + iOS smoke |

### Ambigüedades críticas

| ID | Tema | Resolución A1 |
|----|------|---------------|
| AMB-C-101-17-01 | **HU-101-030** ya existe (Excel) | **Cerrado:** mobile v1 usa **HU-101-031…033** |
| AMB-C-101-17-02 | Landing post-login en v1 | **Cerrado D1-7:** **`/consultas/stock`**, no `/dashboard` |
| AMB-C-101-17-03 | Campos kardex stock v1 | **Cerrado D1-11:** 4 campos tarjeta + popup detalle |
| AMB-C-101-17-04 | ¿Paridad menú API completo? | **Cerrado D1-8:** filtro **`mobileMenuPolicy`** client-side v1; backend sigue filtrando permisos |

### Ambigüedades menores (TR)

| ID | Tema | Resolución |
|----|------|------------|
| AMB-M-101-17-01 | Paginación kardex UX | TR elige scroll infinito vs «Cargar más» |
| AMB-M-101-17-02 | Carátula `fecha_proceso` en mobile | Mostrar bajo título consulta (paridad web) — TR |
| AMB-M-101-17-03 | Dashboard v2 | **Opcional** v2; no bloquea v1 |
| AMB-M-101-17-04 | Chat assistant mobile | Fuera v1–v2 salvo TR explícita |
| AMB-M-101-17-05 | Preferencias idioma/tema en v1 | **Should** v1: heredar selector login/shell si costo bajo; si no, v1.1 mobile |

### Decisiones D1 (A1 — 2026-06-30)

| # | Tema | Decisión |
|---|------|----------|
| D1-7 | Landing v1 | `/consultas/stock` tras login exitoso (native) |
| D1-8 | Menú v1 | `mobileMenuPolicy` oculta rutas no v1; único ítem operativo: stock |
| D1-11 | Kardex stock | Tarjeta: codArticulo, descripcion, disponibleNeto, stock; detalle popup |
| D1-12 | Forgot/reset password v1 | **Fuera v1** — ocultar enlaces en login native; **Must** flujo `firstLogin` → `/change-password` |
| D1-15 | Export stock v1 | No export Excel en mobile v1 |
| D1-16 | Permiso stock | Misma regla web: `pw_consultastock`; 403 → mensaje i18n |
| D1-17 | Idioma/tema shell v1 | **Sí** — mantener selector idioma y apariencia en header mobile (HU-GEN-01 heredado) |
| D1-18 | Tags release | v1 `v1.2.0-mobile` · v2 `v1.2.1-mobile` · v3 `v1.2.2-mobile` |

### Supuestos detectados

- Endpoint `GET /api/v1/consultas/stock` sin cambios backend v1.
- Usuarios seed con permiso stock existen en tenant `desarrollo`.
- Componente kardex reutilizable en v2 (misma abstracción `ConsultaKardexList`).
- Tag **`v1.2.0-mobile`** marca release v1 completo (3 TR v1 en F).

### Veredicto A1

**Apto con observaciones** — autoriza Parte B y Parte C.

---

## Parte B — cierre (2026-06-30)

### Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto B1** | **Cerrado** — 6 HU enriquecidas |
| **¿Puede pasar a Parte C (TR)?** | **Sí** (completado) |

### Entregables parte B

| HU | Release | Estado |
|----|---------|--------|
| [HU-101-031](../../03-historias-usuario/101-PedidosWeb/HU-101-031-mobile-v1-scaffold.md) | `v1.2.0-mobile` | Especificado |
| [HU-101-032](../../03-historias-usuario/101-PedidosWeb/HU-101-032-mobile-login-tenant.md) | `v1.2.0-mobile` | Especificado |
| [HU-101-033](../../03-historias-usuario/101-PedidosWeb/HU-101-033-mobile-consulta-stock-kardex.md) | `v1.2.0-mobile` | Especificado |
| [HU-101-034](../../03-historias-usuario/101-PedidosWeb/HU-101-034-mobile-v2-consultas-kardex.md) | `v1.2.1-mobile` | Especificado |
| [HU-101-035](../../03-historias-usuario/101-PedidosWeb/HU-101-035-mobile-v2-listados-kardex.md) | `v1.2.1-mobile` | Especificado |
| [HU-101-036](../../03-historias-usuario/101-PedidosWeb/HU-101-036-mobile-v3-carga-pedidos.md) | `v1.2.2-mobile` | Especificado |

Transversal GEN: [HU-GEN-11-*](../../03-historias-usuario/001-Generaliddes/README.md#spec-001-11--mobile-capacitor).

### Orden sugerido TR v1

```text
1. TR-GEN-11-mobile-capacitor-scaffold + TR-SPEC-101-17-mobile-v1-scaffold
2. TR-GEN-11-mobile-login-tenant + TR-SPEC-101-17-mobile-v1-login-tenant
3. TR-GEN-11-mobile-shell + TR-SPEC-101-17-mobile-v1-stock-kardex
```

---

## Parte C — cierre v1 (2026-06-30)

| TR | HU | Release | Estado C1 |
|----|-----|---------|-----------|
| [TR-SPEC-101-17-mobile-v1-scaffold](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-scaffold.md) | HU-101-031 | `v1.2.0-mobile` | **Apto** |
| [TR-SPEC-101-17-mobile-v1-login-tenant](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-login-tenant.md) | HU-101-032 | `v1.2.0-mobile` | **Apto** |
| [TR-SPEC-101-17-mobile-v1-stock-kardex](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-stock-kardex.md) | HU-101-033 | `v1.2.0-mobile` | **Apto** |

Transversal: [F-GEN-11-cierre-c1](../../04-tareas/001-Generaliddes/F-GEN-11-cierre-c1.md).

**Veredicto C1 v1:** **Apto** — autoriza **Parte D** release `v1.2.0-mobile`.

**TR v2/v3 pendientes:** HU-101-034…036 — generar tras cierre F v1.

---

## Parte D — cierre v1 (2026-06-30)

| TR | HU | Estado D1 |
|----|-----|-----------|
| [TR-SPEC-101-17-mobile-v1-scaffold](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-scaffold.md) | HU-101-031 | **Implementado** |
| [TR-SPEC-101-17-mobile-v1-login-tenant](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-login-tenant.md) | HU-101-032 | **Implementado** |
| [TR-SPEC-101-17-mobile-v1-stock-kardex](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v1-stock-kardex.md) | HU-101-033 | **Implementado** |

Verificación: [D-VERIFICACION-101-17-mobile-v1](../../04-tareas/101-PedidosWeb/D-VERIFICACION-101-17-mobile-v1.md).

**Veredicto D1/F v1:** **Implementado** — [F-101-17-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-17-cierre-formal.md). Smoke **Android emulador OK**; tag `v1.2.0-mobile` tras smoke iOS.

## Parte C — cierre v2 (2026-06-30)

| TR | HU | Release | Estado C1 |
|----|-----|---------|-----------|
| [TR-SPEC-101-17-mobile-v2-consultas](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v2-consultas.md) | HU-101-034 | `v1.2.1-mobile` | **Apto** |
| [TR-SPEC-101-17-mobile-v2-listados](../../04-tareas/101-PedidosWeb/TR-SPEC-101-17-mobile-v2-listados.md) | HU-101-035 | `v1.2.1-mobile` | **Apto** |

Cierre C1: [F-101-17-cierre-c1-v2](../../04-tareas/101-PedidosWeb/F-101-17-cierre-c1-v2.md).

**Veredicto C1 v2:** **Apto** — autoriza Parte D `v1.2.1-mobile`.

## Parte D / F — cierre v2 (2026-06-30)

| TR | HU | Estado |
|----|-----|--------|
| TR v2 consultas | HU-101-034 | **Implementado** |
| TR v2 listados | HU-101-035 | **Implementado** |

Verificación F1: [D-VERIFICACION-101-17-mobile-v2](../../04-tareas/101-PedidosWeb/D-VERIFICACION-101-17-mobile-v2.md).  
Cierre F: [F-101-17-cierre-formal-v2](../../04-tareas/101-PedidosWeb/F-101-17-cierre-formal-v2.md).

**Veredicto D2/F v2:** **Implementado** — smoke Android OK; tag `v1.2.1-mobile` tras smoke iOS. Siguiente: **v3** carga mobile (HU-101-036).

## Referencias producto

- `docs/02-producto/PedidosWeb/consulta-stock.md`
- HU-101-023 (stock web) — referencia API
- [`SPEC-101-11-consultas-ui.md`](SPEC-101-11-consultas-ui.md) — contraste web DataGrid vs mobile kardex
