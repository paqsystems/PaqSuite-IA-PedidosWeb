# Verificación del agente — TR-SPEC-101-18 / 19 / 20 (Asistente IA carga)

| Campo | Valor |
|-------|--------|
| **Fecha F1** | 2026-07-14 (rev. post D1-25/26; base 2026-07-13) |
| **TR** | [TR-18](./TR-SPEC-101-18-asistente-carga-ia-shell.md) · [TR-19](./TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) · [TR-20](./TR-SPEC-101-20-asistente-carga-ia-consultas.md) |
| **SPEC** | [101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) · [101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) · [101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md) |
| **HU** | 037…042 |
| **Producto** | [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) |
| **Alcance F1** | Implementación D + post-smoke 13/07 + **pedido compuesto / alias / cabecera completa / diferidos** (14/07) |

## Resultado

**Aprobado con observaciones**

## Evidencia revisada

### 1–2. Alcance y código

| Capacidad | Evidencia código | Doc alineada |
|-----------|------------------|--------------|
| Shell / gate / audio / imagen UI | `CargaAsistenteIaPanel`, speech, fileToBase64 | SPEC-18 / HU-037–038 |
| Cliente / I confirm | `CargaAsistenteClienteTool` | SPEC-19 A/I · HU-039 |
| Cabecera B/C + permisos | `CargaAsistenteCabeceraTool` + `resolveModificaFlags` | D1-23 · producto §7–8 |
| **Pedido compuesto** (D1-25) | `IntentDetector::detectCompositeItems` → `compositePedido`; `TurnService::executeCompositeItems` + `deferredCompositeItems` + `withDeferredWork` | producto §4.10 · SPEC-19 D1-25 · HU-039 CA-18 · TR T-19-12 |
| **Alias** (D1-26) | `ARTICULO_KEYWORD_REGEX`; `canti`; Descto→bonifN; Direccion→expresoDire | producto §8–9 · SPEC-19 D1-26 · HU-040 CA-04b · TR T-19-13 |
| Mutar renglón detalle | `ArticuloTool::mutateExistingRenglon` | D1-24 |
| Imagen K cabecera ampliada | `ImageExtractTool` prompt + `buildCabeceraStepsFromParsed` / diferido `cabeceraSteps` | T-19-14 · producto §21 |
| Consultas E–H | Tools stock/deuda/cheques/historial + tabla FE | SPEC-20 |
| FE apply | `applyCargaAsistenteActions` + `patchAsistenteCabecera` (leyendas/obs) | — |

### 3. Datos

- Sin migraciones/DROP en este slice. BYOK existente. **OK**.

### 4. Backend

- Revalidación de permisos en tools de cabecera/artículos.
- Gate BYOK sin inventar acciones.
- Valores de campo sanitizados (primera línea; no comer “lista de precios” dentro de leyenda).
- Keywords cortas (`it`/`art`) con límite de palabra (no parten `item`/`articulo`).

### 5. Frontend

- Panel + testids estables; i18n 5 locales.
- `pendingChoice` round-trip conserva `deferredCompositeItems` / `deferredImageExtract` (`[key: string]: unknown`).

### 6. Tests (ejecutados 2026-07-14)

```text
backend> php vendor/bin/phpunit tests/Unit/Services/PedidosWeb/CargaAsistente tests/Feature/Api/PedidosWeb/CargaAsistenteTurnTest.php --testdox
→ OK — 24 tests / 151 assertions
   · ImageExtract deferred
   · IntentDetector (composite, alias art/item/it, Descto, Direccion, canti, mutate…)
   · Tools (mutate, denied C, formatting F/G)
   · Feature turn (auth, gate config, envelope, help)

frontend> npx vitest run src/features/pedidos/cargaAsistenteIa
→ OK — 10 tests (formatShowConsulta, draftContext, applyActions)
```

### No ejecutados (declarado)

- Suite PHPUnit completa del backend.
- Playwright E2E del panel en esta revisión (existía `pedidos-carga-asistente.spec.ts` en cierre 13/07; no re-corrido hoy).
- Smoke formal mobile (OBS-F-04).

### Smoke manual producto (sesión)

- Pedido completo pegado: solo cliente/transporte/artículos antes del fix → motivó D1-25.
- Conservar datos tras preguntas intermedias: OK (sesión previa).
- Alias art/item/it: cubierto por unit tests; smoke UI no repetido post-alias.

### 7. Documentación

Actualizado 2026-07-14:

- Producto (§4.10, §8–9, CA-C02/D01, decisiones 15–16, §21).
- SPEC-101-19 (D1-25/26, CA-C02, K cabecera, historial).
- HU-039 CA-18 · HU-040 CA-04b.
- TR-19 T-19-12…14 + casos de test.
- Este F1 + [F cierre formal](F-101-18-20-cierre-formal.md).

### 8. Trazabilidad

- Cadena SPEC → HU → TR → código → tests → F1/F coherente para D1-25/26.
- OpenAPI turn ya cerrado en F 2026-07-13 (sin cambio de contrato de request en este rev.).

## Hallazgos críticos

Ninguno bloqueante para cierre F de la revisión 14/07.

## Advertencias

1. **OBS-F-04** (smoke mobile formal) sigue abierto.
2. No hay Feature/E2E del turn **composite** end-to-end (solo unit IntentDetector + orquestación cubierta por código/test unitario del detector; TurnService composite sin test dedicado).
3. Checklists CA en SPEC/HU siguen con checkboxes abiertos a nivel formal (estado documental histórico); comportamiento verificado por tests/smoke parcial.

## Sugerencias

1. Feature test opcional: turn con mensaje multilínea mock tools/DB → N actions.
2. Re-correr Playwright al armar PR si el panel cambió layout/i18n.
3. Cerrar OBS-F-04 en release mobile.

## Pendientes

| Prioridad | Ítem |
|-----------|------|
| Media | Smoke mobile formal (**OBS-F-04**) |
| Baja | Feature test `compositePedido` end-to-end (**OBS-F-06**, nueva) |

## Recomendación final

**Aprobado con observaciones** → autoriza cierre **F** rev. 2026-07-14. Ver [F-101-18-20-cierre-formal.md](F-101-18-20-cierre-formal.md).
