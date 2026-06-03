# TR — 001-Generaliddes

Tareas técnicas (parte C) derivadas de **SPEC-001-01** y **SPEC-001-02**.

**Normas:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) · **Plantilla:** [`../_PLANTILLA-TR-SLICE.md`](../_PLANTILLA-TR-SLICE.md)  
**Matriz viva:** [`matriz-permisos-mvp.md`](matriz-permisos-mvp.md)

**Generado:** 2026-05-29 (parte C OpenSpec)  
**Resincronizado con HU:** 2026-05-28 — menú (3 controles, persistencia), seed AccesoTotal/acotado, bootstrap perfil `cod_login`, E2E cambio contraseña.

## Cambios transversales (post-B1)

| Tema | TRs impactadas |
|------|----------------|
| 3 controles menú + persistencia user/terminal | TR-GEN-01-menu-general-sidebar, TR-GEN-01-shell-layout |
| Seed supervisor/acotado + `PQ_RolAtributo` | TR-GEN-02-modelo-roles-permisos-seed, TR-GEN-02-autorizacion-menu-api, TR-GEN-01-menu-general-sidebar |
| Bootstrap sesión (D-01) | **`POST /login` contexto completo**; `/me` solo F5; menú aparte |
| Decisiones D1 login (2026-05-28) | Seed primero; tenant stub 400; `codigo`; localStorage; `firstLogin` → TR cambio clave |
| Envelope API MONO | [`envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md) — `_NORMAS-TRANSVERSALES-TR.md` §2 |
| E2E cambio contraseña ampliado | TR-GEN-02-cambio-contrasena |

## Orden de implementación sugerido (Fase 0)

```text
1. TR-GEN-02-modelo-roles-permisos-seed
2. TR-GEN-02-login-sesion
3. TR-GEN-02-politicas-endpoints (marco; en paralelo con slices)
4. ~~TR-GEN-02-autorizacion-menu-api~~ (implementada 2026-05-30)
5. TR-GEN-01-shell-layout
6. TR-GEN-01-idioma + TR-GEN-01-apariencia-temas + TR-GEN-01-menu-avatar
7. TR-GEN-01-menu-general-sidebar
8. TR-GEN-02-recuperacion-contrasena + TR-GEN-02-cambio-contrasena
9. TR-GEN-02-expiracion-inactividad
10. TR-GEN-02-visibilidad-datos-pedidosweb (base; se extiende en SPEC-101)
11. TR-GEN-01-ayuda-externa (Should)
```

> **Nota:** `TR-GEN-05-tenancy` (SPEC-001-05) no generada aún; conviene implementar tenancy antes o en paralelo al login en el primer sprint real.

## SPEC-001-01 — Experiencia base

| TR | HU | Prioridad |
|----|-----|-----------|
| [TR-GEN-01-shell-layout](TR-GEN-01-shell-layout.md) | [HU-GEN-01-shell-layout](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-shell-layout.md) | Must |
| [TR-GEN-01-menu-general-sidebar](TR-GEN-01-menu-general-sidebar.md) | [HU-GEN-01-menu-general-sidebar](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-menu-general-sidebar.md) | Must |
| [TR-GEN-01-menu-avatar](TR-GEN-01-menu-avatar.md) | [HU-GEN-01-menu-avatar](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-menu-avatar.md) | Must |
| [TR-GEN-01-idioma](TR-GEN-01-idioma.md) | [HU-GEN-01-idioma](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-idioma.md) | Must |
| [TR-GEN-01-apariencia-temas](TR-GEN-01-apariencia-temas.md) | [HU-GEN-01-apariencia-temas](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-apariencia-temas.md) | Must |
| [TR-GEN-01-ayuda-externa](TR-GEN-01-ayuda-externa.md) | [HU-GEN-01-ayuda-externa](../../03-historias-usuario/001-Generaliddes/HU-GEN-01-ayuda-externa.md) | Should |

## SPEC-001-02 — Acceso y seguridad

| TR | HU | Prioridad |
|----|-----|-----------|
| [TR-GEN-02-modelo-roles-permisos-seed](TR-GEN-02-modelo-roles-permisos-seed.md) | [HU-GEN-02-modelo-roles-permisos-seed](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-modelo-roles-permisos-seed.md) | Must |
| [TR-GEN-02-login-sesion](TR-GEN-02-login-sesion.md) | [HU-GEN-02-login-sesion](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-login-sesion.md) | Must |
| [TR-GEN-02-recuperacion-contrasena](TR-GEN-02-recuperacion-contrasena.md) | [HU-GEN-02-recuperacion-contrasena](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-recuperacion-contrasena.md) | Must |
| [TR-GEN-02-cambio-contrasena](TR-GEN-02-cambio-contrasena.md) | [HU-GEN-02-cambio-contrasena](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-cambio-contrasena.md) | Must |
| [TR-GEN-02-expiracion-inactividad](TR-GEN-02-expiracion-inactividad.md) | [HU-GEN-02-expiracion-inactividad](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-expiracion-inactividad.md) | Must |
| [TR-GEN-02-autorizacion-menu-api](TR-GEN-02-autorizacion-menu-api.md) | [HU-GEN-02-autorizacion-menu-api](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-autorizacion-menu-api.md) | Must |
| [TR-GEN-02-politicas-endpoints](TR-GEN-02-politicas-endpoints.md) | [HU-GEN-02-politicas-endpoints](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-politicas-endpoints.md) | Must |
| [TR-GEN-02-visibilidad-datos-pedidosweb](TR-GEN-02-visibilidad-datos-pedidosweb.md) | [HU-GEN-02-visibilidad-datos-pedidosweb](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-visibilidad-datos-pedidosweb.md) | Must |

## SPEC-001-03 — UI transversal

**C1 (2026-06-01):** revisión de ambigüedad en las 4 TR — todas **aptas con observaciones** (§3.1–3.2).  
**D1 (2026-06-01):** plan de implementación en §3.3 de cada TR — ejecutar **D** en orden 1→4 (empezar por grillas-listados).  
**F formal (2026-06-01):** bloque implementado y verificado — [F-GEN-03-cierre-formal](F-GEN-03-cierre-formal.md) (**Aprobado con observaciones**).

**Orden de implementación:**

```text
1. TR-GEN-03-grillas-listados   (DataGridDx)
2. TR-GEN-03-layouts-grilla     (pq_grid_layouts + API)
3. TR-GEN-03-patron-abm          (modal ABM)
4. TR-GEN-03-exportaciones     (Excel)
```

| TR | HU | Prioridad |
|----|-----|-----------|
| [TR-GEN-03-grillas-listados](TR-GEN-03-grillas-listados.md) | [HU-GEN-03-grillas-listados](../../03-historias-usuario/001-Generaliddes/HU-GEN-03-grillas-listados.md) | Must |
| [TR-GEN-03-layouts-grilla](TR-GEN-03-layouts-grilla.md) | [HU-GEN-03-layouts-grilla](../../03-historias-usuario/001-Generaliddes/HU-GEN-03-layouts-grilla.md) | Must |
| [TR-GEN-03-patron-abm](TR-GEN-03-patron-abm.md) | [HU-GEN-03-patron-abm](../../03-historias-usuario/001-Generaliddes/HU-GEN-03-patron-abm.md) | Must |
| [TR-GEN-03-exportaciones](TR-GEN-03-exportaciones.md) | [HU-GEN-03-exportaciones](../../03-historias-usuario/001-Generaliddes/HU-GEN-03-exportaciones.md) | Must |

**SPEC:** [SPEC-001-03-ui-transversal.md](../../05-open-spec/001-Generaliddes/SPEC-001-03-ui-transversal.md)

**Patrón MONO (reutilizable):** [patron-i18n-grilla-devextreme.md](../../00-contexto/_mono/03-ui-transversal/patron-i18n-grilla-devextreme.md) — i18n `DataGridDx`, `loadMessages`, menús DX, totalizadores por columna.

## SPEC-001-04 — Configuración global

**C (2026-06-03):** TR generada para consulta parámetros PedidosWeb (solo lectura).  
**C1 (2026-06-03):** revisión de ambigüedad — **apta para D1** (§3.1–3.3 TR-GEN-04).

| TR | HU | Prioridad |
|----|-----|-----------|
| [TR-GEN-04-consulta-parametros](TR-GEN-04-consulta-parametros.md) | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) | Should |

**SPEC:** [SPEC-001-04-configuracion-global.md](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md)

## SPEC-001-10 — Chat Asistente IA

| TR | HU | Prioridad |
|----|----|-----------|
| [TR-GEN-10-configuracion-asistente-ia](TR-GEN-10-configuracion-asistente-ia.md) | [HU-GEN-10-configuracion-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-configuracion-asistente-ia.md) | Should |
| [TR-GEN-10-catalogo-proveedores-ia](TR-GEN-10-catalogo-proveedores-ia.md) | [HU-GEN-10-catalogo-proveedores-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-catalogo-proveedores-ia.md) | Should |
| [TR-GEN-10-chat-documental](TR-GEN-10-chat-documental.md) | [HU-GEN-10-chat-documental](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-chat-documental.md) | Should |
| [TR-GEN-10-mensajes-asistente-ia](TR-GEN-10-mensajes-asistente-ia.md) | [HU-GEN-10-mensajes-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-mensajes-asistente-ia.md) | Should |
| [TR-GEN-10-imagenes-asistente-ia](TR-GEN-10-imagenes-asistente-ia.md) | [HU-GEN-10-imagenes-asistente-ia](../../03-historias-usuario/001-Generaliddes/HU-GEN-10-imagenes-asistente-ia.md) | Should |
