# Postman — exportación comprobantes ERP (SPEC-101-25)

## Archivos

| Archivo | Uso |
|---------|-----|
| [PedidosWeb-Integracion-Export-Comprobantes.postman_collection.json](./PedidosWeb-Integracion-Export-Comprobantes.postman_collection.json) | Collection |
| [PedidosWeb-Integracion-Export-Comprobantes.postman_environment.json](./PedidosWeb-Integracion-Export-Comprobantes.postman_environment.json) | Variables de entorno |

Regenerar (opcional, tras cambiar el script):

```powershell
cd backend
php scripts/generar-postman-integracion-export.php
```

## Importar en Postman

1. **Import** → seleccionar ambos JSON de esta carpeta.
2. Activar el environment **PedidosWeb - Export comprobantes (local)**.
3. Completar **`loginPassword`** con el valor **literal** de `SEED_MVP_PASSWORD` en `backend/.env` (en la plantilla suele ser `ChangeMeInLocalEnv`; si cambiaste la variable en `.env`, Postman debe usar **ese** texto exacto, sin espacios al final).
4. Ajustar **`backendUrl`** y **`tenant`** si no usás `http://127.0.0.1:8088` / `demo`.

### Si el login responde `403` + `auth.noCommercialProfile`

La contraseña **sí** es válida. El endpoint `POST /auth/login` arma sesión **comercial** (cliente o vendedor en `pq_pedidosweb_login`). El usuario técnico de integración no la trae por defecto.

Solución (misma BD que el backend):

```powershell
cd backend
php artisan paqsuite:provision-integracion-user --codigo=integracion.erp.mvp
```

El comando crea el vínculo técnico `INTEGRERP01` y vuelve a dejar el login Postman operativo. Alternativa: pegar en `integrationToken` el **token** que imprime el comando (sin pasar por login).

### Si el login responde `401` + `auth.invalidCredentials`

No es un fallo del export: la BD no tiene al usuario o el **hash guardado** no coincide con la contraseña que enviás.

1. Confirmá que el request usa **`loginCodigo` = `integracion.erp.mvp`** (collection **Export comprobantes**, no la collection vieja de maestros que por defecto usa `PAQ`).
2. Re-sincronizá usuario + permiso + contraseña contra la misma BD que usa `php artisan serve`:

   ```powershell
   cd backend
   php artisan paqsuite:provision-integracion-user --codigo=integracion.erp.mvp
   ```

   Usa `config('paqsuite_seed.mvpPassword')` = `SEED_MVP_PASSWORD` del `.env`.

3. Volvé a ejecutar **login - usuario integración**.

Atajo (solo prueba manual): el comando imprime un **token**; podés copiarlo a `integrationToken` y saltear el login (expira si revocás tokens).

## Orden de ejecución (aprobación)

1. **Auth → login - usuario integración** — debe guardar `integrationToken`.
2. **Export (éxito)** — ejecutar la carpeta completa con *Run collection* (o una a una).
3. **Export (errores esperados)** — 401 / 422 documentados.

Variables opcionales para pruebas más finas:

- `exportCodCliente` — cliente existente en la BD.
- `exportCodPedido` — un `cod_pedido` real para el request puntual.
- `exportFechaDesde` / `exportFechaHasta` — ventana de fechas (últimos 30 días por defecto).

## Prerrequisitos backend

- SP `pq_sp_integracion_export_comprobantes_list` desplegado:

  ```powershell
  cd backend
  php scripts/deploy-integracion-export-sqlserver.php
  ```

- Usuario **`integracion.erp.mvp`** con permiso integración (seed MVP / `paqsuite:seed-seguridad-mvp`).

## OpenAPI

Contrato documentado: `http://127.0.0.1:8088/api/documentation` → `GET /api/v1/integracion/comprobantes`.