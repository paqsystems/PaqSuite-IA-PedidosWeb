# SPEC-101-02 — Modelos Eloquent

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Finalizado |
| **Prioridad épica** | Must |

## Objetivo

Modelos Eloquent para tablas operativas y maestras ERP en base tenant, sin lógica de negocio.

## In scope

- `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`
- Maestras: clientes, clientesde, vendedores, artículos, **escalas (cabecera/detalle)**, stock, listas, precios, condiciones, transportes
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
