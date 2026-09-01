# TR-SPEC-101-02-update — DDL leyendas nvarchar(60)

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-02-modelos](../../101-PedidosWeb/TR-SPEC-101-02-modelos.md) |
| **SPEC relacionada** | [SPEC-101-02-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-02-modelos-update.md) |
| **HU relacionada** | Transversal — [HU-101-005-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-005-inicializacion-cabecera-update.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **Última actualización** | 2026-09-01 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Acortar `leyenda_1`…`leyenda_5` a **nvarchar(60)** en:

- `pq_pedidosweb_pedidoscabecera`
- `pq_pedidosweb_clientes`

Sin DROP de tablas. SQL idempotente (no ejecutar si ya es 60).

## 2) Criterios de aceptación

- **AC-CC13-T-M1:** Tras el script, `CHARACTER_MAXIMUM_LENGTH = 60` en las 10 columnas (5+5).
- **AC-CC13-T-M2:** Si había valores > 60, quedan en `LEFT(..., 60)` y el ALTER no falla.
- **AC-CC13-T-M3:** `PedidosWebDevSchemaBootstrap` CREATE de clientes y cabecera usa `nvarchar(60)`.
- **AC-CC13-T-M4:** Re-ejecutar el script no error (idempotente).

## 3) Implementación

1. Script `backend/scripts/sql/alter-pq-pedidosweb-leyendas-60.sql`:
   - Por cada columna: si `max_length > 60` (en bytes sys.columns: nvarchar 60 → `max_length = 120`), `UPDATE` `LEFT`, luego `ALTER COLUMN nvarchar(60) NULL`.
   - `WITH (NOLOCK)` solo en lecturas de catálogo si aplica; destino ALTER sin NOLOCK.
2. Ajustar CREATE en `PedidosWebDevSchemaBootstrap` (clientes ~L191, cabecera ~L318).
3. Producto: `PedidosWeb_Modelo_Datos_Final.md` — `leyenda_1..5` tipo `nvarchar(60)`.
4. **Prohibido** bootstrap destructivo / DROP.

## 4) Tests

- Feature o script de inspección: columnas = 60 (skip si BD sin tablas).
- Unit: no aplica lógica de negocio en modelos.

## 5) Fuera de alcance

- Validación API → TR-101-04-update.
- UI → TR-101-10-update.
