# Cierre I — CC PQ #17 (20/09/2026)

## Alcance

Parte **I** del dispatcher: fusión de updates **Finalizado** (G+D+E+F 21/09/2026) en documentos base (SPEC, HU, TR), cierre formal del **Control de Calidad #17**.

**Fecha unificación:** 21/09/2026

**Partes previas:** [E-CC-PQ-17-tests.md](E-CC-PQ-17-tests.md) · [F-CC-PQ-17-cierre-formal.md](F-CC-PQ-17-cierre-formal.md)

---

## Updates CC #17 fusionados

### SPEC

| Origen update | Destino unificado |
|---------------|-------------------|
| `SPEC-101-02-modelos-update` | [SPEC-101-02-modelos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md) |
| `SPEC-101-07-consultas-api-update` | [SPEC-101-07-consultas-api.md](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| `SPEC-101-10-pantalla-carga-update` | [SPEC-101-10-pantalla-carga.md](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| `SPEC-101-11-consultas-ui-update` | [SPEC-101-11-consultas-ui.md](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |

### HU

| Origen update | Destino unificado |
|---------------|-------------------|
| `HU-101-006-carga-renglones-update` | [HU-101-006-carga-renglones.md](../../03-historias-usuario/101-PedidosWeb/HU-101-006-carga-renglones.md) |
| `HU-101-028-consulta-detalle-pedidos-update` | [HU-101-028-consulta-detalle-pedidos.md](../../03-historias-usuario/101-PedidosWeb/HU-101-028-consulta-detalle-pedidos.md) |

### TR

| Origen update | Destino unificado |
|---------------|-------------------|
| `TR-SPEC-101-02-modelos-update` | [TR-SPEC-101-02-modelos.md](TR-SPEC-101-02-modelos.md) |
| `TR-SPEC-101-07-consultas-api-update` | [TR-SPEC-101-07-consultas-api.md](TR-SPEC-101-07-consultas-api.md) |
| `TR-SPEC-101-10-pantalla-carga-update` | [TR-SPEC-101-10-pantalla-carga.md](TR-SPEC-101-10-pantalla-carga.md) |
| `TR-SPEC-101-11-consultas-ui-update` | [TR-SPEC-101-11-consultas-ui.md](TR-SPEC-101-11-consultas-ui.md) |

---

## Producto (ya alineado en D)

| Documento | Contenido CC #17 |
|-----------|------------------|
| `PedidosWeb_Modelo_Datos_Final.md` §3.4 | Fila `especial` |
| `consulta-detalle-pedidos.md` §4 | Columna `especial` (`""` / `"*"`) |
| `pantalla-carga-comprobante-ui.md` §3.1 | Sufijo ` (*)` en listbox |

---

## Archivos update eliminados

Tras Parte I **no quedan** archivos `*-update.md` de CC #17 en:

- `docs/05-open-spec/updates/101-PedidosWeb/`
- `docs/03-historias-usuario/updates/101-PedidosWeb/`
- `docs/04-tareas/updates/101-PedidosWeb/`

(10 archivos eliminados.)

---

## Observaciones heredadas de F

| ID | Tema | Destino |
|----|------|---------|
| OBS-F-01 | Feature HTTP skipped sin SQL Server tenant | Re-ejecutar pre-deploy |
| OBS-F-02 | Deploy requiere `ALTER especial` en cada BD empresa | Runbook deploy |

---

## Veredicto final

**CC #17:** ciclo **G+D+E+F+I 21/09/2026 — Finalizado.**

**Deploy recordatorio:** `backend/scripts/sql/alter-pq-pedidosweb-articulos-especial.sql` o `PedidosWebSchemaBootstrap::ensureMvpSchema()` antes de smoke en producción.
