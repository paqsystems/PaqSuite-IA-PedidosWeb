# Prompt — API integración: actualización masiva acotada de cabecera (ERP → PedidosWeb)

Jira: PIAPEDWEB-55

| Campo | Valor |
|-------|--------|
| **Para** | Agente / equipo **PaqSuite-IA-PedidosWeb** |
| **Solicitante** | Integración **PaqSuite-IA-TANGO** ([PIATANGO-23](https://paqsystems.atlassian.net/browse/PIATANGO-23)) |
| **Metodología** | Open-Spec / dispatcher del repo (`docs/05-open-spec/101-PedidosWeb/`) |
| **Implementación** | **No** codificar en esta pasada salvo pedido explícito (Parte D); primero inventario + contrato |

---

## Objetivo

Definir (y solo si no existe, proponer) **una sola** API **servidor-a-servidor**, **POST masivo**, para **parches acotados de cabecera** (`pq_pedidosweb_pedidoscabecera` y campos mapeados en camelCase). El ERP envía solo los campos que quiere cambiar por pedido; el portal aplica **whitelist**, reglas de transición de `estado` y validación de negocio.

**Unificar** en este contrato (no dos APIs):

| Proceso ERP | Qué suele mandar en `cabecera` |
|-------------|--------------------------------|
| **Ack importación** (fin de grabación lote 0) | `estado` (1 o 2), `talPedidoTango`, `nroPedidoTango` (u obligatorios según transición en TR) |
| **Reconciliación** (1/2 → 3 u otros) | `estado` (y otros solo si el producto lo permite) |
| Futuros ajustes ERP→portal | Otros campos de cabecera **explícitamente** habilitados en whitelist |

No hardcodear “1/2 → 3” en la ruta. No abrir renglones, totales recalculados ni edición de carga portal — eso sigue en `comprobantes/grabar`.

TANGO: `PaqSuite-IA-TANGO` → `docs/02-producto/PedidosWeb/Integracion-con-Pedidos-Web.md` (P8, P13). Concurrencia proceso posterior: BASE `coordinacion-concurrencia-integraciones.md` §6 / S13.

---

## Fase 0 — Inventario obligatorio

Antes de SPEC nuevo, documentar con evidencia (ruta, método, middleware, archivo):

| Área | Dónde |
|------|--------|
| Integración | `backend/routes/api.php` (`/integracion/...`), OpenAPI |
| Exportación | `APIs-Exportacion-comprobantes-erp.md`, SPEC-101-25 (solo **GET**; escritura fuera de alcance) |
| Carga portal | `POST /api/v1/comprobantes/grabar` → `ComprobanteController` / `PedidoService::grabarComprobante` |
| Modelo | `PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md` §9 |

**Salida:** tabla **Candidato → ¿Actualiza cabecera vía integración? → Veredicto** (`Reutilizar` / `Extender` / `Nuevo` / `No aplica`).

`GET /api/v1/integracion/comprobantes` **no** sustituye esta necesidad (solo lectura).

---

## ¿Reutilizar `POST /api/v1/comprobantes/grabar` con una acción “solo estado”?

**Es lógico a nivel de dominio, no recomendable como único contrato HTTP para el ERP.**

| Criterio | `comprobantes/grabar` (hoy) | Integración ERP (objetivo) |
|----------|-----------------------------|----------------------------|
| Autenticación | Usuario portal + permisos visibilidad (`VisibilityPermissionGuard`, carga alta/modi) | Token integración `pw_integracion_importacion` + `X-Paq-Cliente` |
| Payload | Cabecera + renglones, totales, `accionGrabacion` ∈ {pedido, presupuesto} | Mínimo: `codPedido`, `estadoDestino`, opcional concurrencia / trazas Tango |
| Efectos colaterales | Validación carga, mail, bonificaciones, visibilidad comercial | Solo transición de estado (+ campos acordados, ej. talón/número) |
| Lotes / jobs | Pensado para UI | Miles de ítems, idempotencia, resultado por fila |
| §21 descarga | Reglas de edición portal | Procesos ERP **aparte** de la importación estado **0** |

**Recomendación para el prompt SDD:**

1. **Superficie HTTP:** **un solo** `POST` en lote bajo `/api/v1/integracion/...` (sin ruta unitaria obligatoria), auth de integración, envelope GEN-20.
2. **Dominio compartido:** extraer lógica a un servicio único (ej. `ComprobanteEstadoIntegracionService`) que centralice matriz de transiciones y persistencia (SP regla 74).
3. **Opcional:** si el producto quiere una sola familia de “acciones”, extender `grabar` con `accionGrabacion = actualizarEstado` **solo para usuarios portal**, reutilizando el **mismo servicio de dominio** — **sin** exigir al ERP que llame `comprobantes/grabar`.

La Fase 0 debe cerrar si conviene **solo integración**, **servicio compartido + dos rutas**, o extensión de `grabar` además de integración.

---

## Contrato propuesto (genérico)

**Intención:** establecer `estado = estadoDestino` con validación en servidor.

**Operación (MUST — decisión ERP):** **solo JSON masivo.** Un pedido es el caso particular `items.length === 1`. **No** diseñar PATCH/PUT unitario como contrato principal (evita N round-trips en importación y jobs GEN-13).

| Aspecto | Regla |
|---------|--------|
| Método / ruta (orientativo) | `POST /api/v1/integracion/comprobantes/cabecera` |
| Cuerpo | `items[]` obligatorio, **mín. 1** ítem; máximo `maxBatchSize` en TR |
| Procesamiento | **Por ítem** dentro del lote (éxito parcial permitido salvo que TR exija todo-o-nada) |
| `operacion` (cabecera lote) | Opcional en raíz: `importacionAck`, `reconciliacionGva21`, … (auditoría; puede reforzar reglas) |
| `idempotencyKey` (cabecera lote) | Opcional para replay seguro del **mismo** payload |

**Forma por ítem (MUST):** `codPedido` + **`cabecera`** objeto parcial (solo claves a mutar). **Whitelist** en SPEC (mínimo MVP: `estado`, `talPedidoTango`, `nroPedidoTango`; ampliar solo con HU explícita).

**Ejemplo ack importación (1 ítem):**

```json
{
  "operacion": "importacionAck",
  "items": [
    {
      "codPedido": "PW-2026-001234",
      "estadoOrigenEsperado": 0,
      "cabecera": {
        "estado": 1,
        "talPedidoTango": 1,
        "nroPedidoTango": "00012345"
      }
    }
  ]
}
```

**Ejemplo reconciliación (solo estado):**

```json
{
  "operacion": "reconciliacionGva21",
  "items": [
    {
      "codPedido": "PW-2025-009999",
      "estadoOrigenEsperado": 2,
      "cabecera": { "estado": 3 }
    }
  ]
}
```

**Campos por ítem (`items[]`):**

| Campo | Rol |
|--------|-----|
| `codPedido` | Obligatorio |
| `cabecera` | Obligatorio; ≥1 clave de whitelist; **prohibido** vacío |
| `estadoOrigenEsperado` | Recomendado si se cambia `estado` (concurrencia optimista) |
| `idempotencyKey` | Opcional por ítem |

**Respuesta (envelope GEN-20):** `resultado.items[]` con el **mismo orden** que el request (o `codPedido` en cada fila); códigos por ítem alineados TANGO:  
`procesadoOk` | `omitidoSinCambio` | `rechazadoTransicion` | `rechazadoConcurrencia` | `rechazadoNoElegible` | `error` + resumen de cabecera (`totalOk`, `totalError`, …).

**MUST en PedidosWeb:** matriz de transiciones; idempotencia; `maxBatchSize` documentado; throttle `integracion-*`; sin stack en respuesta; **no** acoplado al flag §21 de descarga de ingresados **0** sin diseño explícito.

---

## Entregables SDD

1. Doc producto: `APIs-Integracion-actualizacion-cabecera-comprobantes.md` (nombre sugerido; sustituye doc “solo estado”).
2. SPEC (ej. SPEC-101-26 o update acotado) + HU/TR + OpenAPI + CA.
3. **Un** endpoint para ack y reconciliación; distinguir con `operacion` y reglas de whitelist por campo.

---

## Referencias

| Repo | Ruta |
|------|------|
| TANGO | `docs/02-producto/PedidosWeb/Integracion-con-Pedidos-Web.md` |
| TANGO | `docs/02-producto/PedidosWeb/Prompt-Definicion-Basica.md` |
| PedidosWeb | `SPEC-101-25-exportacion-comprobantes-erp.md` |
| BASE | `coordinacion-concurrencia-integraciones.md` |