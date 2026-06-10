# SPEC-001-04 - Configuración global

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md), lectura runtime (servicios existentes) |
| **TR relacionada** | [TR-GEN-04-consulta-parametros](../../04-tareas/001-Generaliddes/TR-GEN-04-consulta-parametros.md) (**C1** 2026-06-03) |
| **Estado** | En revisión |
| **Revisión A1** | Apto con observaciones (2026-05-28) |

## Objetivo

Definir el modelo inicial de configuración funcional global por módulo para PedidosWeb.

## Estado de ejecución

Implementable en MVP (lectura y uso de parámetros; **sin** ABM web de parámetros en portal).

## Decisiones humanas

| Tema | Decisión |
|------|----------|
| Inventario parámetros MVP | Fuente: producto **`PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` §10.6** (+ ampliaciones en TR) |
| ABM parámetros en portal | **Fuera de alcance** MVP; administración ERP/herramientas internas |

## Fuente de verdad de producto

- `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` — **§10.6 Listado de Parámetros**
- `docs/05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md` — `DiasVentasDetalladas`, dashboard, mails

## Fuentes (contexto)

| Capa | Documento |
|------|-----------|
| **BASE** | [`docs/_base/pq-parametros-gral-tipo-valor.md`](../../_base/pq-parametros-gral-tipo-valor.md) — catálogo **`tipo_valor`** (S/T/I/D/B/N) y mapeo `Valor_*` |
| **MONO** | [`docs/00-contexto/_mono/04-configuracion-global/parametros-generales.md`](../../00-contexto/_mono/04-configuracion-global/parametros-generales.md) — proceso transversal HU-007 |
| **Producto** | Inventario claves PedidosWeb: §10.6 definición conceptual |

## Alcance

- Catálogo de parámetros generales relevantes para MVP (nombres en producto §10.6).
- Reglas de consumo por backend y frontend (lectura desde tabla de parámetros del ERP/contexto `parametros-generales.md`; cache opcional en TR).
- Defaults y validación; fallback si parámetro ausente (documentar por parámetro crítico en HU).

## Parámetros críticos MVP (referencia producto §10.6)

| Parámetro | Uso en PedidosWeb MVP |
|-----------|------------------------|
| `DiasVentasDetalladas` | Historial ventas (Must) |
| `MinutosWeb` | Expiración sesión (GEN-02) y ventana de pedido en **-1** desde `fechahora_ultima_actividad` (HU-101-011) |
| `MinutosBloqueo`, `MinutosAviso` | Concurrencia edición vs descarga ERP |
| `DetallePorMail` | Mail al grabar comprobante |
| `MailDestinatariosAdicionales` | Destinatarios extra en mail — lista separada por **`;`** (parser tolerante `,`) — HU-101-019 / TR-101-13 |
| `Mail_DireccionRemitente` | Remitente mails comerciales |
| `mailCCO` | CCO global opcional en envíos |
| `CargaRecurrente` | Flujo post-grabación pedido/presupuesto |
| `CodMotivoCierreExitoso` | Conversión presupuesto → pedido: `id_motivo` en `pq_pedidosweb_motivos_cierre` (tipo **positivo**, activo). Ver HU-101-013. |
| Resto §10.6 | Módulos según HU de negocio |

Inventario completo con **`CAPTION`**, **`TOOLTIP`** y `tipo_valor`: [`docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json`](../../backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json) (57 claves, producto §10.6 + ampliaciones MVP).

## Fuera de alcance

- ABM web de parámetros en el portal MVP.
- Parametrización avanzada no listada en producto §10.6.

## Entregables verificables

- Inventario MVP con `CAPTION`/`TOOLTIP` en [`docs/backend/seed/PQ_PARAMETROS_GRAL/`](../../backend/seed/PQ_PARAMETROS_GRAL/README.md).
- Contrato de lectura: servicio/repository parámetros (TR).
- Reglas de fallback documentadas por parámetro crítico (mínimo los de la tabla anterior).

## Criterios de aceptación medibles

- [ ] Cada parámetro crítico de la tabla tiene default documentado antes del cierre del slice que lo usa.
- [ ] Una sola fuente de lectura en runtime (backend; frontend vía API si aplica).
- [ ] Reglas funcionales Must del MVP referencian su parámetro cuando dependen de configuración.

## Trazabilidad HU

| HU | Tema SPEC |
|----|-----------|
| HU-GEN-04-lectura-parametros | Servicio/repository lectura |
| [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) | Pantalla consulta solo lectura — **TR:** [TR-GEN-04-consulta-parametros](../../04-tareas/001-Generaliddes/TR-GEN-04-consulta-parametros.md) |
| HU-GEN-04-fallback-parametros | Defaults y fallback críticos |

### UI consulta parámetros (CC PQ #3)

- Columna **Valor**: encabezado y celdas **centrados** horizontalmente.

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 09/06/2026 | CC PQ #3 | Columna Valor centrada en consulta parámetros |
| 09/06/2026 | Parte I | Unificación `SPEC-001-04-configuracion-global-update` |
