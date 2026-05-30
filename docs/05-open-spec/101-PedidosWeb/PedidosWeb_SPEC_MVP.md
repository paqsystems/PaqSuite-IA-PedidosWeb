# PedidosWeb - SPEC MVP (OpenSpec ejecutable)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-PedidosWeb-MVP |
| **Título** | SPEC MVP PedidosWeb |
| **Épica / carpeta** | `101-PedidosWeb` |
| **Estado** | Pendiente |
| **Última actualización** | 2026-05-28 |
| **HU relacionadas** | Ver §8 y §8.1 |
| **TR relacionadas** | A definir por cada `SPEC-101-xx` |
| **Fuentes** | `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md`, `PedidosWeb_Modelo_Datos_Final.md`, `PedidosWeb_Plan_Cursor_OpenSpec_Final.md`, `docs/_base/00-inicio-arquitectura.md` |

## 1. Objetivo del SPEC

Definir una especificación única, accionable y secuencial para construir el MVP de PedidosWeb, alineada a la documentación de producto y al patrón MONO de PaqSuite.

Este SPEC prioriza entregas incrementales verificables (sin enfoque "hacer todo junto").

**Convención de nombres (codificación única):**

| Ámbito | Prefijo | Ejemplo |
|--------|---------|---------|
| Generalidades (config inicial) | `SPEC-001-xx` | `SPEC-001-02-acceso-y-seguridad.md` |
| PedidosWeb — SPEC de slice / MVP | `SPEC-101-xx` | `SPEC-101-04` (services) |
| PedidosWeb — HU formales | `HU-101-xxx` | `HU-101-001-login.md` |
| PedidosWeb — TR | `TR-SPEC-101-xx` | `TR-SPEC-101-04-pedido-service.md` |

Los slices técnicos del roadmap (**01–15**) y las **HU/TR** derivadas usan **`SPEC-101-xx`** (sustituye el prefijo histórico `PW-SPEC-xx`). En metadatos de cada HU/TR, el campo **SPEC origen** apunta al `SPEC-101-xx` correspondiente.

## 2. Contexto del producto

PedidosWeb es un portal comercial conectado al ERP (Tango), orientado a vendedores y clientes para:

- cargar pedidos y presupuestos,
- consultar información comercial,
- registrar tratativas mínimas de presupuestos,
- cerrar presupuestos con motivo.

No es un CRM completo.

## 3. Modo de instalación y tenancy (obligatorio)

Modo: **MONO** (tenant por `{cliente}`, sin `X-Company-Id` ni selector de empresa en UI).

### 3.1 URLs y contexto

| Concepto | Valor |
|----------|--------|
| `{proyecto}` | `pedidosweb` |
| Entrada | `https://{cliente}.pedidosweb.paqsystems.com` |
| Frontend | `https://frontend.pedidosweb.paqsystems.com` |
| Backend | `https://backend.pedidosweb.paqsystems.com` |
| Header API | `X-Paq-Cliente: {cliente}` |
| Base SQL por tenant | `pq_pedidosweb_{cliente}` |
| Desarrollo sin subdominio | `cliente = desarrollo` (forzado en middleware local) |

### 3.2 Infraestructura de datos

- **Motor:** SQL Server por tenant (no MySQL genérico del stack de referencia).
- **Resolución de conexión:** tabla **`EMPRESAS_CONEXION`** en base central del deploy, keyed por `proyecto` + `CODIGO_TENANT` (`{cliente}`), con `SQL_DATABASE`, credenciales cifradas y `HOST_TAILSCALE` según `docs/_base/resolucion-host-cliente-sql-mono.md`.
- **Desarrollo:** debe existir fila **`CODIGO_TENANT = desarrollo`** en `EMPRESAS_CONEXION` apuntando a la base SQL de trabajo local/compartida; el middleware fuerza `X-Paq-Cliente: desarrollo` en localhost.
- **Prohibido:** inferir tenant solo desde JWT sin validar registro activo; ligar tenant usado en login al token/sesión.

Reglas de referencia:

- `docs/_base/resolucion-host-cliente-sql-mono.md`
- `docs/_mono/README-host-y-tenant.md`
- `.cursor/rules/mono/15-host-subdominio-base-datos-y-branding.md`

### 3.3 Experiencia base (referencia SPEC-001)

| Tema | Fuente |
|------|--------|
| Idioma/tema por defecto | Producto §8.1 (`es`, `generic.light`) + `SPEC-001-01` |
| Menú MVP (11 ítems) | Producto §8 + permisos §7 + `SPEC-001-01` |
| Tenancy / MONO | `SPEC-001-05` + este §3 |

## 4. Alcance funcional MVP

Incluye:

1. Login, recuperación de contraseña y expiración por inactividad.
2. Carga/edición/eliminación de pedidos y presupuestos ingresados.
3. Conversión presupuesto a pedido y pedido a presupuesto.
4. Tratativas y cierre de presupuestos con motivo.
5. Consultas: pedidos ingresados, presupuestos, pendientes, stock, deuda, cheques, historial.
6. Envío de mail al grabar/modificar.
7. Logs de integración.
8. Dashboard con indicadores operativos (§4.1).
9. Auditoría liviana.
10. Tests unitarios, integración y E2E.

### 4.1 Dashboard — primer release (Must)

**Incluye** (indicadores operativos desde el inicio):

| Indicador | Definición |
|-----------|------------|
| Q presupuestos activos | Cantidad de comprobantes **estado 99** visibles al usuario. |
| $ presupuestos activos | Suma de **totales** de esos presupuestos. |
| Q pedidos ingresados | Cantidad de comprobantes **estado 0** (y **-1** si aplica control operativo visible). |
| $ pedidos ingresados | Suma de totales de esos pedidos. |
| Q pedidos pendientes | Cantidad de comprobantes **estado 1**. |
| $ pedidos pendientes | Suma de totales de esos pedidos. |
| Cliente mayor monto presupuesto | Cliente con **mayor suma de totales** en presupuestos activos (estado 99) del universo visible. |
| Cliente mayor monto pedidos ingresados | Cliente con **mayor suma de totales** en pedidos ingresados (estado 0) del universo visible. |

Reglas:

- Respetar visibilidad por perfil (cliente / vendedor / supervisor) igual que consultas.
- **Presupuesto activo** = solo **estado 99** (excluye **98** cerrados).
- **Moneda:** una sola moneda por tenant en MVP; totales `$` suman importe de cabecera sin conversión.

**Excluye** en este release (conceptual §19, quedan para iteración posterior):

- Tasa de cierre de presupuestos por vendedor.
- Ranking de motivos de rechazo.
- Artículos CORE sin movimiento.
- Pedidos por vendedor / top clientes genéricos.

## 5. Decisiones cerradas (replicadas desde conceptual §24 y acuerdos)

| Tema | Decisión |
|------|----------|
| Número visible | Secuencial **único** para pedidos y presupuestos, **por tenant**. |
| Tablas tratativas/cierre | `pq_pedidosweb_tratativas`, `pq_pedidosweb_tratativas_resultados`, `pq_pedidosweb_motivos_cierre`, `pq_pedidosweb_presupuestos_cierres`. |
| Estado `-1` | Solo indica pedido **en modificación** para evitar descarga al ERP. |
| IVA | Se **persiste en renglón y en cabecera**. |
| Mail | Mismo canal que **"olvidé la contraseña"** del Login. |
| Tenant desarrollo | `cliente = desarrollo` en middleware **y** fila `EMPRESAS_CONEXION`. |
| HU formales | **Sí** para `001-Generaliddes` (SPEC-001-01…05) y para `101-PedidosWeb`. |
| Consultas deuda/cheques/historial | **Must** en primer release. |
| Conversión pedido → presupuesto | **Must**; reglas en §5.1. |
| Presupuesto cerrado | Pasa a **estado 98** + registro en `pq_pedidosweb_presupuestos_cierres`. |
| Moneda por tenant | **Una sola moneda** por tenant en MVP (dashboard y totales). |
| Codificación OpenSpec PedidosWeb | Slices, HU y TR: **`SPEC-101-xx`**, **`HU-101-xxx`**, **`TR-SPEC-101-xx`** (no `PW-SPEC-xx`). |

### 5.1 Conversión pedido → presupuesto (Must)

Criterios (conceptual §15.2):

- Solo permitida si el pedido está en **estado 0** (ingresado web, **no** descargado al ERP).
- **No** permitida en estados 1, 2, 99 ni durante **-1** de modificación bloqueada por descarga ERP.
- El comprobante resultante queda como **presupuesto estado 99**.
- Conservar trazabilidad al pedido origen (`cod_pedido_origen` o equivalente en cabecera).
- Aplicar mismas validaciones de cabecera/renglones que alta de presupuesto.

### 5.2 Consultas deuda, cheques e historial (Must)

Criterios (conceptual §17.4–17.6):

| Consulta | Criterio mínimo |
|----------|-----------------|
| **Deuda** | Por cliente o todos según perfil; comprobantes con saldo, vencimiento, saldo acumulado; **fecha_proceso** en carátula. |
| **Cheques** | Por cliente o todos según perfil; cheques en cartera o aplicados con fecha **posterior al día**; **fecha_proceso** en carátula. |
| **Historial ventas** | Período según parámetro **DiasVentasDetalladas**; detalle en modal; **fecha_proceso** en carátula. |

### 5.3 Cierre de presupuesto y estado 98 (Must)

Al cerrar un presupuesto (conversión a pedido, cierre parcial/positivo o rechazo/cierre negativo con motivo):

1. Actualizar cabecera del presupuesto a **estado 98** (presupuesto cerrado).
2. Registrar el evento en **`pq_pedidosweb_presupuestos_cierres`** (tipo, motivo, pedido generado si aplica).
3. Los indicadores de **presupuestos activos** (dashboard §4.1) cuentan solo **estado 99**.
4. Consultas de presupuestos **activos** listan **estado 99**; consulta de **cerrados** (**estado 98**, conceptual §17.2.1) en grilla aparte (HU-101-016).

## 6. Reglas de arquitectura

### 6.1 Backend

- Laravel API REST, prefijo `api/v1`.
- Controllers delgados; services con reglas de negocio; repositories solo datos; DTOs; policies.
- Envelope JSON: `error`, `respuesta`, `resultado` — contrato MONO: [`docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md`](../../00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md); normas TR: `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md` §2.
- **OpenAPI (L5-Swagger):** `/api/documentation`; raíz `backend/OpenApi.php`.
- **Norma transversal TR:** toda TR de slice debe cumplir `docs/04-tareas/_NORMAS-TRANSVERSALES-TR.md` (políticas por endpoint documentadas en OpenAPI con `security`, 401/403, `X-Paq-Cliente`). Plantilla: `docs/04-tareas/_PLANTILLA-TR-SLICE.md`.

### 6.2 Frontend

- React + DevExtreme; layout post-login; cliente HTTP con `X-Paq-Cliente`.
- Grillas según `001-Generaliddes` / UI transversal.

### 6.3 Estados de comprobante

| Estado | Significado |
|--------|-------------|
| `-1` | Modificación en curso (bloqueo operativo frente a descarga ERP). |
| `0` | Pedido ingresado web, no descargado. |
| `1` | Pedido pendiente ERP. |
| `2` | Pedido cerrado/cumplido ERP. |
| `98` | Presupuesto **cerrado** (conversión, cierre comercial o rechazo; ver §5.3). |
| `99` | Presupuesto ingresado / **activo**. |

### 6.4 Auditoría liviana

- Fecha/usuario creación y última modificación en cabecera.

## 7. Modelo de datos mínimo objetivo

Tablas operativas: `pq_pedidosweb_pedidoscabecera`, `pq_pedidosweb_pedidosdetalle`.

Tablas nuevas: ver §5 (tratativas, resultados, motivos, cierres).

Observabilidad: `pq_pedidosweb_logs_integracion`.

## 8. Priorización Must / Should / Could

| Ítem | Prioridad | HU | Bloque |
|------|-----------|-----|--------|
| Login, recuperación, expiración | **Must** | HU-101-001, HU-101-002 | `SPEC-001-02`, `SPEC-101-06` |
| Tenancy + `EMPRESAS_CONEXION` | **Must** | HU-101-003 | `SPEC-001-05`, `SPEC-101-01` |
| Configuración inicial Generaliddes | **Must** (antes) | HU-GEN-01…05 | `SPEC-001-01`…`05` |
| Carga pedido/presupuesto | **Must** | HU-101-005–HU-101-010 | `SPEC-101-04`, `SPEC-101-10` |
| Edición/eliminación ingresados | **Must** | HU-101-011, HU-101-012 | `SPEC-101-04`, `SPEC-101-05` |
| Conversión presupuesto → pedido | **Must** | HU-101-013 | `SPEC-101-12` |
| Conversión pedido → presupuesto | **Must** | HU-101-024 | `SPEC-101-04` |
| Tratativas y cierre con motivo | **Should** | HU-101-014 + ampliar | `SPEC-101-12` |
| Consultas pedidos/presupuestos/pendientes | **Must** | HU-101-015–HU-101-017 (HU-101-016: activos **99** y cerrados **98**) | `SPEC-101-07`, `SPEC-101-11` |
| Consulta stock | **Must** | HU-101-018 | `SPEC-101-07`, `SPEC-101-11` |
| Consulta deuda | **Must** | HU-101-021 | `SPEC-101-07`, `SPEC-101-11` |
| Consulta cheques | **Must** | HU-101-022 | `SPEC-101-07`, `SPEC-101-11` |
| Historial ventas | **Must** | HU-101-023 | `SPEC-101-07`, `SPEC-101-11` |
| Mail al grabar/modificar | **Must** | HU-101-019 | `SPEC-101-13` |
| Logs integración | **Should** | HU-101-020 | `SPEC-101-08` |
| Dashboard §4.1 | **Must** | HU-101-025 | `SPEC-101-14` |
| Auditoría liviana | **Must** | Transversal | `SPEC-101-04` |
| Tests | **Must** | Transversal | `SPEC-101-15` |

### 8.1 Historias de usuario formales (decisión cerrada)

Se generan **HU formales** en Markdown con metadatos, en:

| Carpeta | Origen SPEC | Alcance |
|---------|-------------|---------|
| `docs/03-historias-usuario/001-Generaliddes/` | `SPEC-001-01` … `SPEC-001-05` | Una o más HU por SPEC: p. ej. `HU-GEN-01-shell-layout` … `HU-GEN-01-ayuda-externa` (SPEC-001-01); `HU-GEN-02` … `HU-GEN-05` para el resto. |
| `docs/03-historias-usuario/101-PedidosWeb/` | Este SPEC + plan | `HU-101-001` … `HU-101-025` (y derivadas); **SPEC origen** = `SPEC-101-xx`. |
| `docs/04-tareas/101-PedidosWeb/` | TR por slice | `TR-SPEC-101-xx-*.md` (una o más TR por slice). |

Flujo OpenSpec: **A (SPEC) → B (HU) → C (TR) → D (ejecución)** para ambas carpetas.

## 9. Flujo E2E prioritario (criterio de cierre MVP)

```text
1. Entrada {cliente}.pedidosweb → frontend.pedidosweb (contexto tenant)
2. Login (X-Paq-Cliente; sesión)
3. Shell + menú por rol
4. Selección cliente (vendedor/supervisor)
5. Carga pedido: cabecera + renglón; totales/IVA
6. Grabar pedido (estado 0); mail enviado
7. Consulta pedidos ingresados: comprobante visible
8. Dashboard §4.1: indicadores coherentes con datos cargados
```

Extensiones Must del mismo release: presupuesto (99), conversión bidireccional, consultas deuda/cheques/historial según §5.2.

## 10. Orden de implementación

### Fase 0 — Generalidades (obligatoria primero)

| Orden | SPEC | HU |
|-------|------|-----|
| 1 | `SPEC-001-05` | HU-GEN-05 |
| 2 | `SPEC-001-02` | HU-GEN-02 |
| 3 | `SPEC-001-01` | HU-GEN-01 |
| 4 | `SPEC-001-03` | HU-GEN-03 |
| 5 | `SPEC-001-04` | HU-GEN-04 |
| — | `SPEC-001-06`…`09` | Documental (sin HU de implementación MVP) |

### Fase 1 — `SPEC-101-xx`

| ID | Contenido |
|----|-----------|
| **SPEC-101-01** | Backend, `EMPRESAS_CONEXION`, middleware, healthcheck |
| **SPEC-101-02** | Modelos |
| **SPEC-101-03** | Repositories |
| **SPEC-101-06** | Seguridad (antes de CRUD) |
| **SPEC-101-04** | Services |
| **SPEC-101-05** | Controllers REST |
| **SPEC-101-09** | Frontend base |
| **SPEC-101-10** | Pantalla carga |
| **SPEC-101-07** | Consultas API |
| **SPEC-101-11** | Consultas UI |
| **SPEC-101-12** | Tratativas/cierre |
| **SPEC-101-13** | Mails |
| **SPEC-101-14** | Dashboard §4.1 |
| **SPEC-101-08** | Logs |
| **SPEC-101-15** | Tests y hardening |

## 11. Criterios de aceptación globales (medibles)

- [ ] Tenancy MONO + fila `desarrollo` en `EMPRESAS_CONEXION` operativa.
- [ ] Flujo E2E §9 ejecutable.
- [ ] Dashboard §4.1 con los 8 indicadores definidos.
- [ ] Consultas Must (incl. deuda, cheques, historial) según §5.2.
- [ ] Conversión pedido → presupuesto según §5.1.
- [ ] Cierre presupuesto → **estado 98** + registro en `presupuestos_cierres` (§5.3).
- [ ] Reglas en services; OpenAPI por slice; IVA cabecera+renglón; mail canal login.
- [ ] Cobertura services del slice: **≥ 70 %** (MVP) / **≥ 80 %** (módulo estable); §12.

## 12. Política de tests y cobertura

### 12.1 Por slice (`SPEC-101-xx`)

Cada slice entregado incluye **como mínimo**:

| Tipo | Mínimo |
|------|--------|
| **Unit** | Services y cálculos del slice (bonificaciones, totales, reglas de estado). |
| **Feature / integración API** | Un test por endpoint expuesto del slice (éxito + al menos un error de validación o permiso). |
| **E2E Playwright** | **≥ 2 escenarios** por slice con UI: (1) camino feliz, (2) error, permiso denegado o validación rechazada. |

El flujo E2E §9 es obligatorio además de los E2E por slice.

### 12.2 Umbral de cobertura (obligatorio)

1. **Alcance medido:** `app/Services/**` y helpers de dominio del módulo del slice (no exigir % alto en controllers boilerplate al inicio).
2. **Umbral MVP:** **≥ 70 %** líneas en services del módulo entregado en el slice.
3. **Umbral módulo estable:** **≥ 80 %** en el mismo alcance cuando el slice/módulo se declare estable (cierre de `SPEC-101-xx` en release).
4. **Herramienta:** PHPUnit `--coverage-text` (backend); Vitest coverage en frontend para utilidades puras.
5. **Gate CI (recomendado):** fallar build si services del diff del slice están por debajo del umbral vigente (70 % o 80 % según fase).
6. **Prioridad sobre %:** cumplir **≥ 2 E2E por slice** + feature tests de endpoints; el % complementa, no reemplaza escenarios E2E.

## 13. Dependencias y riesgos

**Dependencias:** SQL Server; `EMPRESAS_CONEXION`; ERP; parámetros (`SPEC-001-04`); `DiasVentasDetalladas` para historial.

**Riesgos:** permisos por atributo; datos ERP vs pruebas; concurrencia descarga ERP.

## 14. Revisión A1 — estado post-decisiones

### Decisiones humanas cerradas (2026-05-28)

1. HU formales en **`001-Generaliddes`** y **`101-PedidosWeb`**.
2. Deuda, cheques e historial → **Must** (HU-101-021, HU-101-022, HU-101-023).
3. Dashboard primer release: **indicadores §4.1**; excluidos indicadores conceptual §19.
4. **`cliente = desarrollo`** en middleware y **`EMPRESAS_CONEXION`**.
5. **Moneda:** una sola moneda por tenant en MVP.
6. **Presupuesto cerrado:** cabecera pasa a **estado 98** (+ registro en `presupuestos_cierres`).
7. **Cobertura tests:** **≥ 70 %** services/slice en MVP; **≥ 80 %** cuando el módulo esté estable (§12.2).

### Ambigüedades menores residuales

| # | Tema | Observación |
|---|------|-------------|
| 1 | **Tratativas / cierre** | Siguen **Should**; HU-101-014 cubre cierre (99→98); tratativas completas pueden ser HU aparte. |
| 2 | **Logs integración** | **Should** (HU-101-020); no bloquean release E2E §9. |
| 3 | **Generaliddes 06–09** | Documental; sin HU de implementación en MVP (acordado). |

### Supuestos confirmados

1. Totales de dashboard usan el **importe total** de cabecera del comprobante. **Confirmado.**
2. **Presupuesto activo** = **estado 99**; al cerrar pasa a **estado 98**. **Confirmado.**
3. Sincronización ERP es externa al portal. **Confirmado.**
4. Permisos finos vía parámetros (`SPEC-001-04`). **Confirmado.**

### Preguntas humanas

Ninguna abierta para este SPEC (A1 **apto** para derivar HU cuando se decida pasar a parte B).

---

## Definición de listo por `SPEC-101-xx`

Cada `SPEC-101-xx` está listo si: alcance cerrado; código del slice; tests según §12; **OpenAPI según `_NORMAS-TRANSVERSALES-TR.md`**; notas en `docs/05-open-spec/101-PedidosWeb/`.
