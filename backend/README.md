# Backend — PaqSuite-IA-PedidosWeb (Laravel 10)

API REST MONO con prefijo **`/api/v1`**, envelope JSON estándar, Sanctum y **OpenAPI (L5-Swagger)**.

## Requisitos

- PHP 8.1+
- Composer 2.x
- Extensiones PHP: mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath

## Instalación

```powershell
cd backend
copy .env.example .env
composer install
php artisan key:generate
composer openapi
php artisan test
php artisan serve --port=8000
```

Guía completa (nuevos productos MONO): [`docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md`](../docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md).

OpenAPI scaffold: [`docs/_base/00-openapi-l5-swagger-scaffold.md`](../docs/_base/00-openapi-l5-swagger-scaffold.md).

## OpenAPI / Swagger UI

| Recurso | Ubicación |
|---------|-----------|
| UI | `http://localhost:8000/api/documentation` |
| Spec JSON | `storage/api-docs/api-docs.json` |
| Raíz anotaciones | `OpenApi.php` |
| Regenerar | `composer openapi` |

Tras modificar anotaciones `@OA\...` en controllers, ejecutar `composer openapi` antes de revisar la UI.

## Envelope JSON

Todas las respuestas usan `App\Http\Responses\ApiResponse`:

| Campo | Tipo | Regla |
|-------|------|--------|
| `error` | int | `0` = OK |
| `respuesta` | string | Texto o clave i18n |
| `resultado` | object | Nunca `null`; vacío = `{}` |

Spec: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md).

## Endpoints

| Método | Path | Auth |
|--------|------|------|
| GET | `/api/v1/health` | No |
| POST | `/api/v1/auth/login` | Tenant header |
| GET | `/api/v1/auth/me` | Bearer + tenant |
| GET | `/api/v1/user/menu` | Bearer + tenant |
| GET/PATCH | `/api/v1/users/me/preferences` | Bearer + tenant |

Ver spec completo en `/api/documentation`.

## Seed MVP (seguridad y menú)

**Post-deploy (Forge, idempotente):**

```powershell
php artisan paqsuite:seed-deploy
```

Incluye menú MVP, catálogo Excel (`PEDIDO_INDIVIDUAL` / `PEDIDO_MASIVO`), atributos visibility y catálogo chat. Ver `docs/Migraciones-en-forge.md`.

Orden manual (SQL Server / diccionario legacy):

```powershell
php artisan paqsuite:seed-menus-mvp
php artisan paqsuite:seed-seguridad-mvp
# Opcional: alinear pq_pedidosweb_login con todos los users (faltantes por usuario=codigo)
php artisan paqsuite:sync-pedidosweb-login-from-users
```

**Recrear tablas comerciales + parámetros PedidosWeb (desarrollo, sin script ERP):**

```powershell
php scripts/bootstrap-pedidosweb-dev.php --yes
# o: php artisan paqsuite:bootstrap-pedidosweb-dev --no-interaction
```

Recrea todas las `pq_pedidosweb_*` (25 tablas, incl. escalas), `PQ_parametros_gral` (57 claves del JSON seed) y datos MVP (stock, dashboard, consultas). No toca `users` ni `pq_menus`.
```

Variables en `.env`:

| Variable | Uso |
|----------|-----|
| `SEED_MVP_PASSWORD` | Contraseña común usuarios seed (`cliente.mvp`, etc.) |
| `SEED_MVP_SYNC_COMMERCIAL` | `false` en dev cliente (solo seguridad); `true` en tests/CI para filas MVP en tablas legacy |
| `PAQSUITE_MONO_EMPRESA_ID` | `id_empresa` en `Pq_Permiso` (FK `PQ_Empresa`; default `8`) |

Catálogo: `config/paqsuite_mvp.php`. Usuarios seed: §4.6 TR-GEN-02-modelo-roles-permisos-seed.

**Login:** autenticación en tabla `users` (`codigo` + `password_hash`). Tras login, vínculo comercial: `pq_pedidosweb_login` → `pq_pedidosweb_clientes` / `pq_pedidosweb_vendedores`. Ver `docs/02-producto/PedidosWeb/patron-acceso-login-comercial.md`.

**Nota legacy:** `pq_menus` usa `procedimiento` ERP (`pw_*`). Rol **`VendedorAcotado`** separa atributos de menú de **`Vendedor`** (`vendedor.sinMenu.mvp`).

## Estructura relevante

```text
OpenApi.php
config/l5-swagger.php
storage/api-docs/api-docs.json
app/Http/Responses/ApiResponse.php
app/Console/Commands/SeedMenusMvpCommand.php
app/Console/Commands/SeedSeguridadMvpCommand.php
app/Services/Seed/SeedUpsertService.php
config/paqsuite_mvp.php
database/seeders/Mvp/
tests/Feature/OpenApiDocumentationTest.php
tests/Feature/SeedMenusMvpTest.php
tests/Feature/SeedSeguridadMvpTest.php
```

## Variables de entorno

Ver `.env.example`: `TENANT_*`, `SEED_MVP_PASSWORD`, `PAQSUITE_MONO_EMPRESA_ID`, `FRONTEND_URL`, `L5_SWAGGER_*`.
