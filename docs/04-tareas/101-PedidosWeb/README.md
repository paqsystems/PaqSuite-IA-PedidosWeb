# Tareas técnicas (TR) — 101-PedidosWeb

**Parte C (OpenSpec):** TR derivadas de [PedidosWeb_SPEC_MVP.md](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md) y [27 HU](../../03-historias-usuario/101-PedidosWeb/README.md).

| Campo | Valor |
|-------|--------|
| **Fecha parte C** | 2026-06-01 |
| **Estado** | **Lista para parte D** (implementación por slice) |
| **Plantilla** | [`../_PLANTILLA-TR-SLICE.md`](../_PLANTILLA-TR-SLICE.md) |
| **Normas** | [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) |

## Índice TR (`TR-SPEC-101-xx`)

| ID | Archivo | Prioridad | HU principales |
|----|---------|-----------|----------------|
| 01 | [TR-SPEC-101-01-backend-base.md](TR-SPEC-101-01-backend-base.md) | **Diferida** | HU-101-003 |
| 02 | [TR-SPEC-101-02-modelos.md](TR-SPEC-101-02-modelos.md) | Must | Transversal |
| 03 | [TR-SPEC-101-03-repositories.md](TR-SPEC-101-03-repositories.md) | Must | Transversal |
| 04 | [TR-SPEC-101-04-services-pedidos.md](TR-SPEC-101-04-services-pedidos.md) | Must | 005–013, 024, 026, 027, 007–012 |
| 05 | [TR-SPEC-101-05-controllers-rest.md](TR-SPEC-101-05-controllers-rest.md) | Must | 009–013, 024, 026, 027, 012 |
| 06 | [TR-SPEC-101-06-seguridad-visibilidad.md](TR-SPEC-101-06-seguridad-visibilidad.md) | Must | 001, 002, 004 |
| 07 | [TR-SPEC-101-07-consultas-api.md](TR-SPEC-101-07-consultas-api.md) | Must | 015–018, 021–023 |
| 08 | [TR-SPEC-101-08-logs-integracion.md](TR-SPEC-101-08-logs-integracion.md) | **Should** | 020 |
| 09 | [TR-SPEC-101-09-frontend-base.md](TR-SPEC-101-09-frontend-base.md) | Must | Rutas / menú `pw_*` |
| 10 | [TR-SPEC-101-10-pantalla-carga.md](TR-SPEC-101-10-pantalla-carga.md) | Must | 004–011, 009, 010, 013, 024, 026 |
| 11 | [TR-SPEC-101-11-consultas-ui.md](TR-SPEC-101-11-consultas-ui.md) | Must | 015–018, 021–023 |
| 12 | [TR-SPEC-101-12-tratativas-cierre.md](TR-SPEC-101-12-tratativas-cierre.md) | Should + Must cierre | 014, 027 |
| 13 | [TR-SPEC-101-13-mails.md](TR-SPEC-101-13-mails.md) | Must | 019 |
| 14 | [TR-SPEC-101-14-dashboard.md](TR-SPEC-101-14-dashboard.md) | Must | 025 |
| 15 | [TR-SPEC-101-15-tests-hardening.md](TR-SPEC-101-15-tests-hardening.md) | Must | E2E §9 madre |

## Orden de implementación (Fase 1)

Omitir **101-01** hasta etapa `EMPRESAS_CONEXION`. Secuencia recomendada (SPEC madre §10):

```text
02 → 03 → 06 (verificación) → 04 → 05 → 09 → 10 → 07 → 11 → 13 → 14 → 12 (Should) → 08 (Should) → 15
```

**Bloqueadores transversales:** consumo formal de parámetros ([SPEC-001-04](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md)); GEN-01/02/03 ya en `v1.1.0-paq`.

## Trazabilidad HU → TR

| HU | TR |
|----|-----|
| 001, 002 | 06 (+ TR-GEN-02) |
| 003 | 01 (diferida) |
| 004–011, 026 | 10 (+ 04, 05) |
| 007, 008, 012, 013, 024, 027 | 04 (+ 05) |
| 009, 010 | 04, 05, 10 |
| 014 | 12 |
| 015–018, 021–023 | 07, 11 |
| 019 | 13 (+ hook 04) |
| 020 | 08 |
| 025 | 14 |
| — | 15 (cierre transversal) |

## Matriz permisos

Actualizar [`matriz-permisos-mvp.md`](../001-Generaliddes/matriz-permisos-mvp.md) en el mismo PR que cada slice que exponga endpoints (§5 de cada TR).

## Siguiente paso

**Parte D:** implementación por TR, empezando por **TR-SPEC-101-02-modelos**.

### Verificación D — comandos de test (2026-06-02)

```powershell
# Backend — unit PedidosWeb (sin SQL Server)
cd backend
php artisan test --filter=PedidosWeb

# Backend — auth 401/403 endpoints 101 (requiere SQL Server + seed)
php artisan test --filter=PedidosWebEndpointsAuthTest

# Frontend
cd frontend
npm run build
npx playwright test tests/e2e/pedidosweb/mvp-section9.spec.ts
```

**Pendiente de entorno:** tests integración repositories, VisibilityDataTest extendido y E2E §9 camino feliz **contra API real** requieren tenant SQL Server `desarrollo` + seeds MVP.
