# TR-SPEC-101-15 — Tests, E2E §9 y hardening transversal

| Campo | Valor |
|-------|--------|
| **HU relacionada** | Transversal — [PedidosWeb_SPEC_MVP.md §12](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md) |
| **SPEC relacionada** | [SPEC-101-15-tests-hardening](../../05-open-spec/101-PedidosWeb/SPEC-101-15-tests-hardening.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must (transversal) |
| **Dependencias** | Slices TR-SPEC-101-02 … TR-SPEC-101-14 según orden §10 SPEC madre; GEN-01/02/03 completados |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** [SPEC-101-15-tests-hardening](../../05-open-spec/101-PedidosWeb/SPEC-101-15-tests-hardening.md), [PedidosWeb_SPEC_MVP.md §9 y §12](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Cerrar política de tests del MVP: flujo E2E §9 obligatorio, cobertura services ≥ 70 %, E2E por slice con UI y checklist cross-slice.

### Narrativa
Como **equipo de entrega**, quiero **una suite verificable y umbral de cobertura**, para **liberar el MVP con regresión controlada**.

### In scope / Out of scope
- **In scope:** flujo E2E §9; ≥ 2 E2E por slice con UI; feature test por endpoint 101; cobertura `app/Services/**` ≥ **70 %** por módulo entregado; gate CI recomendado; checklist cross-slice.
- **Out of scope:** umbral **80 %** hasta declarar módulo estable (§12.2 madre); cobertura alta en controllers boilerplate inicial.

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Flujo E2E §9 ejecutable y **verde** en QA:

```text
1. Entrada {cliente}.pedidosweb → frontend.pedidosweb (contexto tenant)
2. Login (X-Paq-Cliente; sesión)
3. Shell + menú por rol
4. Selección cliente (vendedor/supervisor)
5. Carga pedido: cabecera + renglón; totales/IVA
6. Grabar pedido (estado 0); mail enviado (fake/assert)
7. Consulta pedidos ingresados: comprobante visible
8. Dashboard §4.1: indicadores coherentes con datos cargados
```

- **AC-02:** Cada slice 101 con UI tiene **≥ 2** escenarios Playwright: (1) camino feliz, (2) error / permiso / validación.
- **AC-03:** Cada slice 101 con endpoints expuestos tiene **feature test** por operación (éxito + 401; + 403 cuando aplique).
- **AC-04:** Cobertura PHPUnit `app/Services/**` del módulo PedidosWeb ≥ **70 %** líneas al cierre del release MVP.
- **AC-05:** Gate CI documentado (fallar build si services del diff < umbral vigente).
- **AC-06:** README CI, `_PR-prompt` o `docs/04-tareas/101-PedidosWeb/README.md` lista comandos y suites.
- **AC-07:** Checklist cross-slice (§ abajo) completado antes de cierre formal F.

### Escenarios Gherkin

```gherkin
Feature: Flujo E2E prioritario MVP §9

  Scenario: Camino feliz pedido y dashboard
    Given tenant desarrollo y usuario vendedor seed
    When ejecuta flujo completo §9
    Then pedido visible en consulta ingresados
    And dashboard muestra KPIs coherentes

Feature: Cobertura services

  Scenario: Umbral MVP en CI
    When corre PHPUnit con coverage en app/Services PedidosWeb
    Then porcentaje lineas >= 70
```

---

## 3) Reglas de Negocio

1. **RN-01:** Prioridad: **E2E escenarios** + feature API > porcentaje aislado.
2. **RN-02:** Umbral 70 % aplica al **módulo/slice entregado**, no repo completo irrelevante.
3. **RN-03:** Slices **Should** (101-08 logs, 101-12 tratativas) no bloquean §9 si difieren; deben tener plan de tests si se incluyen en release.
4. **RN-04:** Mail en §9: assert `Mail::fake()` o log, no bandeja real.
5. **RN-05:** Tenant: usar `desarrollo` + stub o real según etapa TR-SPEC-101-01.

---

## 4) Impacto en Datos

### Tablas afectadas
- Ninguna (tests usan seed QA documentado en slices)

### Seed mínimo para tests
- Consolidar script/fixture único `pedidosweb_e2e_seed` referenciado por §9 y dashboard/consultas

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

No introduce endpoints; **verifica** que todos los endpoints de TR 101-04…14 tengan:

- Fila en matriz permisos
- OpenAPI con `security`, 401, 403
- Al menos un feature test

### 5.2 Detalle por operación

N/A — matriz de trazabilidad test ↔ endpoint en anexo post-implementación (tabla en README slice).

### 5.3 Actualización matriz permisos

- [ ] Auditoría: cada fila 101 tiene test 401/403 según norma

---

## 6) Cambios Frontend

### Pantallas / componentes
- Suite Playwright bajo `frontend/e2e/pedidosweb/` (o ruta acordada)
- Helpers: login, selección cliente, navegación menú

### data-testid sugeridos
- Reutilizar `data-testid` públicos definidos en TR de cada slice; no inventar acoplamiento DOM DX interno

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Tests | Implementar spec Playwright `e2e-mvp-section9.spec.ts` | AC-01 |
| T2 | Tests | Inventario ≥ 2 E2E por slice UI | AC-02 |
| T3 | Tests | Auditoría feature tests endpoints 101 | AC-03 |
| T4 | CI | Job coverage services ≥ 70 % | AC-04, AC-05 |
| T5 | Docs | README comandos + checklist | AC-06, AC-07 |
| T6 | QA | Ejecución verde entorno QA | AC-01 |

---

## 8) Estrategia de Tests

### 8.1 Unit
- Services: bonificaciones, totales, estados, visibilidad, mail destinatarios, regla dashboard -1.

### 8.2 Integration API
- Por endpoint: 200 feliz, 401 sin token, 403 sin permiso (matriz).
- Envelope siempre con `resultado` objeto.

### 8.3 E2E Playwright
- **Obligatorio:** suite §9 (un spec o steps encadenados).
- Por slice UI (mínimo):

| Slice TR | E2E mínimos sugeridos |
|----------|----------------------|
| 101-10 pantalla carga | feliz grabar; validación rechazada |
| 101-11 consultas | ingresados feliz; export o sin permiso |
| 101-14 dashboard | KPIs visibles post §9 |
| 101-09 frontend | login + menú rol |
| GEN ya cubierto | login, idioma según dependencias |

### 8.4 Cobertura
- Comando: `phpunit --coverage-text --filter=Services` (ajustar path módulo PedidosWeb)
- Umbral: **70 %** líneas MVP

---

## 9) Riesgos y Edge Cases

- E2E flaky por timing DX → `data-testid` y waits acordados.
- CI sin SQL Server → documentar docker-compose QA.
- Slices Should omitidos → §9 igual debe pasar sin ellos.

---

## 10) Checklist final

### Checklist del slice (cierre MVP)
- [ ] E2E §9 verde QA
- [ ] ≥ 2 E2E por slice UI Must
- [ ] Feature tests endpoints 101
- [ ] Cobertura services ≥ 70 %
- [ ] CI gate documentado
- [ ] README / PR prompt actualizado

### Checklist cross-slice (obligatorio pre-release)

| # | Ítem | OK |
|---|------|-----|
| 1 | Tenancy `X-Paq-Cliente` en E2E y API tests | [ ] |
| 2 | Matriz permisos ↔ OpenAPI ↔ policy código | [ ] |
| 3 | Envelope JSON en todos los tests API | [ ] |
| 4 | Sin `DELETE` presupuesto en API ni UI | [ ] |
| 5 | PDF no expuesto en consultas MVP | [ ] |
| 6 | Export Excel GEN-03 en consultas Must | [ ] |
| 7 | Mail grabación canal GEN-02 (fake en CI) | [ ] |
| 8 | Dashboard 8 KPIs + regla -1 / `MinutosWeb` | [ ] |
| 9 | Conversión `CodMotivoCierreExitoso` (HU-013) con seed | [ ] |
| 10 | Cierre rechazo motivo negativo (HU-027) | [ ] |
| 11 | Visibilidad vendedor/cliente/supervisor en consultas | [ ] |
| 12 | `SPEC-101-01` etapa posterior documentada si sigue stub | [ ] |
| 13 | Should 101-08 / 101-14 tratativas: plan si no en release | [ ] |
| 14 | Agent verification guide (skill F1) ejecutado | [ ] |

### Checklist normas transversales

- [ ] Endpoints 101 con policy
- [ ] Matriz actualizada
- [ ] OpenAPI coherente
- [ ] Tests 401/403
- [ ] Sin ampliación de alcance

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- `tests/Feature/PedidosWeb/*`
- `phpunit.xml` coverage include Services

### Frontend
- `e2e/pedidosweb/mvp-section9.spec.ts`
- Specs por slice

### CI
- Workflow coverage + e2e (GitHub Actions o equivalente)

### Docs
- `docs/04-tareas/101-PedidosWeb/README.md`
- `_PR-prompt.md` evidencia suites
