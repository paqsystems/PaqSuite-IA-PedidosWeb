# APIs de importación de transacciones (ERP → PedidosWeb)

Jira: PIAPEDWEB-35

| Campo | Valor |
|-------|--------|
| **Estado** | OpenSpec — **A1 cerrado** ([acta](../../04-tareas/101-PedidosWeb/F-101-23-cierre-a1-importacion-transacciones-erp.md)); autoriza Parte B |
| **Ámbito** | PedidosWeb — **backend de integración** (sin pantalla de operador en el portal) |
| **Origen de producto** | [Definición conceptual](./PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md) §6.1 · [Modelo de datos](./PedidosWeb_Modelo_Datos_Final.md) §4 |
| **Contrato común** | [APIs-Importacion-datos-maestros.md](./APIs-Importacion-datos-maestros.md) **§4** (auth, envelope, lote, tenant). No redefinir. |
| **Dependencia** | Maestros cargados: artículos antes de stock; clientes antes de deuda/cheques/historial |
| **No confundir con** | GET de consulta del portal (`/api/v1/consultas/…`) · Excel de pedidos (GEN-14) |
| **Observabilidad** | [Logs de integración](../../05-open-spec/101-PedidosWeb/SPEC-101-08-logs-integracion.md) |
| **Framework** | GEN-20 · GEN-19 · SP `pq_sp_integracion_transacciones_*` (regla 74) |
| **Jira** | [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35) |

---

## 1. Objetivo

Permitir que el ERP **empuje** las **cuatro tablas de transacciones / saldos** que alimentan las consultas del portal:

1. **Stock** — `pq_pedidosweb_stock`
2. **Deuda** — `pq_pedidosweb_deuda`
3. **Cheques** — `pq_pedidosweb_cheques`
4. **Historial de ventas** — `pq_pedidosweb_ventadetallada`

El portal las **lee** en UI; el ERP es dueño del contenido. Este SPEC **no** cubre maestros ni parámetros.

---

## 2. Modelo mental

```text
ERP (después del ciclo de maestros)
        │  POST lote
        ▼
/api/v1/integracion/transacciones/{stock|deuda|cheques|historial-ventas}
        │  mismo auth/envelope que maestros §4
        │  SP replace-lote o upsert
        ▼
Tablas §4 modelo  →  GET /api/v1/consultas/… (sin cambio de contrato al usuario)
```

- Un request = un recurso. No mezclar las cuatro en un body.
- **Stock, deuda, cheques:** modo habitual **snapshot** (el lote es el universo vigente).
- **Historial:** **delta / ventana**; prohibido snapshot que borre el histórico completo.
- Visibilidad de vendedor/cliente se aplica en el **GET de consulta**, no en el import.

---

## 3. Alcance

### 3.1 Incluye

- `POST /api/v1/integracion/transacciones/stock`
- `POST /api/v1/integracion/transacciones/deuda`
- `POST /api/v1/integracion/transacciones/cheques`
- `POST /api/v1/integracion/transacciones/historial-ventas`
- Reuso del contrato [maestros §4](./APIs-Importacion-datos-maestros.md#4-contrato-transversal-must--sot-para-maestros-y-transacciones)
- Logs tipo `transaccion_import`
- OpenAPI tag **Integración transacciones**
- Tests de contrato y de políticas §5

### 3.2 Fuera de alcance

| Fuera | Motivo |
|-------|--------|
| Maestros y parámetros | [APIs-Importacion-datos-maestros.md](./APIs-Importacion-datos-maestros.md) |
| `resumen-cuenta` | Opcional futuro; no MUST |
| GET `/consultas/stock|deuda|cheques|historial-ventas` | Ya implementados |
| Comprometido web / métricas `*Base` | Calculados en consulta stock, no se importan |
| Pedidos/presupuestos del portal | No se importan ni se pisan |
| UI nueva | Consultas y logs existentes |

---

## 4. Catálogo (MUST)

Contrato de columnas = documentos de consulta + modelo §4. JSON camelCase; el SPEC mapea 1:1 (incl. legacy deuda/cheques en **escritura canónica**).

| Recurso | Tabla | Clave | Modo habitual | Consulta portal |
|---------|-------|-------|---------------|-----------------|
| `stock` | `pq_pedidosweb_stock` | `cod_articulo` | Snapshot | [consulta-stock](./consulta-stock.md) |
| `deuda` | `pq_pedidosweb_deuda` | `(cod_cliente, t_comp, n_comp, fecha_vto)` | Snapshot | [consulta-deuda](./consulta-deuda.md) |
| `cheques` | `pq_pedidosweb_cheques` | `(interno, numero)` | Snapshot | [consulta-cheques](./consulta-cheques.md) |
| `historial-ventas` | `pq_pedidosweb_ventadetallada` | `id_gva53` | Delta / ventana | [consulta-historial-ventas](./consulta-historial-ventas.md) |

### 4.1 Stock

Ítem mínimo: `codArticulo`, `stock`, `comprometido`. `uma_fecha` = carátula `fechaProceso`.

- Artículo **debe existir** en `pq_pedidosweb_articulos`; si no, fila rechazada.
- **No** enviar `comprometidoWeb` ni métricas `*Base`.
- Snapshot: el SP reemplaza el conjunto de stock del tenant (sin `TRUNCATE` desde PHP). `items: []` **rechazado**.
- `stockeable = 0`: omitir o aceptar; la consulta los excluye.

### 4.2 Deuda

Ítem mínimo: `codCliente`, `tComp`, `nComp`, `fechaEmis`, `fechaVto`, `saldo`.

- No denormalizar razón social (join a `clientes.razon_soci` en consulta).
- Cliente inexistente → rechazo de fila.
- Snapshot = universo vigente de pendientes. `fecha_proceso` de carátula → `metadata.fecha_proceso`.
- Escritura al **esquema canónico**, no al shape legacy.

### 4.3 Cheques

Ítem mínimo: `interno`, `numero`, `codCliente`, `banco`, `fecha`, `importe`, `origen`, `estado`.

- Nombre de cliente en consulta = `clientes.nombre` (no viaja en el lote).
- Cliente inexistente → rechazo.
- El import persiste lo que manda el ERP. El filtro `fecha >= hoy` es de **consulta** (§17.5), no de importación.
- Snapshot recomendado.

### 4.4 Historial de ventas

Ítem: campos exportados de `pq_pedidosweb_ventadetallada` **incluyendo** `idGva53` (PK). La consulta del portal **no** expone `id_gva53` ni `fecha_proceso` en ítems.

- Upsert por `id_gva53`; reenvío no duplica.
- **Prohibido** snapshot que borre el histórico completo. Ventana del origen: solo upsert; no borrar fuera de ella.
- `cod_cli` debe existir en clientes. `cod_articu` se acepta aunque el artículo ya no esté en maestro (fila denormalizada).
- `DiasVentasDetalladas` lo aplica la **consulta**, no el import.

---

## 5. Políticas de snapshot

| Recurso | `snapshot` | `delta` |
|---------|------------|---------|
| `stock`, `deuda`, `cheques` | Reemplazo del conjunto vigente vía SP. `items: []` rechazado. | Upsert (puede dejar saldos viejos; **no** recomendado en prod). |
| `historial-ventas` | **Prohibido** como borrado total. | Upsert por `id_gva53`. |

---

## 6. Orden respecto de maestros

El origen llama **primero** el ciclo de maestros, **después** este:

1. `stock` (exige artículos)
2. `deuda`, `cheques`, `historial-ventas` (exigen clientes)

Este API no reordena ni carga maestros faltantes: rechaza la fila.

---

## 7. Criterios de aceptación de producto (futuro SPEC transacciones)

1. Las cuatro rutas existen, autenticadas con el mismo token de integración que maestros; un vendedor **no** puede llamarlas.
2. Snapshot de deuda/cheques/stock no usa `TRUNCATE` ad-hoc; queda en logs.
3. Reenviar historial con el mismo `id_gva53` no duplica.
4. Stock no pisa ni persiste `comprometidoWeb`.
5. Tras snapshot, las pantallas de consulta reflejan el lote (el GET sigue filtrando visibilidad).
6. OpenAPI cubre las cuatro rutas.
7. Este SPEC **no** expone `/integracion/maestros/*`.

---

## 8. Preguntas abiertas (A1 / SPEC)

**Cerradas en A1** (2026-10-21) — [F-101-23-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-23-cierre-a1-importacion-transacciones-erp.md).

1. Snapshot deuda/cheques/stock: transacción SQL por request; lote **atómico** (422 si hay ítem inválido).
2. `resumen-cuenta`: **fuera de MVP**.
3. Tope de ítems: **1500**.

---

## 9. Próximo paso SDD

1. **A0** — hecho: [PIAPEDWEB-35](https://paqsystems.atlassian.net/browse/PIAPEDWEB-35).
2. **A** — hecho: [SPEC-101-23](../../05-open-spec/101-PedidosWeb/SPEC-101-23-importacion-transacciones-erp.md) (2026-10-21).
3. **A1** — hecho: [F-101-23-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-23-cierre-a1-importacion-transacciones-erp.md).
4. **B** — hecho: [HU-101-049](../../03-historias-usuario/101-PedidosWeb/HU-101-049-integracion-transacciones-plataforma.md), [HU-101-050](../../03-historias-usuario/101-PedidosWeb/HU-101-050-integracion-transacciones-saldos.md), [HU-101-051](../../03-historias-usuario/101-PedidosWeb/HU-101-051-integracion-transacciones-historial-ventas.md).
5. **B1** — hecho: [F-101-23-cierre-b1](../../04-tareas/101-PedidosWeb/F-101-23-cierre-b1-importacion-transacciones-erp.md); HU-101-049…051 listas para TR.
6. **C** — hecho: [F-101-23-cierre-c](../../04-tareas/101-PedidosWeb/F-101-23-cierre-c-importacion-transacciones-erp.md); TR-23a…23c generadas.
7. **C1** — hecho: [F-101-23-cierre-c1](../../04-tareas/101-PedidosWeb/F-101-23-cierre-c1-importacion-transacciones-erp.md); apto con observaciones, autoriza D1.
8. **D1** — hecho: [D1-PLAN-101-23](../../04-tareas/101-PedidosWeb/D1-PLAN-101-23-integracion-transacciones-erp.md); precedencia cerrada: carátula → `fecha_proceso`, comprobante → `fechaEmision`.
9. **D** — implementar en orden 23a → 23b → 23c.

No implementar hasta SPEC + HU + TR. El SPEC de maestros puede cerrarse **sin** estas rutas.
