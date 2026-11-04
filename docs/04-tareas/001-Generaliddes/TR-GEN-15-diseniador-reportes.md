# TR-GEN-15 — Diseñador de reportes (host PedidosWeb)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-15-diseniador-reportes](../../03-historias-usuario/001-Generaliddes/HU-GEN-15-diseniador-reportes.md) |
| **SPEC** | [SPEC-001-06-emision-update](../../05-open-spec/updates/001-Generaliddes/SPEC-001-06-emision-update.md) |
| **Estado** | Pendiente |
| **Origen** | 00-ControlCalidad-PQ #19 — 03/11/2026 |
| **Dependencias** | Pin SDK con GEN-15; sidecar DX opcional (`PAQSUITE_DX_REPORTING_*`) |

---

## 1) Backend

- Montar rutas/controllers provistos por `paqsuite/laravel-core` para emisiones (según versión pinneada).
- Config `config/paqsuite.php` → bloque `dxReporting` / emisiones.
- SP/migraciones GEN-15 desplegados vía `paqsuite:installation:update` (no SQL ad-hoc en controllers de negocio).
- Seed menú: `pw_diseniadorreportes` bajo `grp_general`, `id` jerárquico regla 76, `orden` 63.
- Permisos MVP: rol Administrador / acceso total + Vendedor **sin** diseñador por defecto.

## 2) Frontend

- Ruta lazy `/general/diseniador-reportes` → wrapper `EmissionReportDesignerPage` desde `@paqsuite/react-core`.
- Registrar i18n emission.* si el SDK no auto-carga.
- Excluir ruta en `pedidosWebMobilePolicy`.

## 3) AC técnicos

- **AC-T-01:** `npm run build` y `composer test` smoke sin regresión login.
- **AC-T-02:** Health + página diseñador 200 para admin autenticado.
- **AC-T-03:** OpenAPI incluye rutas emisión GEN-15 expuestas.

## 4) Orden sugerido (D1)

1. Bump SDK si GEN-15 ausente en pin actual.
2. Migrate/installation update SP emisiones.
3. Menú + ruta FE.
4. Piloto plantilla `pw_pedidosingresados`.
5. Enlazar con [TR-SPEC-101-11-consultas-ui-update](../updates/101-PedidosWeb/TR-SPEC-101-11-consultas-ui-update.md).

Ver [D1-PLAN-CC-PQ-19.md](../101-PedidosWeb/D1-PLAN-CC-PQ-19.md).
