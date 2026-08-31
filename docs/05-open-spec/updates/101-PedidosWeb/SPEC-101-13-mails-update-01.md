# SPEC-101-13 — Mails (update-01 — Bultos / Unidades)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-13-mails-update-01 |
| **SPEC base** | [SPEC-101-13-mails](../../101-PedidosWeb/SPEC-101-13-mails.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-28 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12 — **28/08/2026** |
| **Dependencias** | `CargaUnidadesVenta`; `cantidad` / `cantidad_venta` en detalle |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Cuando el mail de confirmación de comprobante se arma con detalle de renglones y aplica el modo de unidades de venta, incluir **dos columnas**:

| Columna mail (i18n) | Origen |
|--------------------|--------|
| **Bultos** | `cantidad_venta` |
| **Unidades** | `cantidad` (unidades netas / stock-precio) |

## In scope (delta)

- Plantilla mail de grabación (pedido/presupuesto) con ambas columnas cuando el producto usa `cantidad_venta` (parámetro activo **o** siempre que el dato exista — decidir en TR; sugerido: **siempre mostrar ambas** si la columna `cantidad_venta` está poblada; si parámetro false, Bultos = derivado).
- i18n claves nuevas (`mail.comprobanteNotification.colBultos`, `…colUnidades`).
- No romper mails sin detalle.

## Fuera de scope

- Cambiar destinatarios / CCO.
- SMS u otros canales.

## HU / TR derivadas

| Artefacto | Ruta |
|-----------|------|
| HU-101-019 | [HU-101-019-mail-grabar-update-01](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-019-mail-grabar-update-01.md) |
| TR-101-13 | [TR-SPEC-101-13-mails-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-13-mails-update-01.md) |

## Definición de listo (update)

- [ ] Mail con Bultos + Unidades.
- [ ] Tests de plantilla / snapshot i18n.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Columnas Bultos/Unidades en mail |
| 28/08/2026 | Parte G | Volcado SPEC-update-01 |
