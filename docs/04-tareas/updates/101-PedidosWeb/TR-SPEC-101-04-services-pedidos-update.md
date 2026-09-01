# TR-SPEC-101-04-update — Recortar leyendas a 60 al grabar

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-04-services-pedidos](../../101-PedidosWeb/TR-SPEC-101-04-services-pedidos.md) |
| **SPEC relacionada** | [SPEC-101-04-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update.md) |
| **HU relacionada** | [HU-101-009-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-009-grabar-pedido-update.md) · [HU-101-010-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-010-grabar-presupuesto-update.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **Última actualización** | 2026-09-01 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Recortar leyendas > 60 al persistir pedido/presupuesto. Helper único (p. ej. `recortarLeyendaCabecera` / `LeyendaCabeceraLimits::MAX_CARACTERES = 60`) reutilizable por Excel e IA.

**Prohibido:** regla Laravel `max:60` que devuelva 4xx.

## 2) Criterios de aceptación

- **AC-CC13-T-G1:** Antes de persistir, `leyenda_1`…`leyenda_5` pasan por el helper (`mb_substr` 60).
- **AC-CC13-T-G2:** PHPUnit: grabar con 61 caracteres → éxito; valor persistido de longitud 60.
- **AC-CC13-T-G3:** OpenAPI `leyenda_N` con `maxLength=60` (longitud almacenada).
- **AC-CC13-T-G4:** `CabeceraInicialService::resolveLeyendaCliente` recorta a 60.

## 3) Implementación

- Normalizar en `PedidoService` (o mapper de cabecera) al armar el array a guardar y al sync de cliente.
- Regenerar spec OpenAPI en D.
- `ComprobanteGrabacionPayload`: **no** añadir `max:60` que falle.

## 4) Tests

- Unit del helper (null, 60, 61, unicode).
- Feature grabar con leyenda larga.

## 5) Fuera de alcance

- UI `maxLength` → TR-101-10-update.
- Excel / IA → TR-101-16/21/19 (llaman el mismo helper).
