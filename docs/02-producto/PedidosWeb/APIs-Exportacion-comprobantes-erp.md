# APIs de exportación de comprobantes (PedidosWeb → ERP)

Jira: PIAPEDWEB-54

| Campo | Valor |
|-------|--------|
| **Estado** | **SPEC-101-25** — **D1 listo**; Parte **D** (25a → 25b) |
| **Ámbito** | Backend HTTP servidor-a-servidor; **sin** pantalla de operador |
| **OpenSpec** | [SPEC-101-25-exportacion-comprobantes-erp.md](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md) |
| **Contrato común integración** | Auth, tenant y envelope como [APIs-Importacion-datos-maestros.md](./APIs-Importacion-datos-maestros.md) **§4.1** (mismo token `pw_integracion_importacion`; **no** visibilidad de vendedor) |
| **Hermano (dirección opuesta)** | [APIs-Importacion-datos-maestros.md](./APIs-Importacion-datos-maestros.md) · [APIs-Importacion-transacciones.md](./APIs-Importacion-transacciones.md) |
| **No confundir con** | `GET /api/v1/consultas/pedidos-*` y `.../presupuestos` (solo cabecera, visibilidad usuario) · `GET /api/v1/consultas/detalle-pedidos` (grilla plana por renglón) · `GET /api/v1/pedidos/{cod}` (un comprobante, permiso carga) |
| **Definición conceptual** | [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](./PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md) §6.2 (hoy: descarga vía proceso ERP; esta API formaliza el pull HTTP) |
| **Framework** | GEN-19 · GEN-20 |
| **Jira** | [PIAPEDWEB-54](https://paqsystems.atlassian.net/browse/PIAPEDWEB-54) |

---

## 1. Objetivo

Permitir que el **ERP** (Tango Gestión, job PaqSuite ERP o agente autorizado) **consulte en lotes paginados** los comprobantes web (pedidos y presupuestos) en formato **cabecera + renglones anidados**, filtrando por **uno o más rangos inclusivos de `estado`**, sin depender de SQL directo al host PedidosWeb ni de las APIs de informe del portal.

---

## 2. Modelo mental

```text
ERP (descarga / sincronización / facturación)
        │  GET paginado + estadoRangos
        ▼
GET /api/v1/integracion/comprobantes
        │  tenancy + pw_integracion_importacion
        │  SP lectura (regla 74)
        ▼
pq_pedidosweb_pedidoscabecera + pq_pedidosweb_pedidosdetalle
```

- Una página cuenta **cabeceras** (`total` = cantidad de comprobantes que cumplen el filtro).
- Cada ítem de `resultado.items[]` incluye `cabecera` y `renglones[]` (orden `renglon` ascendente).
- JSON en **camelCase** (alineado a consultas de producto).

---

## 3. Alcance

### 3.1 Incluye

- `GET /api/v1/integracion/comprobantes`
- Query `estadoRangos` (múltiples pares inclusive `desde:hasta`, `desde` y `hasta` pueden ser iguales, p. ej. `0:0`)
- Valores de `desde` / `hasta` en **[-1, 99]** (cualquier entero del dominio operativo actual y futuro dentro de ese intervalo)
- Unión **OR** entre rangos (un comprobante aparece una sola vez aunque caiga en varios rangos)
- Paginación `page`, `page_size` (contador sobre **cabecera**; default y máximo como consultas: 20 / 1000)
- Filtros opcionales: `codCliente`, `codPedido`, `fechaDesde`, `fechaHasta` (sobre `cabecera.fecha`, inclusivos, ISO 8601 o `YYYY-MM-DD` según TR)
- Campos de cabecera: superset de [consulta-comprobantes-cabecera.md](./consulta-comprobantes-cabecera.md) más `talPedidoTango`, `nroPedidoTango`, `codUsuarioWeb`, trazas de conversión si existen en BD
- Campos de renglón: alineados a [consulta-detalle-pedidos.md](./consulta-detalle-pedidos.md) §4 (sin duplicar cabecera en cada renglón)
- Presupuesto cerrado (`estado` 98): objeto opcional `cierre` (mismo contrato que consulta presupuestos cerrados)
- **Sin** flags UI (`puedeEditar`, `puedeCopiar`, …)
- OpenAPI tag **Integración exportación** · tests de contrato

### 3.2 Fuera de alcance

| Fuera | Motivo |
|-------|--------|
| Mutaciones (POST/PUT descarga, marcar estado 1, etc.) | Proceso ERP posterior; otro SPEC si hace falta |
| Visibilidad vendedor/cliente del portal | Token integración ve **todo el tenant** |
| Formato grilla plana (una fila por renglón) | Sigue en `GET /consultas/detalle-pedidos` |
| GEN-36 async | Lectura síncrona paginada |
| Mobile | Solo servidor-a-servidor |

---

## 4. Contrato — query

| Parámetro | Obligatorio | Regla |
|-----------|-------------|--------|
| `estadoRangos` | **Sí** | Repetible o lista CSV. Cada valor `desde:hasta` (enteros). **Inclusivo** en ambos extremos. Ej.: `estadoRangos=0:0&estadoRangos=1:2&estadoRangos=98:99` o `estadoRangos=0:0,1:2,98:99` |
| `page` | No | Entero ≥ 1, default `1` |
| `pageSize` | No | Entero 1…1000, default `20` (nombre query **camelCase** `pageSize`; **MUST** aceptar alias `page_size` por paridad consultas — SPEC-101-25) |
| `codCliente` | No | Filtro exacto |
| `codPedido` | No | Filtro exacto (una cabecera) |
| `fechaDesde` / `fechaHasta` | No | Sobre fecha de cabecera; rango inclusivo |

Validación:

- Si `desde > hasta` → **422** con clave envelope.
- Si algún extremo ∉ **[-1, 99]** → **422**.
- Si falta `estadoRangos` → **422** (no exportar “todo el universo” sin intención explícita).

Predicado SQL lógico:

```text
estado IN ( ⋃ intervalos cerrados [desde, hasta] )
```

---

## 5. Contrato — respuesta (éxito)

Envelope GEN-20. `resultado`:

```json
{
  "items": [
    {
      "cabecera": {
        "codPedido": "…",
        "codCliente": "…",
        "estado": 0,
        "fecha": "2026-11-18T12:00:00-03:00",
        "total": 1500.5,
        "talPedidoTango": 1,
        "nroPedidoTango": ""
      },
      "renglones": [
        {
          "renglon": 1,
          "codArticulo": "A001",
          "cantidad": 2,
          "cantidadVenta": 2,
          "precioNeto": 100.5,
          "importeNetoConIva": 121.61,
          "especial": ""
        }
      ],
      "cierre": null
    }
  ],
  "page": 1,
  "pageSize": 20,
  "total": 1250,
  "totalPages": 63,
  "metadata": {
    "fechaProceso": "2026-11-18T18:00:00-03:00"
  }
}
```

- `metadata.fechaProceso`: marca de la lectura (no `fecha_proceso` de importación ERP).
- Cabecera sin renglones: `renglones: []` (el comprobante cuenta en `total`).

---

## 6. Seguridad y transporte

Reutilizar §4.1 de [APIs-Importacion-datos-maestros.md](./APIs-Importacion-datos-maestros.md):

- `X-Paq-Cliente`
- Bearer Sanctum + `pw_integracion_importacion`
- Middleware familia `integracion.importacion` (o extensión documentada en TR)
- **401** / **403** según envelope vigente integración

---

## 7. Persistencia

Lectura por **stored procedure** (regla 74), p. ej. familia `pq_sp_integracion_export_comprobantes_*`:

- Filtro por rangos de estado y query opcionales
- Paginación por cabecera (OFFSET/FETCH o equivalente)
- Segundo resultado o join controlado para renglones del page (evitar N+1 desde PHP)

---

## 8. Orden sugerido en SDD

1. **A / A1** — [SPEC-101-25](../../05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md)
2. **B / B1** — [HU-101-057](../../03-historias-usuario/101-PedidosWeb/HU-101-057-integracion-export-comprobantes-plataforma.md) plataforma · [HU-101-058](../../03-historias-usuario/101-PedidosWeb/HU-101-058-integracion-export-comprobantes-datos.md) contrato datos
3. **C / C1** — TR [25a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-25-integracion-export-plataforma.md) / [25b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-25-integracion-export-datos.md) · [C1](../../04-tareas/101-PedidosWeb/F-101-25-cierre-c1-exportacion-comprobantes-erp.md)
4. **D1 / D** — [D1-PLAN-101-25](../../04-tareas/101-PedidosWeb/D1-PLAN-101-25-exportacion-comprobantes-erp.md) → implementación
5. **E / F1** — pruebas y cierre

---

## 9. Postman (aprobación manual)

Collection y environment versionados:

- [docs/06-operacion/postman/PedidosWeb-Integracion-Export-Comprobantes.postman_collection.json](../../06-operacion/postman/PedidosWeb-Integracion-Export-Comprobantes.postman_collection.json)
- [docs/06-operacion/postman/PedidosWeb-Integracion-Export-Comprobantes.postman_environment.json](../../06-operacion/postman/PedidosWeb-Integracion-Export-Comprobantes.postman_environment.json)
- Runbook: [README-export-comprobantes.md](../../06-operacion/postman/README-export-comprobantes.md)

Regenerar: `php backend/scripts/generar-postman-integracion-export.php`.

## 10. Trazabilidad

| Artefacto | Ruta prevista |
|-----------|----------------|
| SPEC | `docs/05-open-spec/101-PedidosWeb/SPEC-101-25-exportacion-comprobantes-erp.md` |
| HU | [HU-101-057](../../03-historias-usuario/101-PedidosWeb/HU-101-057-integracion-export-comprobantes-plataforma.md) (plataforma), [HU-101-058](../../03-historias-usuario/101-PedidosWeb/HU-101-058-integracion-export-comprobantes-datos.md) (datos) |
| TR | [25a](../../04-tareas/101-PedidosWeb/TR-SPEC-101-25-integracion-export-plataforma.md), [25b](../../04-tareas/101-PedidosWeb/TR-SPEC-101-25-integracion-export-datos.md) |