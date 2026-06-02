# SPEC-101-05 — Controllers REST

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |

## Objetivo

Exponer API JSON delgada (`api/v1`) con envelope MONO; sin lógica de negocio en controllers.

## In scope

- `POST/PUT/DELETE/GET` pedidos (DELETE solo estado 0)
- `POST/PUT/GET` presupuestos — **sin** `DELETE` presupuesto
- `POST .../cerrar`, `POST .../convertir-a-pedido`
- Endpoint **copiar** comprobante (nuevo comprobante desde origen)
- OpenAPI + matriz permisos por endpoint
- DTOs de entrada/salida

## Fuera de scope

- Consultas listados (101-07)
- Implementación de reglas (101-04)

## Dependencias

- SPEC-101-04
- `_NORMAS-TRANSVERSALES-TR.md`

## Definición de listo

- [ ] Controllers delegan 100 % en services
- [ ] Feature test por endpoint (éxito + error/permiso)
- [ ] Swagger actualizado
