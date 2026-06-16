## Summary

Integración incremental **`v1.1.0`** ← **`v1.1.0-paq`**: listbox de artículos en carga en modo **provisional** (solo catálogo `codigo - descripcion`), fix de loop infinito en búsqueda, planes técnicos NOLOCK y ajustes CC PQ #5.

1. **Carga de pedidos — listbox artículos (provisional):** precarga catálogo al elegir lista de precios; API `solo_catalogo=1` omite join de stock; display `codigo - descripcion` sin disponible.
2. **Fix búsqueda artículos:** deduplicación de peticiones, manejo de errores, sin doble `load()` en auto-match.
3. **Planes técnicos:** `.cursor/plans/nolock-concurrencia-sql.plan.md` y `paqsuite-framework-compartido.plan.md`.
4. **Consulta stock:** sin cambio — sigue con disponible neto completo.

**Compare:** `v1.1.0` ← **`v1.1.0-paq`**  
**Tip:** `893319a` — `fix(pedidosweb): listbox carga provisional solo catalogo codigo-descripcion`  
**Crear PR:** [Compare v1.1.0...v1.1.0-paq](https://github.com/paqsystems/PaqSuite-IA-PedidosWeb/compare/v1.1.0...v1.1.0-paq)

```powershell
gh pr create --base v1.1.0 --head v1.1.0-paq --title "fix(pedidosweb): listbox catalogo provisional + planes NOLOCK" --body-file .github/PR_BODY_v1.1.0-paq-merge.md
```

**Commits incluidos (delta respecto a `v1.1.0` previo):**

| Commit | Resumen |
|--------|---------|
| `893319a` | Listbox carga provisional solo catálogo (`solo_catalogo`) |
| `6c8c916` | Planes NOLOCK y framework compartido |
| `721c23d` | Lookup carga sin `comprometido_web` (intermedio) |
| `a87f2a2` | Fix loop infinito búsqueda artículos |

---

## Contexto — CC PQ #5

| Aspecto | Detalle |
|---------|---------|
| **Hallazgo CC #5** | Disponible en listbox de carga vs consulta stock |
| **Decisión actual** | Implementación **provisional**: listbox sin disponible; solo `codigo - descripcion` |
| **API** | `GET /articulos?solo_catalogo=1&lista_precios={n}` |
| **Pendiente** | Definir fórmula definitiva de disponible en browse de carga |

Referencias: [`pantalla-carga-comprobante-ui.md`](docs/02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) · [`.cursor/rules/pantalla-carga-comprobante-ui.mdc`](.cursor/rules/pantalla-carga-comprobante-ui.mdc)

---

## Cambios por área

### Backend

| Archivo | Cambio |
|---------|--------|
| `ArticuloController.php` | Parámetro `solo_catalogo` en `GET /articulos` |
| `ArticuloCargaLookupService.php` | Omite join stock cuando `soloCatalogo=true` |

### Frontend

| Archivo | Cambio |
|---------|--------|
| `PedidosCargaPage.tsx` | Precarga catálogo al cambiar lista de precios |
| `comprobanteApi.ts` | `searchArticulos(..., soloCatalogo)` |
| `cargaCatalogos.ts` | `etiquetaArticulo` = `codigo - descripcion` |

### Docs / planes

- `.cursor/plans/nolock-concurrencia-sql.plan.md`
- `.cursor/plans/paqsuite-framework-compartido.plan.md`

---

## Observaciones deploy

| Entorno | Acción |
|---------|--------|
| **Forge (backend)** | Redeploy / `git pull`. **Sin** `migrate` ni cambios `.env` |
| **Vercel (frontend)** | Redeploy con cambios en carga de pedidos |

---

## Test plan

### CC PQ #5 — listbox provisional

- [ ] Carga → elegir cliente con lista de precios: combobox artículos precarga catálogo
- [ ] Ítems muestran `codigo - descripcion` **sin** `Disp.`
- [ ] Agregar renglón y grabar pedido funciona
- [ ] Consulta de stock: disponible neto sin regresión

### Fix búsqueda

- [ ] Sin loop infinito ante timeout backend o error de red

### Automatizado

- [ ] `npm run test` (Vitest `cargaCatalogos`)
- [ ] E2E `mvp-section9` — carga artículo demo
- [ ] CI GitHub Actions en verde
