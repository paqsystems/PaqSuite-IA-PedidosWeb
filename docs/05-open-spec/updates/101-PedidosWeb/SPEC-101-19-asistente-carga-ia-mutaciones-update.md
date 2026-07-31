# SPEC-101-19 — Asistente carga IA mutaciones (update — cantidad / CargaUnidadesVenta)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-19-asistente-carga-ia-mutaciones-update |
| **SPEC base** | [SPEC-101-19-asistente-carga-ia-mutaciones](../../101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-07-30 |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #10 — **30/07/2026** |
| **Dependencias** | [SPEC-101-10-update](SPEC-101-10-pantalla-carga-update.md); [SPEC-101-04-update](SPEC-101-04-services-pedidos-update.md); HU-101-038 (entrada audio/imagen) |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Objetivo del update

Cuando el asistente informa **cantidad** (texto, voz o imagen / extracto), aplicar el **mismo tratamiento** que el modal de renglón según `CargaUnidadesVenta`.

## In scope (delta)

| Canal | Tratamiento |
|-------|-------------|
| Texto / tools `addRenglon` / modify | Cantidad parseada → helper conversión |
| Voz (transcripción → mismo pipeline) | Igual |
| Imagen / apply extracto (capacidad K) | Cantidad de candidatos → helper conversión |

- Default cantidad omitida = **1** se mantiene (D1-06); el `1` se interpreta según el parámetro.
- Importes / precio neto: desde `cantidad` materializada.
- Permisos precio/bonif existentes sin cambio.

## Fuera de scope

- Consultas del asistente (SPEC-101-20).
- Exponer equivalencia al usuario en chat (salvo mensaje técnico de error).

## HU / TR derivadas

| Artefacto | Ruta update |
|-----------|-------------|
| HU-101-040 | [HU-101-040-asistente-carga-ia-articulos-grabar-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar-update.md) |
| TR | [TR-SPEC-101-19-asistente-carga-ia-mutaciones-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update.md) |

## Definición de listo (update)

- [ ] Tools de artículo usan helper compartido.
- [ ] Tests PHPUnit asistente false/true (+ imagen si hay harness).

## Historial

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 30/07/2026 | CC PQ #10 | Asistente cantidad = modal |
| 30/07/2026 | Parte G | Volcado SPEC-update |
