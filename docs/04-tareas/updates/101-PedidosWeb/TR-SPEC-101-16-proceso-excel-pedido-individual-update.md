# TR-SPEC-101-16-update — Excel individual: recortar leyendas (sin rechazo GEN-07)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-16-proceso-excel-pedido-individual](../../101-PedidosWeb/TR-SPEC-101-16-proceso-excel-pedido-individual.md) |
| **SPEC relacionada** | [SPEC-101-16-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel-update.md) |
| **HU relacionada** | [HU-101-029-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual-update.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **Última actualización** | 2026-09-01 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Recortar `leyenda1`…`leyenda5` a 60 en `PedidoIndividualRowResolver` (o equivalente) con el helper de TR-101-04.

**No** cambiar `largo_maximo` del catálogo a 60: `ExcelImportParserService::castTexto` rechazaría la fila.

## 2) Criterios de aceptación

- **AC-CC13-T-X1:** Catálogo de leyendas **sin** `largo_maximo = 60` (sigue 255 o null).
- **AC-CC13-T-X2:** PHPUnit: fila con leyenda de 61 caracteres → válida; valor resuelto de longitud 60.

## 3) Implementación

- Aplicar helper al mapear cada leyenda hacia cabecera.
- Tests del resolver/handler: caso 61 vs 60; lote no error.

## 4) Fuera de alcance

- Cambiar GEN-07 genérico (no truncar en `castTexto` global).
- Masivo → TR-101-21-update (mismo helper).
