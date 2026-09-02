# HU-101-040-update — Extracto imagen: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-040-asistente-carga-ia-articulos-grabar-update |
| **HU base** | [HU-101-040-asistente-carga-ia-articulos-grabar](../../101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) |
| **SPEC origen** | [SPEC-101-19-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones-update.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **TR** | [TR-SPEC-101-19-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones-update.md) |
| **Última actualización** | 2026-09-01 |

## Estado de alcance

| Campo | Valor |
|-------|-------|
| Estado | Pendiente |

## Narrativa

Como **usuario que carga desde imagen**,  
quiero **que una leyenda extraída de más de 60 caracteres se recorte y se aplique al borrador**,  
para **no perder ese dato**.

## Reglas de negocio

1. **RN-CC13-K01:** En apply extracto (K), cada `leyenda1`…`leyenda5` se recorta a 60 y **sí se aplica** (candidato válido recortado).

## Criterios de aceptación

- [ ] **CA-CC13-K01:** Extracto con `leyenda1` de 61 caracteres → el campo se hidrata con 60 caracteres; renglones válidos también se aplican.
