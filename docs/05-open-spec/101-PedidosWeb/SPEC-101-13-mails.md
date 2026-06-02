# SPEC-101-13 — Mails comerciales

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente |
| **Prioridad épica** | Must |

## Objetivo

Envío de mail al crear/modificar pedido o presupuesto usando el **mismo canal** que recuperación de contraseña.

## In scope

- Disparo post-grabación y post-modificación (producto §18)
- Texto simple; detalle según parámetro `DetallePorMail` (contexto SPEC-001-04)
- **Destinatarios TO** (HU-101-019, cerrado):
  1. `clientes.e_mail` del comprobante.
  2. `vendedores.e_mail` del `clientes.cod_vended`.
  3. `vendedores.mail_supervisor` del mismo vendedor, si informado y ≠ `e_mail` del vendedor.
  4. Lista `MailDestinatariosAdicionales` (parámetro ERP).
  - Deduplicar direcciones (normalizar mayúsculas/minúsculas).
- Log de error de envío (no bloquear grabación salvo que HU diga lo contrario)

## Fuera de scope

- PDF adjunto (fuera MVP producto §3)
- Mail en eliminación o eventos ERP

## Dependencias

- SPEC-101-04
- Contexto SPEC-001-04 (`DetallePorMail`, `MailDestinatariosAdicionales`, `Mail_DireccionRemitente`, `mailCCO` opcional)

## HU relacionadas

HU-101-019

## Definición de listo

- [ ] Test integración o feature con mail fake/log
- [ ] Sin secretos en repo
