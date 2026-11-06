# Cierre F Formal — SPEC-101-23 (Importación transacciones ERP)

## Alcance del cierre

Épica **APIs importación transacciones** (ERP → PedidosWeb): plataforma async (23a), stock/deuda/cheques (23b), historial delta (23c). Motor **GEN-36** compartido con maestros; **GEN-19** tenancy; **GEN-20** envelope.

| TR | HU |
|----|-----|
| [TR-SPEC-101-23-integracion-transacciones-plataforma](TR-SPEC-101-23-integracion-transacciones-plataforma.md) | [HU-101-049](../../03-historias-usuario/101-PedidosWeb/HU-101-049-integracion-transacciones-plataforma.md) |
| [TR-SPEC-101-23-integracion-transacciones-saldos](TR-SPEC-101-23-integracion-transacciones-saldos.md) | [HU-101-050](../../03-historias-usuario/101-PedidosWeb/HU-101-050-integracion-transacciones-saldos.md) |
| [TR-SPEC-101-23-integracion-transacciones-historial-ventas](TR-SPEC-101-23-integracion-transacciones-historial-ventas.md) | [HU-101-051](../../03-historias-usuario/101-PedidosWeb/HU-101-051-integracion-transacciones-historial-ventas.md) |

**SPEC:** [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md)  
**Producto:** [APIs-Importacion-transacciones.md](../../02-producto/PedidosWeb/APIs-Importacion-transacciones.md)  
**Runbooks:** [maestros](../../06-operacion/runbook-usuario-integracion-maestros.md) · [transacciones](../../06-operacion/runbook-usuario-integracion-transacciones.md)

**F1:** [D-VERIFICACION-101-23-importacion-transacciones-erp.md](D-VERIFICACION-101-23-importacion-transacciones-erp.md) — **Aprobado con observaciones** (2026-10-29)  
**Parte E:** [E-101-23-importacion-transacciones-erp.md](E-101-23-importacion-transacciones-erp.md)

## Resultado global

**Aprobado con observaciones**

Implementación D+E operativa; SQL transacciones desplegado; worker activo en tenants (ops). QA ERP con lotes nominales **pendiente de acta** (OBS-F1-23-01).

## Resumen por slice

| Slice | Resultado | Evidencia |
|-------|-----------|-----------|
| 23a Plataforma | Aprobado | 401/400/404/422, 202+GET, rate limit compartido, OpenAPI 4 rutas |
| 23b Saldos | Aprobado c/ obs. | SP apply/finalize stock/deuda/cheques; tests delta/FK/duplicado/campos prohibidos |
| 23c Historial | Aprobado c/ obs. | Solo delta; tabla `pq_pedidosweb_ventadetallada`; normalizer alias ERP |

## Verificación automatizada (2026-10-29)

| Comando | Resultado |
|---------|-----------|
| Suite SPEC-101-23 (ver E-101-23) | **OK** c/ 2 skips (403) |
| OpenAPI integración transacciones | **OK** |

## Criterios (síntesis openspec-05)

| Eje | Estado | Notas |
|-----|--------|-------|
| Alcance ⊆ SPEC/HU/TR | OK | Cuatro recursos; sin UI |
| Código ↔ TR | OK c/ obs. | Async 202 (no 200 sync legacy en TR) |
| Datos / SP | OK | Deploy `integracion-transacciones/00-deploy-all.sql` |
| Backend / permisos | OK | `pw_integracion_importacion` |
| Frontend | N/A | API-only |
| Tests | OK c/ obs. | Snapshot E2E no en BD compartida |
| Documentación | OK | SPEC, producto, runbook, E/F1 |
| Trazabilidad | OK | D, E, F1, este F |

## Observaciones (no bloquean)

| ID | Tema | Notas |
|----|------|-------|
| OBS-F-23-01 | QA ERP lotes | Registrar smoke ERP cuando corra el primer ciclo completo |
| OBS-F-23-02 | PR a `develop` | F/I ≠ Desplegado Jira (Parte N) |
| OBS-F-23-03 | Snapshot saldos | Probar en `_dev` antes de prod |

## Deploy post-release

1. `backend/scripts/sql/integracion-transacciones/00-deploy-all.sql` en cada tenant (idempotente).
2. Worker/cola en **cada** sitio (`QUEUE_CONNECTION` ≠ `sync`).
3. Smoke: delta vacío por recurso → 202 → GET `completed`; luego delta con 1 ítem válido (tras maestros).

**Parte F cerrada** (2026-10-29) para **SPEC-101-23** — **Aprobado con observaciones**.
