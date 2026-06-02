# TR-SPEC-101-12 — Tratativas, catálogo de cierre y rechazo de presupuesto

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-014-tratativas-presupuesto](../../03-historias-usuario/101-PedidosWeb/HU-101-014-tratativas-presupuesto.md) (**Should**); [HU-101-027-cierre-rechazo-presupuesto](../../03-historias-usuario/101-PedidosWeb/HU-101-027-cierre-rechazo-presupuesto.md) (**Must**); [HU-101-013](../../03-historias-usuario/101-PedidosWeb/HU-101-013-conversion-presupuesto-pedido.md) (`CodMotivoCierreExitoso` — aplicación en TR-SPEC-101-04) |
| **SPEC relacionada** | [SPEC-101-12-tratativas-cierre](../../05-open-spec/101-PedidosWeb/SPEC-101-12-tratativas-cierre.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | **Should** (tratativas, AMB-C01) + **Must** (cierre/rechazo HU-027; catálogo motivos) |
| **Dependencias** | TR-SPEC-101-04-services-pedidos; TR-SPEC-101-05-controllers-rest; TR-SPEC-101-11-consultas-ui (acciones cerrar en grilla activos) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** HU-101-014, HU-101-027, HU-101-013 (motivo exitoso paramétrico)  
**Referencia SPEC:** [SPEC-101-12-tratativas-cierre](../../05-open-spec/101-PedidosWeb/SPEC-101-12-tratativas-cierre.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Catálogo de motivos de cierre, rechazo de presupuesto 99→98 (Must) y tratativas comerciales mínimas (Should).

### Narrativa
Como **vendedor**, quiero **registrar tratativas en presupuestos activos** (Should) y **cerrar o rechazar con motivo** (Must), para **seguimiento comercial sin CRM ni borrado físico**.

### In scope / Out of scope
- **In scope (Must):** tablas `pq_pedidosweb_motivos_cierre`, `pq_pedidosweb_presupuestos_cierres`; seed motivos positivo/negativo; `GET` catálogo motivos para UI rechazo; `POST` cerrar/rechazar presupuesto 99→98; registro en `presupuestos_cierres`; parámetro **`CodMotivoCierreExitoso`** → `id_motivo` en catálogo (`tipo_cierre = positivo`, `activo`) — **aplicado en `PedidoService` al convertir (HU-101-013 / TR-SPEC-101-04)**, sin selector en UI de conversión.
- **In scope (Should):** tablas `pq_pedidosweb_tratativas`, `pq_pedidosweb_tratativas_resultados`; CRUD mínimo tratativas solo presupuesto **99**; UI menú ítem 9 / panel en comprobante.
- **Out of scope:** cierre parcial/positivo por renglones (AMB-C05); DELETE presupuesto; duplicar reglas conversión 99→98 de 101-04.

---

## 2) Criterios de Aceptación (AC)

### Must — cierre y catálogo (HU-027)
- **AC-01:** `POST /api/v1/presupuestos/{cod}/cerrar` con `id_motivo` negativo obligatorio deja presupuesto en **98** + fila en `presupuestos_cierres`.
- **AC-02:** Solo presupuesto **estado 99** admite cierre/rechazo; 98 → 422.
- **AC-03:** `GET /api/v1/motivos-cierre?tipo=negativo` (y filtros) alimenta selector UI de rechazo.
- **AC-04:** Consulta cerrados (HU-016) muestra motivo/tipo desde `presupuestos_cierres`.
- **AC-05:** Presupuesto 98 no editable ni convertible desde consulta activos.

### Must — motivo exitoso paramétrico (HU-013, implementación en 101-04)
- **AC-06:** Seed incluye motivo positivo ejemplo + parámetro `CodMotivoCierreExitoso` apuntando a `id_motivo`.
- **AC-07:** Conversión presupuesto→pedido usa `id_motivo` del parámetro sin UI; si parámetro vacío/motivo inactivo → error negocio (no cerrar en 98).
- **AC-08:** Rechazo **no** usa `CodMotivoCierreExitoso` (solo motivos negativos elegidos en UI).

### Should — tratativas (HU-014)
- **AC-09:** `POST/GET /api/v1/presupuestos/{cod}/tratativas` sobre presupuesto 99; historial visible en comprobante.
- **AC-10:** Tratativas prohibidas en presupuesto 98 o pedido → 422.
- **AC-11:** Slice tratativas puede diferirse post E2E §9 sin bloquear MVP (CA HU-014).

### Escenarios Gherkin

```gherkin
Feature: Rechazo de presupuesto

  Scenario: Rechazo con motivo negativo
    Given presupuesto estado 99 y motivo negativo activo
    When POST cerrar con id_motivo negativo
    Then presupuesto pasa a 98
    And existe registro en presupuestos_cierres

  Scenario: Conversión usa CodMotivoCierreExitoso
    Given parametro CodMotivoCierreExitoso = 1 y motivo 1 positivo activo
    When convierte presupuesto a pedido via PedidoService
    Then presupuestos_cierres.id_motivo = 1 sin selector en UI

Feature: Tratativas Should

  Scenario: Alta tratativa en presupuesto activo
    Given presupuesto 99
    When registra tratativa con comentario
    Then aparece en historial del comprobante
```

---

## 3) Reglas de Negocio

1. **RN-01:** `pq_pedidosweb_motivos_cierre.tipo_cierre`: `positivo`, `negativo` (MVP sin `parcial`).
2. **RN-02:** Rechazo/cierre manual: motivo **negativo** elegido en UI (`Popup` + `SelectBox` DX).
3. **RN-03:** Conversión exitosa (HU-013): `id_motivo` = `CodMotivoCierreExitoso`; `tipo_cierre` positivo; `cod_pedido_generado` = pedido nuevo; **sin** selector motivo en pantalla carga.
4. **RN-04:** Al cerrar, cabecera `pq_pedidosweb_pedidoscabecera` 99 → **98**.
5. **RN-05:** Tratativas: campos mínimos §16 — `fecha_hora`, `cod_usuario_web`, `comentario`, `id_resultado`, `proxima_fecha`, `proxima_accion` opcionales.
6. **RN-06:** Sin DELETE presupuesto; sin cierre parcial por renglones.
7. **RN-07:** Distinto de conversión aunque ambos terminan en 98 (trazabilidad `cod_pedido_generado` vs rechazo sin pedido).

---

## 4) Impacto en Datos

### Tablas afectadas
- `pq_pedidosweb_motivos_cierre` (§7.3 modelo datos)
- `pq_pedidosweb_presupuestos_cierres` (§7.4)
- `pq_pedidosweb_tratativas` (§7.1) — Should
- `pq_pedidosweb_tratativas_resultados` (§7.2) — Should

### Seed mínimo para tests
- Motivo `id_motivo=1`, `tipo_cierre=positivo`, `activo=1`
- Motivo `id_motivo=2`, `tipo_cierre=negativo`, `activo=1`
- Parámetro ERP `CodMotivoCierreExitoso=1`
- Resultados tratativa: al menos 2 filas activas
- Presupuesto 99 QA para cerrar/rechazar/tratativa

---

## 5) Contratos de API y OpenAPI

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Prioridad slice |
|--------|------|------|---------------|-----------------|
| GET | `/api/v1/motivos-cierre` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` | Must |
| POST | `/api/v1/presupuestos/{cod}/cerrar` | Bearer + `X-Paq-Cliente` | `Permiso_Modi` | Must |
| GET | `/api/v1/presupuestos/{cod}/tratativas` | Bearer + `X-Paq-Cliente` | `Permiso_Repo` | Should |
| POST | `/api/v1/presupuestos/{cod}/tratativas` | Bearer + `X-Paq-Cliente` | `Permiso_Alta` o `Permiso_Modi` | Should |

*Conversión `POST .../convertir-a-pedido` y aplicación `CodMotivoCierreExitoso`: TR-SPEC-101-04/05.*

### 5.2 Detalle por operación

#### GET `/api/v1/motivos-cierre`

**Query:** `tipo_cierre` (`positivo`|`negativo`), `activo` (default 1)

**Response 200:** `resultado.items[]` — `id_motivo`, `descripcion`, `tipo_cierre`

#### POST `/api/v1/presupuestos/{cod}/cerrar`

**Request:**

```json
{
  "id_motivo": 2,
  "observacion": "opcional"
}
```

**Reglas:** `id_motivo` debe ser **negativo** y activo; presupuesto 99.

**Response 200:** `resultado` con `cod_presupuesto`, `estado: 98`, `id_cierre`

**Response 422:** presupuesto no 99; motivo no negativo; motivo inactivo

#### POST `/api/v1/presupuestos/{cod}/tratativas` (Should)

**Request:** `comentario`, `id_resultado` (opcional), `proxima_fecha`, `proxima_accion`

**Response 200:** tratativa creada

**OpenAPI (L5-Swagger):**

- [ ] Anotaciones en controllers
- [ ] `security`, `X-Paq-Cliente`, 401, 403, 422
- [ ] Permisos en description
- [ ] Verificado en `/api/documentation`

### 5.3 Actualización matriz permisos

- [ ] Filas motivos-cierre, cerrar, tratativas

---

## 6) Cambios Frontend

### Pantallas / componentes
- Acción **Cerrar/Rechazar** en grilla presupuestos activos (101-11): `Popup` DX + `SelectBox` motivos negativos
- Panel/historial **Tratativas** (Should): lista + formulario alta en presupuesto 99
- Sin selector motivo en flujo conversión (HU-013)

### data-testid sugeridos
- `presupuestoCerrarPopup`
- `presupuestoCerrarMotivoSelect`
- `presupuestoCerrarConfirmar`
- `tratativasLista`, `tratativaAltaComentario`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | DB | Migraciones motivos, cierres, tratativas | Seed AC-06 |
| T2 | Backend | Catálogo + CerrarPresupuestoService | AC-01–05 |
| T3 | Backend | TratativasService (Should) | AC-09–10 |
| T4 | Backend | Integrar CodMotivo en 101-04 convert | AC-06–08 |
| T5 | Frontend | Popup rechazo + tratativas UI | AC UI |
| T6 | Tests | Feature cerrar + motivos; unit paramétrico | 401/403 |
| T7 | Docs | Matriz + parámetro SPEC-001-04 | Checklist |

---

## 8) Estrategia de Tests

- **Unit:** Validación tipo motivo; resolución `CodMotivoCierreExitoso`.
- **Integration:** POST cerrar feliz/422; GET motivos; 401/403.
- **E2E:** Rechazo desde grilla (Must); tratativas (Should, post §9 si difiere).

---

## 9) Riesgos y Edge Cases

- Motivo positivo desactivado rompe conversión en producción → validar en deploy checklist.
- Duplicar lógica 98 entre 101-04 y 101-12 → único service de cierre compartido.
- Tratativas Should omitidas: catálogo y cierre Must igualmente obligatorios.

---

## 10) Checklist final

### Checklist del slice
- [ ] Must: cierre/rechazo + catálogo + seed `CodMotivoCierreExitoso`
- [ ] Should: tratativas (o explícitamente diferido en release notes)
- [ ] Coherencia HU-013 en TR-SPEC-101-04

### Checklist normas transversales

- [ ] Endpoints con policy
- [ ] Matriz actualizada
- [ ] OpenAPI coherente
- [ ] Envelope JSON
- [ ] Tests 401/403
- [ ] Sin ampliación de alcance

---

## Archivos creados/modificados

(Post-implementación)

### Backend
- Migraciones §7.1–7.4
- `MotivoCierreController`, `PresupuestoCierreService`, `TratativaService`

### Frontend
- Popup cierre; panel tratativas

### OpenAPI
- Endpoints cerrar, motivos, tratativas
