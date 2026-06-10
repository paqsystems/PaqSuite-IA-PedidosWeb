# SPEC-101-01 — Backend base y tenancy

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | **Etapa posterior** (decisión AMB-C07, 2026-06-01) |
| **Prioridad épica** | Diferida — no bloquea primeros slices de dominio con stub actual |

## Objetivo

Completar backend MONO: resolución **`EMPRESAS_CONEXION`**, conexión SQL Server por tenant, healthcheck y capas base (sin lógica de pedidos).

## In scope (cuando se ejecute)

- Middleware tenant alineado a `docs/_base/resolucion-host-cliente-sql-mono.md`
- Fila `CODIGO_TENANT = desarrollo` operativa
- Estructura carpetas Services/Repositories/DTOs/Policies (si faltara)
- Validación `X-Paq-Cliente` + registro activo en `EMPRESAS_CONEXION`

## Fuera de scope

- CRUD pedidos/presupuestos
- Lógica de negocio comercial

## Dependencias

- `SPEC-001-05` (referencia MONO)
- Baseline scaffold en `v1.1.0-paq` (`paq.tenant` stub)

## HU / TR

- HU-101-003 (tenancy) — ejecutar en esta etapa, no antes

## Definición de listo

- [ ] Conexión por tenant desde `EMPRESAS_CONEXION`
- [ ] Tests de resolución tenant + health
- [ ] Documentado en matriz permisos si aplica
