# TR-SPEC-101-09 — Frontend base (rutas, menú pw_*, shell)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | Transversal: entrada a carga y consultas; ver [PedidosWeb_SPEC_MVP.md](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md) §8–§9 |
| **SPEC relacionada** | [SPEC-101-09-frontend-base](../../05-open-spec/101-PedidosWeb/SPEC-101-09-frontend-base.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-01-shell-layout, TR-GEN-01-menu-general-sidebar, TR-GEN-02-login-sesion; `SPEC-001-01`, `SPEC-001-03` implementados |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** SPEC-101-09, menú producto §8, `backend/config/paqsuite_mvp.php`  
**Referencia SPEC:** [SPEC-101-09-frontend-base](../../05-open-spec/101-PedidosWeb/SPEC-101-09-frontend-base.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Registrar estructura React de PedidosWeb: rutas lazy, integración shell/menú y cliente HTTP MONO.

### Narrativa
Como **usuario autenticado**,  
quiero **navegar a los procesos PedidosWeb desde el menú lateral**,  
para **acceder a carga, consultas y dashboard sin reimplementar el shell GEN-01**.

### In scope / Out of scope
- **In scope:** rutas MVP alineadas a `pw_*`; lazy loading; páginas placeholder o shell de proceso; cliente API con `X-Paq-Cliente`; i18n keys de menú/rutas; `data-testid` en ítems de navegación; protección de rutas (auth guard existente).
- **Out of scope:** implementación funcional pantalla carga (TR-SPEC-101-10); grillas consulta (TR-SPEC-101-11); login/shell GEN-01.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Todas las rutas de `mvpMenuRoutePaths` registradas en router con lazy import.
- **AC-02:** Ítem menú ERP `procedimiento` `pw_*` navega a `routePath` correcto vía sidebar GEN-01.
- **AC-03:** Usuario `vendedor.acotado.mvp` solo ve subconjunto seed (`pw_cargapedidos`, `pw_presupuestosingresados`, `pw_pedidosingresados`, `pw_dashboard`).
- **AC-04:** Cliente HTTP inyecta `Authorization` + `X-Paq-Cliente: desarrollo` (o tenant de sesión).
- **AC-05:** Rutas protegidas redirigen a login si no hay token.
- **AC-06:** Placeholders muestran título i18n y `data-testid` de página (`page-pedidos-carga`, etc.).
- **AC-07:** Entrada transversal: desde menú se puede abrir `/pedidos/carga` y rutas `/pedidos/*`, `/presupuestos/*`, `/consultas/*` sin error 404.
- **AC-08:** No se introduce selector de empresa (`X-Company-Id` prohibido SPEC-001-05).
- **AC-09:** DevExtreme + licencia ya inicializada; sin controles HTML nativos finales en placeholders que luego serán DX.
- **AC-10:** Coordinación con TR-SPEC-101-10/11: exports de rutas y constantes reutilizables.

### Escenarios Gherkin

```gherkin
Feature: Navegación PedidosWeb base

  Scenario: Supervisor abre carga desde menú
    Given un supervisor autenticado con menú completo
    When hace clic en el ítem con procedimiento "pw_cargapedidos"
    Then la URL es "/pedidos/carga"
    And ve la página con data-testid "page-pedidos-carga"

  Scenario: Vendedor acotado sin pedidos pendientes
    Given el usuario "vendedor.acotado.mvp"
    When carga el menú lateral
    Then no aparece el ítem "pw_pedidospendientes"

  Scenario: Acceso directo sin sesión
    Given un usuario no autenticado
    When navega a "/consultas/stock"
    Then es redirigido al login
```

---

## 3) Reglas de Negocio

1. **RN-01:** Mapeo 1:1 entre `routeName` en `paqsuite_mvp.php` y path React (`mvpMenuRoutePaths`).
2. **RN-02:** Procedimientos grupo (`grp_pedidos`, `grp_informes`) no navegan; solo expanden (GEN-01).
3. **RN-03:** `data-testid` de navegación: patrón `nav-{menuKey}` o existente (`nav-pedidos-ingresados` en dashboard) — mantener compatibilidad E2E.
4. **RN-04:** Presupuestos cerrados (98): ruta dedicada `/presupuestos/cerrados` si se añade al seed; hasta entonces pestaña interna en TR-101-11 — registrar en README slice si se difiere del seed actual.
5. **RN-05:** Reutilizar `ShellLayout`, providers de tema/idioma, `syncDevExtremeLocale` (GEN-01 / patrón grilla).

---

## 4) Impacto en Datos

Sin cambios de BD. Depende de `GET /api/v1/user/menu` (GEN-02).

### Seed mínimo para tests
- Menú seed en `paqsuite_mvp.php` ya desplegado con TR-GEN-02.

---

## 5) Contratos de API y OpenAPI

Este slice **no crea** endpoints. Consume:

| Método | Path | Uso frontend |
|--------|------|----------------|
| GET | `/api/v1/user/menu` | Construcción sidebar `pw_*` |
| GET | `/api/v1/auth/me` | Restaurar sesión F5 |
| GET | `/api/v1/config/public` | Flags UI (`gridLayoutsEnabled`, etc.) |

Verificación: cliente HTTP existente cumple header `X-Paq-Cliente` (normas §3).

---

## 6) Cambios Frontend

### Pantallas / componentes

| Ruta | Procedimiento | Componente (lazy) | Prioridad MVP |
|------|---------------|-------------------|---------------|
| `/pedidos/carga` | `pw_cargapedidos` | `PedidosCargaPage` (placeholder → TR-10) | Must |
| `/presupuestos/ingresados` | `pw_presupuestosingresados` | `PresupuestosIngresadosPage` | Must |
| `/pedidos/ingresados` | `pw_pedidosingresados` | `PedidosIngresadosPage` | Must |
| `/pedidos/pendientes` | `pw_pedidospendientes` | `PedidosPendientesPage` | Must |
| `/consultas/deuda` | `pw_deudaclientes` | `ConsultaDeudaPage` | Must |
| `/consultas/cheques` | `pw_consultacheques` | `ConsultaChequesPage` | Must |
| `/consultas/historial` | `pw_historialventas` | `ConsultaHistorialPage` | Must |
| `/consultas/stock` | `pw_consultastock` | `ConsultaStockPage` | Must |
| `/presupuestos/tratativas` | `pw_tratativaspresup` | `TratativasPage` (Should) | Should |
| `/dashboard` | `pw_dashboard` | existente / extender | Must |
| `/integracion/logs` | `pw_logsintegracion` | `LogsIntegracionPage` | Should |

**Estructura sugerida:**

```text
frontend/src/features/pedidos/
frontend/src/features/presupuestos/
frontend/src/features/consultas/
frontend/src/routes/pedidosWebRoutes.tsx
```

### data-testid sugeridos

| Elemento | data-testid |
|----------|-------------|
| Página carga | `page-pedidos-carga` |
| Página pedidos ingresados | `page-pedidos-ingresados` |
| Página presupuestos activos | `page-presupuestos-ingresados` |
| Página consulta stock | `page-consulta-stock` |
| Nav pedidos ingresados (enlaces) | `nav-pedidos-ingresados` (ya usado en dashboard) |
| Ítems menú dinámicos | `nav-{menuKey}` vía `elementAttr` / wrapper sidebar |

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | Módulo rutas lazy + registro en router principal | AC-01, AC-07 |
| T2 | Frontend | Páginas placeholder con i18n | AC-06 |
| T3 | Frontend | Verificar cliente HTTP headers | AC-04 |
| T4 | Tests | E2E navegación menú (`menu-sidebar.spec.ts` extensión) | AC-02, AC-03 |
| T5 | Docs | Alinear seed si falta ruta presupuestos cerrados | RN-04 |

---

## 8) Estrategia de Tests

- **Unit:** `flattenOperationalMenu` / mapeo ruta ↔ procedimiento.
- **Integration:** N/A.
- **E2E:** `menu-sidebar.spec.ts`, `smoke.spec.ts` — clic en ítems `pw_*`; vendedor acotado; locale en labels menú.

---

## 9) Riesgos y Edge Cases

- **R1:** Desfase menú backend vs rutas frontend → fuente única `mvpMenuRoutePaths` + test de paridad con `paqsuite_mvp.php`.
- **R2:** Placeholders olvidados bloquean E2E de slices siguientes → incluir título y testid mínimos.
- **R3:** Presupuestos cerrados sin ítem menú dedicado en seed actual → acordar ampliación seed o UI dual en 101-11.

---

## 10) Checklist final

### Checklist del slice
- [ ] Rutas MVP registradas y protegidas
- [ ] Menú `pw_*` navegable
- [ ] Cliente HTTP MONO

### Checklist normas transversales

- [ ] Sin endpoints nuevos (N/A matriz salvo verificación)
- [ ] X-Paq-Cliente en cliente HTTP
- [ ] Sin ampliación fuera de SPEC-101-09
- [ ] DevExtreme obligatorio en UI interactiva futura (regla workspace)

---

## Archivos creados/modificados

(Post-implementación)

### Frontend
- `frontend/src/routes/pedidosWebRoutes.tsx`
- `frontend/src/features/{pedidos,presupuestos,consultas}/pages/*`
- Actualización router principal y `mvpMenuRoutePaths.ts` si aplica

### Docs
- Referencia cruzada TR-SPEC-101-10 / TR-SPEC-101-11
