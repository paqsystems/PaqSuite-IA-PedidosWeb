# HU-101-048 — Importación maestros clientes y parámetros ERP

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-048-integracion-maestros-clientes-parametros |
| **SPEC origen** | [SPEC-101-22](../../05-open-spec/101-PedidosWeb/SPEC-101-22-importacion-maestros-erp.md) |
| **Épica** | APIs importación maestros ERP → PedidosWeb |
| **Prioridad** | **Must** |
| **Estado** | **Finalizado** (Parte I SPEC-101-22, 2026-10-29) |
| **B1** | Cerrado (2026-10-21) |
| **TR** | [TR-SPEC-101-22-integracion-maestros-clientes-parametros](../../04-tareas/101-PedidosWeb/TR-SPEC-101-22-integracion-maestros-clientes-parametros.md) |
| **Dependencias** | [HU-101-046](HU-101-046-integracion-maestros-plataforma.md) · maestros de catálogo cargados (HU-047) para FKs |

## Trazabilidad SPEC

| Recursos SPEC §1 | Esta HU |
|------------------|---------|
| `clientes` | CA-01…CA-04 |
| `clientes-direcciones` | CA-05, CA-06 |
| `clientes-contactos` | CA-07, CA-08 |
| `parametros` | CA-09, CA-10 |
| Leyendas 1–5 recorte 60 | RN-02, CA-02 |
| Ignorar `cod_login` clientes | RN-03, CA-01 |
| Delta contactos no borra omitidos | RN-04, CA-08 |
| Whitelist seed PedidosWeb | RN-05, CA-09 |
| Solo delta direcciones/contactos (A1-C04) | RN-06, CA-06 |
| CA producto §7 (1)(5) | CA-01, CA-03 |

## Narrativa

Como **job ERP**,  
quiero **sincronizar clientes, direcciones, contactos y parámetros de negocio permitidos**,  
para **que el portal opere con cartera y reglas ERP sin pantallas de ABM**.

## Contexto funcional

Cuatro recursos POST bajo el mismo contrato de lote (HU-046). Clientes dependen de vendedores, condiciones, transportes, listas ya cargados. Parámetros actualizan solo claves whitelist en `PQ_parametros_gral` sin vaciar la tabla.

## Alcance incluido

| Recurso | Clave natural | Notas |
|---------|---------------|--------|
| `clientes` | `cod_client` | Snapshot replace por ítem; `eliminarSiAusente`; leyendas recorte 60 |
| `clientes-direcciones` | `(cod_client, id_de)` | `habitual` S/N o boolean; **solo delta** |
| `clientes-contactos` | `(cod_client, cod_contacto)` | **solo delta**; omitidos no se borran |
| `parametros` | clave | Whitelist = seed `PQ_PARAMETROS_GRAL.PedidosWeb.seed.json`; validar `tipoValor` |

- SP por recurso `pq_sp_integracion_maestros_*`.
- No tocar `cod_login` en clientes.
- No crear usuarios web.
- OpenAPI paths para los cuatro recursos.
- Feature tests: cliente delta parcial, leyenda truncada, parámetro no whitelist rechazado, snapshot en contactos → 422.

## Fuera de alcance

- Catálogo/artículos → HU-101-047.
- Consulta parámetros UI (solo lectura existente).
- Transacciones SPEC-101-23.

## Reglas de negocio

1. **RN-01:** Cliente sin `cod_vended` / condición / lista válida en BD → ítem `rechazado` (FK).
2. **RN-02:** Leyendas cliente > 60 caracteres Unicode → persistir truncadas sin rechazar ítem.
3. **RN-03:** Campo `cod_login` en payload ignorado.
4. **RN-04:** Delta contactos: contactos existentes no mencionados en lote permanecen.
5. **RN-05:** Parámetro con clave fuera de whitelist → ítem `rechazado`.
6. **RN-06:** `modo=snapshot` en `clientes-direcciones` o `clientes-contactos` → 422 lote.
7. **RN-07:** Snapshot `clientes`: no borrar clientes ausentes del lote salvo `eliminarSiAusente` en ítem.
8. **RN-08:** La baja física de un cliente solo procede si no tiene referencias en pedidos, deuda, cheques ni ventas detalladas. La operación debe eliminar primero contactos, luego direcciones de entrega y finalmente el cliente, dentro de una transacción.

## Criterios de aceptación

- [ ] **CA-01:** POST `clientes` delta upsert; `cod_login` en JSON no modifica columna.
- [ ] **CA-02:** Leyenda de 80 caracteres se guarda con 60.
- [ ] **CA-03:** Reenvío mismo lote clientes no duplica `cod_client`.
- [ ] **CA-04:** `eliminarSiAusente` en cliente sin pedidos ni transacciones referenciadas elimina contactos/direcciones y luego la fila de cliente.
- [ ] **CA-05:** POST `clientes-direcciones` delta con `habitual` true/false normalizado a S/N.
- [ ] **CA-06:** POST `clientes-direcciones` con `modo=snapshot` → 422.
- [ ] **CA-07:** POST `clientes-contactos` delta upsert contacto nuevo.
- [ ] **CA-08:** Delta contactos sin incluir contacto existente → contacto sigue en BD.
- [ ] **CA-09:** POST `parametros` con clave del seed actualiza valor según tipo.
- [ ] **CA-10:** Clave parámetro inventada → `rechazados` sin abortar lote (delta).
- [ ] **CA-11:** OpenAPI documenta los cuatro paths.
- [ ] **CA-12:** Pedidos/presupuestos en BD sin cambios tras import clientes.
- [ ] **CA-13:** Cliente referenciado por deuda, cheque o venta detallada es rechazado y permanece sin cambios.

## Escenarios Gherkin

```gherkin
Feature: Importación clientes y parámetros

  Scenario: Parámetro fuera de whitelist
    Given lote delta parametros con clave "ClaveInventada"
    When POST "/api/v1/integracion/maestros/parametros"
    Then HTTP 200 y error 0
    And resultado.rechazados es 1

  Scenario: Contactos delta no borra omitidos
    Given cliente con contacto "C1" en BD
    And lote delta sin contacto "C1"
    When POST "/api/v1/integracion/maestros/clientes-contactos"
    Then contacto "C1" sigue existiendo
```

## Supuestos explícitos

- Whitelist parámetros coincide con el JSON de seed versionado en repo hasta que producto publique lista distinta.

## Preguntas abiertas (TR)

- Formato exacto del ítem parámetro (`clave` + objeto valor por `tipoValor`).

## Veredicto B1

**Lista para TR**.
