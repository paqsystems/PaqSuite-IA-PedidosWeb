# TR-SPEC-101-19-update — Asistente IA: recortar leyendas a 60

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-19-asistente-carga-ia-mutaciones](../../101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **SPEC relacionada** | [SPEC-101-19-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones-update.md) |
| **HU relacionada** | [HU-101-039-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera-update.md) · [HU-101-040-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar-update.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **Última actualización** | 2026-09-01 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Executor de `setCampoLibre` / patch cabecera y apply extracto imagen: recortar `leyenda1`…`leyenda5` con el helper de TR-101-04. **No** `validationError` por longitud.

## 2) Criterios de aceptación

- **AC-CC13-T-A1:** Tool/intent leyenda con 61 caracteres → aplica los primeros 60; sin `validationError`.
- **AC-CC13-T-A2:** 60 caracteres → aplica el texto completo.
- **AC-CC13-T-A3:** Extracto imagen: leyenda > 60 entra recortada en `applyImageExtract.cabecera`.

## 3) Implementación

- Recorte en executor PHP (no confiar en el LLM).
- `patchAsistenteCabecera.ts`: defensa FE opcional (misma constante); MUST en backend.
- Tests unit del executor y del extracto imagen.
- Sin clave i18n de rechazo por largo.

## 4) Fuera de alcance

- Grabar (J): TR-101-04-update recorta al persistir.
