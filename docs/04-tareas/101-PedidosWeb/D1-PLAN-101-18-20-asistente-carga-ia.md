# Plan de implementación D1 — Asistente IA en carga (TR-18 / 19 / 20)

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-13 |
| **Parte** | D1 — `ai-planning-mode` |
| **C1** | [F-101-18-20-cierre-c1](F-101-18-20-cierre-c1-asistente-carga-ia.md) — **Apto** |
| **TRs** | [TR-18](TR-SPEC-101-18-asistente-carga-ia-shell.md) · [TR-19](TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) · [TR-20](TR-SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Estado plan** | **Épica implementada en Parte D** (2026-07-13) — pendiente E/F verificación formal |

---

## Alcance entendido

Implementar el asistente conversacional operativo embebido en `/pedidos/carga`:

1. **TR-18:** panel UI (web + native), gate BYOK, `POST /api/v1/pedidos/carga/asistente/turn`, hilo efímero, auditoría log, Web Speech, adjunto imagen, contrato `actions[]`.
2. **TR-19:** tools de mutación A–D, I, J, K (cliente, cabecera, artículos, confirmación, grabar vía FE, apply imagen).
3. **TR-20:** tools de consulta E–H (stock, deuda, cheques, historial) solo lectura.

**Fuera de alcance D:** chat documental, tablas BYOK nuevas, STT proveedor, bootstrap destructivo, refactor de carga no requerido por las TR.

---

## Fuentes leídas

| Tipo | Documento / código |
|------|-------------------|
| SPEC | SPEC-101-18 / 19 / 20 |
| HU | HU-101-037 … 042 |
| TR | TR-SPEC-101-18 / 19 / 20 + C1 |
| Código | `PedidosCargaPage.tsx` (web/mobile), Chat Assistant message/credential/gateway, `api.php`, services PedidosWeb (CabeceraInicial, ArticuloCargaLookup, Stock/Deuda/Cheques/Historial), locales ×5, `pedidosWebMobilePolicy` |
| Normas | `_NORMAS-TRANSVERSALES-TR.md`, envelope MONO |

---

## Impacto esperado

### Base de datos

- **Ningún** cambio de esquema.
- Reuso `pq_asistente_ia_proveedores` / `pq_asistente_ia_credenciales`.

### Backend

| Nuevo | Detalle |
|-------|---------|
| Route | `POST /api/v1/pedidos/carga/asistente/turn` en grupo `auth:sanctum` + `paq.tenant` |
| Controller | `CargaAsistenteTurnController` |
| Request | `CargaAsistenteTurnRequest` (message, modality, draftContext, images[], credentialId?, pendingChoice?) |
| Service | `CargaAsistenteTurnService` — readiness → (sin corpus) → LLM/tools → audit log |
| Prompt | System prompt operativo carga (config/código versionado) |
| Tools registry | Vacío/stub en wave 1; mutaciones TR-19; consultas TR-20 |
| Errors | Reusar o espejar códigos `104001+` / claves i18n `carga.asistente.*` o reusar `chatAssistant.configurationRequired` en gate |
| OpenAPI | Anotaciones L5-Swagger |
| Log | `Log::info('carga.asistente', [...])` |

**Reuso explícito:** `ChatAssistantCredentialResolver`, `ChatAssistantConfigurationReadiness`, `ChatAssistantLlmGateway` (sin `CorpusResolver`), `ChatAssistantImageAttachmentValidator`, services PedidosWeb listados en TR-19/20.

### Frontend

| Nuevo / cambio | Detalle |
|----------------|---------|
| Feature folder | `frontend/src/features/pedidos/cargaAsistenteIa/` |
| Panel | `CargaAsistenteIaPanel` + CSS max-height ≈22vh, colapsado default |
| Host web | Insertar en pie de `PedidosCargaWebPage` (después de observaciones/totales o debajo del footer block) |
| Host mobile | Integrar en `PedidosCargaMobilePage` / sheet inferior (misma feature, testids iguales) |
| API client | `postCargaAsistenteTurn` — imágenes base64 como chat |
| Apply | `applyCargaAsistenteActions` — stub wave 1; completo 19/20 |
| Grabar J | Bridge a `saveComprobante('pedido'|'presupuesto')` |
| Config | `navigate('/preferences')` (mismo que chat; no `window.open` en native) |
| i18n | Prefijo `carga.asistente.*` o `pedidos.carga.asistente.*` en es/en/pt/fr/it |
| Speech | Web Speech en FE; fallback mensaje |

### Tests

| Capa | Alcance |
|------|---------|
| PHPUnit Feature | Auth 401, gate configurationRequired, turn OK con gateway mock |
| PHPUnit Unit | Tools (listas, qty=1, stock total>10, denied permisos) |
| Vitest | Panel collapse, validate images, applyActions |
| E2E | Smoke gate + mock turn (opcional wave 1) |

### Documentación

- Actualizar estado SPECs/producto a “D1 cerrado / en D” al arrancar D.
- Manual usuario § carga: **post** implementación (Parte Q), no bloquea D1.
- Matriz permisos: anotar endpoint turn.

### DevOps

- Sin variables `.env` nuevas obligatorias (BYOK ya existe).
- Sin migrate.
- Deploy: solo código FE+BE.

---

## Orden de trabajo (waves)

### Wave 1 — TR-18 núcleo (prioridad)

1. BE: Request + ErrorCodes + Controller + route + OpenAPI stub.
2. BE: `CargaAsistenteTurnService` con gate + reply stub/`noop` + audit log (LLM mockeable).
3. FE: tipos `actions` + client API + panel UI + i18n 5 locales + testids.
4. FE: montar panel en web carga; gate FE (mensaje fijo si API dice configurationRequired).
5. Tests Feature gate + Vitest panel.
6. FE: Web Speech + attach imagen (validación límites) → modality en turn.

**DoD wave 1:** panel visible, ruedita → `/preferences`, sin LLM → mensaje fijo, con mock → reply en hilo, log `carga.asistente`, hilo efímero al desmontar.

### Wave 2 — TR-19 mutaciones mínimas

7. Tools: buscar/seleccionar cliente + CabeceraInicial → `selectCliente` / `needsChoice` / `needsRefine`.
8. Tools: artículo + cantidad default 1 → `addRenglon`.
9. FE: `applyCargaAsistenteActions` (cliente, renglón, recalc `renglonesCarga`).
10. Confirmación I + campos cabecera B/C.
11. Bridge grabar J + ImageExtract K (visión).
12. Tests unit tools + feature turn con tool calls mock.

### Wave 3 — TR-20 consultas

13. Tools stock (mapping + total>10).
14. Tools deuda/cheques/historial (cliente required, columnas D1-19…21).
15. FE formatter `showConsulta`.
16. Tests mapping stock fixture + denied permiso.

### Wave 4 — Native + cierre

17. Montaje panel en `PedidosCargaMobilePage` (sheet/pie).
18. E2E smoke web; smoke manual native si aplica.
19. Docs: marcar D en curso / checklist OpenAPI.

---

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| LLM sin tool-calling robusto | Empezar con clasificador/heurística + tools explícitos; evolucionar prompt en D |
| Corpus documental filtrado por error | Prompt builder **sin** `ChatAssistantCorpusResolver` |
| Pie web ya denso | max-height 22vh + colapsado; no tocar toolbar grabar |
| Mobile Web Speech | Mensaje fallback; texto siempre disponible |
| Prompt injection | Allowlist actions; BE revalida permisos en cada tool |
| Doble fuente grabar | Solo FE `saveComprobante` (T-19-06) |

---

## Tests a ejecutar (antes de dar por cerrado cada wave)

```text
# Wave 1
php artisan test --filter=CargaAsistente
cd frontend && npx vitest run src/features/pedidos/cargaAsistenteIa

# Wave 2–3 (ampliar filtros tools)
php artisan test --filter=CargaAsistente

# E2E (si existe spec)
npx playwright test carga-asistente --config=...
```

No correr bootstrap destructivo ni `migrate:fresh`.

---

## Decisiones D1 (cerradas en plan)

| ID | Decisión |
|----|----------|
| D1-PLAN-01 | i18n prefijo canónico: **`carga.asistente.*`** (alineado TR); si choca con convención `pedidos.carga.*`, usar `pedidos.carga.asistente.*` y documentar en PR — **preferir `pedidos.carga.asistente.*`** para consistencia con claves existentes de carga |
| D1-PLAN-02 | Gate: reusar código/key `chatAssistant.configurationRequired` **o** espejo `pedidos.carga.asistente.configurationRequired` con mismo número 104001 — **espejo con clave propia** para i18n de carga |
| D1-PLAN-03 | Imágenes: **base64 JSON** igual chat (`contentBase64`) |
| D1-PLAN-04 | Preferencias: `navigate('/preferences')` |
| D1-PLAN-05 | Wave 1 puede shippear con LLM echo/stub si gateway tool-calling no está listo; tools reales desde wave 2 |

---

## Dudas / bloqueos

Ninguno bloqueante. Opcional antes de D:

1. ¿Preferís arrancar solo **Wave 1 (TR-18)** y pausar para review, o la épica completa en serie?
2. Prefijo i18n: confirmar `pedidos.carga.asistente.*` (recomendado).

---

## Confirmación de alcance

- **Sin cambio funcional fuera de SPEC/HU/TR:** **Sí**
- **Sin tablas nuevas / sin DROP:** **Sí**
- **Sin ampliar a chat documental:** **Sí**

## Ejecución Parte D (2026-07-13)

Usuario confirmó: **épica completa** + i18n `pedidos.carga.asistente.*`.

| Wave | Estado |
|------|--------|
| 1 Shell BE+FE | Hecho — endpoint + panel web/mobile + gate |
| 2 Mutaciones | Hecho — tools cliente/artículo/cabecera/grabar/imagen |
| 3 Consultas | Hecho — stock/deuda/cheques/historial |
| 4 Mobile + i18n | Hecho — panel mobile + 5 locales |
| Tests | Feature PHP 4 OK; Vitest 4 OK |

### Siguiente

Parte **E** (ampliar tests) / **F1** verificación formal / smoke manual en `/pedidos/carga`.

---

## Archivos principales a tocar (checklist)

### Backend (nuevos)
- `app/Http/Controllers/Api/V1/PedidosWeb/CargaAsistenteTurnController.php`
- `app/Http/Requests/PedidosWeb/CargaAsistenteTurnRequest.php`
- `app/Services/PedidosWeb/CargaAsistente/CargaAsistenteTurnService.php`
- `app/Services/PedidosWeb/CargaAsistente/Tools/*`
- `app/Support/CargaAsistenteErrorCodes.php`
- `tests/Feature/Api/PedidosWeb/CargaAsistenteTurnTest.php`

### Backend (editar)
- `routes/api.php`
- (opcional) `PedidosWebServiceProvider.php` bindings

### Frontend (nuevos)
- `features/pedidos/cargaAsistenteIa/**`

### Frontend (editar)
- `PedidosCargaPage.tsx` / web page interna
- `PedidosCargaMobilePage.tsx` (wave 4)
- `locales/{es,en,pt,fr,it}.json`

---

## Siguiente paso

Tras **confirmación explícita** del usuario (“implementá Wave 1” / “implementá la épica”), pasar a **Parte D** sin ampliar alcance.
