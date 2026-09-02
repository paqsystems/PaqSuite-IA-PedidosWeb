# SPEC-101-02-update — Leyendas 1–5: longitud máxima 60

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-02-modelos-update |
| **SPEC base** | [SPEC-101-02-modelos](../../101-PedidosWeb/SPEC-101-02-modelos.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **HU relacionadas** | [HU-101-005-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-005-inicializacion-cabecera-update.md) · [HU-101-009-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-009-grabar-pedido-update.md) |
| **TR relacionadas** | [TR-SPEC-101-02-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-02-modelos-update.md) |
| **Última actualización** | 2026-09-01 |

## Objetivo

Limitar `leyenda_1` … `leyenda_5` a **60 caracteres Unicode** en el modelo de datos de cabecera de comprobantes y del maestro de clientes (CC PQ #13).

## Constante canónica

| Clave | Valor |
|-------|--------|
| **Máximo** | **60** caracteres (`mb_strlen` / `nvarchar(60)`) |
| **Exceso** | **Recortar** a los primeros 60 (Unicode). **No** rechazar por longitud en API, Excel ni asistente IA |
| **Alineación ERP** | En Tango/ERP, `Parametro.Leyenda` es `varchar(60)` |

## In scope

- `pq_pedidosweb_pedidoscabecera.leyenda_1` … `leyenda_5`: de `nvarchar(255)` a **`nvarchar(60)` NULL**.
- `pq_pedidosweb_clientes.leyenda_1` … `leyenda_5`: misma longitud (**relevamiento CC #13**). Motivo: inicializan cabecera (`ClienteLeyendaN`) y se sincronizan al grabar (SPEC-101-04 / CC PQ #12). Si el maestro quedara en 255, el sync sucedería o se truncaría de forma inconsistente.
- DDL **idempotente** (ALTER si `CHARACTER_MAXIMUM_LENGTH > 60`):
  1. `UPDATE` `LEFT(leyenda_N, 60)` en filas con texto más largo (necesario para que el ALTER no falle).
  2. `ALTER COLUMN … nvarchar(60) NULL`.
- Bootstrap/dev schema (`PedidosWebDevSchemaBootstrap`) alineado a 60 en CREATE de tablas nuevas.
- Documentar tipo en [PedidosWeb_Modelo_Datos_Final.md](../../../02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md) al ejecutar D.

## Fuera de scope

- Observaciones de cabecera (siguen con su longitud actual).
- Consultas de listado (solo lectura; tras el ALTER no hay valores > 60).
- Mail (no incluye leyendas).
- Copia / conversión de comprobantes (copian el valor ya acotado).

## Definición de listo

- [ ] Columnas `leyenda_1..5` en cabecera y clientes = `nvarchar(60)` en tenant target
- [ ] Filas preexistentes recortadas a 60 antes del ALTER
- [ ] Bootstrap CREATE usa 60
