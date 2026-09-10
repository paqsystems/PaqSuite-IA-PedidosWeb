# Parte E — Tests CC PQ #15 (09/09/2026)

## Alcance

Cobertura de copia de `id_de` + `leyenda_1`…`leyenda_5` en `ComprobanteCopiaService` (TR-SPEC-101-04-update-01).

## Casos agregados

| Test | Archivo | Qué valida |
|------|---------|------------|
| `copiarBorradorCopiaIdDeYLeyendas` | `ComprobanteCopiaServiceTest.php` | `id_de` y leyendas 1–5 del origen en el borrador |
| `copiarBorradorRecortaLeyendasA60` | idem | leyenda 61 → 60 caracteres |
| `copiarBorradorSinIdDeNiLeyendasNoFalla` | idem | nulls sin error |

## Ejecución

```bash
cd backend
php vendor/bin/phpunit --filter ComprobanteCopiaServiceTest
```

**Nota 09/09/2026:** en el entorno del agente la suite falló por timeout TCP a SQL Server (`DatabaseTransactions` en `Tests\TestCase`), no por aserción de negocio. Los casos son unitarios con mocks de repositorio (mismo patrón que la suite previa del archivo).

## Regresión

Mantener suite existente de precios / `ActualizarPrecioCopia` en el mismo archivo.
