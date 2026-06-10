# PedidosWeb - SPEC MVP (OpenSpec ejecutable)

| Campo | Valor |
|-------|--------|
| **ID** | SPEC-101-PedidosWeb-MVP |
| **Título** | SPEC MVP PedidosWeb |
| **Épica / carpeta** | `101-PedidosWeb` |
| **Estado** | B1 cerrado — **listo parte C** (TR) |
| **HU** | [docs/03-historias-usuario/101-PedidosWeb/](../../03-historias-usuario/101-PedidosWeb/README.md) — 27 HU |
| **Revisión A1** | **Apto** (2026-06-01; decisiones §14.3) |
| **Última actualización** | 2026-06-01 |
| **Índice slices** | [README.md](README.md) — `SPEC-101-01` … `SPEC-101-15` |
| **HU relacionadas** | Ver §8 y §8.1 (a generar en `docs/03-historias-usuario/101-PedidosWeb/`) |
| **TR relacionadas** | A definir por cada slice en `docs/04-tareas/101-PedidosWeb/` |
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
- registrar tratativas mínimas de presupuestos (**Should**, slice 101-12),
- cerrar presupuestos con motivo (estado 98, sin eliminación física).

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
| Tenancy / MONO | `SPEC-001-05` + [SPEC-101-01](SPEC-101-01-backend-base.md) (**etapa posterior**, AMB-C07) |

## 4. Alcance funcional MVP

Incluye:

1. Login, recuperación de contraseña y expiración por inactividad (heredado `SPEC-001-02`; verificar en 101-06).
2. Carga/edición de pedidos y presupuestos; **eliminación física solo pedidos estado 0**; presupuestos **sin DELETE** (solo cierre 98).
3. **Copiar comprobante** como base de uno nuevo (producto §10.1).
4. Conversión presupuesto a pedido y pedido a presupuesto.
5. Tratativas de presupuestos (**Should**, §8) y cierre con motivo / conversión → estado 98 (sin cierre parcial/positivo).
6. Consultas: pedidos ingresados, presupuestos, pendientes, stock, deuda, cheques, historial; export **Excel** en MVP (PDF → `SPEC-001-06`).
7. Envío de mail al grabar/modificar.
8. Logs de integración (**Should**).
9. Dashboard con indicadores operativos (§4.1).
10. Auditoría liviana.
11. Tests unitarios, integración y E2E.

### 4.1 Dashboard — primer release (Must)

**Incluye** (indicadores operativos desde el inicio):

| Indicador | Definición |
|-----------|------------|
| Q presupuestos activos | Cantidad de comprobantes **estado 99** visibles al usuario. |
| $ presupuestos activos | Suma de **totales** de esos presupuestos. |
| Q pedidos ingresados | Cantidad de comprobantes **estado 0** y **-1** con regla de exclusión §4.1. |
| $ pedidos ingresados | Suma de totales de esos mismos comprobantes. |
| Q pedidos pendientes | Cantidad de comprobantes **estado 1**. |
| $ pedidos pendientes | Suma de totales de esos pedidos. |
| Cliente mayor monto presupuesto | Cliente con **mayor suma de totales** en presupuestos activos (estado 99) del universo visible. |
| Cliente mayor monto pedidos ingresados | Cliente con **mayor suma de totales** en pedidos ingresados (estado 0) del universo visible. |

Reglas:

- Respetar visibilidad por perfil (cliente / vendedor / supervisor) igual que consultas.
- **Presupuesto activo** = solo **estado 99** (excluye **98** cerrados).
- **Moneda:** una sola moneda por tenant en MVP; totales `$` suman importe de cabecera sin conversión.
- **Pedidos ingresados (estado 0 y -1):** excluir del KPI si `fechahora_ultima_actividad + MinutosWeb >= fechahora_actual` (modificación **-1** activa; HU-101-011). Detalle: [SPEC-101-14-dashboard.md](SPEC-101-14-dashboard.md).

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
| Estado `-1` | Pedido **en modificación** (evita descarga ERP). Vigencia del bloqueo: **`fechahora_ultima_actividad` + `MinutosWeb`** (interrupciones §21 producto). |
| Pantalla carga | **Única** pedido/presupuesto; botones **Grabar pedido** y **Grabar presupuesto** + Cancelar; 6 transiciones §10.1 producto. |
| Trazabilidad presupuesto→pedido | **`presupuestos_cierres.cod_pedido_generado`** + **`cod_presupuesto_origen`** en cabecera pedido; **sin** tabla relación extra en MVP. |
| IVA | Se **persiste en renglón y en cabecera**. |
| Mail | Mismo canal que **"olvidé la contraseña"** del Login. |
| Tenant desarrollo | `cliente = desarrollo` en middleware **y** fila `EMPRESAS_CONEXION`. |
| HU formales | **Sí** para `001-Generaliddes` (SPEC-001-01…05) y para `101-PedidosWeb`. |
| Consultas deuda/cheques/historial | **Must** en primer release. |
| Conversión pedido → presupuesto | **Must**; reglas en §5.1. |
| Presupuesto cerrado | Pasa a **estado 98** + registro en `pq_pedidosweb_presupuestos_cierres`. |
| Moneda por tenant | **Una sola moneda** por tenant en MVP (dashboard y totales). |
| Codificación OpenSpec PedidosWeb | Slices, HU y TR: **`SPEC-101-xx`**, **`HU-101-xxx`**, **`TR-SPEC-101-xx`** (no `PW-SPEC-xx`). |
| Eliminar presupuesto | **Prohibido** — solo cierre **98** + `presupuestos_cierres` (AMB-C03). |
| Copiar comprobante | **In scope** MVP (AMB-C04). |
| Cierre parcial/positivo | **No existe** en MVP; sin clasificación por renglones (AMB-C05). |
| Tratativas | **Should** (AMB-C01). |
| Logs integración | **Should** (AMB-C02). |
| Export consultas PDF | **Fuera MVP** → futuro `SPEC-001-06-emision` (AMB-C08). |
| Parámetros generales | Contexto **SPEC-001-04** dedicado — **pendiente** (AMB-C06). |
| Precio y descuento (V/S) | **No** hardcodeados en portal: habilitación por **parámetros ERP** (`ModificaPrecioV/S`, `ModificaBonArtV/S`, `ModificaBonCliV/S`, etc. — producto §10.5–§10.6). Cliente (**C**) sin edición de precio/lista/descuento artículo. |
| Motivo cierre exitoso | Parámetro ERP **`CodMotivoCierreExitoso`**: código/`id_motivo` en catálogo `pq_pedidosweb_motivos_cierre` al convertir presupuesto → pedido (HU-101-013). Rechazo sigue eligiendo motivo **negativo** en UI. |
| `EMPRESAS_CONEXION` completo | **Etapa posterior** — [SPEC-101-01](SPEC-101-01-backend-base.md) (AMB-C07). |

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

Al cerrar un presupuesto (**conversión a pedido** o **rechazo/cierre negativo con motivo** — sin cierre parcial/positivo):

1. Actualizar cabecera del presupuesto a **estado 98** (presupuesto cerrado).
2. Registrar el evento en **`pq_pedidosweb_presupuestos_cierres`** (tipo, motivo, pedido generado si aplica en conversión).
3. **Conversión presupuesto → pedido (cierre exitoso):** el **`id_motivo`** se toma del parámetro ERP **`CodMotivoCierreExitoso`** (debe existir en `pq_pedidosweb_motivos_cierre` con `tipo_cierre = positivo` y `activo = 1`). El usuario **no** elige motivo en este flujo (AMB-C05).
4. **Rechazo / cierre negativo:** el usuario elige motivo del catálogo con `tipo_cierre = negativo` (HU-101-027).
5. Los indicadores de **presupuestos activos** (dashboard §4.1) cuentan solo **estado 99**.
6. Consultas de presupuestos **activos** listan **estado 99**; consulta de **cerrados** (**estado 98**, conceptual §17.2.1) en grilla aparte (HU-101-016).
7. **Trazabilidad** conversión: `cod_pedido_generado` en cierre + `cod_presupuesto_origen` en pedido nuevo (§15.4 producto).

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
| Tenancy + `EMPRESAS_CONEXION` | **Etapa posterior** | HU-101-003 | `SPEC-001-05`, [SPEC-101-01](SPEC-101-01-backend-base.md) |
| Configuración inicial Generaliddes | **Must** (antes) | HU-GEN-01…05 | `SPEC-001-01`…`05` |
| Carga pedido/presupuesto | **Must** | HU-101-005–HU-101-010 | `SPEC-101-04`, `SPEC-101-10` |
| Edición/eliminación ingresados | **Must** | HU-101-011, HU-101-012 | `SPEC-101-04`, `SPEC-101-05` |
| Conversión presupuesto → pedido | **Must** | HU-101-013 | `SPEC-101-12` |
| Conversión pedido → presupuesto | **Must** | HU-101-024 | `SPEC-101-04` |
| Copiar comprobante | **Must** | HU-101-026 | `SPEC-101-04`, `SPEC-101-10` |
| Tratativas presupuesto | **Should** | HU-101-014 | `SPEC-101-12` |
| Cierre/rechazo → 98 (sin parcial) | **Must** | HU-101-027; conversión HU-101-013 | `SPEC-101-04`, `SPEC-101-12` |
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
| 5 | `SPEC-001-04` | Contexto dedicado parámetros (**pendiente**, AMB-C06) |
| — | `SPEC-001-06`…`09` | Documental; **PDF** consultas → `SPEC-001-06-emision` |

### Fase 1 — `SPEC-101-xx`

Índice completo: [README.md](README.md).

| ID | Archivo | Notas |
|----|---------|--------|
| **SPEC-101-01** | [backend-base](SPEC-101-01-backend-base.md) | **Etapa posterior** (`EMPRESAS_CONEXION`) |
| **SPEC-101-02** | [modelos](SPEC-101-02-modelos.md) | |
| **SPEC-101-03** | [repositories](SPEC-101-03-repositories.md) | |
| **SPEC-101-06** | [seguridad-visibilidad](SPEC-101-06-seguridad-visibilidad.md) | Verificar GEN-02 |
| **SPEC-101-04** | [services-pedidos](SPEC-101-04-services-pedidos.md) | Copia; sin DELETE presupuesto |
| **SPEC-101-05** | [controllers-rest](SPEC-101-05-controllers-rest.md) | |
| **SPEC-101-09** | [frontend-base](SPEC-101-09-frontend-base.md) | Verificar GEN-01 |
| **SPEC-101-10** | [pantalla-carga](SPEC-101-10-pantalla-carga.md) | |
| **SPEC-101-07** | [consultas-api](SPEC-101-07-consultas-api.md) | Excel; no PDF MVP |
| **SPEC-101-11** | [consultas-ui](SPEC-101-11-consultas-ui.md) | |
| **SPEC-101-12** | [tratativas-cierre](SPEC-101-12-tratativas-cierre.md) | Tratativas **Should** |
| **SPEC-101-13** | [mails](SPEC-101-13-mails.md) | |
| **SPEC-101-14** | [dashboard](SPEC-101-14-dashboard.md) | Regla -1 / `MinutosWeb` |
| **SPEC-101-08** | [logs-integracion](SPEC-101-08-logs-integracion.md) | **Should** |
| **SPEC-101-15** | [tests-hardening](SPEC-101-15-tests-hardening.md) | |

## 11. Criterios de aceptación globales (medibles)

- [ ] Tenancy MONO operativo en MVP (stub); fila `desarrollo` en `EMPRESAS_CONEXION` en etapa [SPEC-101-01](SPEC-101-01-backend-base.md).
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

**Dependencias:** SQL Server; ERP; parámetros (**contexto SPEC-001-04**, pendiente); `DiasVentasDetalladas` para historial; `EMPRESAS_CONEXION` (etapa posterior).

**Riesgos:** permisos por atributo; datos ERP vs pruebas; concurrencia descarga ERP.

## 14. Revisión A1 — cierre (2026-06-01)

### 14.1 Resultado

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto** |
| **Parte B (HU)** | **Autorizada** |
| **Slices** | [README.md](README.md) — 15 archivos `SPEC-101-xx-*.md` |

### 14.2 Decisiones humanas — ambigüedades críticas (cerradas)

| ID | Decisión stakeholder |
|----|----------------------|
| **AMB-C01** | Tratativas → **Should** (`SPEC-101-12`) |
| **AMB-C02** | Logs integración → **Should** (`SPEC-101-08`) |
| **AMB-C03** | Presupuestos → **sin DELETE** físico; solo cierre **98** |
| **AMB-C04** | **Incluir copiar comprobante** en MVP (§4, 101-04/10) |
| **AMB-C05** | **No** cierre parcial/positivo; **no** clasificación por renglones |
| **AMB-C06** | Parámetros → **contexto SPEC-001-04 dedicado** (pendiente de redacción) |
| **AMB-C07** | `EMPRESAS_CONEXION` completo → **etapa posterior** ([SPEC-101-01](SPEC-101-01-backend-base.md)) |
| **AMB-C08** | PDF consultas → **futuro** [SPEC-001-06-emision](../001-Generaliddes/SPEC-001-06-emision.md); MVP = Excel |
| **AMB-C09** | KPI y edición **-1**: `fechahora_ultima_actividad + MinutosWeb` vs `fechahora_actual` (HU-101-011, HU-101-025) |
| **AMB-C11** | Pantalla única + botones Grabar pedido / Grabar presupuesto (§10.1 producto) |
| **AMB-C12** | Trazabilidad presupuesto→pedido: cierre + `cod_presupuesto_origen` (sin tabla relación MVP) |
| **AMB-C10** | Un archivo **`SPEC-101-nn-*.md` por slice** (índice README) |

### 14.3 Pendientes no bloqueantes para B

| Tema | Acción |
|------|--------|
| AMB-M02 Mail | Detallar en HU-101-019 + contexto SPEC-001-04 |
| AMB-M03 Cobertura 80 % | Declarar “módulo estable” al cierre release en TR |
| AMB-M07 IVA/redondeo | Detallar en `SPEC-101-04` / HU carga |
| Producto §2 ítems 8/15/18 | Alinear redacción conceptual con decisiones C03/C01/C02 (tarea documental opcional) |

### 14.4 Parte B — cierre (2026-06-01)

| Entregable | Estado |
|------------|--------|
| 27 HU en `docs/03-historias-usuario/101-PedidosWeb/` | Hecho |
| Índice HU README | Hecho |

### 14.5 Parte C — cierre (2026-06-01)

| Entregable | Estado |
|------------|--------|
| 15 TR en `docs/04-tareas/101-PedidosWeb/` | Hecho |
| Índice TR + trazabilidad HU → TR | [README.md](../../04-tareas/101-PedidosWeb/README.md) |
| Contratos API en TR-05, TR-07, TR-14 | Documentados (implementar en D) |
| Matriz permisos | Placeholder §Negocio — completar por slice en D |

**Veredicto parte C:** **Lista para parte D** (implementación por slice §10).

### 14.6 Parte B — detalle histórico (2026-06-01)

- **27 HU** enriquecidas: [README HU 101](../../03-historias-usuario/101-PedidosWeb/README.md).
- **HU-101-026** copiar comprobante; **HU-101-027** cierre/rechazo; **HU-101-014** solo tratativas (Should).
- **HU-101-001/002:** heredadas GEN-02 — TR de verificación en 101-06.

### 14.7 Siguiente paso — parte D

1. Implementar por TR según [índice TR](../../04-tareas/101-PedidosWeb/README.md): **02 → 03 → 06 → 04 → 05 → …**
2. Completar servicio de parámetros SPEC-001-04 en paralelo a TR-04/07/10/14.
3. Actualizar matriz permisos y OpenAPI en el mismo PR de cada slice.

---

## Definición de listo por `SPEC-101-xx`

Cada `SPEC-101-xx` está listo si: alcance cerrado en su archivo slice; código del slice; tests según §12; **OpenAPI según `_NORMAS-TRANSVERSALES-TR.md`**; checklist DoD del slice en [README.md](README.md).
