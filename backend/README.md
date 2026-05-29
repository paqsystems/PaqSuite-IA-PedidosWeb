# Backend scaffold (Laravel API)

Base inicial para `PaqSuite-IA-PedidosWeb` en modo MONO.

## Objetivo del scaffold

- API versionada con prefijo `api/v1`.
- Controladores delgados + capa de servicios/repositories.
- Header de tenant `X-Paq-Cliente`.
- Punto de salud para validación técnica.
- Esqueleto de OpenAPI.

## Estructura mínima

- `app/Http/Controllers/HealthController.php`
- `routes/api.php`
- `tests/Feature/HealthCheckTest.php`
- `OpenApi.php`

## Próximos pasos

1. Crear proyecto Laravel 10 (`composer create-project laravel/laravel .`).
2. Incorporar Sanctum.
3. Implementar middleware tenant (`X-Paq-Cliente`).
4. Generar OpenAPI con L5-Swagger.
5. Completar tests de feature de endpoints críticos.
