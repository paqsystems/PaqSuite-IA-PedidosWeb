# SPEC-101-05 — Controllers REST

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Finalizado |
| **Prioridad épica** | Must |

## Objetivo

Exponer API JSON delgada (`api/v1`) con envelope MONO; sin lógica de negocio en controllers.

## In scope

- `POST/PUT/DELETE/GET` pedidos (DELETE solo estado 0)
- `POST/PUT/GET` presupuestos — **sin** `DELETE` presupuesto
- `POST presupuestos/{id}/cerrar` (rechazo 99→98)
- **`POST /api/v1/comprobantes/grabar`** — canal **canónico** para alta, modificación y conversiones (matriz §10.1)
- **`POST /api/v1/comprobantes/copiar`** — borrador sin persistir; grabación en `comprobantes/grabar`
- OpenAPI + matriz permisos por endpoint
- DTOs de entrada/salida

## Fuera de scope

- Rutas `.../convertir` o `.../convertir-a-*` en MVP (duplican `comprobantes/grabar`)
- Consultas listados (101-07)
- Implementación de reglas (101-04)

## Dependencias

- SPEC-101-04
- `_NORMAS-TRANSVERSALES-TR.md`

## TR de implementación

Contratos detallados: [TR-SPEC-101-05-controllers-rest](../../04-tareas/101-PedidosWeb/TR-SPEC-101-05-controllers-rest.md).

## Definición de listo

- [ ] Controllers delegan 100 % en services
- [ ] Feature test por endpoint (éxito + error/permiso)
- [ ] Swagger actualizado
- [ ] Un solo canal documentado para grabación/conversión
