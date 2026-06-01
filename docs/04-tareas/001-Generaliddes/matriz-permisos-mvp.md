# Matriz endpoint ↔ permiso — MVP PedidosWeb

Documento **vivo**: actualizar en el mismo slice/PR que el endpoint (norma [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) §1).

**Estado:** sincronizado con seed TR-GEN-02-modelo-roles-permisos-seed y login TR-GEN-02-login-sesion (2026-05-29).

## Roles seed (`Pq_Rol`)

| `nombre_rol` | `acceso_total` | Notas |
|--------------|----------------|--------|
| Cliente | false | `cliente.mvp` |
| Vendedor | false | `vendedor.sinMenu.mvp`, `usuario.sinVinculo.mvp` |
| VendedorAcotado | false | `vendedor.acotado.mvp` + `PQ_RolAtributo` |
| Supervisor | true | `supervisor.mvp` |

## Menú acotado (`vendedor.acotado.mvp`)

Procedimientos ERP: `pw_cargapedidos`, `pw_presupuestosingresados`, `pw_pedidosingresados`, `pw_dashboard`.

## Usuarios QA seed

| `codigo` | Objetivo |
|----------|----------|
| `cliente.mvp` | Login OK cliente |
| `vendedor.acotado.mvp` | Menú parcial |
| `supervisor.mvp` | Acceso total |
| `usuario.sinPermiso.mvp` | 403 `auth.noPermission` |
| `usuario.sinVinculo.mvp` | 403 `auth.noCommercialProfile` |
| `vendedor.sinMenu.mvp` | Login OK, menú vacío |
| `primerIngreso.mvp` | Gate `firstLogin` + cambio obligatorio |
| `cambioClave.mvp` | Cambio voluntario + login post-cambio |

## Leyenda

| Columna | Significado |
|---------|-------------|
| **Público** | Sin Bearer; sin `security` en OpenAPI |
| **Permiso** | Atributo mínimo o regla (AccesoTotal satisface atributos) |
| **TR origen** | TR que introduce o documenta el endpoint |

## Rutas públicas

| Método | Path | TR origen | Notas |
|--------|------|-----------|--------|
| GET | `/api/v1/health` | scaffold | Health check |
| POST | `/api/v1/auth/login` | TR-GEN-02-login-sesion | |
| POST | `/api/v1/auth/password/forgot` | TR-GEN-02-recuperacion-contrasena | |
| POST | `/api/v1/auth/password/reset` | TR-GEN-02-recuperacion-contrasena | |

## Autenticación y sesión

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| POST | `/api/v1/auth/logout` | Usuario autenticado | TR-GEN-02-login-sesion |
| GET | `/api/v1/auth/me` | Usuario autenticado | TR-GEN-02-login-sesion |
| POST | `/api/v1/auth/password/change` | Usuario autenticado | TR-GEN-02-cambio-contrasena |

## Preferencias y shell (Generalidades)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/users/me/preferences` | Usuario autenticado | TR-GEN-01-menu-avatar |
| PATCH | `/api/v1/users/me/preferences` | Usuario autenticado | TR-GEN-01-menu-avatar |
| PATCH | `/api/v1/users/me/preferences/locale` | Usuario autenticado | TR-GEN-01-idioma |
| PATCH | `/api/v1/users/me/preferences/theme` | Usuario autenticado | TR-GEN-01-apariencia-temas |
| GET | `/api/v1/config/public` | Usuario autenticado | TR-GEN-01-ayuda-externa, TR-GEN-03-layouts-grilla (`gridLayoutsEnabled`) |

## Menú

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/user/menu` | `Pq_Permiso` + filtro atributos menú | TR-GEN-02-autorizacion-menu-api |

## Layouts de grilla (UI transversal)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/grid-layouts` | Usuario autenticado | TR-GEN-03-layouts-grilla |
| GET | `/api/v1/grid-layouts/active` | Usuario autenticado | TR-GEN-03-layouts-grilla |
| POST | `/api/v1/grid-layouts` | Usuario autenticado | TR-GEN-03-layouts-grilla |
| PUT | `/api/v1/grid-layouts/{id}` | Usuario autenticado; solo creador del layout | TR-GEN-03-layouts-grilla |
| DELETE | `/api/v1/grid-layouts/{id}` | Usuario autenticado; solo creador del layout | TR-GEN-03-layouts-grilla |
| PUT | `/api/v1/grid-layouts/active` | Usuario autenticado | TR-GEN-03-layouts-grilla |

## Visibilidad de datos (base; extiende SPEC-101)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| GET | `/api/v1/clientes` | `Permiso_Repo` + `visibleClientsForUser` + perfil §7.3 | TR-GEN-02-visibilidad-datos-pedidosweb |
| GET | `/api/v1/comprobantes/{id}` | `Permiso_Repo` + `visibleClientsForUser` + perfil §7.3 | TR-GEN-02-visibilidad-datos-pedidosweb |
| GET | `/api/v1/dashboard/resumen` | `Permiso_Repo` + `visibleClientsForUser` + perfil §7.3 | TR-GEN-02-visibilidad-datos-pedidosweb |

## Negocio (placeholder — completar en TR SPEC-101)

| Método | Path | Permiso / regla | TR origen |
|--------|------|-----------------|-----------|
| * | `/api/v1/pedidos/*` | Según operación: Repo/Alta/Modi/Baja | TR-GEN-02-politicas-endpoints |
| * | `/api/v1/presupuestos/*` | Según operación | TR-GEN-02-politicas-endpoints |

## Checklist de mantenimiento

- [ ] Cada fila nueva tiene OpenAPI con `security`, 401, 403 (si protegida)
- [ ] Tests integración 401/403 por endpoint crítico
- [ ] Coherente con seed `Pq_Permiso` / `PQ_RolAtributo`

