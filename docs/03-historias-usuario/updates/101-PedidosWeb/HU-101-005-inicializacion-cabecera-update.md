# HU-101-005-update — Leyendas de cabecera limitadas a 60 caracteres

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-005-inicializacion-cabecera-update |
| **HU base** | [HU-101-005-inicializacion-cabecera](../../101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) |
| **SPEC origen** | [SPEC-101-10-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update.md) · [SPEC-101-02-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-02-modelos-update.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** · ítem Leyendas de Pedidos |
| **TR** | [TR-SPEC-101-10-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga-update.md) |
| **Última actualización** | 2026-09-01 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario que carga un comprobante**,  
quiero **que las leyendas 1 a 5 no superen 60 caracteres**,  
para **alinear el portal con el largo del ERP y evitar textos que no caben en destino**.

## Alcance incluido

- Tope **60** en los cinco `TextBox` de leyendas (web y native, mismo componente).
- Inicialización desde cliente: el valor mostrado no supera 60 (tras DDL; defensa en hidratación).

## Fuera de alcance

- Observaciones.
- Validación de grabación → HU-101-009/010-update.

## Reglas de negocio

1. **RN-CC13-C01:** Cada leyenda 1–5 admite como máximo 60 caracteres Unicode.
2. **RN-CC13-C02:** La cota aplica en carga nueva, edición y copia (misma UI).

## Criterios de aceptación

- [ ] **CA-CC13-C01:** En carga web, `leyenda-1` … `leyenda-5` no aceptan más de 60 caracteres (`maxLength`).
- [ ] **CA-CC13-C02:** Misma cota en native (`ComprobanteLeyendasPie` compartido).
- [ ] **CA-CC13-C03:** Pegar un texto de 80 caracteres deja como máximo 60 en el control.
