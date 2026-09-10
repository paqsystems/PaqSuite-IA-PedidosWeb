# SPEC-101-04-update-01 — Copiar: dirección de entrega + leyendas 1–5

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-04-services-pedidos-update-01 |
| **SPEC base** | [SPEC-101-04-services-pedidos](../../101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Estado** | Pendiente |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #15 · **09/09/2026** |
| **HU relacionadas** | [HU-101-026-update-01](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-026-copiar-comprobante-update-01.md) |
| **TR relacionadas** | [TR-SPEC-101-04-update-01](../../../04-tareas/updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update-01.md) |
| **Última actualización** | 2026-09-09 |

## Objetivo

Al copiar un comprobante (`ComprobanteCopiaService::copiarBorrador` / `POST /api/v1/comprobantes/copiar`), el borrador de cabecera **debe** incluir la **dirección de entrega** (`id_de`) y las **leyendas 1 a 5** del origen, además de los campos ya mapeados (cliente, vendedor, condición, transporte, lista, observaciones, etc.).

## Decisiones (CC PQ #15)

| ID | Tema | Decisión |
|----|------|----------|
| D1-29 | Dirección de entrega | Copiar `id_de` del origen al borrador. Es el identificador de dirección de entrega del cliente (`pq_pedidosweb_clientesde`). La UI hidrata el SelectBox con ese `idDe`. |
| D1-30 | Leyendas 1–5 | Copiar `leyenda_1`…`leyenda_5` del origen al borrador, pasando por `LeyendaCabeceraLimits::recortarLeyendaCabecera` (tope 60, CC #13). |

## In scope

- `mapCabecera` de `ComprobanteCopiaService` (o equivalente).
- Contrato del payload `resultado.borrador.cabecera` del endpoint de copia.
- Tests unitarios del servicio de copia.

## Fuera de scope

- Cambiar política `ActualizarPrecioCopia` / validación de precios (CC #9).
- Sync de leyendas dirty al maestro cliente (CC #12) — aplica al **grabar**, no al copiar.
- Ampliar copia a otros campos de cabecera no listados en el hallazgo (bonif, fecha_entrega, expreso, etc.) salvo que ya se copien hoy.
- Cambios de UI DevExtreme (el mapper FE ya consume `id_de` y `leyenda_N`).

## Criterios de aceptación medibles

- [ ] **CA-CC15-C01:** Origen con `id_de` informado → borrador de copia trae el mismo `id_de`.
- [ ] **CA-CC15-C02:** Origen con `leyenda_1`…`leyenda_5` → borrador trae los mismos textos (recortados a 60 si exceden).
- [ ] **CA-CC15-C03:** Origen sin dirección / leyendas nulas → borrador conserva null/vacío sin error.
- [ ] **CA-CC15-C04:** Regresión: precios / `ActualizarPrecioCopia` sin cambio de comportamiento.

## Definición de listo

- [ ] HU-101-026-update-01 y TR-SPEC-101-04-update-01 alineados
- [ ] PHPUnit de copia cubre `id_de` + leyendas
- [ ] Smoke: Copiar desde consulta → carga muestra dirección y leyendas del origen
