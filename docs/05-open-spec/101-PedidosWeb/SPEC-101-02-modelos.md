# SPEC-101-02 — Modelos Eloquent

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | En revisión |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-31 |

## Objetivo

Modelos Eloquent para tablas operativas y maestras ERP en base tenant, sin lógica de negocio.

## In scope

- `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`
- Maestras: clientes, **clientescontactos**, clientesde (`habitual` **char(1)** `S`/`N`, API boolean), vendedores, artículos, **escalas (cabecera/detalle)**, stock, listas, precios, condiciones, transportes
- Artículos: columna **`stockeable`** (`bit NOT NULL`, default `1`). `false`/`0` = no stockeable: no mostrar stock en listbox de carga y excluir de consulta stock (CC PQ #12). Alimentación vía sync ERP; sin ABM web. DDL canónico: `backend/scripts/sql/create-pq-pedidosweb-articulos.sql`.
- Artículos: columna **`equivalencia_ventas`** (`decimal(18,4) NOT NULL`, default `1`). Si valor leído es `0` o nulo en runtime → tratar como **1** al convertir (CC PQ #10).
- Artículos (resto canónico): `codigo varchar(15)` PK; `descripcion varchar(60)`; `bonificacion decimal(6,2)`; `usa_esc char(1)`; `base`/`valor1`/`valor2` `varchar(15)`; `porc_iva numeric(6,2)`.
- Detalle: columna **`cantidad_venta`** (decimal). Persistir siempre junto con `cantidad`; backfill filas existentes `cantidad_venta = cantidad` (CC PQ #10).
- Detalle: columna **`bonificacion`** `decimal(6,2)` — bonificación de renglón (canónico ERP). El portal/API siguen usando el alias `porc_bonif` / `porcBonif` en payloads.
- **`pq_pedidosweb_clientescontactos`** (CC PQ #11): PK `id` (identity); `cod_client` (FK lógica a clientes); `cod_contacto`; `nombre`; `telefono` nullable; `mail` nullable. Unique (`cod_client`, `cod_contacto`). Relación Cliente 1:N ClienteContacto. Solo lectura portal; alta/edición vía ERP/integración. API: [SPEC-001-02](../001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md).
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
- [x] CC PQ #10: columnas `equivalencia_ventas` + `cantidad_venta`
- [x] CC PQ #11: tabla `pq_pedidosweb_clientescontactos` + modelo Eloquent

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 28/08/2026 | CC PQ #12 | Flag `stockeable` en artículos |
| 30/08/2026 | Parte I | Unificación `SPEC-101-02-modelos-update-02` (CC PQ #12) |
| 30/07/2026 | CC PQ #10 | `equivalencia_ventas` + `cantidad_venta` |
| 18/08/2026 | CC PQ #11 | Tabla contactos de cliente |
| 31/08/2026 | Parte I | Unificación `SPEC-101-02-modelos-update` + `…-update-01`. Sin updates abiertos |
