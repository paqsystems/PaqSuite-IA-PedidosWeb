# TR-SPEC-101-22c — Importación maestros: clientes y parámetros ERP

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-048](../../03-historias-usuario/101-PedidosWeb/HU-101-048-integracion-maestros-clientes-parametros.md) |
| **SPEC relacionada** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Prioridad** | **Must** |
| **Dependencias** | TR-22a plataforma · TR-22b catálogo (FK vendedores/listas/condiciones) |
| **Estado** | **Parte D implementada** — pendiente E / F1 |
| **C1** | [F-101-22-cierre-c1](F-101-22-cierre-c1-importacion-maestros-erp.md) (2026-10-21) |
| **Última actualización** | 2026-10-21 |

---

## 1) Resumen

Cuatro recursos: `clientes`, `clientes-direcciones`, `clientes-contactos`, `parametros`. SP + handlers; políticas A1-C03, C04, whitelist parámetros.

### In scope

- SP `pq_sp_integracion_maestros_clientes_*`, `_clientes_direcciones_*`, `_clientes_contactos_*`, `_parametros_*`
- Handlers + registry
- Whitelist cargada desde lista estática generada del seed [PQ_PARAMETROS_GRAL.PedidosWeb.seed.json](../../backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json) (claves `clave` del JSON)
- Recorte leyendas 1–5 a 60 chars (helper `IntegracionMaestroText::truncateUnicode60`)
- Baja física de cliente solo si no existen referencias en pedidos, deuda, cheques ni ventas detalladas; en transacción se eliminan primero contactos, luego direcciones y finalmente el cliente.
- OpenAPI 4 paths
- Feature tests

### Out of scope

- Alta `users` / `cod_login`
- UI consulta parámetros (solo lectura existente)

---

## 2) AC (HU-101-048)

| AC | Test |
|----|------|
| CA-01, CA-03, CA-04 | clientes delta; cod_login ignorado; idempotencia; eliminarSiAusente con integridad referencial |
| CA-02 | leyenda 80 → 60 en BD |
| CA-05…CA-06 | direcciones delta; snapshot → 422 |
| CA-07…CA-08 | contactos delta; omitidos no borran |
| CA-09…CA-10 | parametros whitelist / rechazo clave |
| CA-11 | OpenAPI |
| CA-12 | count pedidos cabecera unchanged |

---

## 3) Recursos → SP → handler

| Recurso | Handler | SP | Snapshot |
|---------|---------|-----|----------|
| `clientes` | `ClientesMaestroHandler` | `pq_sp_integracion_maestros_clientes_apply` | true (replace; eliminarSiAusente) |
| `clientes-direcciones` | `ClientesDireccionesMaestroHandler` | `pq_sp_integracion_maestros_clientes_direcciones_apply` | **false** |
| `clientes-contactos` | `ClientesContactosMaestroHandler` | `pq_sp_integracion_maestros_clientes_contactos_apply` | **false** |
| `parametros` | `ParametrosMaestroHandler` | `pq_sp_integracion_maestros_parametros_apply` | false (solo upsert claves enviadas) |

### 3.1 Clientes — JSON (camelCase)

Campos modelo §3.1: `codClient`, `nombre`, `fantasia`, `codVended`, `listaPrecios`, `codCondvta`, `codTranspor`, `bonificacion`, `nivel`, `expreso`, `expresoDire`, `eMail`, `razonSoci`, `leyenda1`…`leyenda5`.

- Ignorar `codLogin`.
- Pre-SP: truncar `leyenda1`…`leyenda5`.
- FK: `cod_vended`, `cod_condvta`, `cod_transpor`, `lista_precios` deben existir o ítem rechazado.

### 3.2 Direcciones

Clave `(codClient, idDe)`. `habitual`: aceptar `true`/`false`/`S`/`N` → persistir `char(1)`.

### 3.3 Contactos

Clave `(codClient, codContacto)`. Delta: **no** DELETE contactos no enviados.

### 3.4 Parámetros

Ítem:

```json
{
  "clave": "CodPerfilPedidos",
  "valorInt": 0
}
```

Solo una columna valor según `tipoValor` del seed y del contrato base `PQ_PARAMETROS_GRAL`: `B`→bool, `I`→int, `N`→decimal, `S`→string, `T`→text, `D`→datetime. Clave no whitelist → rechazado `integracion.maestro.parametroNoPermitido`.

Implementación whitelist:

- `config/integracion_maestros_parametros_whitelist.php` generado desde seed (script dev one-shot) **o** array PHP versionado listando las ~60 claves del seed PedidosWeb.
- La whitelist conserva también el `tipoValor` canónico de cada clave para validar el valor recibido y la columna destino.
- El contrato de tipos es el definido en [`pq-parametros-gral-tipo-valor.md`](../../_base/pq-parametros-gral-tipo-valor.md) y en `ParametrosGralTipoValor`:
  - `B` → `Valor_Bool`
  - `I` → `Valor_Int`
  - `N` → `Valor_Decimal`
  - `S` → `Valor_String`
  - `T` → `Valor_Text`
  - `D` → `Valor_DateTime`
- No existe un tipo `F` en el contrato vigente. `D` no representa decimal; representa fecha-hora.

---

## 4) Scripts SQL

`backend/scripts/sql/integracion-maestros/`:

- `sp_pq_integracion_maestros_clientes_apply.sql`
- `sp_pq_integracion_maestros_clientes_direcciones_apply.sql`
- `sp_pq_integracion_maestros_clientes_contactos_apply.sql`
- `sp_pq_integracion_maestros_parametros_apply.sql`

`parametros_apply`: `UPDATE`/`MERGE` solo fila `Programa='PedidosWeb' AND Clave=@clave`; nunca DELETE otras claves.

`clientes_apply` delete: solo vía flag `eliminarSiAusente` en ítem; verificar ausencia pedidos con `cod_cliente` (FK lógica).

---

## 5) OpenAPI

Paths:

- `POST .../clientes`
- `POST .../clientes-direcciones`
- `POST .../clientes-contactos`
- `POST .../parametros`

Documentar ejemplo ítem parámetro y cliente en descriptions.

---

## 6) Tests

| Clase | Escenario |
|-------|-----------|
| `IntegracionMaestroClientesTest` | delta, leyendas, cod_login ignorado |
| `IntegracionMaestroClientesDeleteTest` | eliminarSiAusente; elimina contactos/direcciones y rechaza cliente referenciado |
| `IntegracionMaestroContactosDeltaPreservaTest` | contacto omitido permanece |
| `IntegracionMaestroParametrosWhitelistTest` | clave inválida rechazada |
| `IntegracionMaestroDireccionesSnapshotRejectedTest` | 422 |

Setup: seed mínimo vendedor + cliente en SQL o usar datos dev existentes (tests feature con `@group integracion`).

---

## 7) Orden D1

Implementar **después** de TR-22b (catálogo mínimo cargado).

```text
22a → 22b (provincias…vendedores) → 22c clientes → 22c direcciones/contactos → 22c parametros
```

---

## 8) Checklist

- [x] Whitelist parámetros = seed PedidosWeb
- [x] Leyendas 60
- [x] Snapshot 422 en direcciones/contactos
- [x] SP desplegados
- [x] Sin modificar `pq_pedidosweb_pedidoscabecera` en import (feature tests con SQL Server)
