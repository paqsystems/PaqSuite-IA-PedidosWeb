# E — CC PQ #9 (02/07/2026) — Evidencia tests

## Alcance

Parte **E** previa a **F** / **I** sobre correcciones CC #9 (`ActualizarPrecioCopia` + copia paramétrica + consulta parámetros).

**Fecha ejecución:** 02/07/2026  
**Entorno:** Local — `Ankas_del_sur`  
**Rama / build:** `v1.1.0-paq` @ `29d2ce3` (working tree con fixes F)

---

## Backend — PHPUnit (filtro CC #9)

```text
php vendor/bin/phpunit \
  tests/Unit/PedidosWeb/Services/ComprobanteCopiaServiceTest.php \
  tests/Unit/PedidosWeb/Services/PedidosWebParameterServiceTest.php \
  tests/Unit/Seed/PqParametrosGralPedidosWebSeedTest.php

Tests: 20 passed (40 assertions)
```

### Tests relevantes CC #9

| Archivo | Cobertura |
|---------|-----------|
| `ComprobanteCopiaServiceTest.php` (16) | `false` conserva origen; rechazo precio cero origen; actualiza desde lista; recálculo importes; rechazo sin precio/precio cero en lista; permisos separados `ArticulosSinPrecio` / `ArticulosPrecioCero`; lista inválida valida origen |
| `PedidosWebParameterServiceTest.php` (3) | `getActualizarPrecioCopia`; preferencia clave canónica `ArticulosSinPrecio` sobre legacy `Articulossinprecio` |
| `PqParametrosGralPedidosWebSeedTest.php` (1) | Seed JSON incluye `ActualizarPrecioCopia` tipo `B` |

---

## Frontend — Vitest (filtro CC #9)

```text
npm run test -- src/features/config/utils/resolveParametroConsultaTexts.test.ts

Test Files  1 passed (1)
Tests       3 passed (3)
```

### Tests relevantes CC #9

| Archivo | Cobertura |
|---------|-----------|
| `resolveParametroConsultaTexts.test.ts` | i18n caption/tooltip `ActualizarPrecioCopia` |

---

## Veredicto Parte E

**Aprobado** — suite unitaria CC #9 en verde; sin regresiones detectadas en el filtro ejecutado.

**Nota:** E2E copia paramétrica no automatizado; cubierto por QA manual PQ (Parte F).
