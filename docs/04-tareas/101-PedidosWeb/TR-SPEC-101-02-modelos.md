# TR-SPEC-101-02 — Modelos Eloquent (capa de datos)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | Transversal — [HU-101-005](../../03-historias-usuario/101-PedidosWeb/HU-101-005-inicializacion-cabecera.md) … [HU-101-010](../../03-historias-usuario/101-PedidosWeb/HU-101-010-grabar-presupuesto.md), [HU-101-011](../../03-historias-usuario/101-PedidosWeb/HU-101-011-editar-pedido.md), [HU-101-012](../../03-historias-usuario/101-PedidosWeb/HU-101-012-eliminar-pedido.md), [HU-101-013](../../03-historias-usuario/101-PedidosWeb/HU-101-013-conversion-presupuesto-pedido.md), [HU-101-024](../../03-historias-usuario/101-PedidosWeb/HU-101-024-conversion-pedido-presupuesto.md), [HU-101-026](../../03-historias-usuario/101-PedidosWeb/HU-101-026-copiar-comprobante.md), [HU-101-027](../../03-historias-usuario/101-PedidosWeb/HU-101-027-cierre-rechazo-presupuesto.md) |
| **SPEC relacionada** | [SPEC-101-02-modelos](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | Stub tenant operativo ([SPEC-101-01](../../05-open-spec/101-PedidosWeb/SPEC-101-01-backend-base.md) — etapa posterior AMB-C07); [PedidosWeb_Modelo_Datos_Final.md](../../02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md) |
| **Estado** | Finalizado (Parte I CC PQ #10/#11) |
| **Última actualización** | 2026-08-31 |

**Origen:** [SPEC-101-02](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md), [PedidosWeb_SPEC_MVP.md](../../05-open-spec/101-PedidosWeb/PedidosWeb_SPEC_MVP.md) §7  
**Referencia SPEC:** [SPEC-101-02-modelos](../../05-open-spec/101-PedidosWeb/SPEC-101-02-modelos.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md) (**obligatorio** — sin endpoints en este slice; envelope no aplica en capa modelo)

---

## 1) HU Refinada (resumen)

### Título
Modelos Eloquent para tablas operativas, maestras ERP y tablas nuevas MVP del tenant PedidosWeb.

### Narrativa
Como **equipo backend**,  
quiero **mapear las tablas `pq_pedidosweb_*` en modelos Eloquent con PK, relaciones y casts correctos**,  
para **que repositories y services consuman una capa de persistencia tipada sin duplicar reglas de negocio en los modelos**.

### In scope / Out of scope

**In scope:**

- Modelos para `pq_pedidosweb_pedidoscabecera` y `pq_pedidosweb_pedidosdetalle` (pedido y presupuesto comparten cabecera).
- Maestras comerciales: clientes, clientesde, vendedores, artículos, **escalas (cabecera/detalle)**, stock, listas, listaprecios_articulos, descuentocantidad, condventa, transportes, perfil, provincias.
- Tablas consulta ERP: cheques, deuda, ventadetallada (y resumencuenta si aplica consultas).
- Tablas nuevas MVP: tratativas, tratativas_resultados, motivos_cierre, presupuestos_cierres, logs_integracion.
- Campos de auditoría y bloqueo en cabecera: `usuario_creacion`, `fecha_creacion`, `usuario_modificacion`, `fechahora_inicio_proceso`, **`fechahora_ultima_actividad`**, **`cod_presupuesto_origen`**, `cod_pedido_origen`, `origen_comprobante`, `nro_visible`.
- `$table`, `$primaryKey` / claves compuestas, `$fillable` o `$guarded` acotado, `$casts` (decimal, datetime, bit).
- Relaciones Eloquent mínimas (`hasMany`, `belongsTo`) documentadas en código.
- Conexión tenant (`pq_pedidosweb_{cliente}`) vía middleware existente; modelos sin lógica de negocio.

**Out of scope:**

- Services, validaciones de estado, cálculo de totales/IVA, transiciones -1/0/98/99.
- Repositories (TR-SPEC-101-03).
- Migraciones que alteren tablas heredadas del ERP sin acuerdo explícito.
- Controllers REST (TR-SPEC-101-05).
- Modelos asistente IA (`asistente_ia_*`) salvo que otro slice los requiera antes — documentar como opcional según modelo datos §7.7–7.8.

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: Existe modelo `PedidoCabecera` (tabla `pq_pedidosweb_pedidoscabecera`) con PK `cod_pedido`, casts de `estado` (int), totales (decimal), fechas y campos **`fechahora_ultima_actividad`**, **`cod_presupuesto_origen`** mapeados.
- **AC-02**: Existe modelo `PedidoDetalle` con clave compuesta `cod_pedido` + `renglon`; `cantidad` cast a decimal.
- **AC-03**: Modelos maestros listados en SPEC-101-02 registrados con nombres de tabla/columna **exactos** al script ERP.
- **AC-04**: Modelos `MotivoCierre`, `PresupuestoCierre`, `Tratativa`, `TratativaResultado`, `LogIntegracion` alineados a §7 del modelo datos.
- **AC-05**: Relación `PedidoCabecera hasMany PedidoDetalle`; relaciones a `Cliente`, `Vendedor`, `CondicionVenta`, `Transporte` donde existan FK lógicas.
- **AC-06**: **Ningún** modelo contiene métodos de negocio (cálculo totales, conversión estado, validación MinutosWeb).
- **AC-07**: Tests smoke (factory o seed mínimo) confirman lectura/escritura en tenant `desarrollo` para cabecera+detalle.
- **AC-08**: Claves compuestas resueltas con trait/repository pattern acordado (no forzar `save()` estándar sin documentar limitación).
- **AC-CC12-T-M1:** Columna `stockeable` presente en `pq_pedidosweb_articulos` del tenant.
- **AC-CC12-T-M2:** Default `true` (1) para filas existentes; modelo `Articulo` con cast `boolean`.
- **AC-CC10-T-M1:** Columnas `equivalencia_ventas` (artículos) y `cantidad_venta` (detalle) existen en esquema target.
- **AC-CC10-T-M2:** Backfill `cantidad_venta = cantidad` en filas existentes.
- **AC-CC11-T-M1:** Tabla `pq_pedidosweb_clientescontactos` con UNIQUE (`cod_client`, `cod_contacto`).
- **AC-CC11-T-M2:** Modelo `PqPedidoswebClienteContacto` + `PqPedidoswebCliente::contactos()` hasMany; sin lógica de negocio en modelo.
- **AC-CC11-T-M3:** DDL idempotente CREATE si falta; sin DROP de tablas existentes.

### Escenarios Gherkin

```gherkin
Feature: Modelos Eloquent PedidosWeb

  Scenario: Cabecera expone campos de trazabilidad y bloqueo
    Given el modelo PedidoCabecera configurado
    When se inspecciona fillable/casts
    Then incluye cod_presupuesto_origen y fechahora_ultima_actividad como datetime nullable

  Scenario: Detalle respeta clave compuesta
    Given un pedido con dos renglones
    When se cargan detalles por cod_pedido
    Then cada renglon tiene PK compuesta cod_pedido + renglon

  Scenario: Sin reglas de negocio en modelo
    Given cualquier modelo del slice 101-02
    When se revisan métodos públicos custom
    Then no existen métodos de cálculo de totales ni transición de estado
```

---

## 3) Reglas de Negocio

> **Nota:** Las reglas de negocio **no** se implementan en modelos. Esta sección fija **mapeo y convenciones** para capas superiores.

1. **RN-01**: Tabla y nombres de columna idénticos al ERP; sin renombrar en BD.
2. **RN-02**: `estado` en cabecera discrimina pedido (-1, 0, 1, 2) vs presupuesto (98, 99); el modelo no interpreta transiciones.
3. **RN-03**: `fechahora_inicio_proceso` = auditoría inicio edición **-1**; **`fechahora_ultima_actividad`** = vigencia bloqueo con `MinutosWeb` (HU-101-011).
4. **RN-04**: **`cod_presupuesto_origen`** en pedido nuevo tras conversión; **`cod_pedido_generado`** vive en `presupuestos_cierres`, no duplicar como columna obligatoria en cabecera presupuesto.
5. **RN-05**: Timestamps Laravel (`created_at`/`updated_at`) solo donde la tabla los tenga; cabecera ERP usa `fecha_creacion` / `fecha_modif` según modelo datos.
6. **RN-06**: Modelos de tablas nuevas MVP pueden usar `$timestamps` si el DDL lo define (tratativas, logs).
7. **RN-07 (CC PQ #12):** `pq_pedidosweb_articulos.stockeable` — `bit NOT NULL DEFAULT 1`; modelo `Articulo` expone propiedad `stockeable` (cast boolean); consumido por stock/consultas y API artículos (TR-101-07, TR-101-10).
8. **RN-08 (CC PQ #10):** `pq_pedidosweb_articulos.equivalencia_ventas` decimal NOT NULL default 1 (o nullable + default runtime 1); `pq_pedidosweb_pedidosdetalle.cantidad_venta` decimal; backfill = `cantidad`; casts en modelos `Articulo` y `PedidoDetalle`.
9. **RN-09 (CC PQ #11):** Tabla `pq_pedidosweb_clientescontactos` (`id` IDENTITY PK, `cod_client`, `cod_contacto`, `nombre`, `telefono`, `mail`, UNIQUE compuesta); relación `Cliente hasMany contactos`; lectura API vía misma vía Eloquent que GET `/clientes` (excepción SP documentada, alineada TR-GEN-02).

---

## 4) Impacto en Datos

### Tablas afectadas

| Modelo sugerido | Tabla SQL | PK / notas |
|-----------------|-----------|------------|
| `PedidoCabecera` | `pq_pedidosweb_pedidoscabecera` | `cod_pedido` |
| `PedidoDetalle` | `pq_pedidosweb_pedidosdetalle` | compuesta `cod_pedido`, `renglon` |
| `Cliente` | `pq_pedidosweb_clientes` | `cod_client` |
| `ClienteDireccionEntrega` | `pq_pedidosweb_clientesde` | compuesta |
| `Vendedor` | `pq_pedidosweb_vendedores` | `cod_vended` |
| `Articulo` | `pq_pedidosweb_articulos` | `codigo`; columna **`stockeable`** bit NOT NULL DEFAULT 1 |
| `EscalasCabecera` | `pq_pedidosweb_escalas_cabecera` | `cod_escala` |
| `EscalasDetalle` | `pq_pedidosweb_escalas_detalle` | compuesta `cod_escala`, `cod_valor` |
| `Stock` | `pq_pedidosweb_stock` | `cod_articulo` |
| `ListaPrecios` | `pq_pedidosweb_listaprecios` | `cod_lista` |
| `ListaPreciosArticulo` | `pq_pedidosweb_listaprecios_articulos` | compuesta |
| `DescuentoCantidad` | `pq_pedidosweb_descuentocantidad` | compuesta |
| `CondicionVenta` | `pq_pedidosweb_condventa` | `codigo` |
| `Transporte` | `pq_pedidosweb_transportes` | `codigo` |
| `Perfil` | `pq_pedidosweb_perfil` | `cod_perfil` |
| `Provincia` | `pq_pedidosweb_provincias` | `cod_provin` |
| `Cheque` | `pq_pedidosweb_cheques` | compuesta |
| `Deuda` | `pq_pedidosweb_deuda` | compuesta |
| `VentaDetallada` | `pq_pedidosweb_ventadetallada` | según script |
| `Tratativa` | `pq_pedidosweb_tratativas` | `id_tratativa` |
| `TratativaResultado` | `pq_pedidosweb_tratativas_resultados` | `id_resultado` |
| `MotivoCierre` | `pq_pedidosweb_motivos_cierre` | `id_motivo` |
| `PresupuestoCierre` | `pq_pedidosweb_presupuestos_cierres` | `id_cierre` |
| `LogIntegracion` | `pq_pedidosweb_logs_integracion` | `id_log` |

### Seed mínimo para tests

- 1 cliente + 1 vendedor + 2 artículos con stock y precio en lista.
- 1 cabecera pedido `estado=0` + 2 renglones.
- 1 cabecera presupuesto `estado=99` + 1 renglón.
- Catálogo `motivos_cierre`: al menos un motivo `tipo_cierre=positivo` activo y uno `negativo` activo (para TR 101-04).
- Parámetro `CodMotivoCierreExitoso` documentado en seed de parámetros (referencia SPEC-001-04).

### DDL / alteraciones previas (coordinación)

Según [PedidosWeb_Modelo_Datos_Final.md](../../02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md) §10:

1. `pedidosdetalle.cantidad` → decimal si aplica.
2. Campos auditoría + `fechahora_*` + trazabilidad en cabecera si no existen en tenant.
3. Creación tablas §7 (tratativas, motivos, cierres, logs).
4. **CC PQ #12:** `ALTER` idempotente `pq_pedidosweb_articulos.stockeable` bit NOT NULL DEFAULT 1 (`alter-pq-pedidosweb-stockeable.sql`); bootstrap schema dev si aplica.
5. **CC PQ #10:** `equivalencia_ventas` en artículos; `cantidad_venta` en detalle + backfill; bootstrap schema dev sin DROP.
6. **CC PQ #11:** CREATE idempotente `pq_pedidosweb_clientescontactos`; modelo + relación; bootstrap/ensure-schema si aplica.

---

## 5) Contratos de API y OpenAPI

> **Este slice no expone endpoints HTTP.** La sección documenta la **ausencia** deliberada y la dependencia hacia TR-SPEC-101-05.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| — | — | — | — | — |

**Sin filas:** implementación exclusivamente en `app/Models/PedidosWeb/` (o namespace acordado del módulo).

### 5.2 Detalle por operación

No aplica. Los consumidores son repositories (TR-101-03) y services (TR-101-04).

### 5.3 Actualización matriz permisos

- [ ] N/A — sin endpoints en este slice

---

## 6) Cambios Frontend

### Pantallas / componentes

- **Ninguno** en este slice. El frontend consumirá DTOs vía API en TR-101-05 / pantalla carga TR-101-10.

### data-testid sugeridos

- N/A

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Infra | Verificar DDL tenant `desarrollo` vs modelo datos §10 | Campos críticos presentes o script de alteración acordado |
| T2 | Backend | `PedidoCabecera` + `PedidoDetalle` + relaciones | AC-01, AC-02, AC-05 |
| T3 | Backend | Maestros comerciales (lote 1: cliente, artículo, stock, listas) | AC-03 |
| T4 | Backend | Maestros comerciales (lote 2: vendedor, condición, transporte, perfil) | AC-03 |
| T5 | Backend | Tablas nuevas MVP (motivos, cierres, tratativas, logs) | AC-04 |
| T6 | Backend | Tablas consulta ERP (cheques, deuda, ventadetallada) | AC-03 |
| T7 | Tests | Smoke integración modelos en conexión tenant | AC-07 |
| T8 | Docs | README interno módulo: convención claves compuestas | AC-08 documentado |

---

## 8) Estrategia de Tests

- **Unit:** opcional — tests de casts/fillable en modelos puros (bajo valor; priorizar smoke).
- **Integration:** insert/select cabecera+detalle en tenant test; relación `hasMany` carga N renglones.
- **E2E:** no aplica en slice 101-02.

---

## 9) Riesgos y Edge Cases

- **Claves compuestas:** Eloquent no soporta PK compuesta nativamente — riesgo de `save()` incorrecto en detalle; mitigar con repositories (TR-101-03).
- **Tipos SQL Server:** `bit`, `decimal(15,2)`, `datetime` vs timezone app — definir casts y pruebas de redondeo en TR-101-04, no aquí.
- **Tablas ausentes en tenant legacy:** fallo en deploy — checklist DDL previo (T1).
- **Mezcla pedido/presupuesto en una tabla:** documentar en modelo que `estado` define semántica; evitar scopes con reglas de negocio (usar query objects en repository si hace falta filtro técnico `whereIn estado`).

---

## 10) Checklist final

### Checklist del slice

- [ ] AC cumplidos
- [ ] Modelos sin lógica de negocio
- [ ] Campos `fechahora_ultima_actividad` y `cod_presupuesto_origen` mapeados
- [ ] Smoke integración en tenant desarrollo

### Checklist normas transversales

- [ ] Endpoints nuevos/modificados con policy en código — **N/A**
- [ ] Matriz endpoint ↔ permiso actualizada — **N/A**
- [ ] OpenAPI en /api/documentation coherente — **N/A**
- [ ] 401/403 documentados — **N/A**
- [ ] Envelope JSON respetado — **N/A**
- [ ] X-Paq-Cliente documentado — **N/A**
- [ ] Tests API incluyen 401/403 — **N/A**
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend

- `backend/app/Models/PedidosWeb/PedidoCabecera.php`
- `backend/app/Models/PedidosWeb/PedidoDetalle.php`
- `backend/app/Models/PedidosWeb/*.php` (maestros y tablas nuevas)
- `backend/database/seeders/PedidosWebModelSmokeSeeder.php` (opcional)

### Frontend

- —

### OpenAPI

- —

### Docs

- Actualizar checklist DDL en TR-101-03 si alteraciones pendientes

## CC PQ #12 — Parte I 30/08/2026

Columna `stockeable` en maestro artículos: DDL idempotente, modelo Eloquent y documentación modelo datos §3.4.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | `ALTER` idempotente `stockeable` | `alter-pq-pedidosweb-stockeable.sql` |
| T2 | Modelo `Articulo` + cast | `PqPedidoswebArticulo.php` |
| T3 | Bootstrap schema dev | `PedidosWebDevSchemaBootstrap` (si aplica) |

Unificación delta CC PQ #12 update-02 (archivo `TR-SPEC-101-02-modelos-update-02.md` eliminado en Parte I).

## CC PQ #10 — Parte I 31/08/2026

Columnas unidades venta en artículos y detalle pedido.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | DDL `equivalencia_ventas` + `cantidad_venta` | migración/SQL idempotente |
| T2 | Modelos + casts | `PqPedidoswebArticulo`, `PqPedidoswebPedidoDetalle` |
| T3 | Backfill `cantidad_venta` | script deploy |

Unificación delta CC PQ #10 (archivo `TR-SPEC-101-02-modelos-update.md` eliminado en Parte I).

## CC PQ #11 — Parte I 31/08/2026

Tabla y modelo contactos de cliente.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | DDL `pq_pedidosweb_clientescontactos` | SQL idempotente |
| T2 | Modelo + `hasMany` | `PqPedidoswebClienteContacto.php` |
| T3 | Bootstrap dev CREATE si falta | `PedidosWebDevSchemaBootstrap` (si aplica) |

Unificación delta CC PQ #11 (archivo `TR-SPEC-101-02-modelos-update-01.md` eliminado en Parte I).
