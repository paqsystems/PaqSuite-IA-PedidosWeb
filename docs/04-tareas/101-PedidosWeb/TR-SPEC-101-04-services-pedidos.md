# TR-SPEC-101-04 — Services de pedidos y presupuestos (reglas de negocio)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-005](../../03-historias-usuario/101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) … [HU-101-013](../../03-historias-usuario/101-PedidosWeb/HU-101-013-conversion-presupuesto-pedido.md), [HU-101-024](../../03-historias-usuario/101-PedidosWeb/HU-101-024-conversion-pedido-presupuesto.md), [HU-101-026](../../03-historias-usuario/101-PedidosWeb/HU-101-026-copiar-comprobante.md), [HU-101-027](../../03-historias-usuario/101-PedidosWeb/HU-101-027-cierre-rechazo-presupuesto.md), [HU-101-007](../../03-historias-usuario/101-PedidosWeb/HU-101-007-bonificacion-neta.md), [HU-101-008](../../03-historias-usuario/101-PedidosWeb/HU-101-008-precio-importes.md), [HU-101-009](../../03-historias-usuario/101-PedidosWeb/HU-101-009-grabar-pedido.md), [HU-101-010](../../03-historias-usuario/101-PedidosWeb/HU-101-010-grabar-presupuesto.md), [HU-101-011](../../03-historias-usuario/101-PedidosWeb/HU-101-011-editar-pedido.md), [HU-101-012](../../03-historias-usuario/101-PedidosWeb/HU-101-012-eliminar-pedido.md) |
| **SPEC relacionada** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | [TR-SPEC-101-02-modelos](TR-SPEC-101-02-modelos.md), [TR-SPEC-101-03-repositories](TR-SPEC-101-03-repositories.md); lectura parámetros [SPEC-001-04](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) (defaults temporales documentados); visibilidad [SPEC-101-06](../../05-open-spec/101-PedidosWeb/SPEC-101-06-seguridad-visibilidad.md) / TR-GEN-02-visibilidad |
| **Estado** | Finalizado (Parte I — CC PQ #9) |
| **Última actualización** | 2026-07-02 (Parte I — CC PQ #9) |

**Origen:** [SPEC-101-04](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [PedidosWeb_SPEC_MVP.md](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md) §5, §5.1, §5.3, §12  
**Referencia SPEC:** [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) — **sin endpoints HTTP en este slice** (controllers en TR-SPEC-101-05)

---

## 1) HU Refinada (resumen)

### Título
Services de dominio para pedidos y presupuestos: grabación unificada, edición con estado **-1**, conversiones, copia, cierre 98, totales/IVA y auditoría liviana.

### Narrativa
Como **usuario comercial autorizado**,  
quiero **que el backend aplique todas las reglas de negocio al grabar, editar, convertir, copiar o cerrar comprobantes**,  
para **garantizar coherencia con el ERP, trazabilidad y ausencia de DELETE en presupuestos**, independientemente de la UI o del API REST.

### In scope / Out of scope

**In scope:**

- `PedidoService` (o facades acotados: `GrabacionService`, `ConversionService`, `EdicionPedidoService`, `ComprobanteCopiaService`, `PresupuestoCierreService`) — **una** entrada pública coherente para TR-101-05.
- Matriz unificada **Grabar pedido** / **Grabar presupuesto** (producto §10.1, SPEC-101-10).
- CRUD lógico: crear/editar pedido **0**; crear/editar presupuesto **99**; **eliminar físico solo pedido estado 0**; **prohibido DELETE presupuesto**.
- Edición pedido: transición **0 → -1**; vigencia **`fechahora_ultima_actividad` + `MinutosWeb`**; retorno **-1 → 0** al grabar/cancelar según reglas HU-101-011.
- Conversión presupuesto → pedido (HU-101-013): origen **98**, cierre en `presupuestos_cierres`, pedido **0** nuevo, **`CodMotivoCierreExitoso`**, **`cod_presupuesto_origen`** + **`cod_pedido_generado`**.
- Conversión pedido → presupuesto (HU-101-024, SPEC §5.1): solo desde pedido **0**; presupuesto **99** nuevo; trazabilidad **`cod_pedido_origen`**; pedido origen deja de ser operable (eliminación física del **0** o anulación acordada en implementación — **preferencia TR:** delete físico del pedido origen tras conversión exitosa, alineado a “deja de ser ingresado”).
- Cierre/rechazo presupuesto **99 → 98** (HU-101-027): motivo **negativo** elegido por usuario; sin cierre parcial/positivo.
- Copiar comprobante (HU-101-026): nuevo GUID + `nro_visible`; `origen_comprobante` / referencia al origen.
- Cálculo totales, bonificación neta, IVA cabecera y renglón (HU-101-007, HU-101-008).
- Validación parámetros ERP en grabación (`ModificaPrecio*`, `NOeliminaPedido`, `NOmodificaPedido`, etc.).
- Auditoría liviana: `usuario_creacion`, `fecha_creacion`, `usuario_modificacion`, `fecha_modif`.
- Disparo de evento/domain hook para mail (HU-101-019) — **sin** implementar envío SMTP aquí (SPEC-101-13).
- DTOs de entrada/salida de dominio consumibles por controllers.

**Out of scope:**

- **Controllers REST y rutas `api/v1`** → [TR-SPEC-101-05-controllers-rest](TR-SPEC-101-05-controllers-rest.md).
- UI pantalla carga → SPEC-101-10.
- Tratativas presupuesto → SPEC-101-12 (Should).
- Envío mail → SPEC-101-13.
- Consultas listados → SPEC-101-07.
- Tests E2E Playwright (TR-101-10 / §9 madre).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: `grabarPedido(dto)` persiste cabecera+detalle en transacción; resultado **estado 0** según matriz §3.1.
- **AC-02**: `grabarPresupuesto(dto)` persiste con **estado 99** según matriz §3.1.
- **AC-03**: Desde presupuesto **99** + acción grabar pedido → pedido **0** nuevo, presupuesto **98**, registro cierre con **`cod_pedido_generado`** y **`cod_presupuesto_origen`** en pedido.
- **AC-04**: Desde pedido **0** + acción grabar presupuesto → presupuesto **99** nuevo con **`cod_pedido_origen`**; pedido origen no consultable como ingresado.
- **AC-05**: `eliminarPedido(cod)` solo si **estado 0** y `NOeliminaPedido` deshabilitado; borrado físico cabecera+detalle.
- **AC-06**: Intento eliminar presupuesto → error negocio; **no** existe método delete presupuesto público.
- **AC-07**: `iniciarEdicionPedido` pasa **0 → -1**, setea `fechahora_inicio_proceso` y `fechahora_ultima_actividad`; rechaza si otro editor activo (`ultima_actividad + MinutosWeb >= ahora`).
- **AC-08**: `touchActividadEdicion` actualiza solo `fechahora_ultima_actividad`.
- **AC-09**: `cerrarPresupuestoRechazo` exige `id_motivo` negativo activo; cabecera **98** + fila `presupuestos_cierres`.
- **AC-10**: Conversión exitosa usa **`CodMotivoCierreExitoso`**; si parámetro inválido → error **2000** sin cerrar en 98.
- **AC-11**: `copiarComprobante` genera borrador precargado (sin persistir); grabación posterior usa flujo normal. Con `ActualizarPrecioCopia = true` actualiza precios desde lista y recalcula importes; con `false` conserva precios origen validando parámetros vigentes; rechazo 422 si precios inválidos.
- **AC-12**: Totales/IVA en BD coinciden con cálculo service (tests unitarios con fixtures).
- **AC-13**: Cobertura líneas `app/Services/PedidosWeb/**` ≥ **70 %** (SPEC MVP §12.2).
- **AC-14**: Parámetros leídos vía servicio de configuración SPEC-001-04; defaults documentados si ausentes en tenant test.

### Escenarios Gherkin

```gherkin
Feature: Matriz grabación unificada §10.1

  Scenario: Alta nueva grabar pedido
    Given comprobante nuevo sin cod_pedido
    When grabarPedido con cabecera y renglones válidos
    Then estado es 0
    And cod_pedido es nuevo GUID

  Scenario: Presupuesto activo grabar pedido cierra origen
    Given presupuesto estado 99 visible
    When grabarPedido desde ese origen
    Then presupuesto pasa a 98
    And existe presupuestos_cierres con cod_pedido_generado
    And pedido nuevo tiene cod_presupuesto_origen

  Scenario: No delete presupuesto
    Given presupuesto estado 99
    When se invoca cualquier operación de eliminación física
    Then error de negocio
    And cabecera sigue en 99 o 98 según caso

Feature: Edición pedido -1 y MinutosWeb

  Scenario: Bloqueo activo
    Given pedido estado -1
    And fechahora_ultima_actividad + MinutosWeb >= ahora
    When otro usuario inicia edición
    Then rechazo con error 2000

  Scenario: Interrupción vencida
    Given pedido estado -1
    And fechahora_ultima_actividad + MinutosWeb < ahora
    When usuario retoma edición
    Then puede grabarPedido y estado final 0
```

---

## 3) Reglas de Negocio

### 3.1 Matriz unificada Grabar pedido / Grabar presupuesto

Fuente: producto §10.1, [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md).

| Situación origen | Acción | Resultado | Efectos adicionales |
|------------------|--------|-----------|---------------------|
| Alta nueva | Grabar pedido | Pedido **0** (nuevo `cod_pedido`, `nro_visible`) | Auditoría creación |
| Alta nueva | Grabar presupuesto | Presupuesto **99** (nuevo) | Auditoría creación |
| Pedido **0** o **-1** (sesión editor) | Grabar pedido | Pedido **0** mismo código | Sale de **-1**; limpia marcas edición |
| Presupuesto **99** | Grabar presupuesto | Presupuesto **99** mismo código | — |
| Pedido **0** | Grabar presupuesto | Presupuesto **99** nuevo | Pedido origen deja de ser ingresado (§5.1 TR: delete físico **0**) |
| Presupuesto **99** | Grabar pedido | Pedido **0** nuevo | Presupuesto **98** + `presupuestos_cierres` (§5.3) |

**RN-01**: La UI/API envía `accionGrabacion: pedido | presupuesto` + contexto origen; el service resuelve la fila de la matriz.

**RN-02**: Validaciones comunes antes de persistir: cliente obligatorio, ≥ 1 renglón, parámetros `ArticulosSinPrecio` / `ArticulosPrecioCero`, permisos modificación precio/bonificación según perfil.

### 3.2 Estados y edición

**RN-03**: Pedido editable en **0** y **-1** (con reglas de bloqueo); no en **1**, **2**. Presupuesto editable solo en **99**; **98** solo lectura.

**RN-04**: Al abrir edición desde **0**: `estado=-1`, `fechahora_inicio_proceso=now`, `fechahora_ultima_actividad=now`.

**RN-05**: Vigencia bloqueo: solo `fechahora_ultima_actividad + MinutosWeb` (no `fechahora_inicio_proceso`) — AMB-C09.

**RN-06**: Antes de **-1**, validar ventana ERP `MinutosBloqueo` / `MinutosAviso` vs descarga (producto §21) — errores claros si bloqueado por ERP.

**RN-07**: Al grabar pedido desde **-1**: `estado=0`; opcional limpiar `fechahora_*` o conservar `fechahora_inicio_proceso` para auditoría (documentar en implementación).

### 3.3 Eliminación y presupuestos

**RN-08**: DELETE físico **solo** pedido **estado 0**; respetar `NOeliminaPedido`.

**RN-09**: **Prohibido** DELETE presupuesto (AMB-C03). Cierre comercial = **98** + `presupuestos_cierres`.

### 3.4 Conversión y cierre

**RN-10**: Conversión presupuesto→pedido: `id_motivo` = parámetro **`CodMotivoCierreExitoso`**; `tipo_cierre=positivo`; usuario no elige motivo (HU-101-013).

**RN-11**: Rechazo: `id_motivo` con `tipo_cierre=negativo` activo; observación opcional.

**RN-12**: **Sin** cierre parcial/positivo ni clasificación por renglones (AMB-C05).

**RN-13**: Trazabilidad MVP: **`cod_presupuesto_origen`** en cabecera pedido; **`cod_pedido_generado`** en `presupuestos_cierres`; sin tabla relación extra.

**RN-14**: Conversión pedido→presupuesto solo desde **estado 0**; no desde **-1** bloqueado ni **1/2**.

### 3.5 Copia, totales, auditoría

**RN-15**: Copia: borrador sin persistir; nuevo `cod_pedido`/`nro_visible` solo al grabar; referencia opcional al GUID origen. Precios según `ActualizarPrecioCopia` (CC PQ #9):

- `false`: precios del detalle origen; validar `ArticulosPrecioCero` / `ArticulosSinPrecio` vigentes.
- `true`: lookup `pq_pedidosweb_listaprecios_articulos`; validación granular (sin fila → `ArticulosSinPrecio`; precio 0 → `ArticulosPrecioCero`); recálculo con `CalculoTotalesService`.
- Rechazo: `PedidosWebBusinessException(2000, 'business.precioCeroNoPermitido', 422)`; FE modal copia (`PedidosCargaErroresGrabacionDialog`).

**RN-15b**: Claves ERP canónicas `ArticulosPrecioCero` / `ArticulosSinPrecio` prevalecen sobre legacy `Articulopreciocero` / `Articulossinprecio`.

**RN-16**: IVA persistido en renglón y cabecera; redondeo documentado en tests (AMB-M07).

**RN-17**: `nro_visible` secuencial único pedidos+presupuestos por tenant.

**RN-18**: Auditoría en cada grabación: usuario/fecha modificación; creación en altas.

---

## 4) Impacto en Datos

### Tablas afectadas

- Escritura: `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`, `pq_pedidosweb_presupuestos_cierres`.
- Lectura: maestros, parámetros, `motivos_cierre`.
- Sin escritura en tratativas/logs en este slice salvo hooks futuros.

### Seed mínimo para tests

- Motivo positivo `id_motivo=1` y negativo `id_motivo=2` activos.
- Parámetro `CodMotivoCierreExitoso=1`, `MinutosWeb=30`, `NOeliminaPedido=0`, `NOmodificaPedido=0`.
- Pedido 0 + presupuesto 99 + pedido -1 “abandonado” (última actividad vieja) + pedido -1 “activo”.
- Usuarios: vendedor, supervisor, cliente (para visibilidad — mock policy en unit tests).

---

## 5) Contratos de API y OpenAPI

> **Explícitamente fuera de alcance.** TR-SPEC-101-05 expone HTTP; este slice entrega **servicios PHP** y DTOs.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| — | — | — | — | — |

### 5.2 Contratos de aplicación (DTO / métodos públicos)

| Método service | Entrada | Salida / efecto |
|----------------|---------|-----------------|
| `grabarPedido(GrabarComprobanteDto)` | cabecera, renglones, contexto origen | `PedidoCabeceraDto` estado 0 |
| `grabarPresupuesto(GrabarComprobanteDto)` | idem | estado 99 |
| `iniciarEdicionPedido(codPedido, usuario)` | — | estado -1 o error |
| `touchActividadEdicion(codPedido)` | — | actualiza `fechahora_ultima_actividad` |
| `cancelarEdicionPedido(codPedido, usuario)` | — | estado 0, limpia bloqueo |
| `eliminarPedido(codPedido, usuario)` | — | delete físico o error |
| `cerrarPresupuestoRechazo(CerrarPresupuestoDto)` | cod, id_motivo, obs | estado 98 |
| `copiarComprobante(CopiarComprobanteDto)` | cod origen, tipo destino | DTO precargado (sin persistir) o persist según diseño |
| `calcularTotales(CabeceraDto, RenglonDto[])` | — | totales para preview |

Errores de dominio: excepción con `error` entero (2000 negocio, 4000 not found) — mapeo HTTP en TR-101-05.

### 5.3 Actualización matriz permisos

- [ ] N/A en slice 101-04 (permisos en controllers 101-05)

---

## 6) Cambios Frontend

### Pantallas / componentes

- **Ninguno directo.** Consumo indirecto vía API TR-101-05 y pantalla TR-101-10.

### data-testid sugeridos

- N/A en este slice

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T0 | Plan | **D1** — plan implementación (skill ai-planning-mode): orden services, riesgos, mocks parámetros | Plan aprobado |
| T1 | Backend | DTOs `GrabarComprobanteDto`, `RenglonDto`, `CerrarPresupuestoDto` | Tipado + validación básica |
| T2 | Backend | `CalculoTotalesService` (IVA, bonif neta) | AC-12 unit tests |
| T3 | Backend | `GrabacionService` matriz §3.1 | AC-01, AC-02, AC-03, AC-04 |
| T4 | Backend | `EdicionPedidoService` (-1, MinutosWeb) | AC-07, AC-08 |
| T5 | Backend | `EliminacionPedidoService` | AC-05, AC-06 |
| T6 | Backend | `PresupuestoCierreService` rechazo + conversión motivo | AC-09, AC-10 |
| T7 | Backend | `ComprobanteCopiaService` | AC-11 |
| T8 | Backend | Integración `ParametrosService` (SPEC-001-04) | AC-14 |
| T9 | Backend | Facade `PedidoService` unificada para controllers | API estable TR-101-05 |
| T10 | Tests | Suite unitaria §8 | AC-13 ≥70% |
| T11 | Docs | Tabla errores negocio (códigos 2000) | Referencia TR-101-05 |

---

## 8) Estrategia de Tests

### 8.1 Unit (obligatorio — umbral ≥ 70 %)

| Área | Casos mínimos |
|------|----------------|
| Matriz grabación | 6 filas §3.1 (mock repositories) |
| Estado -1 | bloqueo activo, interrupción vencida, touch actividad |
| Eliminar | ok estado 0; fallo 1/2; `NOeliminaPedido=1` |
| Conversión P→Ped | motivo paramétrico; param inválido; trazabilidad campos |
| Conversión Ped→P | solo 0; fallo desde 1 |
| Cierre rechazo | motivo negativo obligatorio; sin parcial |
| No delete presupuesto | método ausente o excepción |
| Totales/IVA | 2 escenarios redondeo + lista con/sin IVA |
| Copia | nuevo GUID distinto; origen_comprobante |

Herramienta: PHPUnit; mocks de repositories y `ParametrosService`.

### 8.2 Integration

- Transacción real cabecera+detalle en tenant test (opcional en este slice si repositories ya cubren — preferir 1 test integración `grabarPedido` feliz).

### 8.3 E2E

- Diferido a TR-101-10 / §9 madre.

### 8.4 Cobertura

```bash
php artisan test --filter=PedidosWeb --coverage-text
```

Gate: `app/Services/PedidosWeb/**` ≥ 70 % líneas antes de cerrar slice.

---

## 9) Riesgos y Edge Cases

- **Parámetros SPEC-001-04 pendiente:** usar defaults en tests (`MinutosWeb=30`, `CodMotivoCierreExitoso=1`); riesgo de divergencia ERP — documentar en T8.
- **Concurrencia dos grabaciones mismo presupuesto 99:** transacción + `lockForUpdate` en cabecera origen.
- **Pedido -1 huérfano:** job futuro o regla consulta que excluya KPI (dashboard §4.1) — coordinar TR-101-14.
- **Conversión con renglones vacíos post-ajuste:** misma validación que alta.
- **Cliente sin permiso modificar precio:** validar en service aunque UI deshabilite (defensa profundidad).
- **Mail:** no fallar grabación si SMTP cae — evento async en TR-101-13.

---

## 10) Checklist final

### Checklist del slice

- [ ] D1 completado y plan archivado
- [ ] Matriz §3.1 cubierta en tests
- [ ] Reglas §5.1 y §5.3 madre cubiertas
- [ ] Sin DELETE presupuesto
- [ ] `fechahora_ultima_actividad` + `MinutosWeb` implementados
- [ ] `CodMotivoCierreExitoso` + trazabilidad `cod_presupuesto_origen` / `cod_pedido_generado`
- [ ] Cobertura services ≥ 70 %
- [ ] Sin controllers ni rutas nuevas

### Checklist normas transversales

- [ ] Endpoints — **N/A** (TR-101-05)
- [ ] Matriz permisos — **N/A**
- [ ] OpenAPI — **N/A**
- [ ] Envelope — preparar mapeo excepciones para 101-05
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend

- `backend/app/Services/PedidosWeb/PedidoService.php`
- `backend/app/Services/PedidosWeb/GrabacionService.php`
- `backend/app/Services/PedidosWeb/EdicionPedidoService.php`
- `backend/app/Services/PedidosWeb/CalculoTotalesService.php`
- `backend/app/Services/PedidosWeb/PresupuestoCierreService.php`
- `backend/app/Services/PedidosWeb/ComprobanteCopiaService.php`
- `backend/app/DTO/PedidosWeb/*.php`
- `backend/tests/Unit/PedidosWeb/Services/*Test.php`

### Frontend

- —

### OpenAPI

- —

### Docs

- Enlace a TR-SPEC-101-05 para contratos HTTP

## Historial CC PQ #9 (02/07/2026) — Parte I 02/07/2026

Copia paramétrica `ActualizarPrecioCopia` en `ComprobanteCopiaService` + FE `copiarComprobante` + modal error copia.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Backend rama paramétrica + validación precios | `ComprobanteCopiaService.php`, `PedidosWebParameterService.php` |
| T2 | FE hidratación `modo=copia` + modal 422 | `PedidosCargaPage.tsx`, `usePedidosCargaMobile.ts` |
| T3 | Tests | `ComprobanteCopiaServiceTest.php` (16 casos) |

Unificación delta CC PQ #9 (archivo `*-update` eliminado en Parte I). Evidencia: [F-CC-PQ-9-cierre-formal](F-CC-PQ-9-cierre-formal.md).
