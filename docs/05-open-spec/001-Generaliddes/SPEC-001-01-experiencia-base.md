# SPEC-001-01 - Experiencia base

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | `docs/03-historias-usuario/001-Generaliddes/HU-GEN-01-*.md` (6 HU; índice en README de la carpeta) |
| **Estado** | Pendiente |
| **Revisión A1** | Apto con observaciones (2026-05-28) |

## Objetivo

Definir lineamientos base de experiencia de usuario para el arranque del producto: layout, navegación, idioma, apariencia y ayuda contextual.

## Estado de ejecución

Implementable en MVP (documentación + estructura frontend base).

## Decisiones humanas (producto PedidosWeb)

| Tema | Decisión |
|------|----------|
| Idioma y tema por defecto | Fijados en **producto PedidosWeb** → `PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` **§8.1** |
| Menú mínimo MVP por perfil | Fijado en **producto PedidosWeb** → mismo documento **§8** (ítems) + permisos §7 |

## Valores por defecto (copia producto §8.1)

| Parámetro | Valor MVP | Notas |
|-----------|-----------|--------|
| Idioma por defecto | `es` | Si `users.locale` vacío → `navigator.language`; si no soportado → `es` |
| Tema por defecto | `generic.light` | Preferencia por usuario en MONO; catálogo DevExtreme en contexto |

## Menú MVP (copia producto §8)

1. Carga de pedidos/presupuestos.
2. Presupuestos ingresados.
3. Pedidos ingresados.
4. Pedidos pendientes.
5. Deuda de clientes.
6. Cheques en cartera.
7. Historial de ventas.
8. Stock.
9. Tratativas / seguimiento de presupuestos.
10. Dashboard.
11. Logs de integración.

Visibilidad por perfil: permisos §7 + seed `pq_menus` (no duplicar reglas en este SPEC).

## Flujo login vs post-login

1. **Login:** selector de idioma (y branding); sin menú lateral de procesos.
2. **Post-login:** shell completo; menú general desde API; menú avatar con preferencias (idioma/tema).
3. **Persistencia:** `users.locale` y `users.theme` tras guardar en avatar.

## Fuente de verdad de producto (obligatoria)

- `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` — §5 MONO, §7 perfiles, **§8 menú**, **§8.1 defaults**
- `docs/05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md` — tenancy y shell operativo

## Entradas requeridas

- Definición de producto anterior.
- `docs/_base/shell-layout-principal.md`
- `docs/frontend/devextreme-norms.md` (si aplica en el repo)

## Fuentes (contexto MONO)

Subcarpeta: `docs/00-contexto/_mono/01-experiencia-base/`

- `apariencia-temas.md`, `ayuda-externa-asistente.md`, `estructura-sitio.md`, `idioma-multilingual.md`
- `menu-avatar.md`, `menu-general.md`, `navegacion-pestanas.md`, `shell-layout.md`

## Alcance

- Definir shell principal y zonas de pantalla (header, sidebar, área principal, footer).
- Definir navegación principal, menús y avatar (login y post-login).
- Definir estrategia de idioma inicial (valores en producto §8.1).
- Definir reglas de apariencia/temas (valores en producto §8.1).
- Definir puntos de ayuda externa o asistida.

## Fuera de alcance

- Implementación visual pixel-perfect definitiva.
- Optimización avanzada de accesibilidad o performance UI.
- Detalle de ítems de negocio PedidosWeb fuera del menú §8 de producto.

## Entregables verificables

- Shell/navegación: este SPEC + producto §5/§8 + contexto `shell-layout.md`.
- Menú MVP: lista en producto **§8** (11 ítems); visibilidad por perfil vía §7 y `pq_menus`/permisos.
- Defaults: tabla en producto **§8.1** (idioma `es`, tema `generic.light`).

## Criterios de aceptación medibles (SPEC + producto)

- [ ] Shell/layout trazable a producto §5 MONO y contexto `shell-layout.md`.
- [ ] Menú general y menú avatar: comportamiento login/post-login en contexto `menu-general.md` / `menu-avatar.md`.
- [ ] Idioma inicial y fallback sin ambigüedad: producto **§8.1** + `idioma-multilingual.md`.
- [ ] Tema por defecto sin ambigüedad: producto **§8.1** + `apariencia-temas.md`.

## Trazabilidad HU

| HU | Tema SPEC |
|----|-----------|
| HU-GEN-01-shell-layout | Shell, zonas |
| HU-GEN-01-menu-general-sidebar | Menú §8 producto |
| HU-GEN-01-menu-avatar | Avatar |
| HU-GEN-01-idioma | §8.1 idioma |
| HU-GEN-01-apariencia-temas | §8.1 tema |
| HU-GEN-01-ayuda-externa | Ayuda (Should) |
