# TR-SPEC-101-17-mobile-v2-listados — Listados kardex mobile v2

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-035-mobile-v2-listados-kardex](../../03-historias-usuario/101-PedidosWeb/HU-101-035-mobile-v2-listados-kardex.md) |
| **SPEC** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Release** | `v1.2.1-mobile` |
| **Dependencias** | TR-SPEC-101-17-mobile-v2-consultas; TR-SPEC-101-07-consultas-api |
| **Estado** | **D2 implementado** — **F formal 2026-06-30** (smoke Android emulador) |
| **Última actualización** | 2026-06-30 |

---

## 1) Alcance

Listados comprobantes en kardex (solo lectura v2):

| Ruta | Componente | API |
|------|------------|-----|
| `/pedidos/ingresados` | `ComprobanteListadoMobileView` | `fetchPedidosIngresados` |
| `/pedidos/pendientes` | `ComprobanteListadoMobileView` | `fetchPedidosPendientes` |
| `/presupuestos/ingresados` | `ComprobanteListadoMobileView` | `fetchPresupuestosActivos` |
| `/presupuestos/tratativas` | `TratativasPage` | Placeholder (igual web) |

**Acciones v2:** tap → popup detalle read-only. **Sin** ver/editar/copiar/eliminar/convertir (v3 + carga mobile).

**Fuera de scope v2:**

- Tab presupuestos **cerrados** (web tiene tabs activos/cerrados).
- Navegación a `/pedidos/carga` desde listado.
- Acciones masivas Excel.

---

## 2) Tarjeta comprobante

- Título: `numero` o `codPedido`
- Subtítulo: `razonSocial`
- Métricas: `fecha`, `importe`
- Detalle popup: cliente, estado, observaciones si aplica

Permisos: mismos flags API (`puedeEditar`, etc.) **no** exponen UI en v2.

---

## 3) Criterios de aceptación

| AC | Verificación |
|----|--------------|
| CA-01 | Listados kardex con paginación cliente |
| CA-02 | Visibilidad menú según perfil (igual web) |
| CA-03 | Tap → detalle popup |

---

## 4) testids

| Pantalla | `data-testid` |
|----------|---------------|
| Pedidos ingresados | `page-pedidos-ingresados-mobile` |
| Pedidos pendientes | `page-pedidos-pendientes-mobile` |
| Presupuestos ingresados | `page-presupuestos-ingresados-mobile` |

---

## 5) Roadmap v3 (acciones)

Tras **HU-101-036** (carga mobile): habilitar acciones según `puede*` → `/pedidos/carga?modo=ver|editar|copia|convertir`. Definir UX en TR v3.

---

## 6) Veredicto C1 / D2

**C1:** Apto — [F-101-17-cierre-c1-v2](F-101-17-cierre-c1-v2.md).  
**D2:** Implementado — smoke Android OK (2026-06-30).
