# SPEC-101-13 — Mails comerciales

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Finalizado (Parte I CC PQ #10/#11) |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-31 |

## Objetivo

Envío de mail al crear/modificar pedido o presupuesto usando el **mismo canal** que recuperación de contraseña.

## In scope

- Disparo post-grabación y post-modificación (producto §18)
- Plantillas **i18n** según locale de sesión; asunto `{nombreEmpresa} - {tipo} {accion}`
- Cabecera completa en todo mail (13 campos §3.2 TR); tabla de renglones (7 columnas + **precio neto unitario**) solo si `DetallePorMail` activo; pie **solo texto fijo i18n** (`footerConsulta`)
- Detalle de renglones (CC PQ #12 — **prevalece** sobre CC PQ #10): columnas **Bultos** (`cantidad_venta`) y **Unidades** (`cantidad` neta/stock). i18n `mail.comprobanteNotification.colBultos` / `colUnidades`. Mostrar ambas cuando `cantidad_venta` está poblada. *(CC PQ #10 proponía una sola columna según `CargaUnidadesVenta`; CC PQ #12 posterior define dos columnas — regla vigente.)*
- **Importe neto** e **importe bruto** en cabecera del mail reflejan descuentos/bonificaciones aplicados (coherente con totales grabados)
- Redacción mail en **es, en, fr, pt, it** (locales del portal)
- **Destinatarios TO** (HU-101-019, cerrado):
  1. `pq_pedidosweb_clientes.e_mail` del `pq_pedidosweb_pedidoscabecera.cod_cliente` del comprobante.
  2. `pq_pedidosweb_vendedores.e_mail` del `pq_pedidosweb_clientes.cod_vended`.
  3. `pq_pedidosweb_vendedores.mail_supervisor` del mismo vendedor, si informado y ≠ `e_mail` del vendedor.
  4. Lista `MailDestinatariosAdicionales` — separador canónico **`;`** (parser tolerante `,`).
  - Deduplicar direcciones (normalizar mayúsculas/minúsculas).
- `nombreEmpresa`: tenant slug (MVP) o `EMPRESAS_CONEXION` (etapa posterior)
- Log de error de envío (no bloquear grabación)
- Toast informativo en UI si grabación OK y mail no enviado (TR-101-13 §6)

## Fuera de scope

- PDF adjunto (fuera MVP producto §3)
- Mail en eliminación o eventos ERP

## Dependencias

- SPEC-101-04
- Contexto SPEC-001-04 (`DetallePorMail`, `MailDestinatariosAdicionales`, `Mail_DireccionRemitente`, `mailCCO` opcional)

## HU relacionadas

HU-101-019

## TR de implementación

Detalle normativo: [TR-SPEC-101-13-mails](../../04-tareas/101-PedidosWeb/TR-SPEC-101-13-mails.md) (mapeo campos §3.2, formatos §3.4).

## Definición de listo

- [ ] Test integración o feature con mail fake/log
- [ ] Sin secretos en repo
- [ ] Plantillas alineadas a legacy (asunto, intro con `guidSufijo`, cabecera, detalle condicional)
- [x] CC PQ #1: precio neto unitario en detalle mail; importes neto/bruto con descuentos
- [x] CC PQ #12: columnas Bultos / Unidades en detalle mail

## Historial de cambios

| Fecha | Origen | Resumen |
|-------|--------|---------|
| 04/06/2026 | CC PQ #1 | Mail: precio neto renglón + importes con descuento |
| 09/06/2026 | Parte I | Unificación `SPEC-101-13-mails-update` |
| 28/08/2026 | CC PQ #12 | Columnas Bultos / Unidades en mail de grabación |
| 30/08/2026 | Parte I | Unificación `SPEC-101-13-mails-update-01` (CC PQ #12) |
| 30/07/2026 | CC PQ #10 | Mail: una cantidad según parámetro *(supersedido por CC #12 — ver in scope)* |
| 31/08/2026 | Parte I | Unificación `SPEC-101-13-mails-update`. Conflicto #10 vs #12 resuelto: prevalece #12 |
