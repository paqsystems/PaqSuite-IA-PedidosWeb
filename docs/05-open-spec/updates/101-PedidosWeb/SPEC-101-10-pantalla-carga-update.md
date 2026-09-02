# SPEC-101-10-update — Leyendas 1–5: tope 60 en UI de carga

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-10-pantalla-carga-update |
| **SPEC base** | [SPEC-101-10-pantalla-carga](../../101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #13 · **01/09/2026** |
| **HU relacionadas** | [HU-101-005-update](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-005-inicializacion-cabecera-update.md) |
| **TR relacionadas** | [TR-SPEC-101-10-update](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-10-pantalla-carga-update.md) |
| **Última actualización** | 2026-09-01 |

## Objetivo

En la pantalla de carga (web y native), las cinco leyendas de cabecera no admiten más de **60** caracteres (CC PQ #13).

## In scope

- `ComprobanteLeyendasPie`: cada `TextBox` DevExtreme con **`maxLength={60}`** (no introducir `<input>` nativo).
- Misma cota en **web** y **mobile** (`PedidosCargaMobileCabeceraStep` reutiliza el componente).
- `data-testid` vigentes (`leyendas-pie`, `leyenda-1` … `leyenda-5`) sin cambio.
- Producto [pantalla-carga-comprobante-ui.md](../../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) §9: documentar máximo 60 al ejecutar D.
- Pegado de texto más largo: DevExtreme recorta al máximo (comportamiento `maxLength`); el usuario no puede dejar 61+ en el control.

## Fuera de scope

- Observaciones (sin tope 60 en este CC).
- Consultas de cabecera (columnas ocultas de solo lectura).

## Definición de listo

- [ ] No se puede ingresar más de 60 caracteres en ninguna leyenda 1–5 (web y native)
- [ ] Grabar envía como máximo 60 (UI `maxLength`; API recorta si llegara más)
