# SPEC-101-20 — Asistente IA en carga: consultas conversacionales

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Producto** | [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) |
| **Estado** | A1+B1+C1 cerrados — autoriza Parte D1 |
| **Prioridad épica** | Should |
| **Última actualización** | 2026-07-13 |
| **Revisión A1** | [F-101-18-20-cierre-a1-asistente-carga-ia.md](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) |
| **Slices relacionados** | [SPEC-101-18](SPEC-101-18-asistente-carga-ia-shell.md) (canal) · [SPEC-101-19](SPEC-101-19-asistente-carga-ia-mutaciones.md) (mutaciones) |
| **Capacidades producto** | **E** stock · **F** deuda · **G** cheques · **H** historial ventas |

## Objetivo

Definir las **consultas informativas** que el asistente de carga puede responder en el hilo conversacional, reutilizando las APIs y reglas de las consultas PedidosWeb existentes, sin abandonar la pantalla de carga.

## Fuentes

| Fuente | Rol |
|--------|-----|
| [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) | §§10–11 |
| [consulta-stock.md](../../02-producto/PedidosWeb/consulta-stock.md) | Mapping exacto E |
| [consulta-deuda.md](../../02-producto/PedidosWeb/consulta-deuda.md) | F |
| [consulta-cheques.md](../../02-producto/PedidosWeb/consulta-cheques.md) | G |
| [consulta-historial-ventas.md](../../02-producto/PedidosWeb/consulta-historial-ventas.md) | H |
| [SPEC-101-07-consultas-api.md](SPEC-101-07-consultas-api.md) | APIs consultas |
| [SPEC-101-11-consultas-ui.md](SPEC-101-11-consultas-ui.md) | Presentación / TZ de referencia |
| [SPEC-101-18-asistente-carga-ia-shell.md](SPEC-101-18-asistente-carga-ia-shell.md) | Pipeline, gate LLM, tope 10 |

## Principio rector

Las consultas son **solo lectura**. No mutan el borrador salvo que el usuario dispare después una intención de SPEC-101-19. En F/G las fechas del chat se muestran **solo como fecha** (`YYYY-MM-DD`, sin horario).

## Decisiones cerradas en A1 (2026-07-13)

| ID | Tema | Decisión |
|----|------|----------|
| D1-07 | Contador stock `>10` | Usar **`total`** (o conteo equivalente) de matches del filtro `q`; si `total > 10` → refine **sin** listar. |
| D1-08 | Paths API G/H | `GET /api/v1/consultas/cheques` · `GET /api/v1/consultas/historial-ventas` (+ filtro cliente en proceso). |
| D1-11 | F–H con más de 10 filas | Mostrar hasta 10 + indicar `total`; sugerir consulta de menú o criterio más acotado si la API lo permite. |
| D1-12 | LLM vs tool directo | Intención puede resolverse por LLM→tool o clasificador; **datos siempre** desde API (nunca inventados). Detalle en TR. |
| D1-19 | Columnas chat G (cheques) | **Nro** · **Fecha** · **Importe**. |
| D1-20 | Columnas chat H (historial) | **Descripción artículo** · **Cantidad** · **Precio unitario neto** · **Importe**. |
| D1-21 | Columnas chat F (deuda) | **tipo/nro** · **fecha** · **vencimiento** · **saldo** (confirmado producto). |
| D1-23 | Totales pie F/G | Si hay **más de un ítem** listado → pie con suma de `saldo` (deuda) o `importe` (cheques) de las filas mostradas. |
| D1-24 | Fechas chat F/G | Solo fecha (`YYYY-MM-DD`); sin componente horario. |
| D1-25 | Presentación tabular chat | F–H (y E) se muestran como **tabla HTML** alineada en el panel (`data-testid=cargaAsistenteIaConsultaTable`); no como texto con `|` y fuente proporcional. |

## Alcance (in scope)

### E — Stock por descripción / código

| Regla | Detalle |
|-------|---------|
| API | `GET /api/v1/consultas/stock` (`StockConsultaService`) |
| Filtro | `q` — `LIKE` sobre `cod_articulo` y `descripcion` |
| Permiso | Mismo que consulta stock (`Permiso_Repo` / `pw_consultastock`) |
| >10 matches | Si `total > 10` → **No** listar; pedir **refinar la búsqueda** |
| 1–10 matches | Listar con mapping exacto (`total` ≤ 10) |

#### Mapping de respuesta (obligatorio)

| Etiqueta chat (ES ref.) | JSON | Fórmula / origen |
|-------------------------|------|------------------|
| Código | `codArticulo` | stock |
| Descripción | `descripcion` | artículos |
| Stock (real) | `stock` | stock |
| Comprometido | `comprometido` | stock |
| Comprometido web | `comprometidoWeb` | detalle pedidos `estado = 0` |
| Disponible neto | `disponibleNeto` | `stock − comprometido − comprometidoWeb` |
| Código base | `codBase` | `articulos.base` o null |
| Stock base | `stockBase` | §5 consulta-stock o null |
| Comprometido base | `comprometidoBase` | §5 o null |
| Comprometido base web | `comprometidoBaseWeb` | §5 o null |
| Disponible neto base | `disponibleNetoBase` | §5 o null |

**Totales al pie (MVP):** suma de `stock`, `comprometido`, `comprometidoWeb`, `disponibleNeto` de las filas listadas. Métricas `*Base` se muestran por fila si no son null; **no** se totalizan en el pie salvo decisión TR explícita.

Decimales: **2**.

### F — Deuda del cliente en proceso

| Regla | Detalle |
|-------|---------|
| Precondición | Cliente seleccionado en carga; si no → guiar a selección (101-19 A) |
| API | `GET /api/v1/consultas/deuda` (filtro cliente en proceso + visibilidad) |
| Permiso | Igual consulta deuda |
| Presentación | Resumen + máx. **10** filas; si hay más, indicar total y pedir refinar / ver consulta. Columnas chat (D1-21): **tipo/nro** · **fecha** · **vencimiento** · **saldo**. Si **>1** fila listada → pie con suma de **saldo** |
| Fechas | Solo `YYYY-MM-DD` (sin horario) |

### G — Cheques en cartera del cliente en proceso

| Regla | Detalle |
|-------|---------|
| Precondición | Cliente en proceso |
| API | `GET /api/v1/consultas/cheques` (filtro cliente en proceso + visibilidad) |
| Permiso | Igual consulta cheques |
| Presentación | Máx. 10 filas (+ `total` si hay más). Columnas chat (D1-19): **nro** · **fecha** · **importe**. Si **>1** fila listada → pie con suma de **importe**. Fechas solo `YYYY-MM-DD` |

### H — Historial de ventas del cliente en proceso

| Regla | Detalle |
|-------|---------|
| Precondición | Cliente en proceso |
| API | `GET /api/v1/consultas/historial-ventas` (`DiasVentasDetalladas` y reglas producto; filtro cliente) |
| Permiso | Igual consulta historial |
| Presentación | Máx. 10 filas (+ `total` si hay más). Columnas chat (D1-20): **descripción artículo** · **cantidad** · **precio unitario neto** · **importe**; fechas/TZ = UI actual |

## Fuera de alcance

- Mutar cabecera/renglones/grabar → SPEC-101-19.
- Panel UX / audio / BYOK → SPEC-101-18.
- Pivot / export Excel desde el chat.
- Inventar saldos o stock sin API.

## Dependencias

- SPEC-101-18, SPEC-101-07, SPEC-101-06 (visibilidad F–H), SPEC-101-11 (formato fechas).

## Criterios de aceptación medibles

- [ ] **CA-E01:** Stock con propiedades de consulta-stock; ≤10 + totales por artículo; >10 refine.
- [ ] **CA-E02:** Sin permiso stock → mensaje; sin datos inventados.
- [ ] **CA-F01:** Sin cliente → pide selección; con cliente → deuda API; fechas sin hora; >1 ítem → total saldo.
- [ ] **CA-G01:** Cheques del cliente en proceso; permisos y tope 10; fechas sin hora; >1 ítem → total importe.
- [ ] **CA-H01:** Historial del cliente en proceso; permisos y tope 10.

## HU / TR

| Tipo | IDs |
|------|-----|
| HU | [HU-101-041](../../03-historias-usuario/101-PedidosWeb/HU-101-041-asistente-carga-ia-consulta-stock.md) · [HU-101-042](../../03-historias-usuario/101-PedidosWeb/HU-101-042-asistente-carga-ia-consultas-cliente.md) |
| TR | [TR-SPEC-101-20](../../04-tareas/101-PedidosWeb/TR-SPEC-101-20-asistente-carga-ia-consultas.md) · C1 [F-cierre](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-c1-asistente-carga-ia.md) |

## Definición de listo

- [x] A1 cerrado
- [x] HU del slice (041, 042)
- [x] Parte F + F1 (2026-07-13) — [F-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-formal.md)
- [ ] Paridad mapping stock con tests o fixture de contrato (endurecimiento)
- [x] Smoke con cliente en curso para F–H (sesión producto post-smoke)

## Revisión A1 — cierre (2026-07-13)

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Detalle** | [F-101-18-20-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) |

Observaciones no bloqueantes (TR): nombres de campos JSON exactos para D1-19/20/21.

## Historial

| Fecha | Resumen |
|-------|---------|
| 2026-07-13 | Alta desde definición de producto asistente IA carga |
| 2026-07-13 | A1 cerrado — Apto con observaciones; autoriza Parte B |
| 2026-07-13 | Parte B/B1 — HU-101-041, HU-101-042 |
| 2026-07-13 | Parte C/C1 — TR-SPEC-101-20; apto D1 |
| 2026-07-13 | Post-smoke: D1-23…25 fechas sin hora, totales F/G, tabla HTML en panel |
| 2026-07-13 | Parte F cerrada — [F-101-18-20-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-formal.md) |
