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
| 101-04 | Services pedidos | Must | ~46% (6/13) | Cobertura ≥70%; tests matriz/conversión/edición |
| 101-05 | Controllers REST | Must | ~55% (6/11) | OpenAPI PedidosWeb; feature 200/403 |
| 101-09 | Frontend base | Must | ~80% (8/10) | E2E menú vs seed real |
| 101-10 | Pantalla carga | Must | ~42% (5/12) | Matriz §10.1, Modifica*, autocompletar, E2E API real |
| 101-07 | Consultas API | Must | ~60% (6/10) | Flags acción; join cierres 98; OpenAPI |
| 101-11 | Consultas UI | Must | ~40% (4/10) | Acciones cableadas; E2E consultas |
| 101-13 | Mails | Must | ~70% (7/10) | `Mail::fake()` feature tests |
| 101-14 | Dashboard | Must | ~63% (5/8) | Test regla -1; E2E §9 paso 8 |
| 101-12 | Tratativas/cierre | Should+Must | ~25% | Popup cierre UI; feature feliz cerrar |
| 101-08 | Logs integración | Should | ~33% | UI grilla logs |
| 101-15 | Tests hardening | Must | ~15% | E2E §9 completo; gate cobertura CI |

**Cierre formal D:** pendiente hasta resolver ítems **PENDIENTE** Must y ejecutar tanda 2 con entorno.

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
| AC-01…06 | OK/PARCIAL | `PedidoService` — lógica presente; tests solo eliminar/grabar rechazo |
| AC-07 | PARCIAL | Edición -1 implementada; código 409 vs TR 2000; sin test |
| AC-08 | PARCIAL | `touchActividadEdicion` sin test |
| AC-09…10 | PARCIAL | `PresupuestoCierreService` sin tests |
| AC-11 | PARCIAL | Copia borrador OK; falta `origen_comprobante=copia` |
| AC-12 | OK | `CalculoTotalesServiceTest` |
| AC-13 | PENDIENTE | Cobertura services < 70% |
| AC-14 | PARCIAL | `PedidosWebParameterService` + ERP `PQ_parametros_gral` |

---

## TR-SPEC-101-05 — Controllers REST

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01…03, 09…11 | OK | Controllers delgados; rutas `api.php`; sin DELETE presupuesto |
| AC-04 | PARCIAL | Policy `VisibilityPermissionGuard`; 403 solo 1 test |
| AC-05 | PENDIENTE | Sin `@OA\` en controllers PedidosWeb |
| AC-06 | OK | `matriz-permisos-mvp.md` § PedidosWeb carga |
| AC-07 | PARCIAL | 401×25; sin 200 feliz por endpoint |
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
| AC-07 | PARCIAL | Confirmación + mail; E2E mock |
| AC-08, 09 | PARCIAL | Deep link / copia parcial |
| AC-03…06, 10 | PENDIENTE/PARCIAL | Matriz, perfiles, Modifica*, artículos, E2E API real |

---

## TR-SPEC-101-07 — Consultas API

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-02, 04…08 | OK | `ConsultaListadoService` |
| AC-01, 03 | PARCIAL | Sin flags acción; sin join cierres 98 |
| AC-09 | PARCIAL | 401 OK; 403/422 consultas pendientes |
| AC-10 | PARCIAL | Matriz wildcard; OpenAPI pendiente |

---

## TR-SPEC-101-11 — Consultas UI

| AC | Estado | Evidencia / nota |
|----|--------|------------------|
| AC-01, 05, 06, 07 | OK | Grillas, pendientes RO, carátula, layouts |
| AC-03, 04, 08, 10 | PARCIAL | Acciones sin navegar; permisos UI |
| AC-02, 09 | PENDIENTE | E2E export consultas; ≥2 E2E consultas |

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
| AC-04 | PARCIAL | Regla -1 en service; sin unit test |
| AC-07, 08 | PARCIAL/PENDIENTE | E2E §9 paso 8; 403 BLOQUEADO_ENV |

---

## TR-SPEC-101-12 — Tratativas y cierre

| AC Must | PARCIAL/PENDIENTE | Backend rutas/services; UI popup cierre ausente |
| AC Should | OK diferir | Tratativas placeholder coherente con TR |

---

## TR-SPEC-101-08 — Logs (Should)

| AC | PARCIAL/PENDIENTE | API listo; UI placeholder |
|----|-------------------|---------------------------|

---

## TR-SPEC-101-15 — Hardening

| AC | Estado |
|----|--------|
| E2E §9 completo | PARCIAL (mock) |
| Feature 200 por operación | PENDIENTE |
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
