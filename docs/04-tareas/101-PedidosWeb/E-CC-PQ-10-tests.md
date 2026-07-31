# E — CC PQ #10 (30/07/2026) — Evidencia tests

## Alcance

Parte **E** previa a **F** / **I** sobre correcciones CC #10 (`CargaUnidadesVenta`, `equivalencia_ventas`, `cantidad_venta`).

**Fecha ejecución:** 30/07/2026  
**Entorno:** Local — PHPUnit sin BD (suite unitaria pura); Vitest frontend  
**Nota:** tests que extienden `Tests\TestCase` con `DatabaseTransactions` fallan por timeout SQL Server en este entorno; la evidencia E usa tests sin boot Laravel donde aplica.

---

## Backend — PHPUnit (filtro CC #10)

```text
php vendor/bin/phpunit \
  tests/Unit/PedidosWeb/Services/CargaUnidadesVentaConverterTest.php \
  tests/Unit/PedidosWeb/Services/CalculoTotalesServiceTest.php \
  tests/Unit/Seed/PqParametrosGralPedidosWebSeedTest.php

Tests: 15 passed (47 assertions)
```

### Tests relevantes CC #10

| Archivo | Cobertura |
|---------|-----------|
| `CargaUnidadesVentaConverterTest.php` (7) | equiv 0/null→1; modo stock/venta; cantidad visible; `ensurePair` snake/camel; deriva por campo presente |
| `CalculoTotalesServiceTest.php` (6) | regresiones importes + persiste `cantidad_venta` + importes desde `cantidad` + deriva si falta |
| `PqParametrosGralPedidosWebSeedTest.php` (2) | Seed JSON `ActualizarPrecioCopia` + `CargaUnidadesVenta` tipo `B` |

**Mail (AC-CC10-T-MAIL1):** selector de cantidad visible = `CargaUnidadesVentaConverter::cantidadVisibleParaUsuario` (cubierto en converter). Envío end-to-end mail queda para smoke / suite con BD.

**Parámetro runtime `getCargaUnidadesVenta`:** tests en `PedidosWebParameterServiceTest` (requieren BD en este entorno); semántica alineada a seed + config default `false`.

---

## Frontend — Vitest (filtro CC #10)

```text
npx vitest run \
  src/features/pedidos/utils/cargaUnidadesVenta.test.ts \
  src/features/config/utils/resolveParametroConsultaTexts.test.ts \
  src/features/consultas/components/consultaColumns.test.tsx \
  src/features/pedidos/utils/mapExcelImportToCarga.test.ts

Test Files  4 passed (4)
Tests       20 passed (20)
```

### Tests relevantes CC #10

| Archivo | Cobertura |
|---------|-----------|
| `cargaUnidadesVenta.test.ts` (5) | conversión FE / applyCantidadUsuario |
| `resolveParametroConsultaTexts.test.ts` (+1) | caption i18n `CargaUnidadesVenta` |
| `consultaColumns.test.tsx` (+1) | columna `cantidadVenta` en Detalle de Pedidos |
| `mapExcelImportToCarga.test.ts` (+1) | map `cantidad_venta` → `cantidadVenta` |

---

## Veredicto Parte E

**Aprobado** — suite unitaria CC #10 en verde (15 PHPUnit + 20 Vitest en el filtro ejecutado).

**Pendiente opcional:** PHPUnit mail con `Mail::fake` y `PedidosWebParameterServiceTest` cuando haya SQL Server alcanzable; E2E carga param true/false (QA manual / Parte F).
