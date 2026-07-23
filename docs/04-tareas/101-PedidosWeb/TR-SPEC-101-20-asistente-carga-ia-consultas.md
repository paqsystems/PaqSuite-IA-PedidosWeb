# TR-SPEC-101-20 — Asistente IA carga: consultas conversacionales

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-101-041](../../03-historias-usuario/101-PedidosWeb/HU-101-041-asistente-carga-ia-consulta-stock.md) · [HU-101-042](../../03-historias-usuario/101-PedidosWeb/HU-101-042-asistente-carga-ia-consultas-cliente.md) |
| **SPEC relacionada** | [SPEC-101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Dependencias** | [TR-SPEC-101-18](TR-SPEC-101-18-asistente-carga-ia-shell.md); TR-SPEC-101-07; HU-101-018/021/022/023 |
| **Estado** | **C1 cerrado** — apto D1 (2026-07-13) |
| **Última actualización** | 2026-07-13 |

**Normas:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)  
**Cierre C1:** [F-101-18-20-cierre-c1](F-101-18-20-cierre-c1-asistente-carga-ia.md)

---

## 1) Resumen

Tools de **solo lectura** E–H sobre el turno TR-18. Datos siempre desde APIs/services de consultas existentes. No mutan `draftContext`.

### Out of scope
- Mutaciones / grabar / imagen apply → TR-19.
- Export Excel / pivot desde el chat.

---

## 2) AC

| Capacidad | AC | API |
|-----------|-----|-----|
| E Stock | 041 CA-01…07 | `GET /api/v1/consultas/stock` |
| F Deuda | 042 CA-01…04 | `GET /api/v1/consultas/deuda` |
| G Cheques | 042 CA-05…07 | `GET /api/v1/consultas/cheques` |
| H Historial | 042 CA-08…10 | `GET /api/v1/consultas/historial-ventas` |

---

## 3) Tools

| Tool | Input | Reglas |
|------|-------|--------|
| `consultaStock` | `q: string` | Permiso stock; si `total > 10` → `needsRefine`; else list mapping SPEC + totales pie |
| `consultaDeuda` | usa `draftContext.codCliente` | Sin cliente → reply pedir A; columnas D1-21 |
| `consultaCheques` | `codCliente` | Columnas D1-19; tope 10 + total |
| `consultaHistorialVentas` | `codCliente` | Columnas D1-20; `DiasVentasDetalladas`; tope 10 + total |

**Action de presentación:** `showConsulta` con:

```json
{
  "kind": "stock|deuda|cheques|historial",
  "items": [],
  "total": 0,
  "totals": {},
  "columns": []
}
```

FE renderiza `showConsulta` como **tabla HTML** alineada en el hilo (`data-testid=cargaAsistenteIaConsultaTable`); fechas F/G solo `YYYY-MM-DD`; si >1 ítem en F/G → pie con total saldo/importe (D1-23…25).

### Decisiones C1

| ID | Decisión |
|----|----------|
| T-20-01 | Tools llaman **services PHP** de consultas (no HTTP interno); mismos filtros de visibilidad |
| T-20-02 | Stock refine usa campo **`total`** del service (D1-07), no `count(page)` |
| T-20-03 | F–H: `take(10)` para items; `total` = conteo real filtrado |
| T-20-04 | Fechas F/G en chat: solo fecha `YYYY-MM-DD` (sin horario); TZ/format base = consultas UI (`SPEC-101-11`) |
| T-20-05 | Sin permiso → `denied` + clave i18n; sin inventar números |
| T-20-06 | Decimales stock: **2** |
| T-20-07 | Presentación panel: componente tabla HTML (no ASCII `|`) |
| T-20-08 | Totales pie F/G si `items.length > 1` |

---

## 4) Mapping stock (obligatorio en respuesta)

Campos por ítem (camelCase JSON):

`codArticulo`, `descripcion`, `stock`, `comprometido`, `comprometidoWeb`, `disponibleNeto`, `codBase`, `stockBase`, `comprometidoBase`, `comprometidoBaseWeb`, `disponibleNetoBase`

`totals` pie: suma de `stock`, `comprometido`, `comprometidoWeb`, `disponibleNeto` de **items listados** (no *Base*).

---

## 5) Columnas chat F–H

| kind | columns (orden) |
|------|-----------------|
| deuda | `tipoNro`, `fecha`, `vencimiento`, `saldo` |
| cheques | `nro`, `fecha`, `importe` |
| historial | `descripcionArticulo`, `cantidad`, `precioUnitarioNeto`, `importe` |

Mapear desde DTOs existentes de cada consulta en el tool (nombres internos → estos alias en `showConsulta`).

---

## 6) Cambios código

### Backend

| Pieza | Detalle |
|-------|---------|
| Tools | `ConsultaStockTool`, `ConsultaDeudaTool`, `ConsultaChequesTool`, `ConsultaHistorialTool` |
| Reuso | `StockConsultaService`, services deuda/cheques/historial |
| Permisos | Mismos checks que controllers de consultas |

### Frontend

| Pieza | Detalle |
|-------|---------|
| apply | `showConsulta` → tabla HTML en hilo (`CargaAsistenteConsultaTable`) |
| testid | `cargaAsistenteIaConsultaTable` (+ opcional por kind) |
| i18n | Labels columnas `carga.asistente.consulta.*` / `pedidos.carga.asistente.*` |

---

## 7) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T20-1 | BE | Tool stock + mapping + total>10 | CA 041 |
| T20-2 | BE | Tools F–H + sin cliente + tope 10 | CA 042 |
| T20-3 | FE | Formatter showConsulta | UI |
| T20-4 | Test | Unit mapping stock fixture; feature permiso denied | |

**Orden:** después de T18-2; puede paralelizar con TR-19.

---

## 8) Tests

| Caso | Esperado |
|------|----------|
| stock total 4 | 4 items + totals |
| stock total 15 | needsRefine, items [] |
| deuda sin cliente | mensaje pedir cliente |
| cheques sin permiso | denied |
| historial | 4 columnas D1-20 |

---

## 9) Checklist normas

- [ ] No endpoints nuevos (tools internos)
- [ ] No mutación draft en tools
- [ ] i18n en `respuesta` / reply keys
- [ ] OpenAPI: sin cambio salvo descripción del turn que mencione tools de consulta

---

## Veredicto C1

**Apto para D1**. Ver cierre C1.
