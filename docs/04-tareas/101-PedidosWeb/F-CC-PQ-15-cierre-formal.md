# Cierre F — CC PQ #15 (09/09/2026) — Copiar: dirección + leyendas

## Alcance

Verificación **F1 + F** sobre atributos faltantes al copiar comprobante:

| # | Tema | Updates |
|---|------|---------|
| 1 | Copiar `id_de` (dirección de entrega) | SPEC/HU/TR-101-04-update-01 · HU-026-update-01 |
| 2 | Copiar `leyenda_1`…`leyenda_5` (recorte 60) | Idem |

**Fecha verificación F1/F:** 09/09/2026  
**Parte E:** [E-CC-PQ-15-tests.md](E-CC-PQ-15-tests.md)  
**TR:** [TR-SPEC-101-04-update-01](../updates/101-PedidosWeb/TR-SPEC-101-04-services-pedidos-update-01.md)

---

## F1 — Verificación agente (8 ejes)

**Resultado F1:** **Aprobado con observaciones**

### 1. Alcance

| Pedido CC #15 | Implementado | Fuera de alcance respetado |
|---------------|--------------|----------------------------|
| Dirección de entrega al copiar | `id_de` en `mapCabecera` | Sin join descriptivo `direccion_entrega` |
| Leyendas 1–5 al copiar | `leyenda_1`…`5` + `LeyendaCabeceraLimits` | Sin sync maestro cliente al copiar |
| Otros campos cabecera (bonif, fecha, expreso) | No ampliados | Correcto: no estaban en el hallazgo |

### 2. Código

| Pieza | Evidencia |
|-------|-----------|
| Servicio | `backend/app/Services/PedidosWeb/ComprobanteCopiaService.php` → `mapCabecera` |
| Helper | `LeyendaCabeceraLimits::recortarLeyendaCabecera` |
| FE | Sin cambio; `mapCabeceraFromApi` ya consume `id_de` / `leyenda_N` |

### 3. Base de datos

Sin DDL. Usa columnas existentes de `pq_pedidosweb_pedidoscabecera`.

### 4. Backend / API

Mismo `POST /api/v1/comprobantes/copiar`; payload de cabecera enriquecido.

### 5. Frontend

Sin cambio obligatorio.

### 6. Tests

Casos en `ComprobanteCopiaServiceTest` (ver Parte E). Observación: ejecución local del agente bloqueada por timeout SQL Server; lógica cubierta con mocks.

### 7. Documentación

Updates SPEC/HU/TR-update-01 + este F + E + marca en `00-ControlCalidad-PQ` #15.

### 8. Trazabilidad

CC #15 → SPEC-101-04-update-01 → HU-101-026-update-01 → TR-SPEC-101-04-update-01 → código + tests.

---

## Veredicto F

**Aprobado con observaciones** (re-ejecutar PHPUnit cuando haya conectividad a la BD de tests).

## Parte I

Pendiente (unificar updates a docs base cuando se cierre el bloque).
