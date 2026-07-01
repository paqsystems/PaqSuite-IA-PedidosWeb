# Cierre F Formal — SPEC-101-17 Mobile v2 (`v1.2.1-mobile`)

## Alcance del cierre

Release **`v1.2.1-mobile`**: todas las consultas MVP + listados kardex en native.

| TR | HU |
|----|-----|
| [TR-SPEC-101-17-mobile-v2-consultas](TR-SPEC-101-17-mobile-v2-consultas.md) | [HU-101-034](../../03-historias-usuario/101-PedidosWeb/HU-101-034-mobile-v2-consultas-kardex.md) |
| [TR-SPEC-101-17-mobile-v2-listados](TR-SPEC-101-17-mobile-v2-listados.md) | [HU-101-035](../../03-historias-usuario/101-PedidosWeb/HU-101-035-mobile-v2-listados-kardex.md) |

**SPEC:** [SPEC-101-17-mobile-capacitor-pedidosweb.md](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md)

**Base v1:** [F-101-17-cierre-formal](F-101-17-cierre-formal.md)  
**Verificación D / F1:** [D-VERIFICACION-101-17-mobile-v2](D-VERIFICACION-101-17-mobile-v2.md)

## Resultado global

- **Aprobado con observaciones**

Implementación D2 completa. **Smoke Android emulador validado** por QA (consultas, listados, menú ampliado). Tag `v1.2.1-mobile` pendiente smoke **iOS**.

## Resumen por slice

| Slice | Resultado | Evidencia |
|-------|-----------|-----------|
| Consultas kardex | Aprobado | Deuda, cheques, historial, detalle, parámetros, logs |
| Listados kardex | Aprobado | Pedidos ing/pend, presupuestos activos |
| Shell / menú v2 | Aprobado | `filterMenuTreeForMobileV2`, guard rutas MVP |
| Componente transversal | Aprobado | `ConsultaKardexMobileView`, `ConsultaDetailPopup` |

## Verificación automatizada (2026-06-30)

| Comando | Resultado |
|---------|-----------|
| `npm run build:mobile` | OK |
| `npm test -- --run` | OK (190) |
| `npx cap sync` | OK |

## Smoke manual

| Plataforma | Estado |
|------------|--------|
| Android emulador | **OK** |
| iOS | **Pendiente** |
| Web regresión | **Pendiente** |

## Fuera de alcance v2 (confirmado)

- HU-101-036 carga mobile (`v1.2.2-mobile`).
- Acciones comprobante en listados (ver/editar/copiar/eliminar/convertir) → v3.
- Presupuestos cerrados tab; tratativas datos reales.

## Observaciones

| ID | Tema | Notas |
|----|------|-------|
| OBS-V2-01 | Tag | `v1.2.1-mobile` tras iOS |
| OBS-V2-02 | v1 tag | `v1.2.0-mobile` puede etiquetarse junto o antes según política release |
| OBS-V2-03 | Backend | Sin endpoints nuevos |

## Veredicto

**F formal v2 cerrado** — autoriza tag `v1.2.1-mobile` tras smoke iOS. Siguiente: **Parte C/D v3** (HU-101-036 carga mobile).
