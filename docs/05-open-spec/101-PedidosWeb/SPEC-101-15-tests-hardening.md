# SPEC-101-15 — Tests y hardening

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) §12 |
| **Estado** | Pendiente |
| **Prioridad épica** | Must (transversal) |

## Objetivo

Cerrar política de tests del MVP: E2E §9, E2E por slice, cobertura services, CI recomendado.

## In scope

- Flujo E2E §9 madre obligatorio
- ≥ 2 E2E por slice con UI
- Feature test por endpoint 101
- Cobertura `app/Services/**` ≥ 70 % MVP por slice entregado
- Gate CI recomendado (documentar en TR)

## Fuera de scope

- 80 % hasta declarar módulo estable (§12.2 madre)

## Dependencias

- Slices 04–14 según orden §10

## Definición de listo

- [ ] Suite documentada en README CI o `_PR-prompt`
- [ ] E2E §9 verde en entorno QA
