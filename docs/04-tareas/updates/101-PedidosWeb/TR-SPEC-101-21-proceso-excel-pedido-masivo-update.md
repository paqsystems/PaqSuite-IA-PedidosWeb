# TR-SPEC-101-21-update — Excel masivo: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-21-proceso-excel-pedido-masivo](../../101-PedidosWeb/TR-SPEC-101-21-proceso-excel-pedido-masivo.md) |
| **SPEC relacionada** | [SPEC-101-21-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos-update.md) |
| **HU relacionada** | [HU-101-043-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo-update.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **Última actualización** | 2026-09-01 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Recortar leyendas al armar grupos (`PedidoMasivoGroupAssembler` o equivalente) con el mismo helper que TR-101-16/04.

## 2) Criterios de aceptación

- **AC-CC13-T-M1:** Catálogo masivo **sin** `largo_maximo = 60` en leyendas.
- **AC-CC13-T-M2:** PHPUnit/feature: lote con leyenda > 60 entrega grupos; cabecera con 60 caracteres.

## 3) Implementación

- Coordinar con TR-101-16-update (mismo helper).
- No tocar GEN-07 para rechazar.

## 4) Fuera de alcance

- UI grilla masiva. Grabar lote recorta otra vez (TR-101-04).
