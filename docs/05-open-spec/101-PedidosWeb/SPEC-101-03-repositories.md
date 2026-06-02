# SPEC-101-03 — Repositories

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |

## Objetivo

Capa de acceso a datos (queries, persistencia) sin reglas de negocio.

## In scope

- `PedidoRepository`, `PedidoDetalleRepository`
- `ClienteRepository`, `ArticuloRepository`, `ConsultaRepository`
- Filtros de visibilidad **delegados** a policies/services (no duplicar reglas aquí)

## Fuera de scope

- Cálculo de totales, bonificaciones, transiciones de estado
- Endpoints HTTP

## Dependencias

- SPEC-101-02

## Definición de listo

- [ ] Repositories con interfaces acotadas
- [ ] Tests de integración de lectura/escritura básica donde aplique
