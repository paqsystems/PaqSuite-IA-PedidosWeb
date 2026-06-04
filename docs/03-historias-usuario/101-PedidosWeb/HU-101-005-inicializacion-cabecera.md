# HU-101-005 — Inicialización de cabecera desde cliente

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-005-inicializacion-cabecera |
| **SPEC origen** | [SPEC-101-10-pantalla-carga](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
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

## Criterios de aceptación

- [x] **CA-01 (parcial):** Tras elegir cliente, cabecera muestra valores ERP coherentes con maestra (incl. `cod_perfil` inicial y catálogo `perfiles`).
- [ ] **CA-02:** Cambio de cliente recalcula/reemplaza cabecera (con confirmación si hay renglones).
- [ ] **CA-03:** Campos bloqueados por permiso aparecen deshabilitados, no ocultos sin traza.
- [ ] **CA-04:** Textos de labels vía i18n.

## Veredicto B1

**Lista para TR** (SPEC-101-10 + lectura maestras 101-02/03).
