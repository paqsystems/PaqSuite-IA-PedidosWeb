# Backend — PaqSuite-IA-PedidosWeb (Laravel 10)

API REST MONO con prefijo **`/api/v1`**, envelope JSON estándar y Sanctum.

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
php artisan test
php artisan serve --port=8000
```

Guía completa (nuevos productos MONO): [`docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md`](../docs/00-contexto/_mono/00-instalacion-scaffold-fullstack.md).

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

## Seed MVP (seguridad y menú)

Orden obligatorio (SQL Server / diccionario legacy):

```powershell
php artisan paqsuite:seed-menus-mvp
php artisan paqsuite:seed-seguridad-mvp
```

Variables en `.env`:

| Variable | Uso |
|----------|-----|
| `SEED_MVP_PASSWORD` | Contraseña común usuarios seed (`cliente.mvp`, etc.) |
| `SEED_MVP_SYNC_COMMERCIAL` | `false` en dev cliente (solo seguridad); `true` en tests/CI para filas MVP en tablas legacy |
| `PAQSUITE_MONO_EMPRESA_ID` | `id_empresa` en `Pq_Permiso` (FK `PQ_Empresa`; default `8`) |

Catálogo: `config/paqsuite_mvp.php`. Usuarios seed: §4.6 TR-GEN-02-modelo-roles-permisos-seed.

**Nota legacy:** `pq_menus` usa `procedimiento` ERP (`pw_*`). Rol **`VendedorAcotado`** separa atributos de menú de **`Vendedor`** (`vendedor.sinMenu.mvp`).

## Estructura relevante

```text
app/Http/Responses/ApiResponse.php
app/Console/Commands/SeedMenusMvpCommand.php
app/Console/Commands/SeedSeguridadMvpCommand.php
app/Services/Seed/SeedUpsertService.php
config/paqsuite_mvp.php
database/seeders/Mvp/
tests/Feature/SeedMenusMvpTest.php
tests/Feature/SeedSeguridadMvpTest.php
```

## Variables de entorno

Ver `.env.example`: `TENANT_*`, `SEED_MVP_PASSWORD`, `PAQSUITE_MONO_EMPRESA_ID`, `FRONTEND_URL`.

## Próximos slices

- Middleware tenant (400 `tenant.invalid`)
- Auth login/logout/me (`TR-GEN-02-login-sesion`)
- L5-Swagger en `/api/documentation`
