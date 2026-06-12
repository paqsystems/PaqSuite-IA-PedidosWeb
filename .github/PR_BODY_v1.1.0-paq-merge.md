## Summary

Integración incremental **`v1.1.0`** ← **`v1.1.0-paq`**: revierte la implementación de **CC PQ #5** en el listbox de artículos (carga), restaura el **disponible neto** con pedidos web ingresados, y agrega script SQL operativo para catálogo pivots.

1. **Carga de pedidos — listbox artículos:** vuelve a descontar `comprometido_web` (cabeceras `estado = 0`) en `ArticuloCargaLookupService` y en el lookup de carga; misma fórmula que consulta de stock (`stock − comprometido − comprometido_web`).
2. **Ops pivots:** script T-SQL idempotente `backend/scripts/sql/seed-pivot-catalog.sql` (equivalente a `PivotCatalogPilotSeeder` + informes CC PQ #4).
3. **README:** enlaces al runbook de actualización de versión y aviso commit/push (docs BASE vía `docs/_base/`).

**Compare:** `v1.1.0` ← **`v1.1.0-paq`**  
**Tip:** `4702e06` — `fix(pedidosweb): restaurar disponible neto en listbox carga de articulos`  
**Crear PR:** [Compare v1.1.0...v1.1.0-paq](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/v1.1.0...v1.1.0-paq)

```powershell
gh pr create --base v1.1.0 --head v1.1.0-paq --title "fix(pedidosweb): restaurar disponible neto carga + SQL pivots ops" --body-file .github/PR_BODY_v1.1.0-paq-merge.md
```

**Commits incluidos (delta respecto a `v1.1.0`):**

| Commit | Resumen |
|--------|---------|
| `4702e06` | Revert funcional CC PQ #5 en lookup carga; docs/regla §3.1; tests; `seed-pivot-catalog.sql`; README ops |

> En `v1.1.0` existe además `1c4348f` (solo cuerpo PR release). El merge une ambas ramas sin conflicto funcional esperado.

---

## Contexto — CC PQ #5

| Aspecto | Detalle |
|---------|---------|
| **CC original (09/06)** | Pedía disponible en listbox **sin** descontar pedidos ingresados (`7244247`) |
| **Decisión 12/06** | Restaurar comportamiento previo: disponible **neto** con `comprometido_web` también en browse de carga |
| **Sin cambio** | Consulta de stock sigue con disponible neto; display `codigo - descripcion — Disp. X (Y)` con base opcional |
| **Documentación CC** | `00-ControlCalidad-PQ.md` #5 sigue describiendo el hallazgo original; **no** se reabrió Parte I en este PR |

Referencias: [`pantalla-carga-comprobante-ui.md`](docs/02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §3.1 · [`.cursor/rules/pantalla-carga-comprobante-ui.mdc`](.cursor/rules/pantalla-carga-comprobante-ui.mdc)

---

## Cambios por área

### Backend

| Archivo | Cambio |
|---------|--------|
| `ArticuloCargaLookupService.php` | Siempre une `pq_pedidosweb_pedidosdetalle` + cabecera `estado = 0`; eliminado flag `incluirComprometidoWeb` |
| `ArticuloController.php` | Sin bifurcación por presencia de `codigos` |
| `StockConsultaService.php` | `lookupDisponibilidadCargaPorCodigos` delega en `lookupDisponibilidadPorCodigos` |

### Ops / SQL

| Archivo | Uso |
|---------|-----|
| `backend/scripts/sql/seed-pivot-catalog.sql` | Ejecutar en tenant SQL Server **después** de migraciones pivots (`2026_06_11_100000_*`, `2026_06_11_110000_*`); idempotente; alternativa a `php artisan db:seed --class=...PivotCatalogPilotSeeder` |

### Docs / tests

- Regla agente y producto §3.1 alineadas a disponible neto en listbox.
- Tests: `ArticuloCargaLookupServiceTest`, `StockConsultaServiceTest` (requieren SQL Server en local).

---

## Observaciones deploy

| Entorno | Acción |
|---------|--------|
| **Forge (backend)** | Redeploy / `git pull` + restart PHP-FPM. **Sin** `migrate` ni cambios `.env` por este PR |
| **Vercel (frontend)** | **No** requiere redeploy (sin cambios frontend) |
| **SQL pivots** | Solo si el tenant aún no tiene catálogo: ejecutar `seed-pivot-catalog.sql` **o** seeders artisan (previo: migraciones + `PIVOTS_ENABLED=true`) |

Tras merge en `v1.1.0`, actualizar [`.github/PR_BODY_v1.1.0.md`](.github/PR_BODY_v1.1.0.md) (ítem CC PQ #5 y test plan) antes de cerrar [PR #5](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/pull/5) hacia `main`.

---

## Test plan

### CC PQ #5 — regresión (disponible neto restaurado)

- [ ] Carga → combobox artículos (flecha sin texto): ítems muestran `Disp.` descontando pedidos web **ingresados** (`estado = 0`)
- [ ] Artículo con **base**: paréntesis muestra disponible neto del código base
- [ ] Consulta de stock: disponible neto sin regresión respecto a carga
- [ ] Agregar renglón por código puntual (`codigos`): mismo disponible neto que browse

### Ops pivots (opcional, tenant sin seed previo)

- [ ] Migraciones pivots ya aplicadas
- [ ] Ejecutar `seed-pivot-catalog.sql` sin duplicar filas en re-ejecución
- [ ] Con flags: informes pivot (stock, deuda, cheques, detalle pedidos) resuelven metadata

### Automatizado

- [ ] `php artisan test --filter=ArticuloCargaLookupServiceTest` (SQL Server)
- [ ] `php artisan test --filter=StockConsultaServiceTest` (SQL Server)
- [ ] CI GitHub Actions en verde

---

## Post-merge sugerido

1. Merge `v1.1.0-paq` → `v1.1.0` y push `v1.1.0`
2. Ajustar cuerpo PR #5 (`main` ← `v1.1.0`) — CC PQ #5 y checklist
3. QA manual listbox carga en tenant piloto antes de merge a `main`
