# HU-101-026-update-01 — Copiar: dirección de entrega + leyendas

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-026-copiar-comprobante-update-01 |
| **HU base** | [HU-101-026-copiar-comprobante](../../101-PedidosWeb/HU-101-026-copiar-comprobante.md) |
| **SPEC origen** | [SPEC-101-04-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update-01.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #15 · **09/09/2026** |
| **TR** | [TR-SPEC-101-04-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update-01.md) |
| **Última actualización** | 2026-09-09 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario comercial**,  
quiero **que al copiar un pedido o presupuesto se trasladen la dirección de entrega y las leyendas 1 a 5 del origen**,  
para **no tener que volver a cargar esos datos en el borrador**.

## Reglas de negocio

1. **RN-CC15-C01 (D1-29):** El borrador de copia incluye `id_de` (dirección de entrega) del comprobante origen.
2. **RN-CC15-C02 (D1-30):** El borrador incluye `leyenda_1`…`leyenda_5` del origen, con tope de 60 caracteres (recorte, no rechazo).
3. **RN-CC15-C03:** Si el origen no tiene dirección o leyendas, el borrador queda sin esos valores; la copia no falla por ese motivo.
4. Sin cambio en política de precios (`ActualizarPrecioCopia`).

## Criterios de aceptación

- [ ] **CA-CC15-C01:** Copiar un pedido con dirección de entrega seleccionada abre carga con la misma dirección.
- [ ] **CA-CC15-C02:** Copiar un pedido con leyendas 1–5 abre carga con esas leyendas visibles/editables.
- [ ] **CA-CC15-C03:** Copiar un pedido sin dirección ni leyendas sigue abriendo carga sin error.
- [ ] **CA-CC15-C04:** Tras copiar, grabar persiste dirección y leyendas como en un alta normal.

## Escenarios Gherkin

```gherkin
Feature: Copia de dirección y leyendas

  Scenario: Copia traslada dirección y leyendas
    Given un pedido origen con id_de = 3 y leyenda_1 = "Retira cliente"
    When el usuario ejecuta Copiar
    Then el borrador tiene id_de = 3
    And el borrador tiene leyenda_1 = "Retira cliente"

  Scenario: Origen sin dirección ni leyendas
    Given un pedido origen sin id_de ni leyendas
    When el usuario ejecuta Copiar
    Then el borrador abre sin error
    And id_de y leyendas quedan vacíos o nulos
```

## Fuera de alcance

- Re-inicializar leyendas desde maestro cliente al copiar (en copia se usan las del comprobante origen).
- Cambios de UI del SelectBox de dirección o de los campos de leyenda.
