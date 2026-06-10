# HU-101-003 — Resolución de tenant MONO

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-003-resolucion-tenant |
| **SPEC origen** | [SPEC-101-01-backend-base](../../05-open-spec/101-PedidosWeb/SPEC-101-01-backend-base.md), [SPEC-001-05](../../05-open-spec/001-Generaliddes/SPEC-001-05-variantes-y-alcance.md) |
| **Épica** | 101 — Infraestructura |
| **Prioridad** | Etapa posterior (AMB-C07) |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | Documento `docs/_base/resolucion-host-cliente-sql-mono.md` |

## Narrativa

Como **operador del deploy MONO**,  
quiero **resolver el tenant `{cliente}` a la base SQL correcta vía `EMPRESAS_CONEXION`**,  
para **aislar datos por cliente sin `X-Company-Id`**.

## Alcance incluido

- Entrada `{cliente}.pedidosweb` → contexto en `frontend.pedidosweb` / API con `X-Paq-Cliente`
- Fila `CODIGO_TENANT = desarrollo` para local
- Conexión dinámica SQL Server por tenant
- Prohibido inferir tenant solo desde JWT sin registro activo

## Fuera de alcance (MVP actual)

- No bloquea slices 02–15 con stub `paq.tenant` ya desplegado

## Reglas de negocio

1. `proyecto = pedidosweb` en `EMPRESAS_CONEXION`.
2. Login liga tenant usado al token/sesión.

## Criterios de aceptación

- [ ] **CA-01:** Tenant `desarrollo` resuelve base de trabajo local.
- [ ] **CA-02:** Tenant inexistente/inactivo → error controlado antes de negocio.
- [ ] **CA-03:** Health check opera con conexión tenant correcta.
- [ ] **CA-04:** Tests automatizados de resolución (feature).

## Preguntas abiertas

Ninguna — ejecutar en etapa posterior explícita.

## Veredicto B1

**Lista para TR** cuando se active SPEC-101-01.
