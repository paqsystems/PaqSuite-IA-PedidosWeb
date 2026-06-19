# E — CC PQ #7 (15/06/2026) y #8 (19/06/2026) — Evidencia tests

## Alcance

Parte **E** previa a **F** / **I** sobre correcciones CC #7 y CC #8 (implementación directa en originales HU/TR base; sin carpeta `updates/`).

**Fecha ejecución:** 19/06/2026  
**Entorno:** Local — `Ankas_del_sur`  
**Rama / build:** `v1.1.0-paq` @ `df424e8`

---

## Frontend — Vitest

```text
npm run test
Test Files  51 passed (51)
Tests       156 passed (156)
```

### Tests relevantes CC #7 / #8

| Archivo | CC | Cobertura |
|---------|-----|-----------|
| `resolveParametroConsultaTexts.test.ts` | #7 i18n parámetros | caption, tooltip, valor booleano |
| `ParametrosConsultaPage.test.tsx` | #7 | columna Valor centrada |
| `resolveConsultaColumnCaption.test.ts` | #7 i18n pivot | claves `consultas.*` |
| `mapExcelImportToCarga.test.ts` | #8 | conserva vendedor cabecera inicial al importar |
| `articulosCargaLoadPolicy.test.ts` | #8 | política precarga catálogo |
| `ComprobanteGrabacionValidatorTest` (backend) | #7 | sin renglones, nivel extremo, precio cero |
| `CabeceraInicialServicePerfilTest` (backend) | #7 | `CodPerfilPedidos` |

---

## Backend — PHPUnit (filtro CC #7 / #8)

```text
php artisan test --filter="ComprobanteGrabacionValidator|CabeceraInicial|PedidoIndividual"
Tests: 8 passed (14 assertions)
```

---

## Veredicto Parte E

**Aprobado** — suite unitaria relevante en verde; sin regresiones detectadas en el filtro CC #7/#8.
