# SPEC-101-10 — Pantalla de carga pedido/presupuesto

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Especificado |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-06-17 (disponible base agregado — alineación consulta stock) |

## Objetivo

Pantalla **única** pedido/presupuesto: mismo flujo transaccional; cabecera, renglones, totales, grabación y **copia** de comprobante (producto §10).

## In scope

- **Una sola pantalla** para carga y edición de pedido y presupuesto (AMB-C11).
- Botones DevExtreme visibles: **`Grabar pedido`**, **`Grabar presupuesto`**, **`Cancelar`** (`data-testid` estables).
- Matriz de transiciones (producto §10.1): alta pedido/presupuesto; pedido→pedido; presupuesto→presupuesto; pedido→presupuesto; presupuesto→pedido (cierra origen en **98**).
- Entrada: nuevo, edición (**0** / **-1** / **99**), copia de comprobante.
- Selección cliente (vendedor/supervisor; cliente fijo): formato `(codigo) {razonSocial} - {nombreFantasia}`; ordenamiento por código, razón social o nombre fantasía.
- Cabecera/renglones según producto §10; **precio y descuentos** según parámetros ERP **V** / **S** — ver § Permisos precio/descuento.
- Tercera bonificación de cabecera: rango **-99,99 a 99,99**; al cambiar lista de precios o bonificaciones con renglones cargados → recalcular precios e importes del detalle.
- Autocompletar artículos **excluyendo** `pq_pedidosweb_articulos.usa_esc = 'B'` (artículos BASE); cálculo en tiempo real.
- Columna **Precio neto unitario** en grilla de renglones (solo lectura); persistencia en `pq_pedidosweb_pedidosdetalle.precio_neto`.
- Mail post-grabación (101-13); identificación visual pedido vs presupuesto.
- DevExtreme; i18n.

## Permisos precio y descuento (parámetros ERP)

Para **vendedor** (`V`) y **supervisor** (`S`), el portal **no** define en código si pueden cambiar precios o descuentos: lo define el **ERP** mediante parámetros generales sincronizados en la base del tenant (producto §10.6), leídos en runtime (SPEC-001-04).

| Ámbito | Parámetros ERP (sufijo **V** = vendedor común, **S** = supervisor) |
|--------|---------------------------------------------------------------------|
| Precio en renglón | `ModificaPrecioV`, `ModificaPrecioS` |
| Descuento/bonificación en renglón | `ModificaBonArtV`, `ModificaBonArtS` |
| Bonificación de cabecera (cliente) | `ModificaBonCliV`, `ModificaBonCliS` |
| Lista de precios (cabecera) | `ModificaListaPrecV`, `ModificaListaPrecS` |

- **Cliente** (`C`): sin modificar precio, lista ni descuentos de artículo (producto §10.7).
- UI: campos **deshabilitados** cuando el parámetro correspondiente no lo permite; misma regla en backend al grabar.

Fuente: producto §10.7 (permisos) y §10.6 (parámetros ERP).

## Fuera de scope

- DELETE presupuesto
- Tratativas (101-12, Should)
- ABM web de parámetros ERP (administración solo ERP/herramientas internas)

## Dependencias

- SPEC-101-05, SPEC-101-09
- GEN-03 (grillas en listados, no necesariamente en editor renglones)

## HU relacionadas

HU-101-005…010, copia (B), HU-101-011, HU-101-012 (solo pedido delete)

## Precio neto unitario (carga / consultas / mail)

| Concepto | Definición |
|----------|------------|
| **Precio neto unitario** | Precio de lista del renglón menos descuento de renglón y descuento de cabecera (bonificaciones HU-101-007/008). |
| **Persistencia** | Campo existente `pq_pedidosweb_pedidosdetalle.precio_neto`; recalcular y persistir al grabar/actualizar renglón. |
| **UI** | Visible en grilla de carga; no editable salvo recálculo por cambio de cabecera/renglón. |

## Definición de listo

- [ ] E2E camino feliz carga pedido (§9 madre)
- [ ] Copia desde comprobante existente verificada
- [x] CC PQ 04/06/2026: cliente, bonif. 3, exclusión BASE (`usa_esc = 'B'`), precio neto unitario (HU-101-004/005/006)
- [x] CC PQ 09/06/2026: listas carga (loading, performance, display código artículo, búsqueda lazy)
- [x] CC PQ #5 09/06/2026: listbox artículos — disponible neto con `comprometido_web`; display con base opcional
- [x] CC PQ #6 17/06/2026: disponible base en listbox = agregado SUM por `articulos.base` (§5 consulta stock), no stock del código base aislado

## In scope — CC PQ #5 / #6 (listbox artículos)

En el **lookup/browse** de artículos (`GET /articulos` sin `codigos`), servicio `ArticuloCargaLookupService`:

| Contexto | Fórmula disponible |
|----------|-------------------|
| Disponible artículo | `stock − comprometido − comprometido_web` (pedidos ingresados `estado = 0`) |
| Disponible base (si `articulos.base` no vacío) | `SUM(stock) − SUM(comprometido) − comprometido_base_web` sobre **todas** las presentaciones con la misma `base` — ver [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) §5 |
| Consulta stock (`GET /consultas/stock`) | Mismas fórmulas §4–§5 (`StockConsultaService`) |

Display ítem: `{codigo} - {descripcion} — Disp. {disponibleNeto}` y `({disponibleNetoBase})` si hay base. Entre paréntesis va **disponible neto base**, no `comprometidoBaseWeb`.

Fuente de verdad UI: [pantalla-carga-comprobante-ui.md](../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §3.

## In scope — CC PQ #3 (09/06/2026)

1. **Clientes:** patrón SPEC-001-01 (cargando + bloqueo + auto-match); cache catálogo por sesión.
2. **Artículos:** búsqueda remota optimizada (mín. **4** caracteres, espacios incluidos; apertura lista tras **1 s** sin tipear; flecha desplegable sin texto); display **`{codigo} - {descripcion}`**.
3. **Lista de precios:** recálculo batch de precios de renglones al cambiar lista (API `codigos` CSV).

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 04/06/2026 | `00-ControlCalidad-PQ` #1 | CC: cliente, cabecera, renglones, precio neto unitario |
| 09/06/2026 | Parte I CC #1 | Unificación `SPEC-101-10-pantalla-carga-update` (oleada 04/06) |
| 09/06/2026 | CC PQ #3 | Listas carga: loading, performance, display artículo, búsqueda lazy |
| 09/06/2026 | Parte I CC #3 | Unificación `SPEC-101-10-pantalla-carga-update` |
| 09/06/2026 | CC PQ #5 | Listbox carga: disponible sin `comprometido_web` |
| 11/06/2026 | Parte I CC #5 | Unificación `SPEC-101-10-pantalla-carga-update` (CC #5) |
| 17/06/2026 | CC PQ #6 | Disponible base listbox: agregado SUM por `base`; fórmula alineada con consulta stock §5 |
| 23/06/2026 | perf carga | Precarga stock al montar; precios por lista (`solo_catalogo`); merge cliente — ver `pantalla-carga-comprobante-ui.md` §3 |
