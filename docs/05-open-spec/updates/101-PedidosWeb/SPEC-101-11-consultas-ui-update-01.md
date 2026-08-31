# SPEC-101-11 — Consultas UI (update-01 — colores deuda, historial fechas, stock)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-11-consultas-ui-update-01 |
| **SPEC base** | [SPEC-101-11-consultas-ui](../../101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-28 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12 — **28/08/2026** |
| **Dependencias** | [SPEC-101-07-consultas-api-update-01](SPEC-101-07-consultas-api-update-01.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

UI de informes: colores de saldo en deuda; filtros de período en historial de ventas; coherencia consulta stock (sin no-stockeables vía API).

## In scope (delta)

### Deuda (`/consultas/deuda`)

- Importes (`saldo`) en **verde** si saldo a favor del cliente (saldo &lt; 0, o convención documentada = crédito).
- Importes en **rojo** si el comprobante está **vencido** (`fecha_vto < hoy` y saldo pendiente a cargo del cliente).
- Prioridad si ambos aplicaran: documentar en TR (sugerido: vencido manda sobre crédito solo si saldo &gt; 0).
- Aplicar en grilla (y kardex mobile si muestra importe). Pivot: Should si el renderer lo permite sin inventar motor.

### Historial (`/consultas/historial`)

- Controles de período **fecha desde / fecha hasta** (default vacío).
- Al refrescar/cargar, enviar query params al API según SPEC-101-07-update-01.
- i18n + `data-testid` (`historialFechaDesde`, `historialFechaHasta`).

### Stock

- Sin UI extra: la exclusión viene del API. Verificar que la grilla no muestre no-stockeables.

## Fuera de scope

- Cambiar columnas de deuda.
- Quitar `DiasVentasDetalladas` de metadata.

## HU / TR derivadas

| Artefacto | Ruta |
|-----------|------|
| HU-101-021 | [HU-101-021-consulta-deuda-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-021-consulta-deuda-update.md) |
| HU-101-023 | [HU-101-023-historial-ventas-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-023-historial-ventas-update.md) |
| HU-101-018 | [HU-101-018-consulta-stock-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-018-consulta-stock-update.md) |
| TR-101-11 | [TR-SPEC-101-11-consultas-ui-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update-01.md) |

## Definición de listo (update)

- [ ] Colores deuda verificables en grilla.
- [ ] Filtros fecha historial operativos.
- [ ] Stock sin no-stockeables.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Colores deuda + rango historial |
| 28/08/2026 | Parte G | Volcado SPEC-update-01 |
