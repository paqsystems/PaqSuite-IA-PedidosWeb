# SPEC-101-10 — Pantalla carga (update-01 — deuda, unidades, precio neto, leyendas dirty, stockeable)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-10-pantalla-carga-update-01 |
| **SPEC base** | [SPEC-101-10-pantalla-carga](../../101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-28 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12 — **28/08/2026** |
| **Dependencias** | [SPEC-101-07-update-01](SPEC-101-07-consultas-api-update-01.md); [SPEC-101-04-update-01](SPEC-101-04-services-pedidos-update-01.md); [SPEC-101-02-update-02](SPEC-101-02-modelos-update-02.md); `CargaUnidadesVenta` |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Extender la pantalla de carga con: (1) saldo de deuda del cliente con colores y modal; (2) equivalencia unidades / precio neto en modal de renglón cuando aplica; (3) tracking dirty de leyendas; (4) listbox artículos: no mostrar stock en no-stockeables.

## In scope (delta)

### A) Saldo de deuda (tras elegir cliente)

- Mostrar saldo neto de deuda del cliente seleccionado.
- Colores:
  1. Saldo neto **≤ 0** (cero o a favor del cliente) → **verde**.
  2. Saldo neto **> 0** y **ningún** comprobante vencido con saldo → **negro**.
  3. Existe al menos un comprobante con saldo y `fecha_vto < hoy` → **rojo**.
- Si saldo neto **≠ 0**: ícono junto al saldo abre **Popup** DevExtreme con grilla de comprobantes pendientes (mismas columnas que consulta deuda) + total; **sin** export, layouts, pivot ni plantillas.
- Fuente: `GET /consultas/deuda?cod_cliente=…` (visibilidad vigente).
- i18n + `data-testid` estables (`cargaDeudaSaldo`, `cargaDeudaDetalleOpen`, …).
- Mobile: mostrar saldo; modal/detalle usable en native.

### B) Modal renglón — unidades de venta y precio neto

- Si `CargaUnidadesVenta = true`: mostrar en el modal la **cantidad de unidades de stock** equivalentes a las unidades de venta ingresadas (`cantidad_venta * equivalencia_ventas`, equiv≤0→1). Solo lectura / informativo; no segundo campo editable de cantidad.
- Incluir **precio unitario neto** del renglón: precio cargado menos bonificación del renglón menos bonificación neta de cabecera (misma base de cálculo de importes vigente).
- Si `CargaUnidadesVenta = false`: no exigir el texto de equivalencia venta→stock (opcional ocultar).

### C) Leyendas dirty

- Al inicializar/abrir cabecera, guardar snapshot de `leyenda_1…5`.
- Marcar dirty por leyenda si el usuario cambia el valor respecto del snapshot de sesión.
- Enviar al grabar flags o valores dirty para que el service aplique SPEC-101-04-update-01 (contrato exacto en TR).

### D) Listbox artículos

- Artículos con `stockeable = false`: **sí** pueden aparecer en búsqueda/carga, pero **sin** mostrar stock / disponible (ocultar o N/A i18n).
- No alterar reglas de precio/IVA.

## Fuera de scope

- Reimplementar consulta deuda completa en carga.
- Segundo campo editable de cantidad.
- Export desde el modal de deuda.

## HU / TR derivadas

| Artefacto | Ruta |
|-----------|------|
| HU-101-004 | [HU-101-004-seleccion-cliente-update-01](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-004-seleccion-cliente-update-01.md) |
| HU-101-005 | [HU-101-005-inicializacion-cabecera-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-005-inicializacion-cabecera-update.md) |
| HU-101-006 | [HU-101-006-carga-renglones-update-01](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-006-carga-renglones-update-01.md) |
| HU-101-008 | [HU-101-008-precio-importes-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-008-precio-importes-update.md) |
| TR-101-10 | [TR-SPEC-101-10-pantalla-carga-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga-update-01.md) |

## Definición de listo (update)

- [ ] Saldo + colores + modal sin export.
- [ ] Equivalencia unidades si `CargaUnidadesVenta`.
- [ ] Precio unitario neto en modal.
- [ ] Dirty leyendas → grabar.
- [ ] No stockeables sin stock en listbox.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Deuda / unidades / precio neto / leyendas / stockeable UI |
| 28/08/2026 | Parte G | Volcado SPEC-update-01 |
