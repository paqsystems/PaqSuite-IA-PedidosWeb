# TR-SPEC-101-07 — Consultas API (update CC PQ #19 — emisión)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-055-emision-reportes-informes](../../03-historias-usuario/101-PedidosWeb/HU-101-055-emision-reportes-informes.md) |
| **SPEC** | [SPEC-101-07-consultas-api-update](../../05-open-spec/updates/101-PedidosWeb/SPEC-101-07-consultas-api-update.md) |
| **TR base** | [TR-SPEC-101-07-consultas-api](../../101-PedidosWeb/TR-SPEC-101-07-consultas-api.md) |
| **Estado** | Pendiente |
| **Origen** | 00-ControlCalidad-PQ #19 — 03/11/2026 |

---

## 1) Alcance BE

- Delegar emisión/field-schema a adapters laravel-core GEN-15 donde existan.
- Donde el host deba mapear consulta → dataset: reutilizar servicios SP actuales de [TR-SPEC-101-07-consultas-api](../../101-PedidosWeb/TR-SPEC-101-07-consultas-api.md) (sin Eloquent nuevo sobre tablas negocio).
- OpenAPI: anotaciones en controllers GEN-15 / wrappers host.

## 2) Matriz emitir (procedimiento)

`pw_presupuestosingresados`, `pw_pedidosingresados`, `pw_pedidospendientes`, `pw_detallepedidos`, `pw_deudaclientes`, `pw_consultacheques`, `pw_historialventas`, `pw_consultastock`.

## 3) AC

- **AC-CC19-API-01:** POST/GET emit documentado; envelope `error: 0` en éxito.
- **AC-CC19-API-02:** 403 si usuario sin visibilidad cliente (GEN-02).
- **AC-CC19-API-03:** Feature test piloto `pw_pedidosingresados` + `pw_deudaclientes` (PDF magic bytes o content-type).

## 4) Tests

PHPUnit en `backend/tests/Feature/Emission/` (nuevo) + extensión tests consulta existentes si aplica.
