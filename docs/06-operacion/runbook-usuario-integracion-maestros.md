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

## Cola y worker (importaciones async GEN-36)

Rutas `POST /api/v1/integracion/maestros/{recurso}` responden **202** y encolan
`ProcessImportationMaestroJob` cuando `ultimoEnvio=true`. Consulta:
`GET /api/v1/integracion/importaciones/{importacionId}`.

Requieren `QUEUE_CONNECTION` distinto de `sync` (habitual: `database`) y un worker activo.

Desarrollo local (Windows):

```powershell
# Solo worker del repo actual
.\scripts\dev\start-dev.ps1

# Backend + frontend + worker (puertos por producto; PedidosWeb :8088 / :3010)
.\scripts\dev\start-dev.ps1 -All

# Workers en todos los hosts PaqSuite-IA-* bajo C:\Programacion (omite QUEUE_CONNECTION=sync)
.\scripts\dev\start-all-hosts-workers.ps1
```

Alias PedidosWeb: `start-pedidosweb.ps1` → `start-dev.ps1`.

Manual:

```powershell
cd backend
php artisan queue:work database --queue=default --tries=3 --timeout=120
```

Verificar: `php artisan tinker --execute="echo DB::table('jobs')->count();"` (debe bajar al procesar).
Fallidos: `php artisan queue:failed` / `php artisan queue:retry all`.

`POST /api/v1/integracion/transacciones/*` (SPEC-101-23) queda **fuera** de este runbook hasta su implementación.

## Recursos disponibles (TR-22b / 22c)

| Recurso | Estado |
|---------|--------|
| `provincias`, `condventa`, `transportes`, `perfiles` | SP + delta/snapshot |
| `vendedores` | SP + delta; **snapshot** elimina ausentes (`finalize_snapshot`); FK clientes → error en resultado |
| `listaprecios` | Precios en `articulos[]`; alias ERP `items[]` y `codigo` normalizados en API |
| `escalas`, `articulos` | SP + políticas snapshot; escalas aceptan `descripcion` o `descripEs` |
| `descuentocantidad` | SP solo delta; snapshot → 422 |
| `clientes`, `clientes-direcciones`, `clientes-contactos`, `parametros` | SP TR-22c (whitelist parámetros) |

Los procedimientos se despliegan mediante
`backend/scripts/sql/integracion-maestros/00-deploy-all.sql`. El script crea
también el staging técnico de snapshots y no debe ejecutarse en una base
compartida sin autorización.

## Logs

Consulta vía API existente `GET /api/v1/integracion/logs` (permiso `pw_logsintegracion`). Tipo `maestro_import`.
