# Patrón reusable — Asistente operativo embebido (texto / voz / imagen)

| Campo | Valor |
|-------|--------|
| **Tipo** | Definición de patrón transversal PaqSuite / MONO |
| **Versión** | 2026-07-15 |
| **Ejemplo de implementación** | PedidosWeb — carga de pedidos/presupuestos ([fuente de producto](../PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md)) |
| **Manual de usuario (ejemplo)** | [PedidosWeb-asistente-carga-ia.md](../../99-manual-usuario/PedidosWeb-asistente-carga-ia.md) |
| **Distinción obligatoria** | Chat **documental** (ayuda por manuals) ≠ asistente **operativo** (muta el dominio) |

---

## 1. Propósito

Definir un contrato reusable para embeber un **asistente de IA operativo** en un proceso de negocio concreto (wizard, carga, bandeja de atención, ABM guiado, etc.), de modo que el usuario pueda:

- dar instrucciones por **texto**, **voz** y/o **imagen**;
- obtener **acciones reales** sobre el contexto abierto (mismo backend / mismas reglas que la UI);
- resolver ambigüedades con **listas numeradas**;
- reutilizar la **configuración LLM BYOK** ya existente del portal.

Este patrón sirve de base para productos como:

| Dominio ejemplo | Entidad / proceso anfitrión | Acciones típicas del asistente |
|-----------------|-----------------------------|--------------------------------|
| PedidosWeb / Tango carga comprobantes | Pantalla de carga | Cliente, cabecera, renglones, consultas, grabar |
| Partes de atención | Alta / edición de parte | Cliente/contacto, motivo, prioridad, adjuntos, cierre |
| Novedades web | Editor de novedad | Título, cuerpo, vigencia, audiencia, publicar |
| Stock / logística | Movimientos o remitos | Artículo, depósito, cantidad, validar, confirmar |
| Cobranza / tesorería | Carga de recibo | Cliente, medios, imputaciones |

---

## 2. No objetivos (fijos del patrón)

1. **No** reemplazar la UI principal: el asistente es un **canal paralelo** embebido (pie, sheet o drawer del mismo proceso).
2. **No** fusionar con el chat documental global: ese responde “cómo se usa”; este **ejecuta** sobre el borrador/entidad abierta.
3. **No** inventar privilegios: toda mutación revalida permisos en servidor.
4. **No** exigir un segundo catálogo LLM: **misma BYOK** (Preferencias / Asistente IA).
5. **No** grabar “a ciegas” sin pasar por validaciones del dominio anfitrión.

---

## 3. Principios de diseño (obligatorios)

| # | Principio | Norma |
|---|-----------|--------|
| P1 | Misma fuente de verdad | Cada acción = equivalente a un gesto de UI (mismo endpoint / servicio) |
| P2 | Ambiguo → lista 1…N | Máx. 10 visibles; >10 → pedir refinar; nunca elegir en silencio |
| P3 | 1 match → auto-aplicar | Igual espíritu que auto-select de combobox |
| P4 | Permisos primero | Sin permiso → mensaje y no mutar |
| P5 | Contexto anfitrión | Consultas usan la entidad/cliente/caso **en proceso** |
| P6 | Confirmación destructiva | Cambios que vacían o reemplazan datos exigen confirmación explícita |
| P7 | Auditoría | Log: usuario, timestamp, modalidad, intención, acción, resultado |
| P8 | UI sincronizada | Lo aplicado se refleja en la pantalla abierta |
| P9 | Sin LLM → respuesta fija | No invocar modelo ni simular acciones |
| P10 | Entrada composta | Un mensaje puede traer **varios** pasos; aplicar en orden; diferir si hay choice |
| P11 | Gate de precondición | Si falta un requisito duro (ej. cliente / caso / depósito), **no** aplicar el resto del mismo turno |

---

## 4. Arquitectura lógica

```
┌──────────────────────────────────────────────┐
│  Proceso anfitrión (UI DevExtreme / kardex)  │
│  ┌────────────────────────────────────────┐  │
│  │ Panel asistente operativo (pie)        │  │
│  │  hilo · composer · mic · imagen · LLM  │  │
│  └───────────────┬────────────────────────┘  │
└──────────────────┼───────────────────────────┘
                   │ turn(message, modality, context, credentialId, images?)
                   ▼
┌──────────────────────────────────────────────┐
│  TurnService (backend producto)              │
│  1. Gate config LLM operativa                │
│  2. IntentDetector (reglas / keywords)       │
│  3. Tools de dominio (Cliente, Ítem, …)      │
│  4. Acciones tipadas → envelope API          │
│  5. pendingChoice + deferred work            │
└──────────────────┬───────────────────────────┘
                   │
      ┌────────────┼────────────┐
      ▼            ▼            ▼
  BYOK LLM     Lookups/ERP   Validadores
  (visión,     (cartera,     del dominio
   extract)     maestros)
```

### Capas

| Capa | Responsabilidad |
|------|-----------------|
| UI panel | Entrada, dictado continuo, adjuntos, selector credencial, pintar `actions` |
| Draft context | Snapshot del borrador abierto (ids, cabecera, líneas, flags readOnly) |
| Intent detector | Texto → intención(es); soporta **compuesto multilínea y monolínea** por keywords |
| Tools | Búsquedas y mutaciones del dominio, con permisos |
| Continuaciones | Tras `1…N` o confirmación, reanudar `deferred*` |

LLM puede usarse para:

- extracto estructurado de **imagen**;
- ayuda lingüística opcional;

pero **no** como autoridad de permisos ni de identidad de maestros.

---

## 5. Capacidad UX mínima del panel

| Elemento | Requisito |
|----------|-----------|
| Ubicación | Pie del proceso anfitrión (o sheet mobile anclado) |
| Expandir / contraer | Sí; hilo con altura mínima usable y scroll interno |
| Texto + enviar | Sí |
| Dictado | Web Speech (u STT acordado); **continuo hasta Detener** |
| Imagen | Opcional según dominio; gate `supports_vision` |
| Selector LLM | Combo de credenciales BYOK del usuario |
| Preferencias | Enlace/ruedita a la misma config del chat documental |
| Gate sin config | Mensaje fijo + CTA Preferencias |
| `data-testid` | Estables por producto (no acoplar a DOM interno DevExtreme) |
| i18n | Todo texto visible |

---

## 6. Contrato de turno (API conceptual)

Entrada típica:

```json
{
  "message": "string",
  "modality": "texto | audio | imagen",
  "credentialId": 123,
  "draftContext": { "...snapshot del proceso..." },
  "pendingChoice": { "kind": "...", "options": [], "deferred...": [] },
  "images": [{ "fileName": "", "mimeType": "", "content": "base64..." }]
}
```

Salida típica:

```json
{
  "replyText": "string | i18n.key",
  "actions": [
    { "action": "selectEntity|addLine|setField|needsChoice|needsRefine|noop|...", "payload": {}, "resultado": "ok|needsChoice|..." }
  ],
  "pendingChoice": null,
  "configurationRequired": false
}
```

El frontend **aplica** `actions` al estado/UI; no interpreta lenguaje de negocio por su cuenta.

---

## 7. Detector de intenciones — reglas reusables

### 7.1 Modalidades

| Modalidad | Tratamiento |
|-----------|-------------|
| texto | Pipeline directa |
| audio | STT → texto → misma pipeline (etiquetar `modality=audio` en auditoría) |
| imagen | Visión → JSON de dominio → tools; diferir si hay choice de entidad raíz |

### 7.2 Mensaje compuesto

1. Preferir partición por **saltos de línea** si el pegado es etiquetado.
2. Si queda una sola línea (dictado), particionar por **keywords** del dominio (entidad raíz, ítems, campos de cabecera).
3. `detectSingle` por segmento → lista de intenciones.
4. Ejecutar en orden.
5. Ante `pendingChoice`, guardar el resto en `deferredCompositeItems` (o equivalente) y continuar tras la respuesta.

### 7.3 Gate de precondición (ej. cliente)

Parametrizar una **entidad raíz** del proceso (`rootEntity`):

- Si el turno compuesto / imagen **pide** resolver la entidad raíz y falla o queda en choice → **no** aplicar mutaciones subordinadas en ese turno.
- Si ya está resuelta en `draftContext`, sí aplicar subordinadas.

### 7.4 Fallbacks de dictado en búsquedas nominales

Cuando la búsqueda textual de una entidad da 0 matches, antes de “no encontrado” probar variantes tipográficas locales (mínimo recomendado ES-AR):

- intercambio **B ↔ V**;
- terminación **e ↔ i**.

No sustituye la lista numerada cuando hay 2+ matches reales.

---

## 8. Catálogo de intenciones (plantilla)

Adaptar nombres al dominio; mantener la semántica:

| Familia | Ejemplos genéricos | Notas |
|---------|--------------------|--------|
| Raíz | `selectRoot` / `changeRoot` / `confirmChangeRoot` | Gate P11 |
| Campos | `setField` / lookups `setCatalogX` | Permisos por campo |
| Líneas | `addLine` / `mutateLine` / `removeLine` | Ambiguity en detalle vs maestro |
| Consultas | `queryStock` / `queryDebt` / … | Usan raíz en proceso |
| Persistencia | `save` / `submit` / `close` | Mismo validador que el botón UI |
| Meta | `chooseOption` / `unknown` / `help` | Continuaciones |

---

## 9. Checklist de adopción en un proyecto nuevo

### Producto / SPEC

- [ ] Definir proceso anfitrión y **entidad raíz**.
- [ ] Listar mutaciones permitidas = subconjunto de la UI.
- [ ] Definir keywords de compuesto y gate P11.
- [ ] Separar chat documental vs operativo en copy y menú.
- [ ] Criterios de aceptación: CA-UX, CA-audio, CA-compuesto, CA-gate-raíz.

### Backend

- [ ] `TurnService` + `IntentDetector` + tools por dominio.
- [ ] Envelope estándar PaqSuite; `credentialId` hacia gateway BYOK.
- [ ] `pendingChoice` + `deferred*`.
- [ ] Logs de auditoría.
- [ ] Tests: compuesto monolínea, gate sin raíz, continuaciones.

### Frontend

- [ ] Panel DevExtreme al pie; i18n; `data-testid`.
- [ ] Dictado continuo + Detener.
- [ ] SelectBox de credenciales BYOK.
- [ ] Aplicador de `actions` → estado de pantalla.
- [ ] Rama mobile (`isNativeApp` / kardex) si el proceso está en menú MVP mobile.

### Documentación

- [ ] Página en `02-producto/<Producto>/…` (fuente de verdad del dominio).
- [ ] Manual en `99-manual-usuario/…`.
- [ ] Enlace a este patrón desde el producto.

---

## 10. Antipatrones

| Antipatrón | Por qué falla |
|------------|---------------|
| Dejar que el LLM “elija” el maestro ambiguo | Rompe P2 y auditoría |
| Seguir cargando líneas si la raíz falló | Datos huérfanos / comprobantes a medias |
| Dictado one-shot (`continuous=false`) para pedidos largos | UX frustrante; parte el mensaje |
| Credenciales LLM hardcodeadas del servidor para todos | Rompe modelo BYOK y costos |
| Reusar el chat documental para mutar | Mezcla ayuda con operación; permisos y corpus incorrectos |
| Partir compuesto solo por newline | El dictado suele venir en una línea |
| Confiar en el texto del reply como comando UI | Debe mandar `actions` tipadas |

---

## 11. Mapa de ejemplo: PedidosWeb → patrón

| Concepto patrón | PedidosWeb |
|-----------------|------------|
| Proceso anfitrión | `/pedidos/carga` |
| Entidad raíz | Cliente |
| Líneas | Renglones de artículo |
| Compuesto | `compositePedido` + keywords `cliente`/`artículo`/cabecera |
| Gate | Sin cliente no carga cabecera/renglones |
| Diferido | `deferredCompositeItems` / `deferredImageExtract` |
| BYOK | Preferencias Asistente IA + combo en panel |
| Audio | Web Speech continuo |
| Manual | `PedidosWeb-asistente-carga-ia.md` |

---

## 12. Extensiones futuras (opcionales por producto)

- STT nativo del proveedor (Whisper) además de Web Speech.
- Streaming de respuesta / tools.
- Multi-turno con memoria de sesión de dominio.
- Sugerencias proactivas (“faltan lookups obligatorios”).
- Plantillas de mensaje compuesto por vertical (atención, novedades, Tango).

Cualquier extensión **no** debe violar P1–P11.
