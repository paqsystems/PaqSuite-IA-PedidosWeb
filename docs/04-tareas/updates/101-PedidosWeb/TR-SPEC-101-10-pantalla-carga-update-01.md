# TR-SPEC-101-10 — Pantalla carga (update-01 — CC PQ #12)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-10-pantalla-carga](../../101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md) |
| **SPEC update** | [SPEC-101-10-pantalla-carga-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | [00-ControlCalidad-PQ](../../../00-ControlCalidad/00-ControlCalidad-PQ.md) — Control #12, **28/08/2026** |
| **Última actualización** | 2026-08-28 |

## Estado de alcance

| Campo | Valor |
|-------|--------|
| Estado | Pendiente |

## Implementación

1. FE carga: widget saldo deuda + colores + Popup grilla (DX) sin export; fetch deuda por cliente.
2. Modal renglón: texto equivalencia unidades si `CargaUnidadesVenta`; campo precio unitario neto.
3. Snapshot/dirty leyendas 1–5; enviar en grabar.
4. Listbox: ocultar stock si `stockeable=false` (API artículos debe exponer el flag).
5. i18n + `data-testid`; rama `isNativeApp()` para saldo.

## AC técnicos

- [ ] **AC-CC12-T-C1:** Saldo + modal.
- [ ] **AC-CC12-T-C2:** Equivalencia + precio neto.
- [ ] **AC-CC12-T-C3:** Dirty leyendas.
- [ ] **AC-CC12-T-C4:** Listbox sin stock en no-stockeables.
