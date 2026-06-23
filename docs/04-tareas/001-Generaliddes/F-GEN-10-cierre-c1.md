# F-GEN-10 — Cierre revisión C1 (epic Chat Asistente IA)

| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-001-10-chat-asistente-ia](../../05-open-spec/001-Generaliddes/SPEC-001-10-chat-asistente-ia.md) |
| **Fecha** | 2026-06-21 |
| **Alcance** | Revisión C1 de las 5 TR-GEN-10-* + formalización trazabilidad A1/B1/C |
| **Veredicto** | **Apto con observaciones** — **autorizado D** en orden 1→5 |

## Resultado por TR

| TR | Estado C1 | Bloqueantes D1 |
|----|-----------|----------------|
| [TR-GEN-10-catalogo-proveedores-ia](TR-GEN-10-catalogo-proveedores-ia.md) | Apto — C1 cerrado | Ninguno |
| [TR-GEN-10-configuracion-asistente-ia](TR-GEN-10-configuracion-asistente-ia.md) | Apto — C1 cerrado | Ninguno |
| [TR-GEN-10-mensajes-asistente-ia](TR-GEN-10-mensajes-asistente-ia.md) | Apto — C1 cerrado | Ninguno |
| [TR-GEN-10-chat-documental](TR-GEN-10-chat-documental.md) | Apto — C1 cerrado | Ninguno |
| [TR-GEN-10-imagenes-asistente-ia](TR-GEN-10-imagenes-asistente-ia.md) | Apto — C1 cerrado | Ninguno |

## Sobre D1 vs D

| Pregunta | Respuesta |
|----------|-----------|
| ¿Hace falta una oleada D1 aparte antes de codificar? | **No.** Cada TR ya incluye §3.3 *Plan D1 — Implementación* (2026-05-30) con decisiones cerradas en C1. |
| ¿Qué sigue? | **Parte D** — implementación en el orden de abajo, slice por slice. |

## Decisiones transversales cerradas en C1

| Tema | Decisión |
|------|----------|
| Estructura frontend | `frontend/src/features/preferences/` + `features/chatAssistant/`; sin árbol `profile` paralelo |
| Catálogo API | `GET /api/v1/chat-assistant/providers` — solo activos, orden estable documental |
| Configuración API | `GET/PUT /api/v1/chat-assistant/me/configuration`; `PATCH .../status` solo `isEnabled` |
| Chat API | `POST /api/v1/chat-assistant/messages` — `reply`, `references`, `requiresSupportFollowup` |
| Imágenes | Mismo endpoint; JSON `images[].contentBase64`; sin persistencia |
| Mensajes editables | Assets Markdown en frontend; placeholders `{{Proyecto}}`, `{{supportEmail}}` |
| Seguridad MVP | Autenticación + `X-Paq-Cliente`; **401** y **422** obligatorios; **403** no aplica salvo policy nueva |
| Tablas `pq_pedidosweb_*` | Provisión por migración de soporte dev/test o esquema cliente; sin DROP masivo |

## Orden D recomendado

```text
1. TR-GEN-10-catalogo-proveedores-ia
2. TR-GEN-10-configuracion-asistente-ia
3. TR-GEN-10-mensajes-asistente-ia
4. TR-GEN-10-chat-documental      (requiere TR-GEN-01-menu-avatar para entrada)
5. TR-GEN-10-imagenes-asistente-ia
```

## Matriz permisos — filas previstas (aplicar en D)

| Método | Path | Permiso |
|--------|------|---------|
| GET | `/api/v1/chat-assistant/providers` | Usuario autenticado |
| GET | `/api/v1/chat-assistant/me/configuration` | Usuario autenticado |
| PUT | `/api/v1/chat-assistant/me/configuration` | Usuario autenticado |
| PATCH | `/api/v1/chat-assistant/me/configuration/status` | Usuario autenticado |
| POST | `/api/v1/chat-assistant/messages` | Usuario autenticado + configuración válida |

**Estado:** incorporar en [matriz-permisos-mvp.md](matriz-permisos-mvp.md) al implementar cada slice.

## Fuera de alcance confirmado

- MVP portal release actual (epic posterior).
- Configuración compartida por tenant.
- ABM web del catálogo de proveedores.
- Histórico conversacional ni persistencia de adjuntos.
- Envío automático a soporte desde el chat.

## Veredicto final

**C1 cerrado.** Epic listo para **parte D** sin oleada D1 adicional.
