# SPEC-001-06 — Emisión (update CC PQ #19)

| Campo | Valor |
|-------|--------|
| **SPEC base** | [SPEC-001-06-emision](../../001-Generaliddes/SPEC-001-06-emision.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #19, 03/11/2026 |
| **GEN Framework** | 15 — Emisiones / diseñador de reportes |
| **Última actualización** | 03/11/2026 (Parte G) |

## Delta de alcance (respecto del SPEC base documental)

Adoptar en **PedidosWeb host** el subsistema de **emisión documental** del SDK (GEN-15), centrado en **diseño y persistencia de plantillas** y **emisión** desde consultas — sin reimplementar el diseñador DevExpress en el producto.

### In scope (CC #19)

1. **Diseñador de reportes** — montar `EmissionReportDesignerPage` (o export equivalente de `@paqsuite/react-core`) y APIs de persistencia de artefactos acordadas por GEN-15 (`.repx` / metadatos en BD del host).
2. **Menú** — ítem bajo raíz **General** (`grp_general`), `procedimiento` canónico **`pw_diseniadorreportes`**, ruta **`/general/diseniador-reportes`**, orden **63** (entre parámetros 61 e historial Excel 62, o inmediatamente después de historial si el seed ya fijó 62).
3. **Catálogo reporte ↔ consulta** — vínculo plantilla / `consultaId` por cada `procedimiento` de informe (o tabla catálogo GEN-15 desplegada en host).
4. **Field-schema** — alineación columnas API consultas ([SPEC-101-07](../../101-PedidosWeb/SPEC-101-07-consultas-api.md)) con Field List del diseñador; sidecar DX según `backend/config/paqsuite.php` (`PAQSUITE_DX_REPORTING_*`).
5. **Salida MVP CC #19** — **PDF** (o formato único que exponga GEN-15 en la versión pinneada del SDK); convive con export Excel GEN-03.
6. **Permisos** — administrar plantillas: rol con acceso al ítem diseñador (`Permiso_Repo` sobre `pw_diseniadorreportes`); **emitir** en consulta: mismo permiso que la consulta (`Permiso_Repo` del `procedimiento` del informe).
7. **Mobile (Capacitor)** — diseñador y ABM de plantillas **excluidos**; emisión desde kardex: **fase 2** (web obligatoria en v1 CC #19).

### Fuera de scope (CC #19)

- Motor mail operativo de pedidos ([SPEC-101-13](../../101-PedidosWeb/SPEC-101-13-mails.md)).
- Comunicación dirigida masiva (GEN-30).
- Reimplementación local del diseñador DX Reporting.
- Pivot como fuente de emisión (emisión usa dataset de consulta / filtros vigentes de grilla, no layout pivot).

### Dependencias

- Pin SDK: `paqsuite/laravel-core`, `@paqsuite/react-core` con GEN-15 estable (ver [plan-adopcion-sdk-framework.md](../../../06-operacion/plan-adopcion-sdk-framework.md)).
- [44-host-framework-gen-cableado-componentes.mdc](../../../../.cursor/rules/base/00-arquitectura/44-host-framework-gen-cableado-componentes.mdc) § GEN-15.
- Slice consultas: [SPEC-101-11-consultas-ui-update](../101-PedidosWeb/SPEC-101-11-consultas-ui-update.md), [SPEC-101-07-consultas-api-update](../101-PedidosWeb/SPEC-101-07-consultas-api-update.md).

### Criterios de aceptación medibles (SPEC)

- [ ] **CA-SPEC-06-19-01:** Usuario autorizado abre diseñador desde menú General y persiste al menos una plantilla ligada a un `consultaId` / proceso.
- [ ] **CA-SPEC-06-19-02:** Usuario sin permiso no ve ítem menú diseñador ni accede por URL directa (403 / redirect).
- [ ] **CA-SPEC-06-19-03:** Field-schema de un informe piloto (p. ej. `pw_pedidosingresados`) coincide con columnas documentadas en producto/API.
- [ ] **CA-SPEC-06-19-04:** Emisión desde consulta piloto genera archivo descargable con filtros activos de la sesión.

### HU / TR derivadas

- [HU-GEN-15-diseniador-reportes](../../../03-historias-usuario/001-Generaliddes/HU-GEN-15-diseniador-reportes.md)
- [TR-GEN-15-diseniador-reportes](../../../04-tareas/001-Generaliddes/TR-GEN-15-diseniador-reportes.md)
- [HU-101-055-emision-reportes-informes](../../../03-historias-usuario/101-PedidosWeb/HU-101-055-emision-reportes-informes.md)
