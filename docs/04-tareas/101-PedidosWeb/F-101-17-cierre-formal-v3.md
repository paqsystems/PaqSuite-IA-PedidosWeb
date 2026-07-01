# Cierre F Formal — SPEC-101-17 Mobile v3 (`v1.2.2-mobile`)

## Alcance del cierre

Release **`v1.2.2-mobile`**: carga pedidos/presupuestos mobile + acciones comprobante en listados kardex.

| TR | HU |
|----|-----|
| [TR-SPEC-101-17-mobile-v3-carga](TR-SPEC-101-17-mobile-v3-carga.md) | [HU-101-036](../../03-historias-usuario/101-PedidosWeb/HU-101-036-mobile-v3-carga-pedidos.md) |

**SPEC:** [SPEC-101-17-mobile-capacitor-pedidosweb.md](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md)

**Base v2:** [F-101-17-cierre-formal-v2](F-101-17-cierre-formal-v2.md)  
**Verificación D / F1:** [D-VERIFICACION-101-17-mobile-v3](D-VERIFICACION-101-17-mobile-v3.md)

## Resultado global

- **Aprobado con observaciones**

Implementación D2 v3 completa. **Smoke Android emulador validado** (carga pedido, listados con acciones, tooltips). Tag `v1.2.2-mobile` pendiente smoke **iOS** y **dispositivo físico**.

## Resumen por slice

| Slice | Resultado | Evidencia |
|-------|-----------|-----------|
| Carga wizard mobile | Aprobado | `PedidosCargaMobilePage`, grabación pedido OK |
| Cabecera mobile | Aprobado | `PedidosCargaMobileCabeceraStep` + form web reutilizado |
| Artículos mobile | Aprobado | Tarjetas renglón + `PedidosCargaRenglonEditDialog` responsive |
| Policy / menú v3 | Aprobado | `/pedidos/carga` habilitado en native |
| Acciones listados kardex | Aprobado | ver/editar/eliminar/copiar/convertir según `puede*` |
| Tooltips iconos | Aprobado | DevExtreme `Tooltip` táctil + i18n `grid.action.*` |

## Verificación automatizada (2026-07-01)

| Comando | Resultado |
|---------|-----------|
| `npm run build:mobile` | OK |
| `npm test -- --run` | OK (191) |
| `npx cap sync android` | OK |
| `assembleDebug` | OK |

## Criterios HU-101-036

| CA | Estado | Notas |
|----|--------|-------|
| CA-01 Crear pedido mobile | **OK** | Smoke emulador |
| CA-02 Crear presupuesto mobile | **Pendiente** | Flujo implementado; smoke formal pendiente |
| CA-03 Perfiles C/V/S | **OK** | Misma API/permisos web |
| CA-04 Tag `v1.2.2-mobile` | **Pendiente** | Tras smoke iOS + físico |

## Smoke manual

| Plataforma | Estado |
|------------|--------|
| Android emulador | **OK** |
| Android dispositivo físico | **Pendiente** (APK debug disponible) |
| iOS | **Pendiente** |
| Web regresión carga desktop | **OK** |

## Cambios transversales (documentados en F1)

- Fix scroll shell web en `/pedidos/carga`.
- Popup edición renglón responsive en mobile.

## Fuera de alcance v3 (confirmado)

- Excel import, pivot, admin.
- Cerrar presupuesto en barra kardex mobile.
- Paridad total cabecera/desktop SPEC-101-10 (wizard mobile con subset operativo ampliado).

## Observaciones

| ID | Tema | Notas |
|----|------|-------|
| OBS-V3-01 | Tag | `v1.2.2-mobile` tras iOS + físico |
| OBS-V3-02 | APK debug | `frontend/android/app/build/outputs/apk/debug/app-debug.apk` |
| OBS-V3-03 | Backend | Sin endpoints nuevos; permisos vía `ConsultaListadoService` |
| OBS-V3-04 | Reglas Cursor | Mobile activo — ver `.cursor/rules/base/80-mobile/` |

## Veredicto

**F formal v3 cerrado** — autoriza tag `v1.2.2-mobile` tras smoke iOS y dispositivo físico. Épica mobile PedidosWeb (v1+v2+v3) implementada en código.
