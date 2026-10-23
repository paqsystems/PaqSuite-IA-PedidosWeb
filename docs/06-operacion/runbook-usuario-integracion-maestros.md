# Runbook — usuario técnico importación maestros ERP

| Campo | Valor |
|-------|--------|
| **SPEC** | SPEC-101-22 |
| **TR** | TR-SPEC-101-22a (plataforma) |
| **Permiso** | `pw_integracion_importacion` (alta; **sin** ítem de menú MVP) |

## Provisión (no automática en deploy)

```powershell
cd backend
php artisan paqsuite:provision-integracion-user --codigo=integracion.erp
```

Opciones: `--email=`, `--password=`. El comando imprime un **token Sanctum** (una sola vez).

Alternativa dev: usuario seed `integracion.erp.mvp` tras `paqsuite:seed-seguridad-mvp` (contraseña `paqsuite_seed.mvpPassword`).

## Invocación HTTP

| Header | Valor |
|--------|--------|
| `Authorization` | `Bearer {token}` |
| `X-Paq-Cliente` | Tenant permitido en `paqsuite_tenant.allowedClients` |
| `Content-Type` | `application/json` |

```http
POST /api/v1/integracion/maestros/provincias
```

Cuerpo mínimo (delta no-op):

```json
{
  "modo": "delta",
  "fechaProceso": "2026-10-21T12:00:00-03:00",
  "origen": "tango",
  "items": []
}
```

## OpenAPI

`GET /api/documentation` — tag **Integración maestros**.

## Rate limit

60 solicitudes/minuto por token + tenant (`throttle:integracion-maestros`).

## Recursos disponibles (TR-22b)

| Recurso | Estado |
|---------|--------|
| `provincias`, `condventa`, `transportes`, `perfiles` | SP + delta/snapshot |
| `vendedores`, `listaprecios`, `escalas`, `articulos` | SP + políticas específicas de snapshot |
| `listaprecios-articulos`, `descuentocantidad` | SP solo delta; snapshot → 422 |
| `clientes`, `clientes-direcciones`, `clientes-contactos`, `parametros` | Pendientes TR-22c |

Los procedimientos se despliegan mediante
`backend/scripts/sql/integracion-maestros/00-deploy-all.sql`. El script crea
también el staging técnico de snapshots y no debe ejecutarse en una base
compartida sin autorización.

## Logs

Consulta vía API existente `GET /api/v1/integracion/logs` (permiso `pw_logsintegracion`). Tipo `maestro_import`.
