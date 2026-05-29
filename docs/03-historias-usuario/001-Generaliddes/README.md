# Historias de usuario — 001-Generaliddes

Convención: **`HU-GEN-{SPEC}-{tema}.md`** derivadas de `docs/05-open-spec/001-Generaliddes/`.

**A1 (2026-05-28):** SPEC-001-01…05 revisados; parches cortos aplicados (tablas embebidas, trazabilidad HU, flujo login). Producto: §8.1 defaults, §8 menú, §7 perfiles, §5 `desarrollo`.

**B1 (2026-05-28):** Las **14 HU** de SPEC-001-01 y SPEC-001-02 enriquecidas con `/enrich-user-story` (trazabilidad SPEC, Gherkin, supuestos, veredicto). **Lista para TR:** Sí con observaciones en la mayoría; ver sección *Veredicto B1* en cada archivo.

**Parte C (TR):** generar desde SPEC + HU enriquecida usando [`docs/04-tareas/_PLANTILLA-TR-SLICE.md`](../../04-tareas/_PLANTILLA-TR-SLICE.md) y normas [`_NORMAS-TRANSVERSALES-TR.md`](../../04-tareas/_NORMAS-TRANSVERSALES-TR.md). Resolver preguntas abiertas marcadas en cada HU.

## SPEC-001-01 — Experiencia base

| HU | Título | Prioridad |
|----|--------|-----------|
| [HU-GEN-01-shell-layout](HU-GEN-01-shell-layout.md) | Shell principal post-login | Must |
| [HU-GEN-01-menu-general-sidebar](HU-GEN-01-menu-general-sidebar.md) | Menú general y sidebar dinámico | Must |
| [HU-GEN-01-menu-avatar](HU-GEN-01-menu-avatar.md) | Menú avatar y preferencias | Must |
| [HU-GEN-01-idioma](HU-GEN-01-idioma.md) | Selector de idioma e i18n base | Must |
| [HU-GEN-01-apariencia-temas](HU-GEN-01-apariencia-temas.md) | Apariencia DevExtreme | Must |
| [HU-GEN-01-ayuda-externa](HU-GEN-01-ayuda-externa.md) | Asistente IA / ayuda externa | Should |

**SPEC origen:** [SPEC-001-01-experiencia-base.md](../../05-open-spec/001-Generaliddes/SPEC-001-01-experiencia-base.md)

## SPEC-001-02 — Acceso y seguridad

| HU | Título | Prioridad |
|----|--------|-----------|
| [HU-GEN-02-login-sesion](HU-GEN-02-login-sesion.md) | Login, bootstrap de sesión y logout | Must |
| [HU-GEN-02-recuperacion-contrasena](HU-GEN-02-recuperacion-contrasena.md) | Recuperación de contraseña | Must |
| [HU-GEN-02-cambio-contrasena](HU-GEN-02-cambio-contrasena.md) | Cambio de contraseña y primer ingreso | Must |
| [HU-GEN-02-expiracion-inactividad](HU-GEN-02-expiracion-inactividad.md) | Expiración por inactividad | Must |
| [HU-GEN-02-modelo-roles-permisos-seed](HU-GEN-02-modelo-roles-permisos-seed.md) | Roles, permisos y seed MVP | Must |
| [HU-GEN-02-autorizacion-menu-api](HU-GEN-02-autorizacion-menu-api.md) | Autorización de menú (backend) | Must |
| [HU-GEN-02-politicas-endpoints](HU-GEN-02-politicas-endpoints.md) | Políticas por endpoint | Must |
| [HU-GEN-02-visibilidad-datos-pedidosweb](HU-GEN-02-visibilidad-datos-pedidosweb.md) | Visibilidad cliente / vendedor / supervisor | Must |

**SPEC origen:** [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md)

> ABM de usuarios/roles en UI: **fuera de alcance** del SPEC (solo seeds y reglas backend).

## Pendientes (otros SPEC)

- SPEC-001-05 → HU-GEN-05 (variantes / tenancy)
- SPEC-001-03 → HU-GEN-03 (UI transversal)
- SPEC-001-04 → HU-GEN-04 (configuración global)
