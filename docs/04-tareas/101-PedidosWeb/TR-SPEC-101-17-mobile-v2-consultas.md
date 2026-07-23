# TR-SPEC-101-17-mobile-v2-consultas — Consultas kardex mobile v2

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-034-mobile-v2-consultas-kardex](../../03-historias-usuario/101-PedidosWeb/HU-101-034-mobile-v2-consultas-kardex.md) |
| **SPEC** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Release** | `v1.2.1-mobile` |
| **Dependencias** | TR-SPEC-101-17-mobile-v1-*; TR-GEN-11-mobile-shell; TR-SPEC-101-07-consultas-api |
| **Estado** | **D2 implementado** — **F formal 2026-06-30** (smoke Android emulador) |
| **Última actualización** | 2026-06-30 |

---

## 1) Alcance

Consultas MVP en kardex native (sin DataGrid ni pivot):

| Ruta | Página | API |
|------|--------|-----|
| `/consultas/deuda` | `DeudaPage` | `fetchDeuda` |
| `/consultas/cheques` | `ChequesPage` | `fetchCheques` |
| `/consultas/historial` | `HistorialVentasPage` | `fetchHistorialVentas` |
| `/pedidos/detalle` | `DetallePedidosPage` | `fetchDetallePedidos` |
| `/general/parametros` | `ParametrosConsultaPage` | `fetchParametrosConsulta` |
| `/integracion/logs` | `IntegracionLogsPage` | `fetchIntegracionLogs` |

Stock (`/consultas/stock`) permanece en TR v1; refactor a `ConsultaKardexMobileView` modo servidor.

**Fuera de scope v2:** pivot, export Excel, edición parámetros, acciones sobre comprobantes.

---

## 2) Componentes

| Artefacto | Rol |
|-----------|-----|
| `ConsultaKardexMobileView` | Lista kardex genérica (modo `client` / `server`) |
| `ConsultaDetailPopup` | Detalle read-only DevExtreme `Popup` |
| `consultaMobileRenderers.tsx` | Tarjetas y campos detalle por consulta |
| `IntegracionLogsMobileView` | Filtros fecha/severidad + kardex |
| `pedidosWebMobilePolicy.ts` | `mobileV2AllowedRoutePrefixes`, menú y guard |

---

## 3) Criterios de aceptación

| AC | Verificación |
|----|--------------|
| CA-01 | Cada consulta accesible desde menú native si permiso |
| CA-02 | Todas usan kardex (no DataGrid) |
| CA-03 | Sin rutas pivot en native |
| CA-04 | Smoke Android documentado en D-VERIFICACION v2 |

---

## 4) Reglas UX (heredadas v1)

- Filtro `q` con **Enter**; botón refresh (`grid.refresh`); `consultas.resultSummary`.
- Scroll página (`pageScroll`); menú ☰ header derecho; safe area.
- Paginación cliente: slice 20 ítems + «Cargar más»; stock mantiene paginación servidor.

---

## 5) testids

| Pantalla | `data-testid` |
|----------|---------------|
| Deuda | `page-consulta-deuda-mobile` |
| Cheques | `page-consulta-cheques-mobile` |
| Historial | `page-consulta-historial-mobile` |
| Detalle pedidos | `page-detalle-pedidos-mobile` |
| Parámetros | `page-parametros-consulta-mobile` |
| Logs | `page-integracion-logs-mobile` |

---

## 6) Veredicto C1 / D2

**C1:** Apto — [F-101-17-cierre-c1-v2](F-101-17-cierre-c1-v2.md).  
**D2:** Implementado — smoke Android OK (2026-06-30).
