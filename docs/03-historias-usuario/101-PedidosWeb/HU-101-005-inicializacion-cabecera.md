# HU-101-005 — Inicialización de cabecera desde cliente

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-005-inicializacion-cabecera |
| **SPEC origen** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **Última actualización** | 2026-06-23 (precarga stock al montar + precios por lista) |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-101-004; contexto SPEC-001-04 (parámetros §10.6 producto) |

## Narrativa

Como **usuario que carga un comprobante**,  
quiero **que la cabecera se complete con los datos habituales del cliente**,  
para **reducir errores y tiempo de carga**.

## Alcance incluido

Al seleccionar cliente, precargar según producto §10.4: vendedor, condición de venta, transporte, dirección de entrega, expreso, nivel, lista de precios, moneda, IVA, bonificación 1, leyendas, perfil (`CodPerfilPedidos`), etc.

## Reglas de negocio

1. Bonificaciones 2 y 3 inician en 0 salvo regla contraria.
2. Observaciones inician vacías.
3. Campos editables de cabecera según parámetros `Modifica*` del **ERP** por tipo de usuario (**C** / **V** / **S**) — producto §10.5–§10.6; lectura vía SPEC-001-04.
4. **Vendedor y supervisor:** bonificación de cliente (`ModificaBonCliV` / `ModificaBonCliS`) y lista de precios (`ModificaListaPrecV` / `ModificaListaPrecS`) **dependen de parámetros prefijados en el ERP**, no de reglas fijas del portal.
5. **Cliente:** no modifica bonificaciones de cabecera ni lista de precios salvo parámetros explícitos para **C** (producto: cliente no modifica precio/lista/descuento artículo en renglón).
6. **CC PQ 04/06/2026:** Tercera bonificación admite **-99,99 a 99,99**; al cambiar lista de precios o bonificaciones con renglones → recalcular precios e importes del detalle; grilla muestra columna **Precio neto unitario** (solo lectura).
7. **CC PQ #3:** Lista de **clientes:** patrón transversal cargando + bloqueo + auto-match único; cache de catálogo por sesión.
8. **CC PQ #3 (artículos):** Al montar pantalla, precarga **stock/disponible** (hasta 10 000 ítems); tras lista de precios en cabecera, consulta **precios** por lista (`solo_catalogo`) y merge en cliente; búsqueda DevExtreme **local** por `codArticulo` y `descripcion`; display **`{codigo} - {descripcion} — Disp. X (Y)`**.
9. **CC PQ #3:** Al cambiar **lista de precios** con renglones → recálculo batch de precios (API `codigos` CSV).
10. **CC PQ #5 / #6 (listbox artículos):** `disponibleNeto = stock − comprometido − comprometido_web` (pedidos ingresados `estado = 0`). Si `articulos.base` ≠ vacío: `disponibleNetoBase = SUM(stock) − SUM(comprometido) − comprometido_base_web` sobre **todas** las presentaciones con la misma `base` ([consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) §5). Entre paréntesis en el ítem: solo `disponibleNetoBase`.

## Criterios de aceptación

- [x] **CA-01 (parcial):** Tras elegir cliente, cabecera muestra valores ERP coherentes con maestra (incl. `cod_perfil` inicial y catálogo `perfiles`).
- [ ] **CA-02:** Cambio de cliente recalcula/reemplaza cabecera (con confirmación si hay renglones).
- [ ] **CA-03:** Campos bloqueados por permiso aparecen deshabilitados, no ocultos sin traza.
- [ ] **CA-04:** Textos de labels vía i18n.
- [x] **CA-CC-01:** Tercera bonificación admite valores **-99,99 a 99,99** (negativos incluidos).
- [x] **CA-CC-02:** Grilla de renglones muestra columna **Precio neto unitario** (solo lectura).
- [x] **CA-CC-03:** Al cambiar lista de precios o bonificación de cabecera con renglones cargados → recálculo sin pérdida de filas.
- [x] **CA-CC3-01:** SelectBox cliente: cargando, bloqueo durante fetch y auto-selección con un solo match.
- [x] **CA-CC3-02:** Carga de clientes mejorada (cache sesión; sin benchmark numérico formal).
- [x] **CA-CC3-03:** Catálogo artículos precargado; búsqueda local por código/descripción (sin API al tipear).
- [x] **CA-CC3-04:** Cambio de lista de precios → recálculo batch sin pérdida de datos.
- [x] **CA-CC3-05:** Lista artículos muestra `codigo - descripcion — Disp. X,XX` (+ base entre paréntesis si aplica).
- [x] **CA-CC3-06:** Sin regresión CA-CC 04/06/2026.
- [x] **CA-CC5-01:** Browse artículos → `disponibleNeto = stock − comprometido − comprometido_web`.
- [x] **CA-CC5-02:** `disponibleNetoBase` con agregados SUM por `articulos.base` (no fila única `cod_articulo = base`).
- [x] **CA-CC5-03:** Display `codigo - descripcion — Disp. X,XX` y `(Y,YY)` con `Y = disponibleNetoBase` si hay base.
- [x] **CA-CC5-04:** Consulta stock mantiene mismas fórmulas §4–§5.
- [x] **CA-CC6-01:** Paréntesis base no muestra `comprometidoBaseWeb` ni stock aislado del código base.
- [x] **CA-CC6-02:** Presentaciones con misma `base` (ej. AC01) comparten el mismo `disponibleNetoBase`.
- [x] **CA-CC6-03:** Implementación en `ArticuloCargaLookupService` alineada con `StockConsultaService` §5.

## Veredicto B1

**Lista para TR** (SPEC-101-10 + lectura maestras 101-02/03).
