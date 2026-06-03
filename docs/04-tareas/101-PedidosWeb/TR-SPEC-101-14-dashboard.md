# TR-SPEC-101-14 — Dashboard operativo (8 KPIs)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-025-dashboard](../../03-historias-usuario/101-PedidosWeb/HU-101-025-dashboard.md) |
| **SPEC relacionada** | [SPEC-101-14-dashboard](../../05-open-spec/101-PedidosWeb/SPEC-101-14-dashboard.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | TR-SPEC-101-06-seguridad-visibilidad; TR-SPEC-101-07-consultas-api (queries coherentes); parámetro `MinutosWeb` (SPEC-001-04); reemplaza demo `GET /api/v1/dashboard/resumen` (GEN-02) |
| **Estado** | Pendiente de Revisión — **Bloque 4** |
| **Última actualización** | 2026-06-02 |

**Origen:** [HU-101-025-dashboard](../../03-historias-usuario/101-PedidosWeb/HU-101-025-dashboard.md)  
**Referencia SPEC:** [SPEC-101-14-dashboard](../../05-open-spec/101-PedidosWeb/SPEC-101-14-dashboard.md), [PedidosWeb_SPEC_MVP.md §4.1](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Ocho indicadores operativos §4.1 con visibilidad por perfil y regla de exclusión de pedidos -1 en modificación activa.

### Narrativa
Como **usuario comercial**, quiero **ver KPIs al entrar al dashboard**, para **monitorear pedidos y presupuestos de mi universo visible**.

### In scope / Out of scope
- **In scope:** 8 indicadores; una moneda por tenant; visibilidad igual que consultas; sustituir/extender `GET /dashboard/resumen` demo.
- **Out of scope:** indicadores conceptuales §19 (tasa cierre, ranking motivos, CORE, tops genéricos).

---

## 2) Criterios de Aceptación (AC)

- **AC-01:** Los **8 indicadores** visibles tras login con datos seed:

| # | Indicador | Definición |
|---|-----------|------------|
| 1 | Q presupuestos activos | Cantidad estado **99** visible |
| 2 | $ presupuestos activos | Suma totales cabecera estado **99** |
| 3 | Q pedidos ingresados | Cantidad estado **0** y **-1** con regla exclusión §4.1 |
| 4 | $ pedidos ingresados | Suma totales mismos comprobantes que (3) |
| 5 | Q pedidos pendientes | Cantidad estado **1** |
| 6 | $ pedidos pendientes | Suma totales estado **1** |
| 7 | Cliente mayor $ presupuesto | Cliente con mayor suma totales presup. **99** visible |
| 8 | Cliente mayor $ pedidos ingresados | Cliente con mayor suma totales pedidos ingresados (**0**, regla -1 en (3)) |

- **AC-02:** Coherentes con consultas HU-015/016/017 para mismo usuario y fecha.
- **AC-03:** Un solo símbolo moneda por tenant en UI (sin conversión).
- **AC-04:** **Regla -1 (AMB-C09):** incluir estado **-1** en KPI pedidos ingresados **excepto** comprobantes donde  
  `fechahora_ultima_actividad + MinutosWeb >= fechahora_actual`  
  (modificación -1 activa; alineado HU-101-011). Estado **0** siempre cuenta.
- **AC-05:** Presupuestos activos = solo **99** (excluye **98**).
- **AC-06:** Top clientes (indicadores 7 y 8): si empate en el métrico principal, desempate por **`razonSocial` ascendente (A–Z)**; si persiste empate, por `cod_client` ascendente.
- **AC-07:** E2E flujo §9 paso 8 — dashboard verde con datos del flujo.
- **AC-08:** Feature test `GET` dashboard + 401/403.

### Escenarios Gherkin

```gherkin
Feature: Dashboard operativo

  Scenario: KPIs con datos seed supervisor
    Given pedidos y presupuestos visibles para supervisor
    When GET /api/v1/dashboard/operativo
    Then resultado incluye los 8 indicadores con valores > 0 donde aplique

  Scenario: Excluir pedido -1 en ventana activa
    Given pedido estado -1 con fechahora_ultima_actividad reciente
    And MinutosWeb = 30
    When calcula Q pedidos ingresados
    Then ese comprobante no incrementa el KPI

  Scenario: Incluir pedido -1 fuera de ventana
    Given pedido -1 con ultima actividad hace mas de MinutosWeb
    When calcula Q pedidos ingresados
    Then el comprobante cuenta en el KPI
```

---

## 3) Reglas de Negocio

1. **RN-01:** Visibilidad `visibleClientsForUser` + perfil (cliente/vendedor/supervisor) = consultas.
2. **RN-02:** `$` = suma `importe_total` (o campo cabecera acordado) sin conversión de moneda.
3. **RN-03:** `MinutosWeb` leído de parámetros ERP; default documentado en seed si ausente.
4. **RN-04:** Comparación temporal: `fechahora_actual` del servidor SQL/ aplicación consistente con HU-101-011.
5. **RN-05:** Top cliente pedidos ingresados: universo estados **0** + **-1** tras aplicar RN exclusión -1 (SPEC madre §4.1 menciona 0 en top; alinear query con indicadores 3–4 — usar **mismo conjunto** que Q/$ pedidos ingresados).

---

## 4) Impacto en Datos

### Tablas afectadas
- Lectura: `pq_pedidosweb_pedidoscabecera`, clientes, parámetros `MinutosWeb`
- Sin DDL dedicado (agregaciones en service/repository)

### Seed mínimo para tests
- Presupuestos 99 con totales distintos por cliente
- Pedidos 0, 1, -1 (dentro y fuera ventana `MinutosWeb`)
- Parámetro `MinutosWeb` conocido (ej. 30)

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| GET | `/api/v1/dashboard/operativo` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` + visibilidad §7.3 | No |

*Deprecar o redirigir demo `GET /api/v1/dashboard/resumen` hacia este contrato.*

### 5.2 Detalle por operación

#### GET `/api/v1/dashboard/operativo`

**Autorización:** `Permiso_Repo` + `visibleClientsForUser`

**Response 200 — `resultado`:**

```json
{
  "moneda": { "simbolo": "$", "codigo": "ARS" },
  "presupuestosActivos": { "cantidad": 0, "importe": 0 },
  "pedidosIngresados": { "cantidad": 0, "importe": 0 },
  "pedidosPendientes": { "cantidad": 0, "importe": 0 },
  "topClientePresupuestos": { "cod_client": "", "razon_social": "", "importe": 0 },
  "topClientePedidosIngresados": { "cod_client": "", "razon_social": "", "importe": 0 },
  "fechaCalculo": "2026-06-01T12:00:00Z"
}
```

**Response 401 / 403:** estándar MONO

**OpenAPI (L5-Swagger):**

- [ ] Sustituye documentación de `/dashboard/resumen` demo
- [ ] `security`, `X-Paq-Cliente`, 401, 403
- [ ] Descripción regla -1 en `description`

### 5.3 Actualización matriz permisos

- [ ] Reemplazar fila `GET /api/v1/dashboard/resumen` por `/dashboard/operativo` o alias documentado

---

## 6) Cambios Frontend

### Pantallas / componentes
- `DashboardPage` + `DashboardPage.css` (menú ítem 10, ruta `/dashboard`)
- Layout **gráfico** alineado a PaqSuite-IA-Tango `DashboardProduccion` (secciones card, KPIs en grid, acentos por grupo, accesos rápidos, top clientes)
- **Fuente de verdad UI:** [`docs/02-producto/PedidosWeb/patron-dashboard-operativo-ui.md`](../../02-producto/PedidosWeb/patron-dashboard-operativo-ui.md)
- Mostrar 8 KPIs + moneda + `fechaCalculo` (“Actualizado”)
- i18n: `dashboard.title`, `dashboard.subtitle`, `dashboard.section.*`, `dashboard.link*`, `dashboard.kpi.*`
- Sin gráficos Chart obligatorios MVP
- Controles: DevExtreme `Button` (Actualizar + navegación rápida)

### data-testid (obligatorios — ver patrón UI)
- `page-dashboard`, `dashboardOperativo.titulo`, `dashboardOperativo.refresh`, `dashboardOperativo.quickLinks`
- `dashboardOperativo.grupo.presupuestos` | `.ingresados` | `.pendientes`
- `dashboardKpiPresupuestosCantidad`, `dashboardKpiPresupuestosImporte`
- `dashboardKpiPedidosIngresadosCantidad`, `dashboardKpiPedidosIngresadosImporte`
- `dashboardKpiPedidosPendientesCantidad`, `dashboardKpiPedidosPendientesImporte`
- `dashboardTopClientePresupuestos`, `dashboardTopClientePedidos`
- `nav-presupuestos-ingresados`, `nav-pedidos-ingresados`, `nav-pedidos-pendientes`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | `DashboardService` agregaciones + regla -1 | AC-04 |
| T2 | Backend | Controller GET operativo | OpenAPI |
| T3 | Frontend | Tarjetas 8 KPIs | AC-01 |
| T4 | Tests | Feature + coherencia con consultas | AC-02 |
| T5 | Tests | E2E §9 paso 8 | AC-07 |
| T6 | Docs | Matriz permisos | Checklist |

---

## 8) Estrategia de Tests

- **Unit:** Función exclusión -1 con fechas mock; top cliente empate.
- **Integration:** GET 200 supervisor; 403 `usuario.sinPermiso.mvp`; comparar totales vs API consultas.
- **E2E:** Login → dashboard → assert KPIs tras flujo §9.

---

## 9) Riesgos y Edge Cases

- Desfase reloj servidor vs SQL en comparación `fechahora_actual`.
- Demo `resumen` consumida por tests viejos → migrar fixtures.
- Top cliente sin datos → campos vacíos/cero sin error.

---

## 10) Checklist final

### Checklist del slice
- [x] 8 KPIs + regla -1
- [ ] Coherencia consultas (integración tanda 2)
- [x] E2E §9 paso dashboard (mock KPIs)

### Checklist normas transversales

- [ ] Policy + matriz
- [ ] OpenAPI
- [ ] Envelope
- [ ] Tests 401/403
- [ ] Sin indicadores §19

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- `DashboardService`, `DashboardController`

### Frontend
- `DashboardPage.tsx`, `DashboardPage.css`
- `docs/02-producto/PedidosWeb/patron-dashboard-operativo-ui.md`
- `.cursor/rules/dashboard-operativo-ui.mdc`

### OpenAPI
- GET `/api/v1/dashboard/operativo`

### Docs
- Matriz — actualizar fila dashboard
