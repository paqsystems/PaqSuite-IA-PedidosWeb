# TR-SPEC-101-10-update — TextBox leyendas maxLength 60

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-10-pantalla-carga](../../101-PedidosWeb/TR-SPEC-101-10-pantalla-carga.md) |
| **SPEC relacionada** | [SPEC-101-10-update](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-10-pantalla-carga-update.md) |
| **HU relacionada** | [HU-101-005-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-005-inicializacion-cabecera-update.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **Última actualización** | 2026-09-01 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)  
**UI:** [pantalla-carga-comprobante-ui.md](../../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §9

---

## 1) Alcance

`ComprobanteLeyendasPie.tsx`: `maxLength={60}` en cada `TextBox` DevExtreme. Constante camelCase `leyendaMaxCaracteres = 60` (exportada si el asistente la reutiliza en FE).

Web y native quedan cubiertos: mobile importa el mismo componente.

## 2) Criterios de aceptación

- **AC-CC13-T-C1:** Los cinco TextBox tienen `maxLength={60}`.
- **AC-CC13-T-C2:** Vitest: el componente pasa `maxLength={60}` (o constante) a `TextBox`.
- **AC-CC13-T-C3:** `data-testid` `leyenda-1` … `leyenda-5` y `leyendas-pie` sin cambio.
- **AC-CC13-T-C4:** Producto §9 documenta máximo 60.

## 3) Implementación

- No HTML nativo; `maxLength` de DevExtreme `TextBox`.
- i18n: no obligatorio un mensaje (el control no deja teclear de más). Opcional `maxLength` visible no requerido.

## 4) Tests

- Unit Vitest del pie de leyendas (render + prop).

## 5) Fuera de alcance

- Validación backend (TR-101-04-update).
