# Cierre F Formal — SPEC-101-22 (Importación maestros ERP)

## Alcance del cierre

Épica **APIs importación maestros** (ERP → PedidosWeb): plataforma async (22a), catálogo/artículos/precios (22b), clientes/parámetros (22c). Adopción **GEN-36** (recepción multi-lote, staging, worker, GET resultado) en el host; **GEN-19** tenancy; **GEN-20** envelope.

| TR | HU |
|----|-----|
| [TR-SPEC-101-22-integracion-maestros-plataforma](TR-SPEC-101-22-integracion-maestros-plataforma.md) | [HU-101-046](../../03-historias-usuario/101-PedidosWeb/HU-101-046-integracion-maestros-plataforma.md) |
| [TR-SPEC-101-22-integracion-maestros-catalogo-articulos](TR-SPEC-101-22-integracion-maestros-catalogo-articulos.md) | [HU-101-047](../../03-historias-usuario/101-PedidosWeb/HU-101-047-integracion-maestros-catalogo-articulos.md) |
| [TR-SPEC-101-22-integracion-maestros-clientes-parametros](TR-SPEC-101-22-integracion-maestros-clientes-parametros.md) | [HU-101-048](../../03-historias-usuario/101-PedidosWeb/HU-101-048-integracion-maestros-clientes-parametros.md) |

**SPEC:** [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md)  
**Producto:** [APIs-Importacion-datos-maestros.md](../../02-producto/PedidosWeb/APIs-Importacion-datos-maestros.md)  
**Runbook:** [runbook-usuario-integracion-maestros.md](../../06-operacion/runbook-usuario-integracion-maestros.md)

**F1:** [D-VERIFICACION-101-22-importacion-maestros-erp.md](D-VERIFICACION-101-22-importacion-maestros-erp.md) — **Aprobado con observaciones** (2026-10-29)

## Resultado global

**Aprobado con observaciones**

Implementación D operativa; QA ERP manual (LOTE-008, 009, 017) **OK** por PQ (2026-10-29). Ajustes post-QA en commit `4a7b6f6` (aliases payload, finalize vendedores, contadores finalize).

## Resumen por slice

| Slice | Resultado | Evidencia |
|-------|-----------|-----------|
| 22a Plataforma | Aprobado | Auth integración, tenant, 202/GET, lock, idempotencia básica, OpenAPI |
| 22b Catálogo | Aprobado c/ obs. | SP + handlers; listas anidadas; escalas; **vendedores snapshot**; feature tests parciales en BD compartida |
| 22c Clientes/params | Aprobado | Clientes, direcciones, contactos, whitelist parámetros |

## Smoke manual (ERP / PQ)

| Escenario | Estado |
|-----------|--------|
| LOTE-008 listas de precios con artículos (`items[]` ERP) | **OK** |
| LOTE-009 escalas — descripción cabecera | **OK** |
| LOTE-017 vendedores — snapshot elimina ausentes; error si referenciados | **OK** |
| Worker + GET resultado final | **OK** (entorno PQ) |

## Verificación automatizada (2026-10-29)

| Comando | Resultado |
|---------|-----------|
| Unitarios `tests/Unit/PedidosWeb/Integracion/` | **OK** (5/5) |
| Feature plataforma + catálogo | **Parcial** (13/17; ver F1 OBS-F1-01) |

## Criterios (síntesis openspec-05)

| Eje | Estado | Notas |
|-----|--------|-------|
| Alcance ⊆ SPEC/HU/TR | OK | Sin 101-23 |
| Código ↔ TR | OK | SP + `CatalogoMaestroHandler` + job async |
| Datos / SP | OK c/ obs. | Deploy manual `00-deploy-all.sql` en deploy |
| Backend / permisos | OK | `pw_integracion_importacion` |
| Frontend | N/A | API-only |
| Tests | OK c/ obs. | PQ manual + unit; feature en BD dedicada pendiente |
| Documentación | OK | SPEC, producto, runbook, OpenAPI |
| Trazabilidad | OK | F1 + este F |

## Observaciones (no bloquean)

| ID | Tema | Notas |
|----|------|-------|
| OBS-F-01 | Feature tests en `Ankas_del_sur` | Aislar en `_dev` o reforzar seeds |
| OBS-F-02 | PR a `develop` | Cierre F ≠ Desplegado Jira (Parte N) |
| OBS-F-03 | SPEC-101-23 | Épica hermana; no incluida |

## Deploy post-release

1. Ejecutar `backend/scripts/sql/integracion-maestros/00-deploy-all.sql` en cada tenant que use importación (idempotente ALTER PROCEDURE).
2. Verificar worker/cola y `QUEUE_CONNECTION`.
3. Smoke: un lote delta + un snapshot chico + GET hasta `completed`.

**Parte F cerrada** (2026-10-29) para **SPEC-101-22** — **Aprobado con observaciones**.
