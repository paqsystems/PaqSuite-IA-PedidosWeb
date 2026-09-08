# E — Tests CC PQ #14 (08/09/2026) — Alias `codigo` + comillas asistente IA

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #14 · **08/09/2026** |
| **TR** | [TR-SPEC-101-19-update-01](../updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update-01.md) |
| **Fecha** | 08/09/2026 |

## Comando

```text
cd backend
php vendor/bin/phpunit tests/Unit/Services/PedidosWeb/CargaAsistente/CargaAsistenteIntentDetectorTest.php
```

## Resultado

| Suite | Tests | Assertions | Resultado |
|-------|-------|------------|-----------|
| `CargaAsistenteIntentDetectorTest` | 19 | 156 | **OK** |

## Casos nuevos (CC #14)

| Test | AC |
|------|-----|
| `testDetectsCodigoAliasAsArticuloSynonym` | AC-CC14-T-D1 |
| `testExtractsQuotedQueryBetweenCodigoAndCantidadPreservingSpaces` | AC-CC14-T-D2, AC-CC14-T-D3 |
| `testMutateRenglonWithCodigoAliasAndQuotes` | AC-CC14-T-D5 |
| Regresión suite previa (`art.` / `item` / comillas) | AC-CC14-T-D4 |

## Fuera de esta corrida

- Feature/E2E turno LLM completo (no requerido por TR-update-01).
- Vitest FE (sin cambio UI).
