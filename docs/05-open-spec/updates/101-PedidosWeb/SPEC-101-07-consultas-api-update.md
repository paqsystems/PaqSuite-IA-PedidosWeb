# SPEC-101-07 — Consultas API (update CC PQ #19 — emisión)

| Campo | Valor |
|-------|--------|
| **SPEC base** | [SPEC-101-07-consultas-api](../../101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #19, 03/11/2026 |
| **Última actualización** | 03/11/2026 (Parte G) |

## Delta de alcance

Exponer contratos API que GEN-15 requiera para **field-schema** y **emisión** sobre los ocho endpoints de consulta existentes, manteniendo acceso por **SP** (regla 74) y envelope GEN-20.

### In scope

1. **Field-schema** — endpoint(s) GEN-15 por `consultaId` / `procedimiento`, mapeando columnas ya servidas en GET consultas (sin duplicar lógica de negocio en controllers ad-hoc).
2. **Emitir** — endpoint de emisión (POST o GET según SDK) que reciba filtros equivalentes a la consulta UI (query params / body documentados en OpenAPI) y devuelva stream PDF (o formato acordado).
3. **Autorización** — misma policy que lectura de consulta + permiso emisión si GEN-15 lo distingue.
4. **OpenAPI** — documentar por recurso; regenerar `api-docs.json` en Parte D.

### Informes

Misma matriz que [SPEC-101-11-consultas-ui-update](SPEC-101-11-consultas-ui-update.md) (`pw_presupuestosingresados` … `pw_consultastock`).

### Fuera de scope

- Persistencia de plantillas (GEN-15 backend transversal — TR-GEN-15).
- Cambios en shape JSON de listados salvo campos exigidos por field-schema.

### HU / TR

- [HU-101-055-emision-reportes-informes](../../../03-historias-usuario/101-PedidosWeb/HU-101-055-emision-reportes-informes.md)
- [TR-SPEC-101-07-consultas-api-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-07-consultas-api-update.md)
