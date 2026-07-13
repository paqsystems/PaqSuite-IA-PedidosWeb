# Cierre F Formal — SPEC-101-18 / 19 / 20 (Asistente IA en carga)

## Alcance del cierre

Épica **Asistente IA en carga de pedidos/presupuestos**: shell (18), mutaciones (19), consultas (20), más ajustes **post-smoke** (panel, F/G UX, cabecera C, mutar renglones en detalle).

| TR | HU |
|----|-----|
| [TR-SPEC-101-18](TR-SPEC-101-18-asistente-carga-ia-shell.md) | [037](../../03-historias-usuario/101-PedidosWeb/HU-101-037-asistente-carga-ia-panel-gate.md) · [038](../../03-historias-usuario/101-PedidosWeb/HU-101-038-asistente-carga-ia-audio-imagen.md) |
| [TR-SPEC-101-19](TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) | [039](../../03-historias-usuario/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera.md) · [040](../../03-historias-usuario/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) |
| [TR-SPEC-101-20](TR-SPEC-101-20-asistente-carga-ia-consultas.md) | [041](../../03-historias-usuario/101-PedidosWeb/HU-101-041-asistente-carga-ia-consulta-stock.md) · [042](../../03-historias-usuario/101-PedidosWeb/HU-101-042-asistente-carga-ia-consultas-cliente.md) |

**SPEC:** [101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) · [101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) · [101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md)

**Producto:** [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md)

**F1:** [D-VERIFICACION-101-18-20-asistente-carga-ia.md](D-VERIFICACION-101-18-20-asistente-carga-ia.md) — **Aprobado con observaciones** (2026-07-13)

**A1 / B1 / C1:** [cierre A1](F-101-18-20-cierre-a1-asistente-carga-ia.md) · [B1](F-101-18-20-cierre-b1-asistente-carga-ia.md) · [C1](F-101-18-20-cierre-c1-asistente-carga-ia.md)

## Resultado global

**Aprobado con observaciones**

Implementación D operativa + post-smoke validado en sesión de producto. Cierre F documental y OpenAPI del turn completados (2026-07-13).

## Resumen por capacidad

| Slice | Resultado | Evidencia |
|-------|-----------|-----------|
| Panel / gate / audio / imagen (18) | Aprobado | `CargaAsistenteIaPanel`, altura 270px/33vh, Web Speech, adjunto, ruedita Preferencias, gate BYOK FE+BE |
| Cliente / cabecera / cambio I (19 A–C, I) | Aprobado | Tools cliente + cabecera C ampliada (bonif, expreso, transporte, cond, perfil, lista, fecha, dirección) |
| Artículos / grabar / imagen K (19 D, J, K) | Aprobado c/ obs. | Alta + `mutateExistingRenglon` en detalle; comillas/final; conjugados elimina; grabar invoke FE |
| Consultas E–H (20) | Aprobado | Tools stock/deuda/cheques/historial; fechas YYYY-MM-DD; totales F/G; tabla HTML |
| OpenAPI turn | Aprobado | `POST /api/v1/pedidos/carga/asistente/turn` + schemas `CargaAsistente*` |

## Verificación automatizada (2026-07-13)

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit tests/Unit/Services/PedidosWeb/CargaAsistente tests/Feature/Api/PedidosWeb/CargaAsistenteTurnTest.php` | **OK** — 13 tests / 76 assertions |
| `npx vitest run src/features/pedidos/cargaAsistenteIa` | **OK** — 9 tests |
| `php artisan l5-swagger:generate` | **OK** (ver § OpenAPI) |

## Smoke manual (producto)

| Escenario | Estado |
|-----------|--------|
| Consultas deuda/cheques (fechas/totales/tabla) | OK (sesión post-smoke) |
| Cabecera vía chat con permisos | OK |
| Eliminar artículo ambiguo en detalle (`elimina el articulo arroz`) | OK tras fix conjugados |
| Modificar con comillas / descripción al final | OK (convención producto) |
| Mobile nativo panel | No smoke formal en este cierre |

## Criterios (síntesis F / openspec-05)

| Eje | Estado | Notas |
|-----|--------|-------|
| Alcance ⊆ SPEC/HU/TR | OK | Post-smoke D1-23/24/25 reflejados en docs |
| Código ↔ TR | OK | Turn + tools + panel + apply actions |
| Datos / esquema | OK | Sin DROP; BYOK existente |
| Backend / permisos | OK | Revalida en tools; gate sin LLM |
| Frontend / testids / i18n | OK | 5 locales; testids panel + consulta table |
| Tests | OK c/ obs. | IntentDetector + Turn + FE utils; faltan unit tools |
| Documentación | OK | Producto, SPEC, HU, TR, manual §6.17, F1, este F |
| OpenAPI | OK | Schemas + operationId en controller |
| Trazabilidad | OK | F1 + este cierre |

## Observaciones (no bloquean cierre)

| ID | Tema | Estado | Notas |
|----|------|--------|-------|
| OBS-F-01 | Unit tests tools | **Cerrado** (2026-07-13) | `CargaAsistenteToolsTest` + `CargaAsistenteConsultaFormatting` |
| OBS-F-02 | E2E Playwright | **Cerrado** (2026-07-13) | `pedidos-carga-asistente.spec.ts` (mock turn) |
| OBS-F-03 | Reply i18n partido | **Cerrado** (2026-07-13) | BE emite `renglonNoEncontradoConQ` + `payload.q` |
| OBS-F-04 | Smoke mobile | **Abierto** | Rama native montada; smoke formal mobile pendiente |
| OBS-F-05 | DoD checklists SPEC | **Parcial** | Queda ligado a OBS-F-04 / endurecimiento stock fixture |

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

**Parte F cerrada** para SPEC-101-18/19/20 — **Aprobado con observaciones**.

Autoriza PR / release del slice asistente de carga. Seguimiento principal post-merge: **OBS-F-04** (smoke mobile).

## Enlaces

- Manual operador: [PedidosWeb.md §6.17](../../99-manual-usuario/PedidosWeb.md)
- Matriz permisos: acceso vía pantalla `pw_cargapedidos` + BYOK usuario (sin permiso menú nuevo)
