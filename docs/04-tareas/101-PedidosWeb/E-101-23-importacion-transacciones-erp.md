# Parte E — Tests SPEC-101-23 Importación transacciones ERP

| Campo | Valor |
|-------|-------|
| **Épica** | [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) |
| **TR** | [23a](TR-SPEC-101-23-integracion-transacciones-plataforma.md) · [23b](TR-SPEC-101-23-integracion-transacciones-saldos.md) · [23c](TR-SPEC-101-23-integracion-transacciones-historial-ventas.md) |
| **Fecha** | 2026-10-29 |
| **BD** | SQL Server tenant dev (`Ankas_del_sur` / `.env` local) — **compartida ERP** |
| **SQL deploy** | Usuario confirmó ejecución de `integracion-transacciones/00-deploy-all.sql` |

## Veredicto Parte E

**Aprobado con observaciones**

Cobertura automatizada **profunda** en plataforma + saldos + normalizers + OpenAPI. Pendiente QA ERP manual de lotes reales (stock/deuda/cheques/historial) y pruebas snapshot atómico en tenant dedicado.

---

## Matriz CA (TR-23a) → tests

| CA / tema | Test | Resultado |
|-----------|------|-----------|
| Auth 401 | `IntegracionTransaccionPlatformTest::testPostTransaccionesSinTokenRetorna401` | **OK** |
| Auth 403 (4 rutas) | `testCuatroRutasTransaccionRetornan403SinPermisoIntegracion` | **Skip** (sin user `vendedor.acotado.mvp` en BD) |
| Tenant inválido | `testTenantInvalidoRetorna400` | **OK** |
| Envelope 202 GEN-20 | `testRecepcion202ExponeEnvelopeGen20` | **OK** |
| Delta vacío async | `testDeltaVacioStockRetorna202` + GET completed | **OK** |
| Snapshot historial | `testHistorialVentasSnapshotRetorna422` | **OK** |
| Recurso 404 | `testRecursoTransaccionDesconocidoRetorna404` | **OK** |
| Tope 1500 ítems | `testLoteDemasiadoGrandeRetorna422` | **OK** |
| Cuatro slugs config | `testCuatroSlugsTransaccionRegistradosEnOpenApi` | **OK** |
| Permiso integración | `testIntegracionErpUserTienePermisoTransacciones` | **OK** |
| OpenAPI paths + items | `OpenApiDocumentationTest` (integración transacciones) | **OK** |

---

## Matriz TR-23b (stock / deuda / cheques) → tests

| Caso | Test | Resultado |
|------|------|-----------|
| Stock ítem inválido (sin clave) | `testStockItemInvalidoIncrementaErroneos` | **OK** |
| Campo prohibido `comprometidoWeb` | `testStockCampoProhibidoComprometidoWeb` | **OK** |
| Duplicado en mismo lote | `testStockDuplicadoEnMismoLoteRechazaSegundo` | **OK** |
| Delta válido con artículo existente | `testStockDeltaValidoTrasAltaArticuloMaestro` | **OK** |
| FK artículo faltante | `testStockFkFaltanteRechazaItem` (`fkFaltante`) | **OK** |
| Deuda campos faltantes | `testDeudaItemInvalidoFaltanCampos` | **OK** |
| Cheques campos faltantes | `testChequesItemInvalidoFaltanCampos` | **OK** |
| Snapshot stock atómico (vacío / inválido / publish) | — | **Pendiente** (no automatizado; riesgo en BD compartida) |
| Deuda/cheques delta válido con cliente | — | **Pendiente QA ERP** |

---

## Matriz TR-23c (historial) → tests

| Caso | Test | Resultado |
|------|------|-----------|
| Solo delta (422 snapshot) | Platform `testHistorialVentasSnapshotRetorna422` | **OK** |
| Delta vacío async | `testHistorialVentasDeltaVacioCompletaAsync` | **OK** |
| Alias ERP `tComp`/`nComp`/`fechaEmi` | Unit `IntegracionTransaccionItemNormalizerTest` | **OK** |
| Rechazo sin `codCliente` (normalizer + validación) | `testHistorialVentasDeltaConAliasLegacyRechazaSinCliente` | **OK** |
| Delta con cliente real + persistencia | — | **Pendiente QA ERP** |

Tabla física de historial: **`pq_pedidosweb_ventadetallada`** (no `pq_pedidosweb_historial_ventas`).

---

## Unitarios

```text
php artisan test tests/Unit/PedidosWeb/Integracion/IntegracionTransaccionItemNormalizerTest.php
php artisan test tests/Unit/PedidosWeb/Integracion/IntegracionTransaccionItemNormalizerDeudaTest.php
```

| Archivo | Cobertura | Resultado |
|---------|-----------|-----------|
| `IntegracionTransaccionItemNormalizerTest` | Alias historial legacy | **OK** (1 caso) |
| `IntegracionTransaccionItemNormalizerDeudaTest` | Fechas ISO / cortas en deuda | **OK** (2 casos) |

---

## Suite recomendada (SPEC-101-23)

```text
cd backend
php artisan test tests/Feature/Api/PedidosWeb/IntegracionTransaccionPlatformTest.php
php artisan test tests/Feature/Api/PedidosWeb/IntegracionTransaccionSaldosFeatureTest.php
php artisan test tests/Unit/PedidosWeb/Integracion/IntegracionTransaccionItemNormalizerTest.php
php artisan test tests/Unit/PedidosWeb/Integracion/IntegracionTransaccionItemNormalizerDeudaTest.php
php artisan test tests/Feature/OpenApiDocumentationTest.php --filter=integracion
```

**Última corrida agente (2026-10-29):** suite transacciones + OpenAPI integración: **28 passed**, **2 skipped** (403 sin `vendedor.acotado.mvp`). Historial: **2 passed** adicionales sobre `pq_pedidosweb_ventadetallada`.

---

## Observaciones (OBS-E-23)

| ID | Tema | Impacto |
|----|------|---------|
| OBS-E-23-01 | BD compartida ERP: tests de maestros en `--group=integracion` pueden fallar por datos masivos (listas/vendedores) | No bloquea veredicto **transacciones** |
| OBS-E-23-02 | 403 en cuatro rutas requiere seed `vendedor.acotado.mvp` | Skip documentado |
| OBS-E-23-03 | Snapshot stock/deuda/cheques no probado end-to-end en PHPUnit | QA ERP + tenant `_dev` recomendado |
| OBS-E-23-04 | Worker obligatorio en **cada** tenant — [runbook transacciones](../../06-operacion/runbook-usuario-integracion-transacciones.md) § Worker | Ops (usuario: worker activo en todos) |

---

## Seguimiento post-F1 (2026-10-29)

F1/F/I cerrados con observaciones — ver [D-VERIFICACION-101-23-importacion-transacciones-erp.md](D-VERIFICACION-101-23-importacion-transacciones-erp.md).

1. QA ERP: LOTE stock, deuda, cheques (delta + snapshot en `_dev` cuando aplique).
2. Historial delta con ítems reales (`idGva53`, cliente existente).
3. Postman TR-23c: polling GET hasta `completed` en CI.
