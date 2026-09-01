# Cierre F — CC PQ #13 (01/09/2026) — Leyendas 60 caracteres

## Alcance

Verificación **F1 + F** (openspec-05 / agent-verification-guide) sobre el límite de **60 caracteres Unicode** en leyendas 1–5:

| # | Tema | Updates |
|---|------|---------|
| 1 | DDL `nvarchar(60)` cabecera + clientes | SPEC/TR-101-02-update |
| 2 | Recortar al grabar (sin 4xx) + OpenAPI | SPEC/TR-101-04-update · HU-009/010-update |
| 3 | UI `maxLength={60}` (web + native) | SPEC/TR-101-10-update · HU-005-update |
| 4 | Excel individual / masivo: recortar, catálogo 255 | SPEC/TR-101-16/21-update · HU-029/043-update |
| 5 | Asistente IA texto + imagen | SPEC/TR-101-19-update · HU-039/040-update |

**Fecha verificación F1/F:** 01/09/2026  
**Parte E:** [E-CC-PQ-13-tests.md](E-CC-PQ-13-tests.md)  
**Rama / HEAD:** `v1.1.1` @ `40aac3f` (working tree D+E+F, sin commit)  
**Script smoke HTTP:** `backend/scripts/smoke-cc-pq-13-f.php`

**Entorno vivo:** Frontend `http://localhost:3010` · Backend `http://127.0.0.1:8088` · OpenAPI `http://127.0.0.1:8088/api/documentation`

---

## F1 — Verificación agente (8 ejes)

**Resultado F1:** **Aprobado con observaciones**

### 1. Alcance

| Pedido CC #13 | Implementado | Fuera de alcance respetado |
|---------------|--------------|----------------------------|
| Modelo de datos | `nvarchar(60)` cabecera + clientes; bootstrap CREATE | Sin DROP |
| Carga de pedidos | `ComprobanteLeyendasPie` `maxLength={60}` | Observaciones no tocadas |
| Excel individual/masivo | Recorte en resolver/assembler; `largo_maximo` 255 | GEN-07 `castTexto` no trunca global |
| Asistente IA | Recorte en `setCampoLibre` + extracto imagen + patch FE | Sin `validationError` por longitud |
| API grabar | Recorta; **no** `max:60` 4xx | Mail / consultas / copia sin extra |

### 2. Código

| Pieza | Evidencia |
|-------|-----------|
| Helper | `LeyendaCabeceraLimits::recortarLeyendaCabecera` / FE `recortarLeyendaCabecera.ts` |
| Grabar | `PedidoService::grabarComprobante` → `recortarLeyendasEnMapa` |
| Sync maestro | `syncClienteLeyendasSiDirty` recorta |
| Cabecera inicial | `resolveLeyendaCliente` recorta |
| Excel | `PedidoIndividualRowResolver`, `PedidoMasivoGroupAssembler` |
| IA | `CargaAsistenteCabeceraTool`, `CargaAsistenteImageExtractTool`, `patchAsistenteCabecera.ts` |
| UI | `ComprobanteLeyendasPie` + mobile `PedidosCargaMobileCabeceraStep` |

Sin hardcode de 60 disperso: constante `MAX_CARACTERES` / `leyendaMaxCaracteres`.

### 3. Datos

| Pieza | Estado |
|-------|--------|
| Migración `2026_09_01_100000_alter_pq_pedidosweb_leyendas_nvarchar60` | Aplicada en tenant local (D) |
| SQL `alter-pq-pedidosweb-leyendas-60.sql` | Idempotente; `LEFT` + ALTER si `max_length > 120` |
| Schema vivo | 10 columnas `max_length=120` (PHPUnit E + D) |
| Seed Excel | `largo_maximo=255` en seeder y SQL catálogo |

### 4. Backend

| RN | Evidencia | Estado |
|----|-----------|--------|
| Recortar, no rechazar | `ComprobanteGrabacionPayload` sin `max:60` | OK |
| Persistencia 60 | Helper + insert repositorio smoke F | OK |
| OpenAPI `maxLength=60` | Spec JSON + UI 200 | OK |
| Permisos grabar | Sin cambio de matriz | N/A |

### 5. Frontend

| RN | Evidencia | Estado |
|----|-----------|--------|
| Cinco TextBox `maxLength=60` | Vitest E + **browser F** (carga, cliente 101122) | OK |
| `data-testid` `leyendas-pie`, `leyenda-1`…`leyenda-5` | DOM vivo `maxLength=60` / `maxlength="60"` | OK |
| Native | Mismo componente | OK (código; sin emulador) |

### 6. Tests

Ver [E-CC-PQ-13-tests.md](E-CC-PQ-13-tests.md): 17 PHPUnit + 6 Vitest. Re-ejecutados en E 01/09/2026. No re-corridos en F (misma sesión, working tree sin cambio de tests).

### 7. Documentación

| Doc | Alineado |
|-----|----------|
| Producto modelo + UI §9 | Sí (`nvarchar(60)`, máximo 60) |
| `Migraciones-en-forge.md` | Sí (script + migración CC #13) |
| OpenAPI `ComprobanteCabeceraRequest.leyenda_N` | Sí |
| TR-update estado | Implementado (D) — Pendiente de Revisión |

### 8. Trazabilidad

Updates G en `docs/.../updates/101-PedidosWeb/` (SPEC/HU/TR 02, 04, 10, 16, 21, 19). CC #13 ciclo G+D+E+F.

---

## F — Smoke HTTP / UI

```text
php scripts/smoke-cc-pq-13-f.php
```

| Escenario | Resultado |
|-----------|-----------|
| `GET /api/v1/health` | **200** |
| OpenAPI JSON `leyenda_1.maxLength=60` | **OK** |
| OpenAPI UI `/api/documentation` | **200** (título PedidosWeb API) |
| Login Sanctum + GET clientes/artículos | **200** |
| `POST /comprobantes/grabar` con leyenda 61 | **500** — fixture `PedidosWebSchemaBootstrap::upsertArticuloFixture` (`usa_esc` NULL). **Preexistente**, no es recorte de leyendas |
| Persistencia recortada (helper + `insertCabecera`) | **OK** — `cod_pedido=SMK136A9748072D9E839`, `leyenda_1` longitud 60 |
| UI carga: login + cliente + `maxLength` leyendas | **OK** — 5 inputs `leyenda-1`…`leyenda-5` = 60 |

### Checklist QA

| # | Escenario | Resultado |
|---|-----------|-----------|
| 1 | Carga web: TextBox leyendas `maxLength=60` | **OK** (browser 01/09/2026) |
| 2 | Grabar HTTP 61 → persistir 60 | **Parcial** — recorte+insert OK; HTTP grabar bloqueado por fixture `usa_esc` |
| 3 | Excel individual/masivo fila > 60 | Cubierto PHPUnit E; **sin** archivo `.xlsx` en F |
| 4 | Asistente IA `setCampoLibre` / imagen | Cubierto PHPUnit E; **sin** turno LLM en F |
| 5 | Native / emulador | Mismo componente; **no** ejecutado en dispositivo |

---

## Observaciones no bloqueantes

| ID | Tema | Destino |
|----|------|---------|
| OBS-F-01 | `POST /comprobantes/grabar` 500 por `usa_esc` NULL en fixture ART-HP-001 | Fuera de CC #13; arreglar `PedidosWebSchemaBootstrap::upsertArticuloFixture` (`usa_esc` char `'N'` o default) |
| OBS-F-02 | Feature PHPUnit grabar skip (seed tenant) | Misma causa de fixture/seed; E documentó skip |
| OBS-F-03 | Idempotencia SQL (AC-CC13-T-M4) no re-ejecutada en F | Script listo para Forge |
| OBS-F-04 | Excel/IA E2E y native | Checklist PQ opcional post-F |
| OBS-F-05 | Deploy Forge | `php artisan migrate --force` y/o `alter-pq-pedidosweb-leyendas-60.sql` |

---

## Veredicto final

| Control | F1 (agente) | F (smoke HTTP/UI) | F (manual PQ extra) |
|---------|-------------|-------------------|---------------------|
| CC #13 (01/09/2026) | **Aprobado con observaciones** | **Aprobado con observaciones** | Pendiente ítems 3–5 si PQ quiere smoke extra |

**Estado CC #13:** **G+D+E+F 01/09/2026.** Unificación de updates en originales: **Parte I**.

**Recomendación:** Parte I cuando PQ autorice. En Forge aplicar migración/SQL de leyendas. Opcional: corregir fixture `usa_esc` en ticket aparte para desbloquear grabar HTTP de smoke.
