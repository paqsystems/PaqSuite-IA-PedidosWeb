# TR-SPEC-101-19 — Asistente IA carga: mutaciones (cliente, cabecera, artículos, grabar, imagen)

| Campo | Valor |
|-------|--------|
| **HU relacionadas** | [HU-101-039](../../03-historias-usuario/101-PedidosWeb/HU-101-039-asistente-carga-ia-cliente-cabecera.md) · [HU-101-040](../../03-historias-usuario/101-PedidosWeb/HU-101-040-asistente-carga-ia-articulos-grabar.md) |
| **SPEC relacionada** | [SPEC-101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) |
| **Épica** | 101 — PedidosWeb / Asistente IA en carga |
| **Prioridad** | **Should** |
| **Dependencias** | [TR-SPEC-101-18](TR-SPEC-101-18-asistente-carga-ia-shell.md); TR-SPEC-101-04; TR-SPEC-101-10; HU-101-004…010 |
| **Estado** | Finalizado |
| **Última actualización** | 2026-09-13 (Parte I) |

**Normas:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)  
**Cierre C1:** [F-101-18-20-cierre-c1](F-101-18-20-cierre-c1-asistente-carga-ia.md)

---

## 1) Resumen

Extiende el registry de tools del turno (TR-18) con capacidades **A, B, C, D, I, J, K**. Paridad con UI/services existentes. LLM no es fuente de permisos.

### Out of scope
- Panel/gate/audio UI → TR-18.
- Consultas E–H → TR-20.

---

## 2) AC (mapa)

| Capacidad | AC HU | Notas |
|-----------|-------|-------|
| A | 039 CA-01…05 | Listas 0/1/2–10/>10; perfil C |
| B | 039 CA-06…08 | Modifica*; moneda D1-14 |
| C | 039 CA-09…10, CA-16…18 | Nivel/obs/leyendas + C ampliado D1-23; compuesto D1-25; alias D1-26 |
| I | 039 CA-11…14 | Confirm D1-18 |
| D | 040 CA-01…09, CA-04b, CA-17…21 | Cantidad default 1; art/item/it; mutar detalle D1-24 |
| J | 040 CA-10…13 | = botones grabar |
| K | 040 CA-14…16 | Solo válidos + cabecera ampliada; no grabar |
| CC #10 | — | Tools add/modify/image: cantidad vía helper TR-101-04 |
| **AC-CC10-T-A1** | Tools texto convierten cantidad false/true |
| **AC-CC10-T-A2** | Extracto imagen convierte cantidad |
| **AC-CC13-T-A1** | Leyenda de tool/intent con 61 caracteres se aplica recortada a 60, sin `validationError` |
| **AC-CC13-T-A2** | Leyenda de 60 caracteres se conserva completa |
| **AC-CC13-T-A3** | Extracto imagen normaliza a 60 las leyendas de `applyImageExtract.cabecera` |
| **AC-CC14-T-D1** | `codigo`, `código`, `cod.`/`cod` y `cód.`/`cód` disparan alta de renglón como la familia artículo |
| **AC-CC14-T-D2** | `codigo "texto con espacios" cantidad 10` conserva el texto literal como `q` y extrae cantidad 10 |
| **AC-CC14-T-D3** | Se aceptan comillas dobles, simples y tipográficas |
| **AC-CC14-T-D4** | Los alias anteriores `art.`/`item`/`it` y sus casos con comillas no regresionan |
| **AC-CC14-T-D5** | Los alias `codigo*` también resuelven mutaciones sobre el detalle del borrador |

---

## 3) Tools / actions (contrato)

Todas pasan por `POST .../carga/asistente/turn`. El service registra tools; el LLM elige; el executor valida y arma `actions[]`.

### 3.1 Catálogo de actions (payload)

| `action` | `payload` clave | Efecto FE |
|----------|-----------------|-----------|
| `needsChoice` | `{ kind, options: [{ n, label, code }] }` | Mostrar lista; guardar pending |
| `needsRefine` | `{ kind, hint }` | Mensaje refinar |
| `needsConfirm` | `{ kind: "changeCliente", candidate }` | Esperar sí/confirmo/aceptado |
| `selectCliente` | `{ codCliente, cabeceraInicial }` | setCliente + init cabecera |
| `setCabeceraField` | `{ field, value }` | Update cabecera + side-effects UI |
| `setCabeceraFields` / `patchCabecera` | `{ fields: {…} }` | Patch múltiple (lista+moneda/IVA, etc.) |
| `setCampoLibre` | `{ field: "nivel\|observaciones\|leyendaN\|bonificacionN\|expreso\|…", value }` | Update campo; leyendas recortadas a 60 en executor |
| `addRenglon` | `{ codArticulo, cantidad, precio?, porcBonif?, descripcion? }` | Push renglón + recalc |
| `updateRenglon` | `{ renglon, patch: { cantidad?, precio?, porcBonif? } }` | Patch renglón existente + recalc |
| `removeRenglon` | `{ renglon }` | Quitar renglón del borrador + recalc |
| `clearDraftForClienteChange` | `{}` | Limpiar luego selectCliente |
| `grabarPedido` / `grabarPresupuesto` | `{ body }` o flag `invokeLocalGrabar: true` | Disparar mismo handler botones |
| `applyImageExtract` | `{ cabecera?, renglonesValidos[], errores[] }` | Hidratar parcial; leyendas de cabecera recortadas a 60 |
| `denied` / `validationError` | `{ messageKey, details? }` | Toast/hilo; sin mutar |
| `needsRefine` (renglón) | `{ kind: "renglonExistente", q?, hint }` | Mostrar no encontrado **con `q` buscada** |

### 3.2 Decisiones técnicas (C1)

| ID | Decisión |
|----|----------|
| T-19-01 | Búsqueda clientes: reusar service/repo de `GET /api/v1/clientes` (misma visibilidad) |
| T-19-02 | Tras 1 cliente o elección: reusar `CabeceraInicialService` (mismo que combobox) |
| T-19-03 | Artículos: mismo universo lookup carga; excluir `usa_esc = 'B'` |
| T-19-04 | Cantidad omitida → **1** en executor (no en LLM) |
| T-19-05 | Confirmación I: normalizar texto FE o BE (`si|sí|confirmo|aceptado` case-insensitive ES; i18n maps en locales) |
| T-19-06 | Grabar: FE ejecuta handlers existentes `handleGrabarPedido` / `handleGrabarPresupuesto` cuando action lo indica (evita duplicar validaciones). Alternativa BE: llamar `ComprobanteGrabarService` solo si TR-18 envía draft completo — **preferir FE invoke** (C1) |
| T-19-07 | Imagen K: visión → JSON candidatos → validate each con mismos guards A–D → `applyImageExtract` |
| T-19-08 | `readOnly` / perfil C: executor corta con `denied` antes de mutar |
| T-19-09 | Permisos ERP: leer parámetros carga ya usados en pantalla (`ModificaPrecio*`, etc.) en BE o validar en FE apply — **doble check:** BE en tools de precio; FE respeta disabled state |
| T-19-10 | Mutar renglón: `IntentDetector` → `mutateRenglon`; `ArticuloTool::mutateExistingRenglon` filtra **solo** `draftContext.renglones`; comillas/`extractMutateArticuloQuery`; conjugados elimina/borra…; `pendingChoice.kind=renglonExistente` |
| T-19-11 | Cabecera C: `CargaAsistenteCabeceraTool` + flags `ParametrosCarga` / `resolveModificaFlags` ampliados |
| T-19-12 | Pedido compuesto: `IntentDetector` → `compositePedido` (≥2 segmentos etiquetados); `TurnService::executeCompositeItems`; diferir en `pendingChoice.deferredCompositeItems`; reanudar en `chooseOption`/`confirmChangeCliente` vía `withDeferredWork` |
| T-19-13 | Alias: `ARTICULO_KEYWORD_REGEX` (art/item/it/prod…); cantidad `canti`/`cant`; cabecera `Descto` N→`bonifN`; `Direccion:`→`expresoDire` |
| T-19-14 | Imagen K: prompt + `buildCabeceraStepsFromParsed` (perfil/cond/fecha/expreso/lista/bonif/leyendas/obs) + diferido `cabeceraSteps` |
| T-19-15 | Leyendas: executor PHP de campo libre, patch de cabecera e imagen reutiliza el helper de TR-101-04; no confía en el LLM ni rechaza por longitud |
| T-19-16 | Alias artículo: `ARTICULO_KEYWORD_REGEX`, `articuloKeywordList` y patrones mutate/composite incluyen `codigo`/`código`/`cod*`; formas largas preceden a abreviaturas |
| T-19-17 | Parseo `q`: el tramo entre comillas ubicado entre keyword de artículo/código y cantidad prevalece y se conserva literal, incluyendo comillas simples y tipográficas |

---

## 4) Reglas de listas (compartidas)

```text
matches == 0 → reply + needsRefine/inform
matches == 1 → auto action (selectCliente | addRenglon | setCabeceraField)
2..10 → needsChoice + pendingChoice
>10 → needsRefine (no options)
```

Stock usa `total` (TR-20); clientes/artículos: conteo del resultado filtrado (máx. evaluar 11+ para decidir refine sin listar 11).

---

## 5) Confirmación cambio cliente (I)

Estado `pendingChoice.kind = "changeClienteConfirm"`.

| Input usuario | Resultado |
|---------------|-----------|
| sí, si, confirmo, aceptado (+ i18n) | `clearDraftForClienteChange` + `selectCliente` |
| no, cancelar (+ i18n) | reply cancelado; no mutar |
| otro | re-pedir confirmación; no mutar |

---

## 6) Apply imagen (K)

1. Vision model → candidatos estructurados (schema JSON en system/tool).
2. Validate cliente/artículos/cantidades/permisos.
3. `renglonesValidos` → auto apply; `errores` / dudosos → texto + `needsChoice` si aplica.
4. Nunca emitir `grabar*` desde extracto.

---

## 7) Cambios código (delta sobre TR-18)

### Backend

| Pieza | Detalle |
|-------|---------|
| Tools | `SelectClienteTool`, `SetCabeceraTool`, `SetCampoLibreTool`, `AddRenglonTool`, `ChangeClienteTool`, `GrabarIntentTool`, `ImageExtractTool` |
| Services reuso | Clientes, CabeceraInicial, Articulos lookup, ParametrosCarga, (opcional) Grabar |
| Parseo D | `IntentDetector` / helper: `extractArticuloFrase` (qty, precio, porcBonif, query limpia); familia artículo incluye alias `codigo`/`código`/`cod*`; preferencia por texto literal entre comillas; `setBonificacionRenglon` / sinónimo descuento; `extractMutateArticuloQuery` (comillas / final); conjugados remove |
| Lookup D | Alta: filtro AND tokens + maestro; **2–10 matches → `needsChoice`** (ordenar por cercanía, sin colapsar a uno). Mutate: **solo detalle**; label choice cant·precio·bonif; i18n `renglonNoEncontradoConQ` |
| Permisos D / turno | `ModificaPrecio*` / `ModificaBonArt*` en alta y update; extracto imagen: strip precio/bonif sin permiso. `TurnService` fuerza `perfilUsuario` desde perfil comercial autenticado |
| Cabecera C | Tool set transporte/cond/perfil/lista/fecha/dir/bonif/expreso |
| Tests | Unit por tool: permiso denied, listas, cantidad default 1, parse “N unidades…”, confirm I, `elimina el articulo arroz` → mutate remove q=arroz |

### Frontend

| Pieza | Detalle |
|-------|---------|
| `applyCargaAsistenteActions` | Implementar tabla §3.1 |
| Recalc | `renglonesCarga.ts` igual carga manual |
| Grabar | Bridge a handlers toolbar existentes |
| i18n | `carga.asistente.confirm.*`, errores tools |

---

## 8) Plan de tareas

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T19-1 | BE | Tools A/B/C + tests | CA 039 |
| T19-2 | BE | Tools D + default qty 1 + precio guards | CA 040 D |
| T19-3 | BE | Tool I confirm + ImageExtract | CA I/K |
| T19-4 | FE | applyActions completo + recalc | Sync UI |
| T19-5 | FE | Bridge grabar J | CA J |
| T19-6 | Test | Feature turn con mock LLM tool calls; E2E cliente+artículo | |

**Orden:** tras T18-2 estable; T19-1→2→3; T19-4/5 paralelo; T19-6.

---

## 9) Tests

| Caso | Esperado |
|------|----------|
| 3 clientes | `needsChoice` length 3 |
| 11+ clientes | `needsRefine` |
| add sin qty | cantidad 1 |
| “10 unidades del artículo 1001” | qty=10, q≈1001 (no tokens unidad) |
| 0 matches | reply `articulosNone` (i18n) |
| >10 matches | reply `articulosRefine` (distinto a none) |
| “descuento 5” / “bonificación 5” | `porcBonif` con permiso |
| precio sin permiso | `denied` |
| `elimina el articulo arroz` (2 en detalle) | `needsChoice` kind `renglonExistente` (no alta maestro) |
| `cambiar cantidad a 5 del articulo ABC` | update cantidad 5, q=ABC |
| `cambiar cantidad del articulo "almendra" a 150` | q=almendra, cantidad 150 |
| 0 match mutate | reply con `q` buscada |
| changeCliente + "confirmo" | clear + select |
| image mixed | solo válidos en state |
| multilínea Cliente+Perfil+…+art | `compositePedido` + N actions |
| `Descto 3: 4` | `setCampoLibre` field=bonif3 |
| `Direccion: calle` | field=expresoDire |
| `art.` / `item` / `it` + cant | `addRenglon` |
| `codigo` / `cod.` / `cód` + cant | `addRenglon` con el mismo extract |
| `codigo "texto con espacios" cantidad 10` | q literal `texto con espacios`, cantidad 10 |
| alias `codigo*` en eliminar/cambiar | lookup solo sobre renglones del borrador |
| leyenda de 61 por tool o imagen | action aplicada con 60 caracteres, sin error |

---

## 10) Checklist normas

- [ ] Sin endpoints nuevos obligatorios (reusa turn TR-18)
- [ ] Si se exponen helpers internos, documentar OpenAPI solo el turn
- [ ] Envelope en errores de tools vía `respuesta` i18n
- [ ] No corpus documental en prompts de mutación

---

## Veredicto C1

**Apto para D1** (después o en paralelo controlado con TR-18). Ver cierre C1.

## CC PQ #10 — Parte I 31/08/2026

Tools mutación: conversión cantidad compartida con carga manual.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | `addRenglon` / modify / image apply → helper TR-101-04 | `CargaAsistenteTools` |
| T2 | PHPUnit false/true + default equiv 1 | `CargaAsistenteToolsTest` |

Unificación delta CC PQ #10 incorporada previamente en esta TR base.

## CC PQ #13 — Parte I 13/09/2026

El executor del asistente normaliza las cinco leyendas a 60 caracteres en campo libre, patch de cabecera y extracto de imagen. El recorte es backend obligatorio y no genera rechazo por longitud.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Recorte en executor/patch de cabecera | tools de mutación del asistente |
| T2 | Recorte en `applyImageExtract` | executor de imagen |
| T3 | Casos 60/61 sin `validationError` | tests unitarios |

Unificación delta CC PQ #13 (archivo `TR-SPEC-101-19-asistente-carga-ia-mutaciones-update.md` eliminado en Parte I 2026-09-13).

## CC PQ #14 — Parte I 13/09/2026

El detector incorpora la familia `codigo`/`código`/`cod*` para altas y mutaciones, y preserva como consulta literal el contenido entre comillas antes de la cantidad.

| ID | Tarea | Evidencia |
|----|-------|-----------|
| T1 | Extender regex y lista de keywords | `CargaAsistenteIntentDetector` |
| T2 | Priorizar tramo entre comillas | parseo `q` de alta/mutate/composite |
| T3 | Regresión de alias, comillas y cantidad | `CargaAsistenteIntentDetectorTest` |

Unificación delta CC PQ #14 (archivo `TR-SPEC-101-19-asistente-carga-ia-mutaciones-update-01.md` eliminado en Parte I 2026-09-13).
