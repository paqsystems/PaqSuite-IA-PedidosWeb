# SPEC-101-10 — Pantalla de carga pedido/presupuesto

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |

## Objetivo

Pantalla **única** pedido/presupuesto: mismo flujo transaccional; cabecera, renglones, totales, grabación y **copia** de comprobante (producto §10).

## In scope

- **Una sola pantalla** para carga y edición de pedido y presupuesto (AMB-C11).
- Botones DevExtreme visibles: **`Grabar pedido`**, **`Grabar presupuesto`**, **`Cancelar`** (`data-testid` estables).
- Matriz de transiciones (producto §10.1): alta pedido/presupuesto; pedido→pedido; presupuesto→presupuesto; pedido→presupuesto; presupuesto→pedido (cierra origen en **98**).
- Entrada: nuevo, edición (**0** / **-1** / **99**), copia de comprobante.
- Selección cliente (vendedor/supervisor; cliente fijo).
- Cabecera/renglones según producto §10; **precio y descuentos** según parámetros ERP **V** / **S** — ver § Permisos precio/descuento.
- Autocompletar artículos; cálculo en tiempo real.
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

## Definición de listo

- [ ] E2E camino feliz carga pedido (§9 madre)
- [ ] Copia desde comprobante existente verificada
