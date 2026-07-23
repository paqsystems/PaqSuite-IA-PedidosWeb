# Pull Request Report — Asistente IA en carga (SPEC-101-18/19/20)

## Resumen

Cierre F/F1 rev. **2026-07-14** del asistente IA embebido en carga de pedidos/presupuestos: shell, mutaciones (incluye **pedido compuesto multilínea** y **alias** art/item/it), consultas, imagen con cabecera ampliada, y documentación alineada.

## Contexto funcional

Operadores pueden armar el comprobante por texto/voz/imagen al pie de `/pedidos/carga`, con la misma BYOK del Asistente IA y los mismos permisos `Modifica*` que la UI DevExtreme. La revisión reciente corrige que un pedido pegado completo solo aplicaba cliente/transporte/artículos.

## SPEC / HU / TR relacionadas

- SPEC: [101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) · [101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) · [101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md)
- HU: 037…042
- TR: TR-SPEC-101-18 / 19 / 20
- Producto: [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md)
- F1: [D-VERIFICACION-101-18-20-asistente-carga-ia.md](D-VERIFICACION-101-18-20-asistente-carga-ia.md) — **Aprobado con observaciones**
- F: [F-101-18-20-cierre-formal.md](F-101-18-20-cierre-formal.md) — **Aprobado con observaciones**

## Cambios realizados

### Backend

- `CargaAsistenteIntentDetector`: `compositePedido`, alias art/item/it/prod, `canti`, Descto→bonifN, Direccion→expresoDire, sanitize valores.
- `CargaAsistenteTurnService`: `executeCompositeItems`, `deferredCompositeItems`, `withDeferredWork`.
- `CargaAsistenteImageExtractTool`: cabecera ampliada + diferido `cabeceraSteps`.
- Tools cliente/artículo/cabecera (mutaciones, permisos) — evolución post-smoke previa.

### Frontend

- Panel, speech, apply actions, `patchAsistenteCabecera` (leyendas/observaciones).
- i18n 5 locales (claves asistente).

### Base de datos

- Sin migraciones / sin DROP.

### Tests

- PHPUnit CargaAsistente + Feature turn.
- Vitest `cargaAsistenteIa`.

### Documentación

- Producto, SPEC-19 (D1-25/26), HU-039/040, TR-19, F1, F, manual §6.17.

### DevOps

- Sin cambios de deploy obligatorios (reusa BYOK y endpoint turn ya documentado).

## Validaciones y tests ejecutados

| Comando | Resultado |
|---------|-----------|
| `php vendor/bin/phpunit tests/Unit/Services/PedidosWeb/CargaAsistente tests/Feature/Api/PedidosWeb/CargaAsistenteTurnTest.php` | OK — 24 / 151 |
| `npx vitest run src/features/pedidos/cargaAsistenteIa` | OK — 10 |

No re-ejecutado en esta rev.: Playwright E2E; suite PHPUnit completa; `l5-swagger:generate`.

## Evidencia

- F1 2026-07-14 con matriz de ejes y archivos.
- Smoke: diferidos tras preguntas intermedias; pedido pegado motivó D1-25.

## Riesgos

- OBS-F-04: smoke mobile formal pendiente.
- OBS-F-06: falta Feature test end-to-end de `compositePedido` (unit detector sí).

## Pendientes / follow-ups

- [ ] Smoke mobile panel (OBS-F-04)
- [ ] (Opcional) Feature test composite (OBS-F-06)
- [ ] Re-correr Playwright al abrir PR si se desea evidencia E2E fresca

## Checklist para reviewer

- [ ] Pedido multilínea aplica cabecera + renglones con permisos
- [ ] Choice intermedia no pierde el resto del parse
- [ ] Alias `art`/`item`/`it` y `Descto 3` / `Direccion:`
- [ ] Solo lectura / perfil C no elevan privilegios
- [ ] Docs F1/F / SPEC-19 / producto coherentes con el diff

## Notas para QA

1. Pegar pedido completo (cliente, perfil, condición, fecha, transporte, expreso, dirección, lista, bonif 1–3, leyenda, observaciones, 3 ítems).
2. Responder una lista numerada y verificar que el resto se aplica.
3. Probar `item … cant:` e `it "…" canti:`.
4. Usuario sin `ModificaBonCli*` → bonificaciones denied, resto OK.
5. Sin LLM → mensaje fijo Preferencias.
