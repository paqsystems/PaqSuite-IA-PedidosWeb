# Cierre B / B1 — SPEC-101-18 / 19 / 20 — Asistente IA en carga

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-13 |
| **Épica** | Asistente IA operativo en carga |
| **A1** | [F-101-18-20-cierre-a1](F-101-18-20-cierre-a1-asistente-carga-ia.md) — Apto con observaciones |
| **Veredicto B1** | **Cerrado** — 6 HU enriquecidas; listas para Parte C (TR) |

## HU generadas

| HU | SPEC | Título | B1 |
|----|------|--------|----|
| [HU-101-037](../../03-historias-usuario/101-PedidosWeb/HU-101-037-asistente-carga-ia-panel-gate.md) | 18 | Panel, gate BYOK, orquestación | Sí |
| [HU-101-038](../../03-historias-usuario/101-PedidosWeb/HU-101-038-asistente-carga-ia-audio-imagen.md) | 18 | Audio Web Speech + imagen entrada | Sí |
| [HU-101-039](../../03-historias-usuario/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera.md) | 19 | Cliente, cabecera, cambio I | Sí |
| [HU-101-040](../../03-historias-usuario/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) | 19 | Artículos, grabar, apply imagen | Sí |
| [HU-101-041](../../03-historias-usuario/101-PedidosWeb/HU-101-041-asistente-carga-ia-consulta-stock.md) | 20 | Consulta stock E | Sí |
| [HU-101-042](../../03-historias-usuario/101-PedidosWeb/HU-101-042-asistente-carga-ia-consultas-cliente.md) | 20 | Deuda / cheques / historial F–H | Sí |

## Cobertura SPEC → HU

| SPEC CA | HU |
|---------|-----|
| CA-UX01, CA-UX02, CA-SYNC01 | 037 |
| CA-L01, CA-K-IN01 | 038 |
| CA-A01, CA-B01, CA-C01, CA-I01 | 039 |
| CA-D01, CA-J01, CA-K01 | 040 |
| CA-E01, CA-E02 | 041 |
| CA-F01, CA-G01, CA-H01 | 042 |

## Orden sugerido Parte C (TR)

1. TR-SPEC-101-18 — shell + gate + contrato turno (037)  
2. TR-SPEC-101-18b — audio + imagen entrada (038)  
3. TR-SPEC-101-19a — cliente/cabecera (039)  
4. TR-SPEC-101-19b — artículos/grabar/K (040)  
5. TR-SPEC-101-20a — stock (041)  
6. TR-SPEC-101-20b — F–H (042)  

(Puede consolidarse en 3 TR: 18 / 19 / 20 si el equipo prefiere menos archivos.)

## Siguiente paso

Parte **C/C1** — **cerrada** ([F-101-18-20-cierre-c1](F-101-18-20-cierre-c1-asistente-carga-ia.md)). Siguiente: **D1** (plan de implementación) sobre TR-18 → 19 → 20.
