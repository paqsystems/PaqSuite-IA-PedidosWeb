# E — CC PQ #22 (y residual #20) — Tests GEN-13 / Backup

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · CC **#22** · **14/11/2026** (cubre smoke #20) |
| **TR** | [TR-GEN-13-tareas-programadas](../001-Generaliddes/TR-GEN-13-tareas-programadas.md) |
| **Fecha** | 15/11/2026 (reconfirmación F1) · D 14/11/2026 |

## Backend — PHPUnit

```text
cd backend
php artisan test --filter=Gen13Cc22BackupObjectNameTest|BackupRdsExecutionPollerTest|InMemoryMonoRdsBackupGatewayTest|PedidosWebMonoBackupCompanyTargetResolverTest
```

| Suite | Resultado 15/11/2026 |
|-------|----------------------|
| `Gen13Cc22BackupObjectNameTest` | **OK** (`-sNN` / `-mNN`) |
| `BackupRdsExecutionPollerTest` | **OK** |
| `InMemoryMonoRdsBackupGatewayTest` | **OK** (lab) |
| `PedidosWebMonoBackupCompanyTargetResolverTest` | **OK** (MONO, sin picker) |
| Total | **8 tests, 15 assertions** — OK (1 deprecation PHPUnit) |

CLI (D 14/11, no re-corrido F1): `php artisan tasks:validate-catalog` · `php artisan pedidosweb:tasks-run-due --dry-run`.

## Frontend — Vitest

```text
cd frontend
npx vitest run src/shared/platform/pedidosWebMobilePolicy.test.ts
```

| Suite | Resultado 15/11/2026 |
|-------|----------------------|
| `pedidosWebMobilePolicy.test.ts` | **7/7 OK** — `/general/tareas-programadas` excluida native |

## E2E — Playwright

| Spec | Cobertura | Esta sesión |
|------|-----------|-------------|
| `frontend/tests/e2e/gen/tareas-programadas-cc22.spec.ts` | checkboxes nombre, rehidratación, baja física + historial, `EN_EJECUCION`, run bloqueado si inactiva | Presente; **no** re-ejecutado en F1 15/11 |
| `frontend/tests/e2e/gen/tareas-programadas.spec.ts` | smoke módulo | Presente |

Aprobación UI en PedidosWeb: usuario 15/11/2026.
