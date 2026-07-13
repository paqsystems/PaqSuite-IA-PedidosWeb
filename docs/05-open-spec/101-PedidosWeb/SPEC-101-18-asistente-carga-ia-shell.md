# SPEC-101-18 — Asistente IA en carga: shell, canal y entradas

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Producto** | [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) |
| **Estado** | A1+B1+C1 cerrados — autoriza Parte D1 |
| **Prioridad épica** | Should (extensión post-MVP carga) |
| **Última actualización** | 2026-07-13 |
| **Revisión A1** | [F-101-18-20-cierre-a1-asistente-carga-ia.md](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) |
| **Slices relacionados** | [SPEC-101-19](SPEC-101-19-asistente-carga-ia-mutaciones.md) (mutaciones) · [SPEC-101-20](SPEC-101-20-asistente-carga-ia-consultas.md) (consultas) |
| **Capacidades producto** | UX panel, **M** (config), **L** (audio), **K** (imágenes — entrada y extracción hacia mutaciones) |

## Objetivo

Definir el **canal conversacional operativo** embebido en la pantalla de carga de pedidos/presupuestos: ubicación UI, sincronización con el formulario, reuso del BYOK del Asistente IA, gate sin configuración, entradas por **texto**, **audio** e **imágenes**, y contrato de orquestación hacia las acciones de negocio (SPEC-101-19 / 101-20).

## Fuentes

| Fuente | Rol |
|--------|-----|
| [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) | Definición de producto (§1–5, §14–16) |
| [SPEC-101-10-pantalla-carga.md](SPEC-101-10-pantalla-carga.md) | Pantalla host |
| [pantalla-carga-comprobante-ui.md](../../02-producto/PedidosWeb/pantalla-carga-comprobante-ui.md) | Contrato UI carga |
| [SPEC-001-10-chat-asistente-ia.md](../001-Generaliddes/SPEC-001-10-chat-asistente-ia.md) | BYOK, Preferencias, visión, límites de imágenes |
| [SPEC-101-17-mobile-capacitor-pedidosweb.md](SPEC-101-17-mobile-capacitor-pedidosweb.md) | Rama native / pie de carga mobile |
| Manual [Chat-Asistente-IA.md](../../99-manual-usuario/Chat-Asistente-IA.md) | Distinción chat documental vs operativo |

## Decisiones humanas (cerradas en producto)

| Tema | Decisión |
|------|----------|
| Ubicación del chat | **Pie del formulario** de `/pedidos/carga` (no nueva pestaña del chat documental) |
| Proveedor LLM | **El mismo BYOK** que Asistente IA / Preferencias (`pq_asistente_ia_*`) |
| Sin LLM configurado | Respuesta **fija** i18n; no invocar modelo ni mutar |
| Audio | Dictado → texto → misma pipeline (L) |
| Imágenes | **Incluidas en MVP** (K); adjunto desde el panel |
| Chat documental | Coexiste; **no** opera la carga |

## Decisiones cerradas en A1 (2026-07-13)

| ID | Tema | Decisión |
|----|------|----------|
| D1-01 | Orquestación | API **dedicada** a carga (p. ej. `POST /api/v1/pedidos/carga/asistente/*`); **no** mezclar corpus del chat documental. Reusa solo catálogo/credenciales BYOK. |
| D1-02 | Multi-config BYOK | Misma resolución de credencial activa/preferida que el Chat Asistente IA al enviar mensaje. |
| D1-03 | Persistencia hilo | Hilo **efímero** por sesión de pantalla (se pierde al salir/cancelar); sin historial en BD. |
| D1-04 | Pending lista numerada | Nueva intención de búsqueda/acción **cancela** la elección pendiente (alineado a «reformular»). |
| D1-15 | Audio (STT) | **Web Speech API** del navegador → texto → misma pipeline. Sin STT del proveedor en MVP. |
| D1-16 | Altura hilo expandido | **Mínimo 270px**; altura efectiva `max(270px, 33vh)`; scroll interno + auto-scroll al último mensaje. |
| D1-17 | Auditoría | **Log de aplicación** Laravel (canal default); no tabla BD en MVP. Campos: usuario, timestamp, modalidad, intención, acción, resultado. |

## Alcance (in scope)

### 1. Panel UI en pantalla de carga

| Aspecto | Regla |
|---------|-------|
| Host | `PedidosCargaPage` / vista mobile de carga equivalente |
| Ubicación | Debajo de observaciones / totales / toolbar de grabación |
| Comportamiento | Bloque **colapsable/expandible**; default **colapsado**. Expandido: hilo con **mín. 270px** / hasta **33vh** (D1-16) + scroll interno |
| Toolbar | Texto + enviar + **micrófono** + **adjuntar imagen** + **ruedita** Preferencias |
| `data-testid` | `cargaAsistenteIaPanel`, `cargaAsistenteIaInput`, `cargaAsistenteIaSend`, `cargaAsistenteIaMic`, `cargaAsistenteIaAttach`, `cargaAsistenteIaConfig` (nombres finales en TR) |
| i18n | Todas las cadenas visibles vía locale activo; replies del turn (`carga.asistente.reply.*` y equivalentes) se **resuelven** en el panel — nunca mostrar la clave cruda al usuario |
| Controles | DevExtreme cuando exista equivalente razonable |
| Modo Ver / solo lectura | Consultas (101-20) permitidas según permiso; mutaciones (101-19) rechazadas igual que UI |
| Mobile | Pie o sheet/drawer inferior **sin salir** del flujo de carga; respetar exclusiones mobile del producto |

### 2. Configuración LLM (capacidad M)

- Ruedita → misma ruta de **Preferencias → Asistente IA** que el chat documental.
- Sin segundo catálogo ni credenciales propias de “carga”.
- Visión (`supports_vision`) condiciona K. Audio: **solo Web Speech** (D1-15); no STT del proveedor en MVP.

### 3. Gate sin configuración válida

Ante **cualquier** entrada (texto, audio, imagen), si no hay configuración LLM habilitada:

1. No llamar al proveedor.
2. No ejecutar acciones de mutación ni consultas vía LLM.
3. Responder mensaje fijo (ES referencia): *Debe configurar primero el proveedor LLM. Ir a **Asistente IA** (Preferencias).*
4. CTA/enlace a Preferencias (misma destino que la ruedita).

Validar gate en **cliente y servidor**.

### 4. Pipeline de intenciones (orquestación)

```text
[entrada texto | audio→texto | imagen→extracto]
        → gate LLM
        → interpretar intención (LLM + tools/acciones)
        → ejecutar acción autorizada (101-19 / 101-20)
        → sincronizar estado UI de carga
        → respuesta conversacional (listas numeradas, errores, confirmaciones)
```

Reglas transversales de listas:

- Matches **2–10** → lista numerada; usuario elige por número.
- Matches **>10** → no listar; pedir **refinar búsqueda**.
- Match **1** → auto-selección.
- Match **0** → informar y pedir otro criterio.

### 5. Audio (capacidad L)

| Paso | Regla |
|------|-------|
| Captura | Micrófono en toolbar vía **Web Speech**; permiso denegado → mensaje; no mutar |
| Resultado | Texto que alimenta la misma pipeline que el chat escrito |
| Fallo transcripción | Mensaje claro; sin cambios al borrador |

### 6. Imágenes — entrada (capacidad K, parte canal)

| Regla | Detalle |
|-------|---------|
| Adjunto | Desde el panel (no otro menú) |
| Límites | Alinear a SPEC-001-10 (p. ej. hasta 4 imágenes / tamaño máx.) |
| Sin visión | Informar; pedir texto o audio |
| Persistencia adjuntos | No persistir imágenes en BD del portal (igual principio Asistente IA) |
| Efecto | Extracto candidato → validación → hidratación de **borrador** vía acciones 101-19; **grabación** solo con intención J |

Detalle de validación/carga de campos: SPEC-101-19.

### 7. Sincronización con el formulario

Toda mutación exitosa debe reflejarse en el estado visible de carga (cliente, cabecera, renglones, totales) sin requerir F5. En mobile, mismo borrador de sesión.

### 8. Seguridad y auditoría (canal)

- Backend revalida permisos; no confiar solo en el LLM (anti prompt-injection).
- Auditoría mínima en **log de aplicación** (D1-17): usuario, timestamp, modalidad (`texto` \| `audio` \| `imagen`), intención, acción, resultado. Canal Laravel default (`LOG_CHANNEL`, típicamente `storage/logs/laravel.log`).

## Fuera de alcance (este slice)

- Lógica de negocio de selección cliente/artículos/grabar → **SPEC-101-19**.
- Consultas stock/deuda/cheques/historial → **SPEC-101-20**.
- Reemplazar chat documental SPEC-001-10.
- Nuevo proveedor LLM o tablas de credenciales propias.

## Dependencias

- SPEC-101-10, SPEC-101-09, SPEC-001-10, SPEC-101-17 (mobile).
- Tablas BYOK vigentes: `pq_asistente_ia_proveedores`, `pq_asistente_ia_credenciales`.

## HU / TR

| Tipo | IDs |
|------|-----|
| HU | [HU-101-037](../../03-historias-usuario/101-PedidosWeb/HU-101-037-asistente-carga-ia-panel-gate.md) · [HU-101-038](../../03-historias-usuario/101-PedidosWeb/HU-101-038-asistente-carga-ia-audio-imagen.md) |
| TR | [TR-SPEC-101-18](../../04-tareas/101-PedidosWeb/TR-SPEC-101-18-asistente-carga-ia-shell.md) · C1 [F-cierre](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-c1-asistente-carga-ia.md) |

## Criterios de aceptación medibles

- [ ] **CA-UX01:** Panel al pie de carga; ruedita abre Preferencias / Asistente IA.
- [ ] **CA-UX02:** Sin LLM, cualquier prompt → mensaje fijo; sin mutación.
- [ ] **CA-L01:** Audio → texto → misma pipeline que texto.
- [ ] **CA-K-IN01:** Adjunto imagen con visión habilitada inicia extracto; sin visión → mensaje.
- [ ] **CA-SYNC01:** Acción de mutación exitosa (stub/integración 101-19) actualiza UI de carga.

## Definición de listo

- [x] A1 cerrado sobre este SPEC
- [x] HU del slice generadas (037, 038)
- [x] TR del slice generadas + C1 apto
- [x] i18n 5 locales + `data-testid` estables
- [x] Gate BYOK cubierto en test (Feature `Turn rejects when configuration is missing`)
- [x] Parte F + OpenAPI turn (2026-07-13) — [F-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-formal.md)

## Revisión A1 — cierre (2026-07-13)

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Detalle** | [F-101-18-20-cierre-a1](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-a1-asistente-carga-ia.md) |

Observaciones no bloqueantes (TR): CSS del hilo `min-height: 16.875rem` (270px) y `height/max-height: max(270px, 33vh)`.

## Historial

| Fecha | Resumen |
|-------|---------|
| 2026-07-13 | Alta desde definición de producto asistente IA carga |
| 2026-07-13 | A1 cerrado — Apto con observaciones; autoriza Parte B |
| 2026-07-13 | Parte B/B1 — HU-101-037, HU-101-038 |
| 2026-07-13 | Parte C/C1 — TR-SPEC-101-18; apto D1 |
| 2026-07-13 | Post-smoke: D1-16 altura 270px/33vh confirmada en CSS; sin scroll de página al responder |
| 2026-07-13 | Parte F cerrada — [F-101-18-20-cierre-formal](../../04-tareas/101-PedidosWeb/F-101-18-20-cierre-formal.md); OpenAPI `pedidosCargaAsistenteTurn` |
