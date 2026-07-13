# Verificación del agente — TR-SPEC-101-18 / 19 / 20 (Asistente IA carga)

| Campo | Valor |
|-------|--------|
| **Fecha F1** | 2026-07-13 |
| **TR** | [TR-18](./TR-SPEC-101-18-asistente-carga-ia-shell.md) · [TR-19](./TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) · [TR-20](./TR-SPEC-101-20-asistente-carga-ia-consultas.md) |
| **SPEC** | [101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) · [101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) · [101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md) |
| **HU** | 037…042 |
| **Producto** | [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) |
| **Alcance F1** | Implementación D + ajustes post-smoke (panel, consultas F/G, cabecera C, mutar renglones) |

## Resultado

**Aprobado con observaciones**

## Evidencia revisada

### Código (eje 1–2, 4–5)

| Capacidad | Evidencia |
|-----------|-----------|
| Shell panel / altura D1-16 | `CargaAsistenteIaPanel.tsx` + `.css` (`min-height: 16.875rem`, `max(270px, 33vh)`, scroll interno) |
| Gate BYOK | `CargaAsistenteTurnService` + Feature test `Turn rejects when configuration is missing` |
| Turn API | `POST /pedidos/carga/asistente/turn` — Controller + Request |
| Cabecera C ampliada (D1-23) | `CargaAsistenteCabeceraTool` + `IntentDetector` (bonif, expreso, transporte, cond, perfil, lista, fecha, dirección) + FE `patchAsistenteCabecera.ts` |
| Mutar renglón detalle (D1-24) | `ArticuloTool::mutateExistingRenglon` solo `draftContext.renglones`; conjugados elimina→remove; comillas/`extractMutateArticuloQuery`; choice lista cant·precio·bonif; FE `removeRenglon`/`updateRenglon` |
| Consultas E–H + UX F/G | Tools Deuda/Cheques (fecha date-only, totals si >1); FE `CargaAsistenteConsultaTable` + `formatShowConsulta` |
| i18n | Claves `elegirRenglon`, `renglonNoEncontrado`, `renglonNoEncontradoConQ`, etc. en es/en/fr/pt/it |

### Datos (eje 3)

- Sin migraciones/DROP en este slice (reusa BYOK `pq_asistente_ia_*` y APIs de carga/consultas). **OK** — no aplica esquema nuevo.

### Documentación (eje 7)

- Producto §9/§21, SPEC-18/19/20 historiales D1, HU-039/040/042, TR-18/19/20, manual `PedidosWeb.md` §6.17 actualizados (2026-07-13).
- **Gap:** no se halló entrada OpenAPI/Swagger explícita para `POST .../carga/asistente/turn` en artefactos buscados.

### Trazabilidad (eje 8)

- TR-18/19/20 + D1-PLAN + cierres A1/B1/C1 existentes.
- Post-smoke reflejado en producto/SPEC/HU/TR (no hay documento F de cierre formal post-smoke aún — pendiente openspec-05 / F).

## Hallazgos críticos

Ninguno que bloquee el uso operativo del MVP verificado en smoke manual (eliminar/modificar renglón, cabecera, consultas).

## Advertencias

1. **Cobertura de tests incompleta vs DoD SPEC:** no hay unit tests de tools (`ArticuloTool` mutate, `CabeceraTool` permisos denied, Deuda/Cheques totals). Feature turn cubre auth/gate/envelope/help, no mutaciones end-to-end.
2. **Sin E2E Playwright** del panel asistente.
3. **Contrato i18n partido:** BE emite `renglonNoEncontrado` + `payload.q`; FE traduce a `renglonNoEncontradoConQ` — funciona, pero conviene documentarlo en TR como contrato estable.
4. Checklists “Definición de listo” en SPEC-18/19/20 siguen con ítems abiertos (paridad smoke formal, fixture stock, etc.).
5. OpenAPI del turn no verificado / posiblemente ausente.

## Sugerencias

1. ~~Unit tests tools~~ — hecho (`CargaAsistenteToolsTest`).
2. ~~OpenAPI turn~~ — hecho.
3. ~~Unificar i18n ConQ~~ — hecho.
4. ~~E2E panel~~ — hecho (`pedidos-carga-asistente.spec.ts`).
5. Smoke mobile formal (OBS-F-04) — diferido.

## Tests

### Comandos ejecutados (evidencia 2026-07-13)

```text
backend> php vendor/bin/phpunit tests/Unit/Services/PedidosWeb/CargaAsistente/CargaAsistenteToolsTest.php --testdox
→ OK — 5 tests (mutate, denied, formatting F/G)

frontend> npx playwright test tests/e2e/pedidosweb/pedidos-carga-asistente.spec.ts
→ OK — 1 passed
```

### No ejecutados (declarado)

- Suite PHPUnit completa del backend
- Smoke dispositivo/mobile nativo del panel (OBS-F-04)

### Smoke manual (sesión producto, no automatizado)

- Usuario validó: eliminar artículo ambiguo en detalle (`elimina el articulo arroz` → lista), modificar con comillas/final, corrección conjugados (antes caía en maestro).

## Pendientes

| Prioridad | Ítem |
|-----------|------|
| Media | Smoke mobile formal (**OBS-F-04**) |
| ~~Media~~ | ~~Unit tests / E2E / i18n ConQ / OpenAPI / Parte F~~ |

## Recomendación final

**Parte F cerrada**; OBS-F-01/02/03 resueltos. Queda **OBS-F-04** (mobile). Ver [F-101-18-20-cierre-formal.md](F-101-18-20-cierre-formal.md).
