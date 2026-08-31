# Cierre I — CC PQ #11 (31/08/2026) — Contactos en API clientes

## Alcance

Parte **I** del dispatcher: fusión de updates **Finalizado** (confirmados por PQ 31/08/2026) en documentos base. CC #11 tenía G+D+E; Parte F no formalizada (alcance API-only, sin UI PedidosWeb).

**Fecha unificación:** 31/08/2026  
**Parte E:** [E-CC-PQ-11-tests.md](E-CC-PQ-11-tests.md)

---

## Updates fusionados y eliminados

| Origen update (eliminado) | Destino unificado |
|---------------------------|-------------------|
| `SPEC-101-02-modelos-update-01` | [SPEC-101-02-modelos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md) |
| `SPEC-001-02-acceso-y-seguridad-update` | [SPEC-001-02-acceso-y-seguridad.md](../../05-open-spec/001-Generaliddes/SPEC-001-02-acceso-y-seguridad.md) |
| `HU-GEN-02-visibilidad-datos-pedidosweb-update` | [HU-GEN-02-visibilidad-datos-pedidosweb.md](../../03-historias-usuario/001-Generaliddes/HU-GEN-02-visibilidad-datos-pedidosweb.md) |
| `HU-101-004-seleccion-cliente-update` | [HU-101-004-seleccion-cliente.md](../../03-historias-usuario/101-PedidosWeb/HU-101-004-seleccion-cliente.md) |
| `TR-SPEC-101-02-modelos-update-01` | [TR-SPEC-101-02-modelos.md](TR-SPEC-101-02-modelos.md) |
| `TR-GEN-02-visibilidad-datos-pedidosweb-update` | [TR-GEN-02-visibilidad-datos-pedidosweb.md](../001-Generaliddes/TR-GEN-02-visibilidad-datos-pedidosweb.md) |

---

## Lista C — originales desbloqueados

| ID | Documento base | Estado |
|----|----------------|--------|
| C2 | SPEC-001-02 | Finalizado (Parte I CC PQ #10/#11) |
| C3 | SPEC-101-02 (contactos + CC #10/#12) | Finalizado (Parte I CC PQ #10/#11) |
| C10 | HU-GEN-02 | Finalizado (Parte I CC PQ #10/#11) |
| C11 | HU-101-004 | Finalizado |
| — | TR-GEN-02, TR-101-02 | Finalizado (Parte I CC PQ #10/#11) |

---

## Manual

Sin cambio de operatoria web (consumo API para terceros). Tabla `pq_pedidosweb_clientescontactos` documentada en SPEC/TR-101-02.

---

## Veredicto Parte I

**Estado CC #11 en `00-ControlCalidad-PQ.md`:** **Finalizado (Parte I 31/08/2026)**

**Deploy:** SQL idempotente `backend/scripts/sql/alter-pq-pedidosweb-clientescontactos.sql` en tenants que no tengan la tabla/UQ.
