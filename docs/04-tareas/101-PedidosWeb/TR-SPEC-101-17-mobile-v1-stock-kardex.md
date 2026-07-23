# TR-SPEC-101-17-mobile-v1-stock-kardex — Consulta stock kardex mobile

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-033-mobile-consulta-stock-kardex](../../03-historias-usuario/101-PedidosWeb/HU-101-033-mobile-consulta-stock-kardex.md) |
| **SPEC relacionada** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Producto** | [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) |
| **Épica** | 101 — PedidosWeb / Mobile |
| **Prioridad** | Must |
| **Release** | `v1.2.0-mobile` |
| **Dependencias** | TR-SPEC-101-17-mobile-v1-login-tenant; TR-GEN-11-mobile-shell; TR-SPEC-101-07-consultas-api; TR-SPEC-101-11-consultas-ui (referencia web) |
| **Estado** | **D1 implementado** — **F formal 2026-06-30** (smoke Android emulador validado) |
| **Última actualización** | 2026-06-30 (post-D + smoke QA) |

**Origen:** [HU-101-033](../../03-historias-usuario/101-PedidosWeb/HU-101-033-mobile-consulta-stock-kardex.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Consulta stock en vista kardex (tarjetas) para app mobile; misma API web sin cambios backend.

### Narrativa
Como usuario comercial mobile, quiero ver stock en tarjetas para consultar disponibilidad sin grilla desktop.

### In scope / Out of scope
- **In scope:** kardex, filtro `q`, paginación, actualizar, popup detalle, carátula `fecha_proceso`, permiso `pw_consultastock`, menú v1 único ítem stock.
- **Out of scope:** DataGrid web en native, pivot, export Excel, cambios API.

---

## 2) Criterios de aceptación (AC)

| AC | Verificación |
|----|--------------|
| CA-01 | Kardex con ≥1 artículo tras login con permiso |
| CA-02 | Tap → popup detalle read-only |
| CA-03 | Filtro `q` reduce resultados (aplicar con **Enter** en campo búsqueda) |
| CA-04 | Actualizar re-fetch servidor |
| CA-05 | Sin permiso → 403 UI clara |
| CA-06 | `data-testid="page-consulta-stock-mobile"` |
| CA-07 | Sin toggle grilla/pivot |

---

## 3) Reglas de negocio

1. **RN-01:** API existente `GET /api/v1/consultas/stock` — [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) §6.
2. **RN-02:** Tarjeta resumen (D1-11): `codArticulo`, `descripcion`, `disponibleNeto`, `stock`.
3. **RN-03:** Detalle popup: `comprometido`, `comprometidoWeb`, `codBase`, métricas `*Base` si no null.
4. **RN-04:** Formato `#,##0.00` en cantidades.
5. **RN-05:** Carátula `metadata.fecha_proceso` bajo título (AMB-M-101-17-02).
6. **RN-06:** Menú v1 `mobileMenuPolicy`: único ítem operativo consulta stock (D1-8) — filtrar resto en native v1 además de exclusiones GEN.
7. **RN-07:** Paginación: botón «Cargar más» incrementando `page` (default `page_size=20`).

### v1 menú restringido (PedidosWeb)

Además de exclusiones SPEC-001-11, en native v1 **ocultar** ítems menú distintos de stock hasta v2:

```typescript
export const mobileV1AllowedRoutes = ['/consultas/stock'] as const;
```

Implementar en capa producto (`pedidosWebMobilePolicy.ts`) sobre `mobileMenuPolicy`.

---

## 3.1) Informe C1 (2026-06-30)

**Apto** — backend sin cambios confirmado A1; componente kardex reutilizable v2.

| ID | Resolución |
|----|------------|
| D1-11 | Campos tarjeta cerrados |
| D1-15 | Sin export Excel v1 |
| D1-16 | Permiso `pw_consultastock` |

---

## 3.2) Plan D1 — Componentes

### `ConsultaKardexList<T>` (shared)

Ubicación: `frontend/src/shared/consultas/ConsultaKardexList.tsx`

| Prop | Tipo | Descripción |
|------|------|-------------|
| `items` | `T[]` | Filas actuales |
| `renderCard` | `(item: T) => ReactNode` | Contenido tarjeta |
| `onItemClick` | `(item: T) => void` | Abrir detalle |
| `isLoading` | boolean | Estado carga |
| `onLoadMore` | `() => void` | Paginación |
| `hasMore` | boolean | Hay más páginas |
| `onRefresh` | `() => void` | Re-fetch servidor |
| `hideToolbar` | `boolean` | Ocultar toolbar interno (stock mobile: refresh en fila filtro) |
| `pageScroll` | `boolean` | Default `true`: lista **HTML** (`<ul>`) + scroll del `shellMain` (Capacitor WebView). `false`: DevExtreme `List` con scroll interno |

**Decisión post-smoke (Capacitor Android):** `pageScroll=true` evita que el `dx-scrollable` del `List` capture gestos y recorte ítems. En v2 se puede reevaluar scroll infinito interno.

### `StockMobileView`

- Ruta: `/consultas/stock` con branch `isNativeApp()` en `StockPage.tsx`.
- **No** montar `DataGridDx` en native.
- Filtro: `TextBox` + aplicar `q` con tecla **Enter** (no debounce automático en v1).
- Fila superior: buscador (`width 100%`) + botón refresh (`grid.refresh`, `data-testid="gridRefresh"`).
- Resumen: i18n `consultas.resultSummary` — «Mostrando {shown} de {total}» (`data-testid="stockResultSummary"`).
- Popup detalle: DevExtreme `Popup` solo lectura.

### API client

Reutilizar `consultaApi.ts` / función stock existente:

```typescript
GET /consultas/stock?page=&page_size=&q=
```

Header tenant desde TR login.

---

## 4) Impacto en datos

**N/A** — lectura `pq_pedidosweb_stock` vía servicio existente.

---

## 5) Contratos de API y OpenAPI

**Sin endpoints nuevos.** Verificar existente en [TR-SPEC-101-07-consultas-api.md](TR-SPEC-101-07-consultas-api.md).

### 5.1 Endpoint

| Método | Path | Auth | Permiso |
|--------|------|------|---------|
| GET | `/api/v1/consultas/stock` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` / `pw_consultastock` |

### 5.2 Query params

| Param | Tipo | Descripción |
|-------|------|-------------|
| `page` | int | Default 1 |
| `page_size` | int | Max 100; default 20 mobile |
| `q` | string | Filtro código/descripción |

### 5.3 Response 200 (extracto)

```json
{
  "error": 0,
  "respuesta": "ok",
  "resultado": {
    "items": [
      {
        "codArticulo": "ART-001",
        "descripcion": "Producto demo",
        "stock": 100,
        "comprometido": 10,
        "comprometidoWeb": 5,
        "disponibleNeto": 85,
        "codBase": null,
        "stockBase": null,
        "comprometidoBase": null,
        "comprometidoBaseWeb": null,
        "disponibleNetoBase": null
      }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1,
    "metadata": { "fecha_proceso": "2026-06-30" }
  }
}
```

### 5.4 OpenAPI / matriz

- [ ] **Sin cambios** — endpoint ya documentado TR-101-07.
- [ ] Tests 401/403 existentes siguen vigentes.

---

## 6) Cambios frontend

| Archivo | Acción |
|---------|--------|
| `ConsultaKardexList.tsx` | **Nuevo** shared |
| `StockPage.tsx` o `StockMobileView.tsx` | Branch native |
| `StockDetailPopup.tsx` | **Nuevo** |
| `pedidosWebMobilePolicy.ts` | Menú v1 solo stock |
| `consultaApi.ts` | Reutilizar fetch stock |
| i18n | Reutilizar `consultas.column.*` |

### data-testid

| testid | Elemento |
|--------|----------|
| `page-consulta-stock-mobile` | Contenedor página native |
| `stockKardexList` | Lista |
| `stockFilterQ` | Filtro búsqueda |
| `gridRefresh` | Actualizar (en fila filtro en native) |
| `stockResultSummary` | Contador «Mostrando X de Y» |
| `stockDetailPopup` | Popup detalle |
| `consultaKardexLoadMore` | Botón cargar más |

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Frontend | `ConsultaKardexList` genérico | Render lista |
| T2 | Frontend | Stock mobile view + API | CA-01 |
| T3 | Frontend | Popup detalle | CA-02 |
| T4 | Frontend | Filtro q + paginación | CA-03 |
| T5 | Frontend | Refresh servidor | CA-04 |
| T6 | Frontend | Manejo 403 | CA-05 |
| T7 | Frontend | Menú v1 solo stock | D1-8 |
| T8 | Frontend | Carátula fecha_proceso | AMB-M-101-17-02 |
| T9 | Tests | Unit card mapper | Opcional |
| T10 | E2E/Manual | Smoke login → stock kardex | Pre-tag release |

---

## 8) Estrategia de tests

- **Unit:** formatter `#,##0.00`; mapper tarjeta.
- **Integration:** reutilizar tests stock backend — sin cambios.
- **E2E manual (obligatorio v1):** Android + iOS — login `desarrollo` → lista stock → detalle → filtro.
- **E2E Playwright:** opcional con viewport mobile browser (no sustituye Capacitor smoke).

---

## 9) Riesgos

| Riesgo | Mitigación |
|--------|------------|
| Duplicar lógica StockPage web | Branch claro `isNativeApp()` |
| Performance listas grandes | Paginación server-side |
| Usuario sin permiso stock | Empty state + mensaje 403 |

---

## 10) Checklist final

- [x] CA-01 … CA-07 (smoke Android emulador)
- [x] Sin DataGrid/pivot en native stock
- [x] i18n `grid.refresh`, `consultas.resultSummary`

### Checklist normas transversales

- [x] Endpoint existente — OpenAPI ya en TR-101-07
- [x] Envelope respetado
- [x] X-Paq-Cliente en requests

---

## Archivos creados/modificados (post-D)

### Frontend
- `frontend/src/shared/consultas/ConsultaKardexList.tsx` — `pageScroll`, lista HTML native
- `frontend/src/shared/consultas/consultaKardexList.css`
- `frontend/src/features/consultas/components/StockMobileView.tsx`
- `frontend/src/features/consultas/components/StockDetailPopup.tsx`
- `frontend/src/features/consultas/pages/StockPage.tsx` — branch native
- `frontend/src/features/mobile/pedidosWebMobilePolicy.ts`
- `frontend/src/features/consultas/api/consultaApi.ts` — `fetchStockPage`

### Docs
- Actualizar manual usuario mobile (opcional post-F)
