# HU-101-026 — Copiar comprobante

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-026-copiar-comprobante |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must (AMB-C04) |
| **Estado** | Finalizado (Parte I — CC PQ #9) |
| **Última actualización** | 2026-07-02 (Parte I — CC PQ #9) |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario comercial**,  
quiero **copiar un comprobante anterior como base de uno nuevo respetando la política de precios configurada en el ERP**,  
para **agilizar cargas repetitivas conservando importes históricos o aplicando la lista de precios vigente según corresponda**.

## Alcance

- Acción **Copiar** en consultas de pedidos ingresados (`0`), pedidos pendientes (`1`) y presupuestos activos (`99`).
- `POST /api/v1/comprobantes/copiar` devuelve borrador según parámetro **`ActualizarPrecioCopia`** (SPEC-001-04).
- Tras abrir el borrador, el usuario puede editar y grabar con `POST /api/v1/comprobantes/grabar`.

## Reglas de negocio

1. Origen puede ser pedido o presupuesto visible según permisos.
2. Nuevo comprobante obtiene nuevo GUID y número visible; estado según tipo elegido (0 o 99).
3. Trazabilidad opcional al origen en cabecera si el modelo lo soporta.
4. Aplicar validaciones de alta (cliente, renglones, parámetros).
5. **RN-C01 (CC PQ #9):** Leer `ActualizarPrecioCopia` al copiar; default `false` si ausente.
6. **RN-C02:** Con `false`, conservar precios del detalle origen; validar contra `ArticulosPrecioCero` / `ArticulosSinPrecio` **vigentes**. Si no se admiten precios cero y el origen tiene `precio ≤ 0` → rechazar copia (422).
7. **RN-C03:** Con `true`, resolver precio por renglón desde `pq_pedidosweb_listaprecios_articulos` (`lista_precios` cabecera + `cod_articulo`).
8. **RN-C04:** Con `true`, artículo sin precio en lista o precio cero: validar **por separado** según `ArticulosSinPrecio` (sin fila) y `ArticulosPrecioCero` (precio 0). Si no se admite → rechazar copia (`business.precioCeroNoPermitido`, 422); modal en UI; no abrir carga.
9. **RN-C05:** Recalcular importes con `CalculoTotalesService` cuando se actualizan precios desde lista.
10. **RN-C06:** Conversión presupuesto→pedido **no** usa `ActualizarPrecioCopia`.

## Criterios de aceptación

- [x] **CA-01:** Acción «Copiar» llama `POST /api/v1/comprobantes/copiar` y abre pantalla con `resultado.borrador` **sin persistir** en BD.
- [x] **CA-02:** Usuario puede cambiar tipo pedido/presupuesto antes de grabar si el flujo lo permite.
- [x] **CA-03:** Grabar copia invoca `POST /api/v1/comprobantes/grabar` y persiste como comprobante nuevo independiente (nuevo GUID / número visible).
- [x] **CA-C01:** Con `ActualizarPrecioCopia = false` y precios origen válidos, copiar abre carga con mismos precios que el origen.
- [x] **CA-C07:** Con `ActualizarPrecioCopia = false` y parámetros restrictivos, renglón origen con precio cero → copia rechazada; no abre carga.
- [x] **CA-C02:** Con `ActualizarPrecioCopia = true` y precios en lista, renglones muestran precio de lista e importes recalculados.
- [x] **CA-C03:** Con `ActualizarPrecioCopia = true`, artículo sin precio en lista o precio cero y parámetros restrictivos → error modal; no abre carga.
- [x] **CA-C04:** Copiar desde pedido pendiente (`1`) y presupuesto activo (`99`) cumple las mismas reglas que pedido ingresado (`0`).
- [x] **CA-C05:** Conversión presupuesto→pedido no usa `ActualizarPrecioCopia`.

## Historial CC PQ #9 (02/07/2026) — Parte I 02/07/2026

Unificación delta CC PQ #9 (archivo `*-update` eliminado en Parte I). Evidencia: [F-CC-PQ-9-cierre-formal](../../04-tareas/101-PedidosWeb/F-CC-PQ-9-cierre-formal.md).

## Veredicto B1

**Lista para TR** (SPEC-101-04/10).
