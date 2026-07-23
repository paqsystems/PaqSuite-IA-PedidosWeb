# SPEC-101-19 — Asistente IA en carga: mutaciones del comprobante

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Producto** | [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) |
| **Estado** | A1+B1+C1 cerrados — autoriza Parte D1 |
| **Prioridad épica** | Should |
| **Última actualización** | 2026-07-14 |
| **Revisión A1** | [F-101-18-20-cierre-a1-asistente-carga-ia.md](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) |
| **Slices relacionados** | [SPEC-101-18](SPEC-101-18-asistente-carga-ia-shell.md) (canal) · [SPEC-101-20](SPEC-101-20-asistente-carga-ia-consultas.md) (consultas) |
| **Capacidades producto** | **A** cliente · **B** cabecera lookups · **C** campos libres · **D** artículos · **I** cambio cliente · **J** grabar · **K** (aplicación de extracto validado) |

## Objetivo

Definir las **acciones de negocio** que el asistente de carga puede ejecutar sobre el **borrador** de pedido/presupuesto y la **grabación**, con la misma semántica que la UI DevExtreme y los services existentes (SPEC-101-04 / SPEC-101-10).

## Fuentes

| Fuente | Rol |
|--------|-----|
| [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) | §§6–9, 12–14 |
| [SPEC-101-10-pantalla-carga.md](SPEC-101-10-pantalla-carga.md) | Permisos precio/lista/bonif., matriz grabación |
| [pantalla-carga-comprobante-ui.md](../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) | Inicialización cabecera, renglones, diálogo cambio cliente |
| [SPEC-101-04-services-pedidos.md](SPEC-101-04-services-pedidos.md) | Servicios grabación / cabecera / totales |
| [SPEC-101-18-asistente-carga-ia-shell.md](SPEC-101-18-asistente-carga-ia-shell.md) | Pipeline, listas ≤10, gate LLM |

## Principio rector

Cada acción del asistente **equivale** a la misma operación que el usuario haría en pantalla (mismos endpoints/services, mismos guards, mismos mensajes). El LLM **no** es fuente de verdad de permisos ni de maestros.

## Decisiones cerradas en A1 (2026-07-13)

| ID | Tema | Decisión |
|----|------|----------|
| D1-05 | Extracto imagen (K) | Aplicar **automáticamente** solo candidatos **válidos**; dudosos/inválidos → lista/errores (producto §14). |
| D1-06 | Cantidad omitida al agregar | Si falta cantidad → **asumir 1** y agregar (confirmado producto A1+). |
| D1-13 | Precio / acción sobre “último renglón” | Un solo renglón → ese; varios → lista numerada (**código — descripción · cant · precio · bonif%**). |
| D1-14 | Moneda vía IA | Solo si la UI de carga permite editar moneda para el perfil; si no → `denied`. |
| D1-18 | Frases confirmación (I) | Aceptar (ES, case-insensitive): `sí` / `si`, `confirmo`, `aceptado`. Rechazo: `no`, `cancelar` (y equivalentes i18n en TR). |
| D1-19 | Parseo frase alta artículo | Extraer qty (`N unidades` / `cantidad N`), precio y bonif/descuento **antes** del texto de búsqueda; no mezclar esos tokens en el `q` de lookup. |
| D1-20 | Sinónimos descuento / bonif. línea | `descuento` / `bonificación` / `bonif` en frase de renglón → mismo campo `porcBonif` (con permiso `ModificaBonArt*`). |
| D1-21 | Mensajes 0 vs >10 artículos | Textos i18n **distintos** (`articulosNone` vs `articulosRefine`); no reutilizar “no encontrado” cuando hay demasiados matches. |
| D1-22 | Búsqueda descripción multi-palabra | Match AND por tokens significativos sobre descripción (además de código exacto / LIKE código). |
| D1-23 | Cabecera C ampliada | Bonif. 1–3, expreso/dir. expreso, transporte, cond. venta, perfil, lista precios, fecha entrega, dirección entrega vía chat + mismos `Modifica*` que UI. |
| D1-24 | Mutar renglón existente | Busca en **detalle del borrador** (no maestro). Comillas o descripción al **final**. 0 → informar `q` buscada. 2–10 → lista D1-13. Conjugados: elimina/borra/quita/saca (+ infinitivos). |
| D1-25 | Pedido compuesto multilínea | Si el mensaje tiene ≥2 líneas con etiquetas de carga, intent `compositePedido`: parse línea a línea (A–D), aplicar en orden; ante `needsChoice`/`needsConfirm`, diferir resto en `deferredCompositeItems` y continuar tras la respuesta. |
| D1-26 | Alias renglón / cabecera | Prefijos renglón: artículo(s), art., art, producto(s), prod., item(s), it., it. Cantidad: `cantidad`/`canti`/`cant`. Cabecera: `Descto`/`Descuento`/`Desc`/`Dto` + 1\|2\|3 → `bonifN`. `Direccion:` suelta → `expresoDire`. |

## Alcance (in scope)

### A — Selección de cliente

| Regla | Detalle |
|-------|---------|
| Búsqueda | Código y/o razón social / nombre / fantasía; visibilidad = `GET /api/v1/clientes` |
| 0 / 1 / 2–10 / >10 | Informar / auto-seleccionar / lista numerada / **refinar** |
| Tras selección | Misma **inicialización de cabecera** que el combobox de carga (`CabeceraInicial` / equivalente) |
| Perfil **cliente** | Cliente fijo de sesión; no ofrecer elegir otro |
| Modo solo lectura | Rechazar mutación |

### B — Cabecera con lookups

Campos ejemplo: perfil, condición de venta, transporte, dirección entrega, lista de precios, bonificaciones de cabecera editables, moneda si aplica.

| Regla | Detalle |
|-------|---------|
| Permisos | Verificar parámetros ERP (`ModificaListaPrec*`, `ModificaCondVta*`, `ModificaDirEntr*`, `ModificaExpreso*`, `ModificaBonCli*`, etc.) y perfil V/S/C |
| Sin permiso | Mensaje; no mutar |
| Ambiguo | Lista numerada (máx. 10; si más → refinar) |
| Efectos | Igual UI (p. ej. cambio de lista → recálculo precios renglones) |

### C — Campos libres

| Atributo | Regla |
|----------|--------|
| Nivel | Respetar `NivelExtremo` (0/100 si aplica) |
| Observaciones | Texto libre |
| Leyenda 1…5 | Usuario indica atributo + valor |
| Bonif. cabecera 1/2/3 | Aplicar directo; permiso `ModificaBonCli*`; perfil **C** denied; bonif3 ∈ [-99.99, 99.99]; alias Descto/Descuento N (D1-26) |
| Expreso / dirección expreso | Texto libre; permiso `ModificaExpreso*`; solo lectura → denied (turn); `Direccion:` suelta → expresoDire (D1-26) |
| Transporte | Lookup catálogo → `codTranspor`; 0 none / 1 apply / 2–10 lista / >10 refine |
| Condición de venta | Lookup → `codCondvta`; permiso `ModificaCondVta*` |
| Perfil | Lookup → `codPerfil` |
| Lista de precios | Lookup → `listaPrecios`(+ moneda/IVA); permiso `ModificaListaPrec*` |
| Fecha de entrega | Parse fecha → `fechaEntrega` (ISO date) |
| Dirección de entrega | Lookup por cliente → `idDe`+`direccionEntrega`; permiso `ModificaDirEntr*` |

Confirmar valor aplicado. Rechazar en solo lectura.

### D — Artículos y renglones

| Regla | Detalle |
|-------|---------|
| Universo | Mismo lookup de carga; excluir `usa_esc = 'B'`; lista de precios válida cuando aplique precio; **2–10 candidatos por tokens → lista** (no auto-pick del más corto) |
| Parseo frase | Qty / precio / bonif-descuento fuera del texto de búsqueda (D1-19, D1-20); prefijos art/item/it y `canti` (D1-26) |
| Pedido compuesto | Multilínea con etiquetas → aplicar todas las acciones permitidas en orden; diferir tras choice (D1-25) |
| Búsqueda | Código o descripción; multi-palabra → AND por tokens (D1-22) |
| 0 / 1 / 2–10 / >10 | None i18n / auto-add / lista / refine i18n **distinto** (D1-21) |
| Cantidad | `> 0`; bonificación inicial como UI (maestro / descuento por cantidad) salvo % pedido. Si el usuario **no** indica cantidad → **asumir 1** |
| Precio / bonif. línea | Solo con `ModificaPrecio*` / `ModificaBonArt*`; perfil **C** nunca; sinónimo descuento↔bonif. Alta/update con override sin permiso → `denied`. Extracto imagen: strip. Perfil efectivo desde usuario autenticado. |
| Eliminar / modificar existente | **Solo detalle** del borrador (D1-24). Match por código/desc/"último" o comillas. 0 → i18n con `q` buscada. >1 → lista cant·precio·bonif (elegir n). Conjugados elimina/borra… ≠ alta maestro. |
| Duplicados | Un código de artículo por comprobante (misma regla UI) |
| Importes | Recalcular con lógica vigente (`CalculoTotales` / `renglonesCarga`) |
| UI reply | Claves `carga.asistente.reply.*` / `pedidos.carga.asistente.*` resueltas a locale en el panel (no mostrar la clave cruda) |

### I — Cambio de cliente con comprobante iniciado

Si hay cliente y/o renglones/cabecera y se pide otro cliente:

1. Advertir pérdida de datos (mismo espíritu diálogo UI).
2. Confirmación explícita. Aceptar (ES): **sí/si**, **confirmo**, **aceptado** (D1-18); rechazar: **no**, **cancelar** (+ i18n).
3. Solo entonces limpiar + aplicar A.
4. Sin confirmar → no cambiar.

### J — Grabar pedido / grabar presupuesto

| Regla | Detalle |
|-------|---------|
| Equivalencia | Mismos botones toolbar (`Grabar pedido` / `Grabar presupuesto`) |
| Validaciones | Cliente + servidor actuales |
| Errores | Misma lista que modal de errores de grabación |
| Éxito | Mismo post-grabación (número/GUID, carga recurrente, mail 101-13) |
| Bloqueos | Respetar `NOmodificaPedido`, estado no editable, etc. |

### K — Aplicación de extracto de imagen (MVP)

Tras lectura/visión en SPEC-101-18:

1. Validar candidatos contra maestros y permisos (A–D).
2. Hidratar **automáticamente** en el borrador **solo** lo validado (sin confirmación global), incluyendo cabecera ampliada cuando el extracto la traiga (perfil, cond., fecha, expreso, lista, bonif 1–3, leyendas 1–5, observaciones) con los mismos `Modifica*`.
3. Dudoso/inválido → lista numerada o errores; no forzar.
4. **No** grabar hasta intención J.
5. Si hay choice de cliente/catálogo, diferir cabecera/renglones restantes (mismo espíritu D1-25).

## Fuera de alcance

- UI del panel, audio, gate BYOK → SPEC-101-18.
- Consultas stock/deuda/cheques/historial → SPEC-101-20.
- ABM maestros; excepciones comerciales fuera de parámetros.

## Dependencias

- SPEC-101-18 (obligatoria para canal).
- SPEC-101-04, SPEC-101-05, SPEC-101-10, SPEC-101-06 (visibilidad).

## Contrato de acciones (orientativo para TR)

Cada acción es un comando tipado ejecutado en backend (o orquestado FE→APIs existentes) con:

| Campo | Uso |
|-------|-----|
| `action` | p. ej. `selectCliente`, `setCabeceraField`, `addRenglon`, `confirmChangeCliente`, `grabarPedido`, `grabarPresupuesto` |
| `payload` | Datos ya resueltos (códigos, cantidades) — no texto crudo del LLM como única entrada al service |
| `resultado` | `ok` \| `needsChoice` (lista 1–10) \| `needsRefine` \| `needsConfirm` \| `denied` \| `validationError` |

## Criterios de aceptación medibles

- [ ] **CA-A01:** 2–10 clientes → lista numerada; 1 → init cabecera; >10 → refinar.
- [ ] **CA-B01:** Cambio cabecera sin permiso rechazado.
- [ ] **CA-C01:** Leyenda N / nivel / observaciones asignan solo ese campo.
- [ ] **CA-C02:** Pedido multilínea (D1-25) aplica cabecera+renglones permitidos; choice intermedia no pierde el resto.
- [ ] **CA-D01:** Artículo ≤10 lista; >10 refine ≠ “no encontrado”; parse “N unidades…”; prefijos art/item/it + canti (D1-26); precio/bonif solo con permiso; descuento≡bonif línea; renglón con cantidad (default 1).
- [ ] **CA-D02:** Eliminar/modificar sobre detalle; comillas o desc. al final; `elimina el artículo X` no busca maestro; 0 muestra q; 2+ lista elige n.
- [ ] **CA-I01:** Cambio cliente con datos pide confirmación.
- [ ] **CA-J01:** Grabar pedido/presupuesto = mismo flujo botones.
- [ ] **CA-K01:** Extracto imagen válido hidrata borrador; inválido no se carga.

## HU / TR

| Tipo | IDs |
|------|-----|
| HU | [HU-101-039](../../03-historias-usuario/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera.md) · [HU-101-040](../../03-historias-usuario/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) |
| TR | [TR-SPEC-101-19](../../04-tareas/101-PedidosWeb/TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) · C1 [F-cierre](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-c1-asistente-carga-ia.md) |

## Definición de listo

- [x] A1 cerrado
- [x] HU del slice (039, 040)
- [x] Parte F + F1 (2026-07-13; **rev. 2026-07-14** D1-25/26) — OBS-F-04 mobile pendiente
- [x] Tests de permisos y listas numeradas (tools — mutate/denied/perfil C)
- [x] Tests IntentDetector: composite + alias art/item/it + Descto/Direccion
- [x] Paridad con pantalla-carga verificada en smoke manual (sesión producto)

## Revisión A1 — cierre (2026-07-13)

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Detalle** | [F-101-18-20-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) |

Observaciones no bloqueantes (TR): equivalentes i18n de D1-18 en en/pt/fr/it; facade única vs N llamadas FE.

## Historial

| Fecha | Resumen |
|-------|---------|
| 2026-07-13 | Alta desde definición de producto asistente IA carga |
| 2026-07-13 | A1 cerrado — Apto con observaciones; autoriza Parte B |
| 2026-07-13 | Parte B/B1 — HU-101-039, HU-101-040 |
| 2026-07-13 | Parte C/C1 — TR-SPEC-101-19; apto D1 |
| 2026-07-13 | Post-smoke: D1-23 cabecera C ampliada; D1-24 mutar renglón en detalle + comillas/final + conjugados |
| 2026-07-13 | Parte F cerrada — [F-101-18-20-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-formal.md) |
| 2026-07-14 | D1-25 pedido compuesto multilínea + diferidos; D1-26 alias art/item/it/canti/Descto/Direccion; imagen K cabecera ampliada; F1/F rev. |
