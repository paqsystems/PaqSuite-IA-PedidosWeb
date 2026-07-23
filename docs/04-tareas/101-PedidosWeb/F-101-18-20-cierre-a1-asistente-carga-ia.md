# Cierre A1 — SPEC-101-18 / 19 / 20 — Asistente IA en carga

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-13 |
| **Épica** | Asistente IA operativo en carga de pedidos/presupuestos |
| **Producto** | [asistente-ia-carga-pedidos-presupuestos.md](../../02-producto/PedidosWeb/asistente-ia-carga-pedidos-presupuestos.md) |
| **SPECs** | [101-18](../../05-open-spec/101-PedidosWeb/SPEC-101-18-asistente-carga-ia-shell.md) · [101-19](../../05-open-spec/101-PedidosWeb/SPEC-101-19-asistente-carga-ia-mutaciones.md) · [101-20](../../05-open-spec/101-PedidosWeb/SPEC-101-20-asistente-carga-ia-consultas.md) |
| **Método** | Skill `spec-ambiguity-review` + regla `11-spec-ambiguity-review.md` |

---

## Resultado general (épica)

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno (tras decisiones A1 § abajo) |

---

# Revisión de ambigüedad — SPEC-101-18 (shell)

## Checklist (10 ejes)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance | OK | Panel, BYOK, audio, imágenes entrada; excluye mutaciones/consultas |
| 2 | Actores | OK | Usuario autenticado en carga; perfiles vía 101-19/10 |
| 3 | Flujo | OK | Pipeline gate → intención → acción → sync |
| 4 | Reglas | Obs. | Motor STT deferido a TR (producto ya lo marca abierto) |
| 5 | Datos | Obs. | Sesión conversacional / pending choice — cerrado A1 |
| 6 | UI | Obs. | Altura máx. y default colapsado — TR/UX |
| 7 | APIs | Obs. | Endpoint orquestación — decisión A1 D1 |
| 8 | Especiales | OK | Sin LLM, sin visión, mic denegado |
| 9 | CA | OK | CA-UX / L / K-IN / SYNC |
| 10 | Trazabilidad | OK | Producto + SPEC-001-10 |

## Ambigüedades críticas

Ninguna bloqueante para Parte B tras cierre A1.

| ID | Tema | Resolución A1 |
|----|------|----------------|
| AMB-C-101-18-01 | ¿Endpoint nuevo vs reusar chat-assistant documental? | **Cerrado (D1):** API dedicada de carga (p. ej. `POST /api/v1/pedidos/carga/asistente/*`) — **no** mezclar corpus documental del Asistente IA. Reusa solo **credenciales/catálogo** BYOK. |
| AMB-C-101-18-02 | ¿Cuál config BYOK si el usuario tiene varias? | **Cerrado (D1):** misma regla de resolución que el **Chat Asistente IA** al enviar mensaje (configuración activa / preferida vigente). |

## Ambigüedades menores (TR, no bloquean B)

| ID | Tema | Resolución / defer |
|----|------|---------------------|
| AMB-M-101-18-01 | Panel expandido vs colapsado al abrir carga | **Cerrado:** default **colapsado**; expandido ≤ ≈20–22% viewport (D1-16) |
| AMB-M-101-18-02 | Web Speech vs STT proveedor | **Cerrado (D1-15):** Web Speech API |
| AMB-M-101-18-03 | Persistencia del hilo al salir de la pantalla | **Cerrado A1:** hilo **efímero** por sesión de pantalla (se pierde al salir/cancelar); no historial en BD |
| AMB-M-101-18-04 | Pending lista numerada + nuevo mensaje | **Cerrado A1:** nueva intención de búsqueda/acción **cancela** la elección pendiente (alineado a «reformular») |
| AMB-M-101-18-05 | Destino auditoría (tabla/log) | **Cerrado (D1-17):** log de aplicación Laravel (`storage/logs/laravel.log` vía canal `stack`/`single`) |

## Veredicto 101-18

**Apto con observaciones** → autoriza HU del slice shell.

---

# Revisión de ambigüedad — SPEC-101-19 (mutaciones)

## Checklist (10 ejes)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance | OK | A–D, I, J, K aplicación |
| 2 | Actores | OK | V/S/C + parámetros ERP |
| 3 | Flujo | OK | Equivalencia UI |
| 4 | Reglas | Obs. | Cantidad omitida; “último renglón” |
| 5 | Datos | OK | Borrador; no grabar hasta J |
| 6 | UI | OK | Sync vía 101-18 |
| 7 | APIs | Obs. | Reuso services vs facade — TR |
| 8 | Especiales | OK | Confirm I; solo lectura |
| 9 | CA | OK | CA-A…K |
| 10 | Trazabilidad | OK | Producto + 101-10 |

## Ambigüedades críticas

Ninguna bloqueante para Parte B.

| ID | Tema | Resolución A1 |
|----|------|----------------|
| AMB-C-101-19-01 | Extracto imagen: ¿aplicar validados sin confirmación global? | **Cerrado (D1):** aplicar **automáticamente** solo candidatos **válidos**; dudosos/inválidos → lista/errores (producto §14) |
| AMB-C-101-19-02 | Intención sin cantidad al agregar artículo | **Cerrado (D1+):** si falta cantidad → **asumir 1** (decisión producto 2026-07-13) |

## Ambigüedades menores

| ID | Tema | Resolución / defer |
|----|------|---------------------|
| AMB-M-101-19-01 | “Último renglón” / renglón ambiguo para precio | **Cerrado A1 (D1-13):** si un solo renglón → ese; si varios → lista numerada de renglones (código + cantidad) |
| AMB-M-101-19-02 | Frases exactas de confirmación cambio cliente | **Cerrado (D1-18):** sí/si, confirmo, aceptado; rechazo no/cancelar (+ i18n) |
| AMB-M-101-19-03 | Moneda editable vía IA | **Cerrado A1 (D1-14):** solo si la UI de carga permite editar moneda para ese perfil; si no, `denied` |
| AMB-M-101-19-04 | Facade única vs N llamadas FE | **Defer TR** (contrato `action`/`resultado` ya orientativo) |

## Veredicto 101-19

**Apto con observaciones** → autoriza HU del slice mutaciones.

---

# Revisión de ambigüedad — SPEC-101-20 (consultas)

## Checklist (10 ejes)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance | OK | E–H solo lectura |
| 2 | Actores | OK | Permisos de cada consulta |
| 3 | Flujo | OK | Cliente en curso para F–H |
| 4 | Reglas | OK | Tope 10; stock mapping |
| 5 | Datos | OK | APIs existentes |
| 6 | UI | Obs. | Columnas chat F–H resumidas |
| 7 | APIs | OK | Paths cerrados A1 |
| 8 | Especiales | OK | Sin cliente; sin permiso |
| 9 | CA | OK | CA-E…H |
| 10 | Trazabilidad | OK | Producto consultas |

## Ambigüedades críticas

Ninguna.

| ID | Tema | Resolución A1 |
|----|------|----------------|
| AMB-C-101-20-01 | ¿`>10` stock = `total` API o filas de una página? | **Cerrado (D1):** usar **`total`** (o conteo equivalente) de matches del filtro `q`; si `total > 10` → refine **sin** listar |
| AMB-C-101-20-02 | Paths API G/H | **Cerrado:** `GET /api/v1/consultas/cheques`, `GET /api/v1/consultas/historial-ventas` (+ filtro cliente en proceso) |

## Ambigüedades menores

| ID | Tema | Resolución / defer |
|----|------|---------------------|
| AMB-M-101-20-01 | F–H con >10 filas (no son búsqueda libre) | **Cerrado A1:** mostrar hasta 10 + indicar `total`; sugerir ir a la consulta de menú o pedir criterio más acotado si la API lo permite |
| AMB-M-101-20-02 | Columnas mínimas en chat para F/G/H | **Cerrado (D1-19/20/21):** G nro·fecha·importe; H desc·cant·PU neto·importe; F tipo/nro·fecha·vto·saldo |
| AMB-M-101-20-03 | ¿Consultas E–H requieren LLM o tool directo? | **Cerrado A1:** intención puede resolverse por LLM→tool o clasificador; **datos siempre** desde API (nunca inventados). Detalle en TR |

## Veredicto 101-20

**Apto con observaciones** → autoriza HU del slice consultas.

---

## Decisiones humanas A1 (D1) — resumen

| ID | Decisión |
|----|----------|
| D1-01 | API orquestación **dedicada** a carga; BYOK compartido con Asistente IA |
| D1-02 | Credencial BYOK = misma resolución que chat documental |
| D1-03 | Hilo conversacional **efímero** (sesión de pantalla) |
| D1-04 | Nueva intención cancela pending de lista numerada |
| D1-05 | Imagen: auto-aplicar solo válidos |
| D1-06 | Sin cantidad → **asumir 1** (confirmado producto) |
| D1-07 | Stock `total > 10` → refine |
| D1-08 | Paths cheques / historial-ventas explícitos |
| D1-11 | F–H: máx. 10 + total + hint consulta |
| D1-12 | Datos de consulta siempre desde API |
| D1-13 | Renglón ambiguo → lista numerada |
| D1-14 | Moneda vía IA solo si UI lo permite |
| D1-15 | Audio = Web Speech |
| D1-16 | Panel expandido ≤ ≈20–22% viewport |
| D1-17 | Auditoría = log aplicación |
| D1-18 | Confirmación I: sí/confirmo/aceptado |
| D1-19…21 | Columnas chat F/G/H cerradas |

## Preguntas abiertas (no bloquean B)

Ninguna. A1 listo para Parte B.

## Recomendaciones Parte B (HU)

Agrupación sugerida (sin generar HU aún):

| HU sugerida | SPEC | Contenido |
|-------------|------|-----------|
| HU-101-037 | 18 | Panel pie, gate BYOK, ruedita, i18n, testids |
| HU-101-038 | 18 | Audio L + adjunto imagen K (entrada) |
| HU-101-039 | 19 | Cliente A + cambio I + cabecera B/C |
| HU-101-040 | 19 | Artículos D + grabar J + apply imagen |
| HU-101-041 | 20 | Stock E |
| HU-101-042 | 20 | Deuda/cheques/historial F–H |

> Numeración HU a confirmar contra el último ID existente en `101-PedidosWeb` al generar Parte B.

## Veredicto final épica

**Apto con observaciones.** **Autoriza Parte B** (generación de HU) sobre SPEC-101-18, 101-19 y 101-20.
