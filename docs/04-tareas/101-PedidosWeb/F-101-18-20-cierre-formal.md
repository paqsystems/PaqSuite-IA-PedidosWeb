# Cierre F Formal — SPEC-101-18 / 19 / 20 (Asistente IA en carga)

## Alcance del cierre

Épica **Asistente IA en carga de pedidos/presupuestos**: shell (18), mutaciones (19), consultas (20), post-smoke 13/07, y revisión **14/07** (pedido compuesto multilínea, alias renglón/cabecera, extracto imagen con cabecera ampliada, diferidos post-choice).

| TR | HU |
|----|-----|
| [TR-SPEC-101-18](TR-SPEC-101-18-asistente-carga-ia-shell.md) | [037](../../03-historias-usuario/101-PedidosWeb/HU-101-037-asistente-carga-ia-panel-gate.md) · [038](../../03-historias-usuario/101-PedidosWeb/HU-101-038-asistente-carga-ia-audio-imagen.md) |
| [TR-SPEC-101-19](TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) | [039](../../03-historias-usuario/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera.md) · [040](../../03-historias-usuario/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) |
| [TR-SPEC-101-20](TR-SPEC-101-20-asistente-carga-ia-consultas.md) | [041](../../03-historias-usuario/101-PedidosWeb/HU-101-041-asistente-carga-ia-consulta-stock.md) · [042](../../03-historias-usuario/101-PedidosWeb/HU-101-042-asistente-carga-ia-consultas-cliente.md) |

**SPEC:** [101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) · [101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) · [101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md)

**Producto:** [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md)

**F1:** [D-VERIFICACION-101-18-20-asistente-carga-ia.md](D-VERIFICACION-101-18-20-asistente-carga-ia.md) — **Aprobado con observaciones** (2026-07-14)

**A1 / B1 / C1:** [cierre A1](F-101-18-20-cierre-a1-asistente-carga-ia.md) · [B1](F-101-18-20-cierre-b1-asistente-carga-ia.md) · [C1](F-101-18-20-cierre-c1-asistente-carga-ia.md)

## Resultado global

**Aprobado con observaciones**

Implementación D operativa + post-smoke 13/07 + revisión producto 14/07 (D1-25/26). Documentación alineada a código. OpenAPI del turn se mantiene del cierre previo.

## Resumen por capacidad

| Slice | Resultado | Evidencia |
|-------|-----------|-----------|
| Panel / gate / audio / imagen (18) | Aprobado | `CargaAsistenteIaPanel`, 270px/33vh, Web Speech, adjunto, ruedita Preferencias, gate BYOK |
| Cliente / cabecera / cambio I (19 A–C, I) | Aprobado | Tools + **compositePedido** + Descto/Direccion; flags `Modifica*` |
| Artículos / grabar / imagen K (19 D, J, K) | Aprobado c/ obs. | Alta + mutate detalle; alias art/item/it + canti; imagen cabecera ampliada; grabar FE |
| Consultas E–H (20) | Aprobado | Stock/deuda/cheques/historial; fechas; totales F/G; tabla HTML |
| OpenAPI turn | Aprobado | `POST .../carga/asistente/turn` — sin cambio de shape en rev. 14/07 |

## Decisiones post-cierre inicial (rev. 2026-07-14)

| ID | Tema | Estado |
|----|------|--------|
| D1-25 | Pedido compuesto multilínea + `deferredCompositeItems` | Cerrado en código + docs |
| D1-26 | Alias art/item/it, canti, Descto N, Direccion→expresoDire | Cerrado en código + docs |
| T-19-14 | Imagen K con pasos de cabecera diferibles | Cerrado en código + docs |

## Verificación automatizada (2026-07-14)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit tests/Unit/Services/PedidosWeb/CargaAsistente tests/Feature/Api/PedidosWeb/CargaAsistenteTurnTest.php` | **OK** — 24 tests / 151 assertions |
| `npx vitest run src/features/pedidos/cargaAsistenteIa` | **OK** — 10 tests |
| `php artisan l5-swagger:generate` | No re-ejecutado en rev. 14/07 (sin cambio OpenAPI) |

## Smoke manual (producto)

| Escenario | Estado |
|-----------|--------|
| Consultas deuda/cheques (fechas/totales/tabla) | OK (13/07) |
| Cabecera vía chat con permisos | OK (13/07) |
| Conservar datos tras preguntas intermedias | OK (sesión 14/07) |
| Pedido completo pegado (cliente+cabecera+renglones) | OK tras D1-25 (sesión / unit composite) |
| Alias `art`/`item`/`it` | Unit OK; smoke UI no repetido |
| Mobile nativo panel | No smoke formal (**OBS-F-04**) |

## Criterios (síntesis F / openspec-05)

| Eje | Estado | Notas |
|-----|--------|-------|
| Alcance ⊆ SPEC/HU/TR | OK | D1-25/26 reflejados en producto/SPEC/HU/TR |
| Código ↔ TR | OK | Turn + IntentDetector + ImageExtract + panel/apply |
| Datos / esquema | OK | Sin DROP; BYOK existente |
| Backend / permisos | OK | Revalida en tools; gate sin LLM |
| Frontend / testids / i18n | OK | 5 locales; pendingChoice conserva diferidos |
| Tests | OK c/ obs. | Composite/alias en unit; falta Feature E2E composite (**OBS-F-06**) |
| Documentación | OK | Producto, SPEC-19, HU-039/040, TR-19, F1, este F |
| OpenAPI | OK | Sin regresión de contrato |
| Trazabilidad | OK | F1 rev. + este cierre |

## Observaciones (no bloquean cierre)

| ID | Tema | Estado | Notas |
|----|------|--------|-------|
| OBS-F-01 | Unit tests tools | **Cerrado** (2026-07-13) | `CargaAsistenteToolsTest` |
| OBS-F-02 | E2E Playwright | **Cerrado** (2026-07-13) | `pedidos-carga-asistente.spec.ts` (no re-corrido 14/07) |
| OBS-F-03 | Reply i18n partido | **Cerrado** (2026-07-13) | `renglonNoEncontradoConQ` |
| OBS-F-04 | Smoke mobile | **Abierto** | Rama native montada; smoke formal pendiente |
| OBS-F-05 | DoD checklists SPEC | **Parcial** | Ligado a OBS-F-04 |
| OBS-F-06 | Feature test compositePedido | **Abierto** | Baja prioridad; unit IntentDetector cubre parse |

## OpenAPI

| Ítem | Valor |
|------|--------|
| Path | `POST /api/v1/pedidos/carga/asistente/turn` |
| operationId | `pedidosCargaAsistenteTurn` |
| Request schema | `CargaAsistenteTurnRequest` |
| Success envelope | `ApiEnvelopeCargaAsistenteTurn` → `CargaAsistenteTurnResultado` |
| Annotations | `CargaAsistenteTurnController` + `OpenApiSchemas` |
| Auth | Sanctum + `X-Paq-Cliente` |
| Gate | 422 / error envelope con `resultado.configurationRequired` |

## Fuera de alcance (confirmado)

- Corpus del chat documental en el turn de carga.
- STT del proveedor LLM (solo Web Speech).
- Pivot / Excel desde el asistente.
- Tablas BD de historial del hilo de carga.

## Veredicto

**Parte F cerrada** (rev. **2026-07-14**) para SPEC-101-18/19/20 — **Aprobado con observaciones**.

Autoriza PR / release del slice asistente de carga con pedido compuesto y aliases. Seguimiento post-merge: **OBS-F-04** (smoke mobile); opcional **OBS-F-06**.

## Enlaces

- Manual operador: [PedidosWeb.md §6.17](../../99-manual-usuario/PedidosWeb.md)
- Matriz permisos: acceso vía pantalla `pw_cargapedidos` + BYOK usuario (sin permiso menú nuevo)
- PR report: [`_PR-prompt-asistente-carga-ia.md`](_PR-prompt-asistente-carga-ia.md)
