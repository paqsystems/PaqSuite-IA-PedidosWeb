# Verificación del agente — SPEC-101-22 Importación maestros ERP

| Campo | Valor |
|---|---|
| Alcance | **SPEC-101-22** (TR 22a, 22b, 22c) — motor **GEN-36** en host + **GEN-19/20** |
| Fecha | 2026-10-29 |
| Parte | **F1** |
| Commit referencia | `4a7b6f6` (`v1.1.1`) — fixes LOTE-008/009/017 y snapshot vendedores |
| Estado | **Aprobado con observaciones** |

## Resultado

**Aprobado con observaciones** para cierre **F** e **I** de SPEC-101-22.

**Fuera de este F1:** SPEC-101-23 (transacciones) y el cierre global GEN-36 sobre 101-23 — ver [D-VERIFICACION-GEN-36-importaciones](D-VERIFICACION-GEN-36-importaciones.md).

## Evidencia revisada

- Rutas: `POST /api/v1/integracion/maestros/{recurso}` → **202** + `GET /api/v1/integracion/importaciones/{importacionId}`.
- Registry `integracion_maestros.php`: recursos §1 SPEC, finalize snapshot (incl. **vendedores**).
- SP `backend/scripts/sql/integracion-maestros/00-deploy-all.sql`.
- Normalización ERP: `items[]`/`codigo` en listas; `descripcion` en escalas; finalize rechazos con `error_code`.
- OpenAPI tag **Integración maestros** + schemas actualizados.
- **QA manual producto (PQ):** lotes integración **LOTE-008** (listas + artículos anidados), **LOTE-009** (descripción escalas), **LOTE-017** (snapshot vendedores) — **OK** tras deploy SQL y worker (confirmación usuario 2026-10-29).

## Hallazgos críticos

Ninguno bloqueante para SPEC-101-22 con QA PQ aceptada.

## Advertencias

| ID | Tema | Notas |
|---|---|---|
| OBS-F1-01 | Feature PHPUnit vs BD compartida | En `Ankas_del_sur`, 3 fallos + 1 error en suite feature (SP desactualizado en CI local, datos ERP masivos, seed usuario vendedor). No invalida QA PQ con SP redeployado. |
| OBS-F1-02 | Deploy SQL obligatorio | Cada release que altere `00-deploy-all.sql` exige ejecutar SP en tenant antes de smoke ERP. |
| OBS-F1-03 | Worker/cola | `QUEUE_CONNECTION` ≠ `sync` + `queue:work` para cierre de ciclo async. |
| OBS-F1-04 | GEN-36 parcial | Motor host listo para maestros; **101-23** sigue pendiente de D. |

## Tests

| Comando | Resultado |
|---|---|
| `php vendor/bin/phpunit tests/Unit/PedidosWeb/Integracion/` | **OK** — 5 tests, 10 assertions |
| `php vendor/bin/phpunit tests/Feature/Api/PedidosWeb/IntegracionMaestroPlatformTest.php tests/Feature/Api/PedidosWeb/IntegracionMaestroCatalogoFeatureTest.php` | **Parcial** — 17 tests: 13 OK, 3 failures, 1 error (ver OBS-F1-01) |
| QA manual ERP (PQ) | **OK** — LOTE-008, 009, 017 |

## Pendientes (no bloquean cierre 101-22)

- Endurecer feature tests con BD dedicada `_dev` o fixtures aislados (OBS-F1-01).
- CI: job con SQL Server + deploy SP + worker efímero.
- SPEC-101-23 / TR 23a–23c — épica aparte.

## Recomendación final

Proceder con **F** e **I** de SPEC-101-22. Mantener PIAPEDWEB-34 en **Finalizado** (no Desplegado hasta PR a `develop` si aplica regla N).
