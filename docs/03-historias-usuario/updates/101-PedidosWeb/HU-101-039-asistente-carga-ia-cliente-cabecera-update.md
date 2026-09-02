# HU-101-039-update — Asistente IA: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-039-asistente-carga-ia-cliente-cabecera-update |
| **HU base** | [HU-101-039-asistente-carga-ia-cliente-cabecera](../../101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera.md) |
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

Como **usuario en carga con asistente**,  
quiero **que al indicar una leyenda 1–5 más larga de 60 caracteres el sistema la recorte y la aplique**,  
para **completar el borrador sin un error de validación**.

## Reglas de negocio

1. **RN-CC13-A01:** `setCampoLibre` / patch de `leyendaN`: recortar a 60 y mutar; **no** `validationError` por longitud.
2. **RN-CC13-A02:** Pedido compuesto multilínea: mismo recorte por cada leyenda.

## Criterios de aceptación

- [ ] **CA-CC13-A01:** Mensaje «leyenda 1:» + 61 caracteres → `leyenda1` del borrador queda en 60; sin error.
- [ ] **CA-CC13-A02:** Mensaje con 60 caracteres → el campo se asigna completo.
