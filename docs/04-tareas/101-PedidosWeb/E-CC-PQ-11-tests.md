# E — CC PQ #11 (18/08/2026) — Evidencia tests

## Alcance

Parte **E** previa a **F** / **I** sobre correcciones CC #11 (`pq_pedidosweb_clientescontactos` + nodo `contactos[]` en API de clientes).

**Fecha ejecución:** 18/08/2026  
**Entorno:** Local — `Ankas_del_sur` (SQL Server)  
**Rama / HEAD:** `v1.1.1` @ `9e7785f` (working tree con D de #11)

---

## Backend — PHPUnit (filtro CC #11)

```text
php artisan test --filter="VisibleClientPayloadMapperTest|PedidosWebModelsTest::clienteExponeRelacionContactosSinLogicaDeNegocio|VisibilityDataTest|OpenApiDocumentationTest"

Tests: 21 passed (292 assertions)
Duration: 57.13s
```

### Tests relevantes CC #11

| Archivo | Cobertura |
|---------|-----------|
| `VisibleClientPayloadMapperTest.php` (3) | `contactos[]` vacío; camelCase `codContacto`/`telefono`/`mail`; vacío → null |
| `PedidosWebModelsTest.php` (1) | `PqPedidoswebCliente::contactos()`; PK `cod_client` sin identity |
| `VisibilityDataTest.php` (12) | listado 0/N contactos; vendedor no ve cartera ajena; GET unitario 200/401/403/404 |
| `OpenApiDocumentationTest.php` (5) | path `/api/v1/clientes/{codCliente}`; envelope `ApiEnvelopeVisibleClient`; schema `VisibleClientContactItem` |

**AC cubiertos:** AC-CC11-T-M2 (modelo), AC-CC11-T-V1 (listado camelCase), AC-CC11-T-V2 (unitario 200/401/403/404), AC-CC11-T-V3 (OpenAPI tipado), AC-CC11-T-V4 (sin leak de contactos no visibles).

**Nota entorno:** `paqsuite:seed-seguridad-mvp` en CLI choca si locale/theme de usuarios MVP divergieron (`es` vs `es-AR`, tema custom). `VisibilityDataTest` continúa con password de test en transacción si el seed no cierra 0; rollback al terminar. No es DROP ni cambio persistido de esquema.

---

## Frontend — Vitest (regresión HU-101-004)

```text
npx vitest run src/features/pedidos/utils/cargaCatalogos.test.ts

Test Files  1 passed (1)
Tests       4 passed (4)
```

El selector de carga no mapea `contactos` (`loadClientesFromApi` usa solo `codCliente` / `nombre` / `razonSocial` / `nombreFantasia`).

---

## Veredicto Parte E

**Aprobado** — 21 PHPUnit + 4 Vitest en el filtro ejecutado.

**Pendiente Parte F:** smoke HTTP listado/unitario con un contacto de prueba; OpenAPI UI `http://127.0.0.1:8088/api/documentation`.
