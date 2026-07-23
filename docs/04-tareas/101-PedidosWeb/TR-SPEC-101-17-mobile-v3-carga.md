# TR-SPEC-101-17-mobile-v3-carga — Carga pedidos mobile v3

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-036-mobile-v3-carga-pedidos](../../03-historias-usuario/101-PedidosWeb/HU-101-036-mobile-v3-carga-pedidos.md) |
| **SPEC** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Release** | `v1.2.2-mobile` |
| **Dependencias** | TR-SPEC-101-17-mobile-v2-*; SPEC-101-10; API `comprobanteApi` |
| **Estado** | **F** |
| **Última actualización** | 2026-07-01 |
| **Cierre F** | [F-101-17-cierre-formal-v3](F-101-17-cierre-formal-v3.md) |
| **Verificación F1** | [D-VERIFICACION-101-17-mobile-v3](D-VERIFICACION-101-17-mobile-v3.md) |

---

## 1) Alcance

Pantalla mobile dedicada `/pedidos/carga` — **wizard por pasos** (no wrapper `PedidosCargaPage` desktop).

| Paso | Contenido |
|------|-----------|
| 1 Cliente | `SelectBox` clientes (vendedor/supervisor); cliente fijo perfil `C` |
| 2 Cabecera | `ComprobanteCabeceraForm` + leyendas pie + observaciones (reutiliza web; layout mobile) |
| 3 Artículos | Tarjetas renglón; agregar con `SelectBox`; editar en `Popup` (reutiliza `PedidosCargaRenglonEditDialog`) |
| 4 Confirmar | Totales; `Grabar pedido` / `Grabar presupuesto` / `Cancelar` |

**Modos URL:** `modo=nuevo|ver|editar|copia|convertir` + `codComprobante` (igual web).

**Acciones listados v3:** iconos en tarjeta kardex (ver / editar / copiar / eliminar / convertir según `puede*`) con tooltip táctil; popup detalle solo lectura al tap en cuerpo de tarjeta.

**Fuera de scope v3:**

- Importación Excel.
- Paridad cabecera desktop (leyendas 1–5, bonif. editables si ERP lo permite en web completo).
- Pivot, admin.

---

## 2) Componentes

| Artefacto | Rol |
|-----------|-----|
| `PedidosCargaMobilePage` | Wizard + orquestación |
| `usePedidosCargaMobile` | Estado, API, grabación (subset desktop) |
| `pedidosWebMobilePolicy` | Ruta `/pedidos/carga` en native |
| `ComprobanteCardMobileActions` | Iconos + tooltips en tarjeta kardex |
| `useComprobanteMobileRowActions` | Acciones por listado (ingresados, pendientes, presupuestos) |
| `ComprobanteListadoMobileView` | Orquesta kardex + `renderCardActions` |
| Reutilizados | `PedidosCargaRenglonEditDialog`, `PedidosCargaConfirmacionDialog`, `PedidosCargaErroresGrabacionDialog`, `PedidosCargaArticulosStockLoadPanel` |

---

## 3) API / reglas

- Mismos endpoints que web: `fetchClientes`, `fetchCabeceraInicial`, `fetchArticulosStockCatalogoCarga`, `fetchArticulosPreciosCatalogoCarga`, `grabarComprobante`, `fetchComprobante`, `iniciarEdicionPedido`, `cancelarEdicionPedido`.
- Validaciones grabado backend (HU-101-009/010).
- Parámetros ERP (`modificaPrecio`, `modificaBonArt`, …) desde `fetchParametrosCarga`.

---

## 4) Criterios de aceptación

| AC | Verificación |
|----|--------------|
| CA-01 | Crear pedido nuevo smoke mobile |
| CA-02 | Crear presupuesto smoke mobile |
| CA-03 | Perfiles C/V/S según reglas web |
| CA-04 | Tag release `v1.2.2-mobile` |

---

## 5) testids

| Elemento | `data-testid` |
|----------|---------------|
| Página | `page-pedidos-carga-mobile` |
| Paso cliente | `carga-mobile-step-cliente` |
| Paso cabecera | `carga-mobile-step-cabecera` |
| Paso artículos | `carga-mobile-step-articulos` |
| Paso confirmar | `carga-mobile-step-confirmar` |
| Nav anterior/siguiente | `carga-mobile-btn-prev`, `carga-mobile-btn-next` |
| Acciones tarjeta listado | `comprobante-card-actions`, `gridAction-{ver,editar,...}` |

---

## 6) Veredicto

**F:** Cerrado — [F-101-17-cierre-formal-v3](F-101-17-cierre-formal-v3.md). Tag `v1.2.2-mobile` pendiente smoke iOS y dispositivo físico.
