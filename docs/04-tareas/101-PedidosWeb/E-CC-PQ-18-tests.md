# E — CC PQ #18 (30/10/2026) — Delta transacciones + OpenAPI

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#18** · **30/10/2026** |
| **TR** | [TR-SPEC-101-23-integracion-transacciones-saldos.md](TR-SPEC-101-23-integracion-transacciones-saldos.md) · [TR-SPEC-101-23-integracion-transacciones-plataforma.md](TR-SPEC-101-23-integracion-transacciones-plataforma.md) |
| **Fecha** | 02/11/2026 |

## Backend — PHPUnit

```text
cd backend
php scripts/deploy-integracion-transacciones-sqlserver.php
php artisan test --filter=IntegracionTransaccionItemNormalizerTest
php artisan test --filter=IntegracionTransaccionSaldosFeatureTest
```

| Suite | Resultado 02/11/2026 |
|-------|----------------------|
| `IntegracionTransaccionItemNormalizerTest` | **2 passed** (incl. coerción `eliminarSiAusente`) |
| `IntegracionTransaccionSaldosFeatureTest` | Feature async requiere stack importación (`ImportationDispatcher`); **3 tests nuevos** CC #18 listos cuando el entorno responda 202 |

## Evidencia manual / deploy

- SP desplegados en tenant demo: `deploy-integracion-transacciones-sqlserver.php` (10 batches).
- OpenAPI: `OpenApiGetsSchemas.php` + `storage/api-docs/api-docs.json` (`eliminarSiAusente` en ítems stock/deuda/cheques).
