# Cierre I — CC PQ #12 (30/08/2026; re-unificación 31/08/2026)

## Alcance

Parte **I** del dispatcher: fusión de updates **Finalizado** (autorizado por QA manual PQ) en documentos base (SPEC, HU, TR), actualización de manual de usuario y cierre formal del **Control de Calidad #12** tras Partes **D + E + F**.

**Primera unificación:** 30/08/2026 (updates `*-update-01` CC #12)  
**Re-unificación:** 31/08/2026 — integración conjunta con deltas CC #10/#11 en las mismas familias SPEC/HU/TR; eliminación total de `docs/.../updates/` PedidosWeb pendientes.

**Partes previas:** [E-CC-PQ-12-tests.md](E-CC-PQ-12-tests.md) · [F-CC-PQ-12-cierre-formal.md](F-CC-PQ-12-cierre-formal.md)

Ver también: [I-CC-PQ-10-cierre-formal.md](I-CC-PQ-10-cierre-formal.md) · [I-CC-PQ-11-cierre-formal.md](I-CC-PQ-11-cierre-formal.md)

---

## Updates CC #12 fusionados (30/08/2026)

### SPEC

| Origen update | Destino unificado |
|---------------|-------------------|
| `SPEC-001-04-configuracion-global-update-01` | [SPEC-001-04-configuracion-global.md](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) |
| `SPEC-101-02-modelos-update-02` | [SPEC-101-02-modelos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md) |
| `SPEC-101-04-services-pedidos-update-01` | [SPEC-101-04-services-pedidos.md](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| `SPEC-101-07-consultas-api-update-01` | [SPEC-101-07-consultas-api.md](../../05-open-spec/101-PedidosWeb/SPEC-101-07-consultas-api.md) |
| `SPEC-101-10-pantalla-carga-update-01` | [SPEC-101-10-pantalla-carga.md](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| `SPEC-101-11-consultas-ui-update-01` | [SPEC-101-11-consultas-ui.md](../../05-open-spec/101-PedidosWeb/SPEC-101-11-consultas-ui.md) |
| `SPEC-101-13-mails-update-01` | [SPEC-101-13-mails.md](../../05-open-spec/101-PedidosWeb/SPEC-101-13-mails.md) |

### HU

| Origen update | Destino | Estado HU base |
|---------------|---------|----------------|
| `HU-GEN-04-…-update-01` | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) | **Finalizado** |
| `HU-101-004-…-update-01` | [HU-101-004-seleccion-cliente](../../03-historias-usuario/101-PedidosWeb/HU-101-004-seleccion-cliente.md) | **Finalizado** |
| `HU-101-005-…-update` | [HU-101-005-inicializacion-cabecera](../../03-historias-usuario/101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-006-…-update-01` | [HU-101-006-carga-renglones](../../03-historias-usuario/101-PedidosWeb/HU-101-006-carga-renglones.md) | **Finalizado** |
| `HU-101-008-…-update` | [HU-101-008-precio-importes](../../03-historias-usuario/101-PedidosWeb/HU-101-008-precio-importes.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-009-…-update` | [HU-101-009-grabar-pedido](../../03-historias-usuario/101-PedidosWeb/HU-101-009-grabar-pedido.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-010-…-update` | [HU-101-010-grabar-presupuesto](../../03-historias-usuario/101-PedidosWeb/HU-101-010-grabar-presupuesto.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-011-…-update` | [HU-101-011-editar-pedido](../../03-historias-usuario/101-PedidosWeb/HU-101-011-editar-pedido.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-018-…-update` | [HU-101-018-consulta-stock](../../03-historias-usuario/101-PedidosWeb/HU-101-018-consulta-stock.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-019-…-update-01` | [HU-101-019-mail-grabar](../../03-historias-usuario/101-PedidosWeb/HU-101-019-mail-grabar.md) | **Finalizado** |
| `HU-101-021-…-update` | [HU-101-021-consulta-deuda](../../03-historias-usuario/101-PedidosWeb/HU-101-021-consulta-deuda.md) | **Finalizado (Parte I CC PQ #12)** |
| `HU-101-023-…-update` | [HU-101-023-historial-ventas](../../03-historias-usuario/101-PedidosWeb/HU-101-023-historial-ventas.md) | **Finalizado (Parte I CC PQ #12)** |

### TR

| Origen update | Destino unificado |
|---------------|-------------------|
| `TR-GEN-04-consulta-parametros-update-01` | [TR-GEN-04-consulta-parametros.md](../001-Generaliddes/TR-GEN-04-consulta-parametros.md) |
| `TR-SPEC-101-02-modelos-update-02` | [TR-SPEC-101-02-modelos.md](TR-SPEC-101-02-modelos.md) |
| `TR-SPEC-101-04-services-pedidos-update-01` | [TR-SPEC-101-04-services-pedidos.md](TR-SPEC-101-04-services-pedidos.md) |
| `TR-SPEC-101-07-consultas-api-update-01` | [TR-SPEC-101-07-consultas-api.md](TR-SPEC-101-07-consultas-api.md) |
| `TR-SPEC-101-10-pantalla-carga-update-01` | [TR-SPEC-101-10-pantalla-carga.md](TR-SPEC-101-10-pantalla-carga.md) |
| `TR-SPEC-101-11-consultas-ui-update-01` | [TR-SPEC-101-11-consultas-ui.md](TR-SPEC-101-11-consultas-ui.md) |
| `TR-SPEC-101-13-mails-update-01` | [TR-SPEC-101-13-mails.md](TR-SPEC-101-13-mails.md) |

---

## Re-unificación 31/08/2026

Las familias anteriores recibieron además los deltas CC #10 (`CargaUnidadesVenta`) y CC #11 (contactos API) en el mismo cuerpo documental. **No quedan archivos `*-update.md`** en `docs/05-open-spec/updates/`, `docs/03-historias-usuario/updates/` ni `docs/04-tareas/updates/` para PedidosWeb CC #10/#11/#12.

**Lista C:** todos los originales bloqueados (C1–C21) pasaron a **Finalizado** — ver [I-CC-PQ-10](I-CC-PQ-10-cierre-formal.md).

---

## Manual y producto

| Documento | Cambio |
|-----------|--------|
| `docs/99-manual-usuario/PedidosWeb.md` | §6.2 saldo deuda; §6.7 no stockeables; §6.13 sync leyendas; §6.15 equivalencia y precio neto; §6.16 parámetros (incl. `CargaUnidadesVenta`); mail Bultos/Unidades; §9.1 colores; §9.3 fechas; §9.4 stock |
| `docs/02-producto/PedidosWeb/consulta-parametros.md` | `IncluyeArticulosNoStockeables`, `CargaUnidadesVenta` |

---

## Observaciones no bloqueantes (heredadas de F)

| ID | Tema | Destino |
|----|------|---------|
| OBS-F-01 | Script smoke HTTP no re-ejecutado en F1 | Opcional ops |
| OBS-F-03 | Historial fechas DateBox en web | Aceptado |
| OBS-F-04 | `migrate` / `alter-pq-pedidosweb-stockeable.sql` en tenants | Runbook deploy |

---

## Veredicto Parte I

| Familia | Estado documental |
|---------|-------------------|
| SPEC/HU/TR CC #12 + familias compartidas #10/#11 | **Finalizado (Parte I)** — sin updates hermanos |
| HU 005, 008, 009, 010, 011, 018, 021, 023 | **Finalizado (Parte I CC PQ #12)** — CA CC #12 marcados `[x]` |

**Estado CC #12 en `00-ControlCalidad-PQ.md`:** **Finalizado (Parte I 30/08/2026; re-unificación 31/08/2026)**

**Activación deploy (no incluida en Parte I):** `php artisan migrate --force` o SQL `backend/scripts/sql/alter-pq-pedidosweb-stockeable.sql`; seed/INSERT `IncluyeArticulosNoStockeables`.
