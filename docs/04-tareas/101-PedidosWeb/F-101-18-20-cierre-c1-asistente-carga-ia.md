# Cierre C1 — SPEC-101-18 / 19 / 20 — Asistente IA en carga

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-13 |
| **Método** | Skill `tr-ambiguity-review` + normas `_NORMAS-TRANSVERSALES-TR.md` |
| **TRs** | [TR-18 shell](TR-SPEC-101-18-asistente-carga-ia-shell.md) · [TR-19 mutaciones](TR-SPEC-101-19-asistente-carga-ia-mutaciones.md) · [TR-20 consultas](TR-SPEC-101-20-asistente-carga-ia-consultas.md) |

---

## Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto** (con observaciones menores) |
| **Puede pasar a D1/D** | **Sí** |
| **Bloqueantes** | Ninguno |

---

## Checklist C1 (épica)

| Eje | 18 | 19 | 20 | Notas |
|-----|----|----|----|-------|
| Alcance ⊆ HU ⊆ SPEC | OK | OK | OK | 3 TR consolidan 6 HU |
| API path + envelope | OK | OK | OK | Un turn; tools internos 19/20 |
| Seguridad / gate BYOK | OK | OK | OK | Resolver credencial chat; revalidar permisos tools |
| Datos / sin DROP | OK | OK | OK | Sin tablas nuevas |
| UI / testids / i18n | OK | OK | OK | Prefijo `carga.asistente.*` |
| Tests ↔ AC | OK | OK | OK | § tests en cada TR |
| Coherencia entre TRs | OK | OK | OK | actions catalog compartido |

---

## Ambigüedades críticas

Ninguna.

| ID | Tema | Resolución C1 |
|----|------|----------------|
| AMB-C-TURN-01 | ¿Grabar en BE o FE? | **FE invoke** handlers toolbar (T-19-06) |
| AMB-C-TURN-02 | ¿Borrador en servidor? | **No** — solo `draftContext` por request (T-18-02) |

---

## Ambigüedades menores (no bloquean D1)

| ID | Tema | Guía |
|----|------|------|
| AMB-M-01 | Path exacto Preferencias | Reusar helper/ruta del chat assistant |
| AMB-M-02 | Multipart vs base64 imágenes | Alinear a implementación vigente `chat-assistant/messages` |
| AMB-M-03 | Render tabla consultas en hilo | `replyText` + `showConsulta`; detalle CSS en D1 |
| AMB-M-04 | Maps i18n confirmación I | Claves por locale en D1 |
| AMB-M-05 | System prompt LLM operativo | Texto fijo versionado en código/config; sin corpus |

---

## Contradicciones TR ↔ HU ↔ SPEC

Ninguna detectada. Cantidad default 1, Web Speech, columnas F–H, log auditoría y API dedicada alineados a A1+.

---

## Supuestos

1. `ChatAssistantCredentialResolver` y gateway LLM son reutilizables sin traer retrieval documental.
2. Services de consultas/stock/clientes son invocables desde tools sin HTTP loopback.
3. Mobile carga puede montar el mismo panel o sheet con mismos testids (rama `isNativeApp`).

---

## Preguntas abiertas

Ninguna bloqueante para D1.

---

## Orden D1 sugerido

1. **TR-18** T18-1…T18-4 (shell + gate + turn stub)  
2. **TR-19** tools A/D mínimos (cliente + artículo) + apply FE  
3. **TR-20** stock (demo valor)  
4. Completar B/C/I/J/K y F–H  
5. Audio/imagen (T18-5) cuando visión BYOK disponible en QA  

---

## Recomendaciones

- [x] Consolidar 6 HU → 3 TR (hecho).
- [ ] En D1: no ejecutar bootstrap destructivo; solo código + tests.
- [ ] Actualizar manual usuario § carga post-implementación (Parte Q).
- [ ] Matriz permisos: anotar endpoint turn con permiso carga.

## Veredicto

**Apto.** **Autoriza Parte D1** (plan + implementación) sobre TR-18 → 19 → 20.
