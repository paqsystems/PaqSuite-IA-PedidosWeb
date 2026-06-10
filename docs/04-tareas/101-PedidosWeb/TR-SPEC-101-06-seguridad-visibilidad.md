# TR-SPEC-101-06 — Seguridad y visibilidad comercial (verificación GEN-02)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-001-login](../../03-historias-usuario/101-PedidosWeb/HU-101-001-login.md), [HU-101-002-recuperacion-contrasena](../../03-historias-usuario/101-PedidosWeb/HU-101-002-recuperacion-contrasena.md), [HU-101-004-seleccion-cliente](../../03-historias-usuario/101-PedidosWeb/HU-101-004-seleccion-cliente.md) |
| **SPEC relacionada** | [SPEC-101-06-seguridad-visibilidad](../../05-open-spec/101-PedidosWeb/SPEC-101-06-seguridad-visibilidad.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-GEN-02-* (login, recuperación, políticas, visibilidad base); SPEC-101-04/05 para policies de dominio en endpoints 101 |
| **Estado** | Finalizado |
| **Última actualización** | 2026-06-01 |

**Origen:** HU-101-001, HU-101-002, HU-101-004  
**Referencia SPEC:** [SPEC-101-06-seguridad-visibilidad](../../05-open-spec/101-PedidosWeb/SPEC-101-06-seguridad-visibilidad.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio**)

---

## 1) HU Refinada (resumen)

### Título
Verificar herencia de seguridad GEN-02 y extender policies de visibilidad comercial en el dominio PedidosWeb.

### Narrativa
Como **equipo PedidosWeb**,  
quiero **confirmar que login, recuperación y visibilidad por perfil ya entregados en Generalidades siguen vigentes**,  
para **no reimplementar auth y aplicar el mismo criterio en consultas, carga y APIs 101**.

### In scope / Out of scope
- **In scope:** checklist de verificación contra `SPEC-001-02` / baseline `v1.1.0-paq`; policies en controllers 101 que combinan `Permiso_*` + `visibleClientsForUser`; matriz permisos ampliada; E2E perfiles en `auth-login-profiles.spec.ts`; reglas de selector de cliente (HU-101-004) alineadas a visibilidad.
- **Out of scope:** reescribir login, forgot/reset, cambio de clave, inactividad o menú GEN-02 salvo gap documentado; tenancy `EMPRESAS_CONEXION` (HU-101-003 / SPEC-101-01); implementación de endpoints de negocio (TR-SPEC-101-07, TR-SPEC-101-10).

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Login PedidosWeb (HU-101-001) verificado: envelope MONO, `X-Paq-Cliente`, perfil comercial único, seeds QA (`cliente.mvp`, `usuario.perfilAmbiguo.mvp`, `usuario.sinVinculo.mvp`).
- **AC-02:** Recuperación de contraseña (HU-101-002) verificada contra TR-GEN-02-recuperacion-contrasena; E2E `password-recovery.spec.ts` verde en CI.
- **AC-03:** Toda API 101 de lectura/escritura sobre clientes o comprobantes aplica `visibleClientsForUser` en service/repository (no solo en UI).
- **AC-04:** Acceso a recurso fuera del universo visible → **404** (sin fuga), coherente con TR-GEN-02-visibilidad-datos-pedidosweb.
- **AC-05:** Policies por endpoint documentadas en matriz y OpenAPI; tests integración **401** y **403** donde aplique regla funcional adicional.
- **AC-06:** Selector de cliente (HU-101-004): cliente sin combo; vendedor solo asignados; supervisor todos; fuente `GET /api/v1/clientes` filtrada.
- **AC-07:** E2E `auth-login-profiles.spec.ts`: ≥ escenarios cliente OK, sin permiso, sin vínculo comercial (y perfil ambiguo si mock/seed disponible).
- **AC-08:** Sin ampliación de alcance: gaps se registran como observación o TR aparte, no parches ad hoc en este slice.

### Escenarios Gherkin

```gherkin
Feature: Verificación seguridad PedidosWeb (herencia GEN-02)

  Scenario: Login cliente MVP
    Given el usuario seed "cliente.mvp" con contraseña válida
    And header X-Paq-Cliente "desarrollo"
    When autentica en POST /api/v1/auth/login
    Then recibe token y functionalProfile "cliente"
    And puede cargar GET /api/v1/user/menu con ítems de pedidos

  Scenario: Usuario sin vínculo comercial
    Given el usuario seed "usuario.sinVinculo.mvp"
    When intenta login con credenciales correctas
    Then recibe HTTP 403
    And respuesta contiene clave "auth.noCommercialProfile"

  Scenario: Consulta fuera de cartera vendedor
    Given un vendedor autenticado con clientes A y B asignados
    When solicita GET /api/v1/comprobantes/{id} de cliente C no asignado
    Then recibe HTTP 404
    And el cuerpo no expone datos del comprobante

  Scenario: Selector cliente vendedor
    Given un vendedor autenticado
    When invoca GET /api/v1/clientes
    Then solo recibe clientes de su cartera asignada
```

---

## 3) Reglas de Negocio

1. **RN-01:** No reimplementar flujos ya cerrados en `docs/04-tareas/001-Generaliddes/TR-GEN-02-*`; esta TR es **verificación + extensión dominio**.
2. **RN-02:** Un login = un perfil funcional (`cliente` | `vendedor` | `supervisor`); ambigüedad cliente+vendedor → **403** `auth.noCommercialProfile`.
3. **RN-03:** Capa **policy endpoint** (`Permiso_Repo` / `Alta` / `Modi` / `Baja` / `AccesoTotal`) y capa **visibilidad datos** (`visibleClientsForUser`) son complementarias; ambas obligatorias en slices 101.
4. **RN-04:** Reutilizar helper `visibleClientsForUser` de TR-GEN-02-visibilidad-datos-pedidosweb; prohibido duplicar criterios de filtro en frontend.
5. **RN-05:** HU-101-004: el universo del SelectBox de cliente = resultado de `GET /api/v1/clientes` (ya filtrado por perfil).
6. **RN-06:** Procedimientos menú ERP `pw_*` siguen gobernados por `Pq_Permiso` + `PQ_RolAtributo` (TR-GEN-02-autorizacion-menu-api); visibilidad de **datos** no sustituye permiso de menú.
7. **RN-07:** Cualquier gap entre SPEC-101-06 y código GEN-02 se documenta en checklist §10 con severidad (bloqueante / observación).

---

## 4) Impacto en Datos

### Tablas afectadas
- Lectura: `users`, `pq_permiso`, `pq_rol`, `pq_rolatributo`, `pq_pedidosweb_login`, `pq_pedidosweb_clientes`, `pq_pedidosweb_vendedores`.
- Sin migraciones nuevas en este slice salvo gap explícito.

### Seed mínimo para tests
- Usuarios existentes en `backend/config/paqsuite_mvp.php` §users (tabla § matriz-permisos-mvp).
- Datos comerciales mínimos para probar cartera vendedor vs supervisor en consultas 101 (coordinar con seed de SPEC-101-02/03 si aún no existe).

---

## 5) Contratos de API y OpenAPI

> **Norma transversal:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1–§2. Envelope: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

Este slice **no introduce** rutas auth nuevas. Verifica las existentes y exige que **toda TR 101 posterior** registre policy + visibilidad.

### 5.1 Endpoints verificados (herencia GEN-02)

| Método | Path | Auth | Permiso / regla | Público | Verificación |
|--------|------|------|-----------------|---------|--------------|
| POST | `/api/v1/auth/login` | — | Credenciales + tenant + `Pq_Permiso` + perfil comercial | Sí | HU-101-001 |
| POST | `/api/v1/auth/password/forgot` | — | Usuario válido (respuesta genérica) | Sí | HU-101-002 |
| POST | `/api/v1/auth/password/reset` | — | Token vigente | Sí | HU-101-002 |
| GET | `/api/v1/clientes` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` + `visibleClientsForUser` | No | HU-101-004 / AC-06 |
| GET | `/api/v1/comprobantes/{id}` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad | No | Base GEN-02 |
| GET | `/api/v1/user/menu` | Bearer + `X-Paq-Cliente` | `Pq_Permiso` + atributos menú | No | Menú `pw_*` |

### 5.2 Policies dominio 101 (extensión — checklist implementación)

Al implementar TR-SPEC-101-07 y TR-SPEC-101-10, cada controller debe:

1. Registrar policy Laravel alineada a matriz (`Permiso_*`).
2. Invocar `visibleClientsForUser` (o scope equivalente) en service antes de devolver filas o detalle.
3. Devolver **404** si el comprobante/cliente no pertenece al universo visible.

| Ámbito | Policy sugerida | Visibilidad datos |
|--------|-----------------|-------------------|
| Consultas listado/detalle | `Permiso_Repo` | Filtro `cod_cliente` ∈ universo visible |
| Alta pedido/presupuesto | `Permiso_Alta` | Cliente cabecera ∈ universo visible |
| Modificación | `Permiso_Modi` | Ídem + estado permitido |
| Baja pedido (solo estado 0) | `Permiso_Baja` | Ídem |

### 5.3 Actualización matriz permisos

- [ ] Confirmar filas GEN-02 en `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md`
- [ ] Al cerrar endpoints 101, completar sección «Negocio» con TR origen `TR-SPEC-101-06` / slice funcional

---

## 6) Cambios Frontend

### Pantallas / componentes
- Sin pantallas nuevas: verificación de login/forgot ya en GEN-02.
- Consumo de `sessionContext.functionalProfile` para ocultar selector cliente (HU-101-004) — implementación principal en TR-SPEC-101-10; esta TR valida contrato API `GET /clientes`.

### data-testid sugeridos
- Reutilizar existentes en auth (`login-form`, etc.) según `docs/00-contexto/_mono/01-experiencia-base/patron-ui-auth-devextreme.md`.
- `cliente-select` (o equivalente acordado en TR-SPEC-101-10) debe listar solo clientes visibles.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | QA | Ejecutar checklist verificación GEN-02 vs HU-101-001/002 | Informe §10 sin bloqueantes críticos |
| T2 | Backend | Auditar policies + `visibleClientsForUser` en stubs/controllers 101 | Matriz actualizada |
| T3 | Tests | Integration: 404 fuera de cartera; 403 login sin perfil | Verde en CI |
| T4 | Tests | E2E `auth-login-profiles.spec.ts` ampliado si faltan escenarios | ≥ 2 escenarios por norma MVP |
| T5 | Docs | Registrar gaps en SPEC-101-06 o TR derivada | Trazabilidad HU |

---

## 8) Estrategia de Tests

- **Unit:** `visibleClientsForUser` — casos cliente / vendedor / supervisor (reutilizar tests GEN-02 si existen).
- **Integration:** `GET /clientes` con tokens de cada perfil; `GET /comprobantes/{id}` fuera de alcance → 404; login seeds ambiguo/sin vínculo → 403.
- **E2E:** `frontend/tests/e2e/auth-login-profiles.spec.ts` — login cliente, rechazo sin permiso, rechazo sin perfil comercial; coordinar con `password-recovery.spec.ts` para HU-101-002.

---

## 9) Riesgos y Edge Cases

- **R1:** Asumir que menú `pw_*` visible implica acceso a todos los clientes → mitigar con RN-03.
- **R2:** Divergencia entre filtro UI y API → mitigar con RN-04 y AC-03.
- **R3:** HU-101-003 (tenant real) posterior puede cambiar conexión SQL; no mezclar con verificación auth actual (stub `desarrollo`).
- **R4:** Perfil ambiguo solo en seed QA — no debe ocurrir en producción; documentar en runbook.

---

## 10) Checklist final

### Checklist del slice
- [ ] AC-01…AC-08 cumplidos o gaps documentados
- [ ] Herencia GEN-02 verificada sin reimplementación innecesaria
- [ ] Policies dominio 101 definidas para TR-07/10

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código
- [ ] Matriz endpoint ↔ permiso actualizada
- [ ] OpenAPI en /api/documentation coherente con código y matriz
- [ ] 401 y 403 cuando aplique documentados por operación protegida
- [ ] Envelope JSON respetado (`error` entero, `resultado` objeto, nunca null)
- [ ] X-Paq-Cliente documentado donde aplique
- [ ] Tests API incluyen 401 (y 403 si aplica)
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- Policies/middleware en controllers PedidosWeb (coordinación TR-07/10)
- Tests Feature visibilidad 101

### Frontend
- `frontend/tests/e2e/auth-login-profiles.spec.ts`

### Docs
- `docs/04-tareas/001-Generaliddes/matriz-permisos-mvp.md` (filas 101)
