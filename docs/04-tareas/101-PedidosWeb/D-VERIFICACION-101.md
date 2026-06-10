# Verificación Parte D — Épica 101 PedidosWeb

| Campo | Valor |
|-------|--------|
| **Fecha inicio D** | 2026-06-02 |
| **Rama** | `v1.1.0-paq` |
| **Commit baseline** | `6a57231` → P1 en curso |
| **Orden** | 02 → 03 → 06 → 04 → 05 → 09 → 10 → 07 → 11 → 13 → 14 → 12 → 08 → 15 |

## Leyenda de estados

| Estado | Significado |
|--------|-------------|
| **OK** | AC cumplido con evidencia en código y/o test (sin depender de SQL Server) |
| **PARCIAL** | Implementado con gaps (test incompleto, UI stub, OpenAPI, etc.) |
| **PENDIENTE** | Falta implementación o test exigido por la TR |
| **BLOQUEADO_ENV** | Test/caso existe pero requiere SQL Server + tenant `desarrollo` + seeds |
| **N/A** | Fuera de alcance del slice (ej. 101-01 diferida) |

## Baseline tanda 1 (2026-06-02)

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | **40 passed**, **8 skipped** |
| `npm run build` (frontend) | **OK** |

Skipped: integración repositories (6) + 403 feature (2) — PHPUnit sin BD `Ankas_del_sur` en `.env` testing.

---

## Resumen por TR

| TR | Título | Must/Should | % OK unit | Bloqueadores principales |
|----|--------|-------------|-----------|---------------------------|
| 101-01 | Backend base tenant | Diferida | N/A | `EMPRESAS_CONEXION` |
| 101-02 | Modelos | Must | 38% (3/8) | Smoke integración modelos |
| 101-03 | Repositories | Must | 38% (3/8) | Tests integración → **BLOQUEADO_ENV** |
| 101-06 | Seguridad visibilidad | Must | 13% (1/8) | Visibilidad en escritura; E2E mock |
| 101-04 | Services pedidos | Must | **Bloque 3 PARCIAL** (~54%) | Cobertura ≥70%; tests matriz/conversión integración |
| 101-05 | Controllers REST | Must | **Bloque 3 PARCIAL** (~64%) | Feature 200 feliz; matriz §5.3 parametros-carga |
| 101-09 | Frontend base | Must | ~80% (8/10) | E2E menú vs seed real |
| 101-10 | Pantalla carga | Must | **Bloque 3 PARCIAL** (~75%) | E2E API real tanda 2; matriz T5/T6 integración |
| 101-07 | Consultas API | Must | **Bloques 1–2 OK**; matriz pendiente | 403 consultas; matriz permisos |
| 101-11 | Consultas UI | Must | **Bloque 4 PARCIAL** | E2E export mock OK; feature real tanda 2 |
| 101-13 | Mails | Must | ~70% (7/10) | `Mail::fake()` feature tests |
| 101-14 | Dashboard | Must | **Bloque 4 PARCIAL** (~88%) | Feature 200 BLOQUEADO_ENV |
| 101-12 | Tratativas/cierre | Should+Must | ~25% | Popup cierre UI; feature feliz cerrar |
| 101-08 | Logs integración | Should | **Bloque 4 PARCIAL** | UI grilla + filtros DX |
| 101-15 | Tests hardening | Must | **Bloque 4 PARCIAL** (~35%) | E2E §9 pasos 7–8 mock; cobertura CI pendiente |

**Cierre formal D:** cerrado con observaciones (2026-06-03) — QA manual usuario; ver paso F.

---

## Verificación paso F1 (agente) — 2026-06-03

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | **75 passed**, 51 skipped (SQL) |
| `php artisan test --filter=ParametrosConsulta` | **3 passed** |
| `npm run build` | **OK** (fix i18n fr/pt menú) |
| `npx playwright test consultas-d1.spec.ts mvp-section9.spec.ts` | **7/7 OK** |

**Resultado F1:** Aprobado con observaciones. Detalle: [`F-101-PedidosWeb-cierre-formal.md`](F-101-PedidosWeb-cierre-formal.md).

---

## Verificación paso F (docs vs código) — 2026-06-03

Contraste documentación canónica vs implementación: rutas menú, consultas, carga/edición comprobante, dashboard, parámetros, matriz permisos.

**Resultado F:** Aprobado con observaciones (descuento por cantidad UI pendiente; tests integración SQL skipped; 101-01 diferida).

Informe completo: [`F-101-PedidosWeb-cierre-formal.md`](F-101-PedidosWeb-cierre-formal.md).

---

## TR-SPEC-101-02 — Modelos

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01 | OK | `PqPedidoswebPedidoCabecera` + `PedidosWebModelsTest` |
| AC-02 | OK | `PqPedidoswebPedidoDetalle` + composite PK |
| AC-03 | PARCIAL | 22 modelos maestros; sin test nombres tabla |
| AC-04 | PARCIAL | Motivos, cierres, tratativas, logs — sin test dedicado |
| AC-05 | PARCIAL | Relaciones cabecera; sin test eager load |
| AC-06 | PARCIAL | Test solo cabecera+detalle |
| AC-07 | PENDIENTE | Sin smoke insert/select tenant |
| AC-08 | OK | `HasCompositePrimaryKey` + repos README |

---

## TR-SPEC-101-03 — Repositories

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01…04 | BLOQUEADO_ENV | `PedidoRepositoryIntegrationTest` (6 escenarios) |
| AC-05 | OK | Sin excepciones negocio en repos |
| AC-06 | OK | Interfaces + `PedidosWebRepositoryBindingTest` |
| AC-07 | BLOQUEADO_ENV | Mismo archivo integración |
| AC-08 | OK | Sin filtro visibilidad en repos |

---

## TR-SPEC-101-06 — Seguridad y visibilidad

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01 | BLOQUEADO_ENV | `AuthLoginTest` + seeds MVP |
| AC-02 | PARCIAL | Feature password recovery; E2E mock |
| AC-03 | PARCIAL | Visibilidad en consultas/dashboard; **gap** escritura `PedidoService` |
| AC-04 | BLOQUEADO_ENV | `VisibilityDataTest` 404 universo |
| AC-05 | PARCIAL | 401 masivo 101; OpenAPI paths negocio sin anotar |
| AC-06 | PARCIAL | Cliente/vendedor; falta supervisor “todos” |
| AC-07 | PARCIAL | E2E perfiles con login mockeado |
| AC-08 | OK | Slice verificación; gaps documentados |

---

## TR-SPEC-101-04 — Services pedidos

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01…06 | OK/PARCIAL | `PedidoService` — edición, eliminar, rechazos grabación |
| AC-07 | PARCIAL | Edición -1 implementada + tests unitarios; código 409 vs TR 2000 |
| AC-08 | PARCIAL | `touchActividadEdicion` sin test dedicado |
| AC-09…10 | PARCIAL | `PresupuestoCierreService` tests rechazo; sin feliz |
| AC-11 | OK | Copia borrador + `origen_comprobante=copia` en grabación |
| AC-12 | OK | `CalculoTotalesServiceTest` |
| AC-13 | PENDIENTE | Cobertura services < 70% |
| AC-14 | OK | `resolveModificaFlags` + `ParametrosCargaService` + validación payload |

---

## TR-SPEC-101-05 — Controllers REST

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01…03, 09…11 | OK | Controllers delgados; rutas `api.php`; sin DELETE presupuesto |
| AC-04 | PARCIAL | Policy `VisibilityPermissionGuard`; 403 solo 1 test |
| AC-05 | PARCIAL | `@OA\` en `PedidosWebOpenApiPaths` incl. parametros-carga y articulos |
| AC-06 | OK | `matriz-permisos-mvp.md` § PedidosWeb carga |
| AC-07 | PARCIAL | 401×27; sin 200 feliz por endpoint |
| AC-08 | PARCIAL | Ruta cerrar OK; sin feature 200 |

---

## TR-SPEC-101-09 — Frontend base

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01, 04…09 | OK | Rutas lazy, headers, DevExtreme, sin X-Company-Id |
| AC-02, 03 | PARCIAL | E2E menú parcial / mocks |
| AC-10 | OK | Módulos exportados para 10/11 |

---

## TR-SPEC-101-10 — Pantalla carga

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01, 02, 11, 12 | OK | Ruta, botones DX, testids, toast mail |
| AC-03 | PARCIAL | Matriz §10.1 en UI (visibilidad botones); validación backend |
| AC-04, 05 | OK | Perfil cliente fijo; `Modifica*` deshabilita precio/bonif |
| AC-06 | PARCIAL | Autocompletar artículos SelectBox + `GET /articulos` |
| AC-07 | PARCIAL | Confirmación + mail; E2E mock con parametros-carga |
| AC-08, 09 | PARCIAL | Edición -1 (`iniciar`/`cancelar`); copia con `cod_comprobante_origen_copia` |
| AC-10 | PARCIAL | Modo `ver` solo lectura |

---

## TR-SPEC-101-07 — Consultas API

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01…03 (pedidos/presup) | OK | flags acción; estados 0/-1/1/99/98; join cierres 98 |
| AC-04…08 (gestión) | OK | stock/deuda/cheques/historial; paginación; `DiasVentasDetalladas` |
| AC-07 | OK | `PedidosWebVisibilityGuard` en comprobantes y gestión |
| AC-09 | PARCIAL | 401 + 404 `cod_cliente`; 403 consultas pendiente |
| AC-10 | PARCIAL | Paths OpenAPI; matriz consultas pendiente |

---

## TR-SPEC-101-11 — Consultas UI

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01, 06, 07, 10 | OK | Grillas gestión + comprobantes; i18n; layouts |
| AC-03…05, 08 | OK | Acciones cableadas; flags `puede*`; cierre DX |
| AC-02, 09 | PARCIAL | E2E export consultas mock (`gridExportExcel` habilitado) |

---

## TR-SPEC-101-13 — Mails

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-03, 05…08, 10 | OK | Blade, dedup, canal GEN-02, log, toast |
| AC-01, 02, 04, 09 | PARCIAL/PENDIENTE | Sin `Mail::fake()` feature comprobante |

---

## TR-SPEC-101-14 — Dashboard

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01, 03, 05, 06 | OK | 8 KPIs UI + service |
| AC-04 | OK | Regla -1 documentada + `DashboardOperativoServiceTest` |
| AC-07 | PARCIAL | E2E §9 paso 8 mock (8 KPIs assert) |
| AC-08 | PARCIAL | 401 OK; 403 BLOQUEADO_ENV |

---

## TR-SPEC-101-12 — Tratativas y cierre

| AC Must | PARCIAL/PENDIENTE | Backend rutas/services; UI popup cierre ausente |
| AC Should | OK diferir | Tratativas placeholder coherente con TR |

---

## TR-SPEC-101-08 — Logs (Should)

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01, 02 | OK | `LogIntegracionService` list + write |
| AC-03 | PARCIAL | `IntegracionLogsPage` grilla DX + filtros DateBox/SelectBox |
| AC-04, 05 | PARCIAL | 401 auth test; 403/feature BLOQUEADO_ENV |
| AC-06 | OK | Should — no bloquea §9 |

---

## TR-SPEC-101-15 — Hardening

| AC | Estado |
|----|--------|
| E2E §9 completo | PARCIAL | Mock pasos 7–8 (consulta + dashboard KPIs) |
| Feature 200 por operación | PENDIENTE | BLOQUEADO_ENV |
| Cobertura ≥70% services | PENDIENTE |
| Gate CI | PENDIENTE |

---

## Tanda 2 — entorno (cuando cierres bloqueadores)

1. `backend/.env` → `DB_DATABASE=Ankas_del_sur` (o `.env.testing` equivalente).
2. Seeds: `php artisan paqsuite:seed-seguridad-mvp` (si aplica).
3. Terminal A: `cd backend && php artisan serve`
4. Terminal B: `cd frontend && npm run dev` (o Playwright `webServer`).
5. Re-ejecutar:
   - `php artisan test --filter=PedidosWeb` (0 skipped ideal)
   - `php artisan test --filter=VisibilityDataTest`
   - `npx playwright test tests/e2e/pedidosweb/`

---

## Plan de cierre D (Must primero)

| Prioridad | TR | Acción |
|-----------|-----|--------|
| P0 | 101-15 | Desbloquear tests integración/403 con BD real |
| P1 | 101-05, 101-07 | OpenAPI controllers PedidosWeb + matriz consultas |
| P1 | 101-04 | Tests matriz §3.1, conversión, edición; cobertura 70% |
| P1 | 101-13 | Feature `Mail::fake()` grabar/modificar |
| P2 | 101-10 | Modifica*, selector cliente por perfil, autocompletar artículos |
| P2 | 101-11 | Cablear acciones grilla + E2E consultas |
| P2 | 101-06 | Visibilidad en `PedidoService` lectura/escritura |
| P3 | 101-12, 101-08 | UI cierre presupuesto; grilla logs (Should) |

---

## Registro de sesiones D

| Fecha | TR revisadas | Tests | Notas |
|-------|--------------|-------|-------|
| 2026-06-02 | 02…15 (auditoría) | 40 pass / 8 skip; build OK | Informe inicial; CAPTION/TOOLTIP ERP aplicados por usuario |
| 2026-06-02 | 101-13, 101-04 (P1) | **44 pass / 9 skip** | `ComprobanteMailServiceTest` (Mail::fake); `PresupuestoCierreServiceTest`; fix `ComprobanteNotificationMail::$viewData` |
| 2026-06-02 | **101-07, 101-11 Bloque 1** (gestión) | **55 pass / 44 skip** | Visibilidad `cod_cliente` consultas; mapeo API↔UI frontend; TR actualizadas |
| 2026-06-02 | **101-07, 101-11 Bloque 2** (pedidos/presup) | **55 pass / 46 skip** | Flags acción API; join cierres 98; acciones grilla + cierre presupuesto DX |
| 2026-06-02 | **101-04, 101-05, 101-10, 101-13 Bloque 3** | **61 pass / 46 skip** | Parametros-carga; articulos; Modifica*; PedidosCargaPage edición/copia/ver; OpenAPI |
| 2026-06-02 | **101-08, 101-14, 101-11, 101-15 Bloque 4** | **66 pass / 47 skip**; build OK | Dashboard regla -1 test; logs UI; E2E §9 KPIs+export; i18n fr/pt/it |

---

## Verificación paso E (2026-06-03)

Alcance: cierre D1 **consulta parámetros** (GEN-04), **detalle pedidos** (101-07/11 Bloque 3), **perfil cabecera** en carga (101-10 / HU-101-005).

| Comando | Resultado |
|---------|-----------|
| `php artisan paqsuite:seed-menus-mvp` | OK |
| `php artisan paqsuite:seed-seguridad-mvp` | OK |
| `php artisan test --filter=ParametrosConsulta` | 2 pass, 1 skip (403 sin SQL) |
| `php artisan test --filter=PedidosWebModelsTest` | 3 pass (incl. relación `perfil`) |
| `php artisan test --filter=PedidosWebEndpointsAuthTest` | 31 pass (incl. detalle-pedidos + config/parametros) |
| `npm run build` | OK |
| `npx playwright test consultas-d1.spec.ts` | **3/3 OK** |
| `npx playwright test mvp-section9.spec.ts` | **2/4 OK** — perfil visible tras elegir cliente; fallo preexistente en diálogo grabar (no bloqueante perfil/consultas) |

**Documentación canónica actualizada:** `pantalla-carga-comprobante-ui.md` §5, `Updates-2026-06-03.md` §5, `TR-SPEC-101-10` contrato `cabecera-inicial`.

**Pendiente tanda 2 (SQL):** feature 200/403 consultas y parámetros; smoke manual menús `pw_consultaparametros` / `pw_detallepedidos`.

| Fecha | TR / tema | Tests | Notas |
|-------|-----------|-------|-------|
| 2026-06-03 | Cierre F MVP | E2E **7/7**; backend scoped OK | TR 101-02…15 + GEN-04 → **Finalizado**; manuales usuario actualizados |
