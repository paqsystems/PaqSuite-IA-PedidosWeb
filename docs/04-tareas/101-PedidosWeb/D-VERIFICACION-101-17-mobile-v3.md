# Verificación Parte D / F1 — SPEC-101-17 mobile v3 (`v1.2.2-mobile`)

| Campo | Valor |
|-------|--------|
| **Fecha D** | 2026-07-01 |
| **Fecha smoke Android** | 2026-07-01 (emulador; carga pedido + listados con acciones OK) |
| **Release** | Tag Git **`v1.2.2-mobile`** (pendiente smoke iOS + dispositivo físico) |
| **SPEC** | [SPEC-101-17](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **TR** | [TR-SPEC-101-17-mobile-v3-carga](TR-SPEC-101-17-mobile-v3-carga.md) |
| **Cierre F v3** | [F-101-17-cierre-formal-v3](F-101-17-cierre-formal-v3.md) |

## Verificación automatizada (2026-07-01)

| Comando | Resultado |
|---------|-----------|
| `npm run build` / `build:mobile` | **OK** |
| `npm test -- --run` | **OK** (191 tests; incl. `pedidosWebMobilePolicy.test.ts` v3) |
| `npx cap sync android` | **OK** |
| `.\gradlew assembleDebug` | **OK** — APK debug generado |

## Resumen por TR v3

| TR | HU | Estado D | Evidencia |
|----|-----|----------|-----------|
| TR-SPEC-101-17-mobile-v3-carga | HU-101-036 | **OK** | Wizard `PedidosCargaMobilePage`, `usePedidosCargaMobile` |
| Ext. listados v3 | HU-101-035 | **OK** | `ComprobanteCardMobileActions`, `useComprobanteMobileRowActions` |
| Ext. shell / policy | — | **OK** | `filterMenuTreeForMobileV3`, `/pedidos/carga` en guard |

## Inventario código ↔ documentación

| Artefacto | Documentado en TR | Notas |
|-----------|-------------------|-------|
| `PedidosCargaMobilePage.tsx` | §2 | Wizard 4 pasos + testids TR §5 |
| `usePedidosCargaMobile.ts` | §2 | API `comprobanteApi` compartida |
| `PedidosCargaMobileCabeceraStep.tsx` | §2 (enriquecido) | Reutiliza `ComprobanteCabeceraForm` + leyendas (más que subset mínimo TR §1) |
| `pedidosWebMobilePolicy.ts` v3 | §2 | `mobileV3AllowedRoutePrefixes` |
| `ComprobanteListadoMobileView` + acciones | §1 acciones listados | Iconos + tooltips táctiles |
| `ConsultaKardexList` `renderCardActions` | §1 | Barra acciones fuera del tap tarjeta |
| `PedidosCargaRenglonEditDialog` responsive | — (fix transversal) | Popup mobile; documentado OBS-D3-03 |
| `shellLayout.css` scroll web | — (fix transversal) | Paridad scroll `/pedidos/carga` web; OBS-D3-04 |

## Smoke manual Android (emulador)

| # | Caso | Estado |
|---|------|--------|
| 1 | Menú native incluye **Carga pedidos** (`/pedidos/carga`) | **OK** |
| 2 | Wizard: cliente → cabecera → artículos → confirmar → grabar pedido | **OK** |
| 3 | Pedido grabado visible en web **Pedidos ingresados** | **OK** |
| 4 | Listados: iconos ver/editar/eliminar/copiar según `puede*` | **OK** |
| 5 | Tooltips en iconos (tap / hold) | **OK** |
| 6 | Tap tarjeta → popup detalle solo lectura | **OK** |
| 7 | Modos URL ver/editar/copiar desde listado | **OK** (smoke parcial) |
| 8 | Presupuesto mobile smoke | **Pendiente** formal |
| 9 | Dispositivo físico Android | **Pendiente** (APK debug generado) |
| 10 | iOS | **Pendiente** |
| 11 | Web regresión `/pedidos/carga` desktop | **OK** (scroll fix) |

## Fuera de alcance v3 (confirmado)

| Ítem | Notas |
|------|--------|
| Importación Excel | Exclusión mobile |
| Paridad total SPEC-101-10 desktop | Wizard mobile dedicado |
| Presupuestos tab cerrados | Igual v2 |
| Cerrar presupuesto en listado mobile | Web only; OBS-D3-05 |
| Pivot / admin | Exclusión mobile |

## Observaciones

| ID | Tema | Notas |
|----|------|-------|
| OBS-D3-01 | Tag `v1.2.2-mobile` | Tras smoke iOS + dispositivo físico |
| OBS-D3-02 | Cabecera mobile | Implementación reutiliza formulario web completo (leyendas, bonif., transporte) |
| OBS-D3-03 | Popup renglón | Ancho responsive mobile |
| OBS-D3-04 | Scroll web carga | `shellLayout` height/overflow |
| OBS-D3-05 | Acción cerrar presupuesto | No incluida en barra kardex v3 |

## Veredicto D / F1

**D2 + verificación smoke Android emulador: OK** — listo para cierre F v3.

Informe F: [F-101-17-cierre-formal-v3](F-101-17-cierre-formal-v3.md).
