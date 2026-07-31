# SPEC-101-13 — Mails (update — cantidad según CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-13-mails-update |
| **SPEC base** | [SPEC-101-13-mails](../../101-PedidosWeb/SPEC-101-13-mails.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | [SPEC-001-04-update](../001-Generaliddes/SPEC-001-04-configuracion-global-update.md); [SPEC-101-10-update](SPEC-101-10-pantalla-carga-update.md) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

En el mail de grabación/modificación (tabla de renglones cuando `DetallePorMail` está activo), mostrar **una sola** columna de cantidad con el valor que el usuario carga según `CargaUnidadesVenta` — no ambas cantidades.

## In scope (delta)

| `CargaUnidadesVenta` | Valor mostrado en mail (columna cantidad) |
|----------------------|-------------------------------------------|
| `false` | `cantidad` |
| `true` | `cantidad_venta` |

- Misma etiqueta i18n de «cantidad» (sin renombrar a «unidades de venta» salvo decisión de producto posterior).
- Importes del mail siguen calculados/mostrados desde la base económica (`cantidad` / importes ya persistidos).

## Fuera de scope

- Segunda columna de cantidad en el mail.
- Cambiar destinatarios o plantilla cabecera.

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-019 | [HU-101-019-mail-grabar-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-019-mail-grabar-update.md) |
| TR mails | [TR-SPEC-101-13-mails-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-13-mails-update.md) |

## Definición de listo (update)

- [ ] Template mail usa cantidad según parámetro.
- [ ] Test/feature o unit del resolver de cantidad para mail.

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Mail: una cantidad según parámetro |
| 30/07/2026 | Parte G | Volcado SPEC-update |
