# SPEC-101-02 — Modelos Eloquent

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | En revisión |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-30 (Parte I — CC PQ #12) |

## Objetivo

Modelos Eloquent para tablas operativas y maestras ERP en base tenant, sin lógica de negocio.

## In scope

- `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`
- Maestras: clientes, clientesde, vendedores, artículos, **escalas (cabecera/detalle)**, stock, listas, precios, condiciones, transportes
- Artículos: columna **`stockeable`** (bit, default `true`). `false` = no stockeable: no mostrar stock en listbox de carga y excluir de consulta stock (CC PQ #12). Alimentación vía sync ERP; sin ABM web.
- Tablas nuevas MVP: tratativas, resultados, motivos_cierre, presupuestos_cierres, logs_integracion (según modelo datos)
- PK, relaciones y casts según `PedidosWeb_Modelo_Datos_Final.md`

## Fuera de scope

- Services, validaciones de estado, totales
- Migraciones que alteren tablas heredadas sin acuerdo explícito

## Dependencias

- Stub tenant operativo (101-01 diferido)
- Modelo de datos producto

## HU / TR

- Transversal a HU carga/consultas; TR dedicada por entidad si hace falta

## Definición de listo

- [ ] Modelos registrados y relaciones mínimas probadas
- [ ] Sin reglas de negocio en modelos
- [x] CC PQ #12: columna `stockeable` (default true) en `pq_pedidosweb_articulos`

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Flag `stockeable` en artículos |
| 30/08/2026 | Parte I | Unificación `SPEC-101-02-modelos-update-02`. Quedan abiertos `…-update.md` y `…-update-01.md` |
