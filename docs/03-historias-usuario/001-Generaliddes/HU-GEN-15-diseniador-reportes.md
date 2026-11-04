# HU-GEN-15 — Diseñador de reportes (GEN-15)

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-15-diseniador-reportes |
| **SPEC origen** | [SPEC-001-06-emision-update](../../05-open-spec/updates/001-Generaliddes/SPEC-001-06-emision-update.md) · base [SPEC-001-06-emision](../../05-open-spec/001-Generaliddes/SPEC-001-06-emision.md) |
| **Épica** | 001 — Generalidades / GEN-15 |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **TR relacionada** | [TR-GEN-15-diseniador-reportes](../../04-tareas/001-Generaliddes/TR-GEN-15-diseniador-reportes.md) |
| **Origen** | 00-ControlCalidad-PQ #19 — 03/11/2026 |

## Origen

Control de calidad PQ 03/11/2026 — ítem **GEN-15 · Diseñador de reportes**.

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **administrador funcional**,  
quiero **diseñar y mantener plantillas de reportes** asociadas a consultas del portal,  
para **emitir documentos PDF alineados al negocio** sin depender de desarrollo por cada formato.

## Alcance incluido

- Pantalla diseñador SDK bajo menú **General** (`pw_diseniadorreportes`, `/general/diseniador-reportes`).
- Alta/edición/guardado de plantillas ligadas a `consultaId` / `procedimiento`.
- Field List alimentado por field-schema backend (sidecar DX si aplica).
- i18n + `data-testid` (`emissionDesignerOpen`, etc. según TR).

## Fuera de alcance

- Emisión desde grillas ([HU-101-055](../101-PedidosWeb/HU-101-055-emision-reportes-informes.md)).
- Mobile Capacitor.

## Criterios de aceptación

- [ ] **CA-01:** Ítem menú visible bajo General para rol autorizado; oculto para Cliente MVP.
- [ ] **CA-02:** Abrir diseñador carga shell GEN-15 sin errores de licencia DX.
- [ ] **CA-03:** Guardar plantilla para `pw_pedidosingresados` (piloto) y listarla al reabrir.
- [ ] **CA-04:** Usuario sin `Permiso_Repo` sobre `pw_diseniadorreportes` recibe 403 al deep-link.
- [ ] **CA-05:** Ruta no registrada en `pedidosWebMobilePolicy` native.
