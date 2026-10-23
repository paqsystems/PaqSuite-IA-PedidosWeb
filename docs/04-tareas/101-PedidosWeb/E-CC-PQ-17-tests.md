# E — CC PQ #17 (20/09/2026) — Atributo `especial` en artículos

| Campo | Valor |
|-------|--------|
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #17 · **20/09/2026** |
| **TR** | [TR-SPEC-101-02-modelos.md](TR-SPEC-101-02-modelos.md) · [TR-SPEC-101-07-consultas-api.md](TR-SPEC-101-07-consultas-api.md) · [TR-SPEC-101-10-pantalla-carga.md](TR-SPEC-101-10-pantalla-carga.md) · [TR-SPEC-101-11-consultas-ui.md](TR-SPEC-101-11-consultas-ui.md) |
| **Fecha** | 21/09/2026 |

## Alcance verificado (PQ)

1. **Consulta detallada (Detalle de pedidos):** columna **Especial** visible en grilla; valor `*` cuando el artículo es especial.
2. **Lookup de artículos (carga):** sufijo **` (*)`** al final de la línea del ítem cuando `especial=true`.

---

## Frontend — Vitest

```text
cd frontend
npm run test -- --run src/features/pedidos/utils/cargaCatalogos.test.ts src/features/consultas/components/consultaColumns.test.tsx
```

| Archivo | Cobertura | AC |
|---------|-----------|-----|
| `cargaCatalogos.test.ts` (+3) | Sufijo ` (*)` sin base, con base, no stockeable | CA-CC17-C01…C03 |
| `consultaColumns.test.tsx` (+1) | Columna `especial` en `DetallePedidosConsultaColumns` | CA-CC17-U01 |

**Resultado:** 12 tests passed (2 archivos).

---

## Frontend — Playwright E2E

```text
cd frontend
npx playwright test tests/e2e/pedidosweb/consultas-d1.spec.ts -g "consulta detalle pedidos: renglón visible"
npx playwright test tests/e2e/pedidosweb/mvp-section9.spec.ts -g "lookup articulos muestra sufijo especial"
```

| Test | Verificación |
|------|----------------|
| `consultas-d1.spec.ts` | `columnheader` **Especial** + `gridcell` `*` en `/pedidos/detalle` |
| `mvp-section9.spec.ts` | Ítem lookup `ART-001` contiene `(*)` tras disponible |

**Resultado:** 2 passed (Chromium).

---

## Backend — PHPUnit

```text
cd backend
php artisan test tests/Unit/PedidosWeb/Services/DetallePedidosConsultaServiceTest.php
php artisan test --filter="consultaDetallePedidosIncluyeEspecialArticulo|buscarBrowseExponeEspecial"
```

| Archivo | Cobertura |
|---------|-----------|
| `DetallePedidosConsultaServiceTest.php` (3) | `resolveEspecialArticulo` → `*`, `""`, sin artículo |
| `PedidosWebEndpointsHappyPathTest.php` (+1) | API detalle pedidos incluye `especial: '*'` |
| `ArticuloCargaLookupServiceTest.php` (+1) | Browse expone clave `especial` boolean |

### Observación entorno (21/09/2026)

PHPUnit unit **4 passed** en F (mapeo `resolveEspecialArticulo` + browse `especial`). Feature `consultaDetallePedidosIncluyeEspecialArticulo` **skipped** sin tenant SQL Server activo.

---

## Veredicto Parte E

**Aprobado con observación** — verificación automática FE completa (Vitest + E2E) sobre los dos puntos pedidos por PQ.

| Ítem PQ | Evidencia |
|---------|-----------|
| 1) Columna **Especial** en consulta detallada | Vitest `consultaColumns` + E2E `consultas-d1` |
| 2) Atributo **Especial** en lookup artículos | Vitest `cargaCatalogos` + E2E `mvp-section9` |

**Pendiente antes de deploy:** feature PHPUnit verde con SQL Server; smoke manual opcional con artículo `especial=1` en tenant.

**Deploy (recordatorio):** `backend/scripts/sql/alter-pq-pedidosweb-articulos-especial.sql` o `PedidosWebSchemaBootstrap::ensureMvpSchema()`.
