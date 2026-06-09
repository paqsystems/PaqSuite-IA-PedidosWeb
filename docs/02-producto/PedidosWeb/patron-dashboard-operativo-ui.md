# Patrón UI — Dashboard operativo PedidosWeb

| Campo | Valor |
|-------|--------|
| **Estado** | Fuente de verdad (vigente) |
| **Ámbito** | Pantalla `/dashboard` (épica 101, HU-101-025) |
| **Referencia visual** | Repo **PaqSuite-IA-Tango**: TR-033 dashboard Producción, `frontend/src/features/partesProduccion/components/DashboardProduccion.tsx`, `frontend/src/app/Dashboard.css` |
| **Contrato datos** | [TR-SPEC-101-14-dashboard](../../04-tareas/101-PedidosWeb/TR-SPEC-101-14-dashboard.md), `GET /api/v1/dashboard/operativo`, `GET /api/v1/dashboard/resumen-mensual` |
| **Implementación** | `frontend/src/features/shell/pages/DashboardPage.tsx`, `DashboardPage.css` |

---

## 1) Objetivo

Presentar los **8 KPIs operativos** §4.1 con un layout **gráfico e impactante**: tarjetas agrupadas por dominio, acentos de color, accesos rápidos y bloque de clientes destacados — sin gráficos Chart obligatorios en MVP (alineado a Producción en tarjetas + listas).

---

## 2) Estructura de pantalla

```
┌─ Header (card) ─────────────────────────────────────────────┐
│ Título + subtítulo + “Actualizado: …”     [Actualizar]      │
│ [Presupuestos ingresados] [Pedidos ingresados] [Pendientes] │
└─────────────────────────────────────────────────────────────┘

┌─ Fila KPI (3 columnas en viewport ≥960px) ─────────────────────────────────────────┐
│ Presupuestos activos │ Pedidos ingresados │ Pedidos pendientes                  │
│ [Q] [$] [Unidades]   │ [Q] [$] [Unidades] │ [Q] [$] [Unidades]                  │
└──────────────────────────────────────────────────────────────────────────────────┘

┌─ Mes en curso por estado ────────────────────────────────────────────────────────┐
│ junio de 2026 (periodo)                                                        │
│ [99 Presupuesto activo] [98 Cerrado] [0 Ingresado] [1 Pendiente] [2] [3]       │
│   Q / $ / Unidades por grupo                                                   │
└────────────────────────────────────────────────────────────────────────────────┘

┌─ Clientes destacados ───────────────────────────────────────┐
│ [Top $ presupuesto]  [Top $ pedidos ingresados]             │
└─────────────────────────────────────────────────────────────┘
```

---

## 3) Reglas de diseño (heredadas de Tango)

| Regla | Detalle |
|-------|---------|
| **Tokens shell** | Usar variables del layout (`--shell-*`) para texto, bordes, acento y peligro; no hardcodear paleta fuera de acentos por grupo KPI. |
| **Secciones en card** | `border-radius: 12px`, sombra suave, borde `1px` — equivalente a `.dashboard-section` en Tango. |
| **Fila de grupos KPI** | `.dashboard-pedidosweb__groups`: `grid` 3 columnas iguales (`repeat(3, minmax(0, 1fr))`); debajo de 960px, 1 columna. |
| **KPI cards por grupo** | Apiladas en vertical (`1fr`) dentro de cada columna; valor numérico ~`1.5rem` / `1.65rem` en importes. |
| **Acento por grupo** | Presupuestos `#6366f1`, ingresados `#0ea5e9`, pendientes `#f59e0b`, top clientes `#10b981`. |
| **Estados** | Loading: bloque centrado; error: `.dashboard-error` con `role="alert"`. |
| **Controles** | Solo DevExtreme `Button` (`outlined` / `default`); textos vía i18n. |
| **Responsive** | En `<640px`, importes ocupan fila completa en grid 2 columnas. |

---

## 4) i18n obligatorio

Claves bajo `dashboard.*` en todos los locales activos (`es`, `en`, `pt`, `it`, `fr`):

- `dashboard.title`, `dashboard.subtitle`, `dashboard.updatedAt`, `dashboard.refresh`
- `dashboard.section.*` (presupuestos, pedidosIngresados, pedidosPendientes, topClientes, **mesEnCurso**)
- `dashboard.link*` (presupuestos, pedidos, pendientes)
- `dashboard.kpi.*` (etiquetas de cada indicador, incl. **estadoCantidad/Importe/Unidades**)
- `consultas.comprobanteEstado.*` (títulos grupos mes en curso)
- `dashboard.emptyTopClient`, `dashboard.loading`, `dashboard.loadError`

---

## 5) `data-testid` estables (QA / E2E)

| Elemento | testid |
|----------|--------|
| Página | `page-dashboard` |
| Título | `dashboardOperativo.titulo` |
| Actualizar | `dashboardOperativo.refresh` |
| Accesos rápidos | `dashboardOperativo.quickLinks` |
| Grupos KPI | `dashboardOperativo.grupo.presupuestos` / `.ingresados` / `.pendientes` |
| KPIs (8 + unidades) | `dashboardKpiPresupuestosCantidad`, `dashboardKpiPresupuestosImporte`, **`dashboardKpiPresupuestosUnidades`**, `dashboardKpiPedidosIngresadosCantidad`, `dashboardKpiPedidosIngresadosImporte`, **`dashboardKpiPedidosIngresadosUnidades`**, `dashboardKpiPedidosPendientesCantidad`, `dashboardKpiPedidosPendientesImporte`, **`dashboardKpiPedidosPendientesUnidades`** |
| Mes en curso | `dashboardOperativo.mesEnCurso`, `dashboardMesEnCurso.periodo`, `dashboardMesEnCurso.estado.{0\|1\|2\|3\|98\|99}`, `dashboardMesEnCurso-{estado}-cantidad\|importe\|unidades` |
| Top clientes | `dashboardTopClientePresupuestos`, `dashboardTopClientePedidos` |
| Error carga | `dashboardLoadError` |
| Navegación | `nav-presupuestos-ingresados`, `nav-pedidos-ingresados`, `nav-pedidos-pendientes` |

Los tests E2E §9 (`mvp-section9.spec.ts`) deben seguir validando los **8 KPIs** por `data-testid`, no por clases CSS de DevExtreme.

---

## 6) Formato de valores

- **Cantidades / unidades:** entero o decimal según métrica; `toLocaleString('en-US')`.
- **Importes:** `toLocaleString('en-US')` con 2 decimales + símbolo de `resultado.moneda.simbolo` (punto decimal fijo en KPIs para coherencia con API/tests).
- **Top cliente:** nombre (`razon_social`) + importe debajo; si vacío → `dashboard.emptyTopClient`.
- **Fecha:** `fechaCalculo` ISO → `dashboard.updatedAt` en locale activo.

---

## 7) Fuera de scope (MVP)

- Gráficos Chart.js / barras (opcional en iteración futura, como en Tango TR-054).
- Filtros de período en dashboard (Producción los tiene; PedidosWeb usa universo visible + instante de cálculo).
- Indicadores conceptuales SPEC §19.

---

## 8) Referencias cruzadas

- Actualizar TR-SPEC-101-14 §6 al cambiar layout o testids.
- Regla Cursor sugerida: `.cursor/rules/dashboard-operativo-ui.mdc` (enlace a este documento).
