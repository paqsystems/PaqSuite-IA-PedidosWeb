# SPEC-101-06 — Seguridad y visibilidad comercial

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente — **verificar herencia** `SPEC-001-02` / `v1.1.0-paq` |
| **Prioridad épica** | Must (verificación + extensión dominio) |

## Objetivo

Asegurar login, sesión, visibilidad cliente/vendedor/supervisor y policies en endpoints **101** sin reimplementar GEN-02.

## In scope

- Verificación de baseline: login, recuperación, cambio clave, inactividad, menú por permisos
- Policies de visibilidad en pedidos/presupuestos/consultas
- Asociación usuario ↔ cliente **o** vendedor (única)

## Fuera de scope

- Reescribir auth ya cerrado en GEN-02 salvo gap documentado
- Tenancy `EMPRESAS_CONEXION` (101-01, etapa posterior)

## Dependencias

- `SPEC-001-02` implementado
- SPEC-101-04/05 para policies de dominio

## HU relacionadas

HU-101-001, HU-101-002 (marcar **heredado / verificación** en B)

## Definición de listo

- [ ] Matriz permisos actualizada para endpoints 101
- [ ] Tests 403/401 en slice de dominio
