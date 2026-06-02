# HU-101-009 — Grabación de pedido (estado 0)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-009-grabar-pedido |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-01) |
| **Dependencias** | HU-101-006…008; HU-101-019 |

## Narrativa

Como **usuario autorizado**,  
quiero **grabar con el botón “Grabar pedido” en la pantalla única de carga**,  
para **dejar un pedido ingresado (0), actualizar uno existente o convertir un presupuesto activo**.

## Reglas de negocio

1. Acción disparada por botón **Grabar pedido** en SPEC-101-10 (misma pantalla que presupuesto).
2. Resultado según origen: **alta** → pedido **0** nuevo; **edición pedido 0/-1** → **0** mismo código; **presupuesto 99** → pedido **0** nuevo + origen **98** + cierre (HU-101-013).
3. GUID/número visible según secuencia por tenant en altas/conversiones.
4. Validar cabecera obligatoria y ≥ 1 renglón.
5. En conversión desde presupuesto: **`cod_presupuesto_origen`** en cabecera pedido y **`cod_pedido_generado`** en `presupuestos_cierres`.
6. Auditoría liviana; mail (HU-101-019); post-grabación según `CargaRecurrente`.

## Criterios de aceptación

- [ ] **CA-01:** Grabar pedido válido persiste cabecera+detalle en transacción.
- [ ] **CA-02:** Confirmación muestra últimos caracteres GUID y número visible.
- [ ] **CA-03:** Pedido aparece en consulta ingresados (HU-101-015).
- [ ] **CA-04:** E2E §9 madre: paso grabar pedido + mail (mock/log).

## Veredicto B1

**Lista para TR** (SPEC-101-04/05/10).
