# Runbook — usuario técnico importación transacciones ERP

Complementa [runbook-usuario-integracion-maestros.md](./runbook-usuario-integracion-maestros.md) (motor GEN-36 compartido).

## Recursos

| Slug | Modos |
|------|--------|
| `stock` | snapshot, delta |
| `deuda` | snapshot, delta |
| `cheques` | snapshot, delta |
| `historial-ventas` | **solo delta** |

## Endpoints

```http
POST /api/v1/integracion/transacciones/{recurso}
GET  /api/v1/integracion/importaciones/{importacionId}
```

Mismo contrato de lote que maestros (`importacionId`, `numeroLote`, `ultimoEnvio`, `modo`, `fechaProceso`, `items`). Respuesta de recepción: **202 Accepted**; resultado final vía GET.

## Permiso

Ability `pw_integracion_importacion` (usuario técnico seed `integracion.erp.mvp` en dev).

## Deploy

1. SQL: `backend/scripts/sql/integracion-transacciones/00-deploy-all.sql` en la BD empresa.
2. Motor GEN-36 (staging/importaciones) ya desplegado con maestros.
3. Worker/cola activo (`ProcessImportationMaestroJob`).

## Errores frecuentes

| Síntoma | Causa |
|---------|--------|
| 422 `modoNoSoportado` | `historial-ventas` con `modo=snapshot` |
| GET con contadores null | Ciclo aún en cola/proceso |
| Item rechazado FK | Maestro (artículo/cliente) ausente |
