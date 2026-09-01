# SPEC-101-04 — Services (pedidos y presupuestos)

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | En revisión |
| **Prioridad épica** | Must |
| **Última actualización** | 2026-08-31 |

## Objetivo

Reglas de negocio en services: CRUD, conversiones, copia, totales/IVA, auditoría liviana.

## In scope

- Crear / editar / **eliminar físico** solo **pedido estado 0**
- Crear / editar presupuesto **estado 99** — **sin DELETE** de presupuesto (solo cierre → 98, §5.3 madre)
- Cerrar/rechazar presupuesto **99 → 98** + `presupuestos_cierres` (sin **cierre parcial/positivo** ni clasificación por renglones — decisión AMB-C05)
- Convertir presupuesto → pedido (presupuesto 98, pedido nuevo 0); `id_motivo` desde parámetro **`CodMotivoCierreExitoso`** (SPEC-001-04, HU-101-013)
- Convertir pedido → presupuesto (§5.1 madre)
- **Copiar comprobante** como base de uno nuevo (AMB-C04); precios según parámetro **`ActualizarPrecioCopia`** (CC PQ #9): conservar origen (`false`, default) o actualizar desde lista vigente (`true`), con validación `ArticulosPrecioCero` / `ArticulosSinPrecio`
- Cálculo totales; IVA en cabecera y renglón
- Auditoría: usuario/fecha creación y última modificación
- Transición **-1** en edición pedido; **`fechahora_inicio_proceso`** (auditoría) y **`fechahora_ultima_actividad`** (vigencia bloqueo con **`MinutosWeb`** — HU-101-011)
- Grabación desde pantalla única: acciones **grabar pedido** / **grabar presupuesto** (matriz §10.1 producto)
- **Sync leyendas cliente** al grabar (CC PQ #12): si `ClienteLeyendaN = true` y la leyenda N quedó **dirty en la sesión** (valor distinto al snapshot al abrir/inicializar cabecera), actualizar `pq_pedidosweb_clientes.leyenda_N`. Si no se modificó en la sesión, **no** tocar el maestro aunque el texto del comprobante difiera del maestro actual (escenario pedido 100 vs 200).
- Trazabilidad conversión: **`cod_presupuesto_origen`** en pedido; **`cod_pedido_generado`** en `presupuestos_cierres`
- **Persistencia dual cantidad (CC PQ #10):** grabación, edición, copia e hidratación de borradores persisten y recalculan **`cantidad`** + **`cantidad_venta`** según regla canónica SPEC-101-10; importes siempre desde `cantidad`. Validadores/mappers aceptan entrada según `CargaUnidadesVenta`; lectura `equivalencia_ventas` del artículo (fallback 1). Copia de comprobante: preservar ambos campos del origen. Helper de conversión compartido (Excel, asistente IA). No recalcular históricos al cambiar el parámetro (solo ingreso/edición nueva).

## Fuera de scope

- Controllers REST (101-05)
- UI carga (101-10)
- Tratativas (101-12, Should)
- Mail (101-13)

## Dependencias

- SPEC-101-02, SPEC-101-03
- Lectura parámetros: **contexto SPEC-001-04** (pendiente; defaults temporales documentados allí)

## HU relacionadas

HU-101-005…012, HU-101-013, HU-101-024, copia (trazar en B), auditoría transversal

## Definición de listo

- [ ] Reglas §5.1 y §5.3 cubiertas en tests unitarios
- [ ] Cobertura services ≥ 70 % (§12 madre)
- [ ] Sin DELETE presupuesto
- [x] Copia paramétrica `ActualizarPrecioCopia` (CC PQ #9) — ver HU-101-026 / TR-SPEC-101-04
- [x] CC PQ #12: sync leyendas dirty de sesión (HU-101-009/010/011)
- [x] CC PQ #10: persistencia dual `cantidad` / `cantidad_venta` + helper conversión

## Historial CC PQ #9 (02/07/2026) — Parte I 02/07/2026

Extensión `ComprobanteCopiaService::copiarBorrador` con lectura `ActualizarPrecioCopia`, lookup lista precios, validación granular precio cero/sin precio y recálculo importes. Unificación delta CC PQ #9 (archivo `*-update` eliminado en Parte I). Evidencia: [F-CC-PQ-9-cierre-formal](../../04-tareas/101-PedidosWeb/F-CC-PQ-9-cierre-formal.md).

## Historial CC PQ #12 (28/08/2026) — Parte I 30/08/2026

Al grabar pedido o presupuesto, `PedidoService` sincroniza leyendas 1–5 del maestro cliente solo si el parámetro `ClienteLeyendaN` está activo y el frontend envía dirty de sesión (`leyendas_dirty`). Unificación `SPEC-101-04-services-pedidos-update-01`. Evidencia: [F-CC-PQ-12-cierre-formal](../../04-tareas/101-PedidosWeb/F-CC-PQ-12-cierre-formal.md).

## Historial CC PQ #10 (30/07/2026) — Parte I 31/08/2026

Persistencia dual `cantidad`/`cantidad_venta` según `CargaUnidadesVenta`; helper compartido para pantalla, Excel y asistente. Unificación `SPEC-101-04-services-pedidos-update`. Evidencia: CC PQ #10 / HU-101-006-update.
