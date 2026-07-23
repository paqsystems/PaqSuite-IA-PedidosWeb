# TR-SPEC-101-21a — Proceso Excel `PEDIDO_MASIVO` (catálogo, handler, agrupación)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-043-proceso-excel-pedido-masivo](../../03-historias-usuario/101-PedidosWeb/HU-101-043-proceso-excel-pedido-masivo.md) |
| **SPEC relacionada** | [SPEC-101-21-importacion-masiva-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | **Should** |
| **Dependencias** | TR-GEN-07-* (motor Excel); [TR-SPEC-101-16-proceso-excel-pedido-individual](TR-SPEC-101-16-proceso-excel-pedido-individual.md) (columnas/i18n/handler base); TR-SPEC-101-06; SPEC-001-04 |
| **Estado** | **C1 cerrado** — apto D1 |
| **Última actualización** | 2026-07-19 (Parte C1) |

**Origen:** HU-101-043  
**Producto:** [importacion-masiva-pedidos.md](../../02-producto/PedidosWeb/importacion-masiva-pedidos.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Proceso Excel `PEDIDO_MASIVO`: catálogo, validación, defaults, vendedor de cliente y agrupación multi-comprobante.

### Narrativa
Como usuario con `pw_importacionmasiva`, quiero validar un Excel multi-cabecera y recibir grupos armados, para alimentar la grilla masiva sin parcial erróneo.

### In scope / Out of scope
- **In:** seeder `PEDIDO_MASIVO`; handler + lot validator (coherencia **por grupo**); agrupación; payload de grupos; i18n reutilizado; tests unit/feature.
- **Out:** pantalla FE → TR-21b; Consultar → TR-21c; endpoint grabación lote; cambiar columnas de 101-16.

---

## 2) Criterios de aceptación (AC)

Heredados de HU-101-043 (CA-01 … CA-19). Resumen ejecutable:

| AC | Verificación |
|----|----------------|
| CA-01 | Seeder: proceso + mismos campos que individual; host=`pw_importacionmasiva`; parcial=false |
| CA-02 | Plantilla usa `excelImport.column.PEDIDO_INDIVIDUAL.*` |
| CA-03…CA-04 | Feature: ≥2 grupos; orden 1ª aparición |
| CA-05…CA-11, CA-16…CA-17 | Unit + feature errores negocio |
| CA-12…CA-13 | 403 sin permiso; flag Excel |
| CA-14 | Historial `PQ_EXCEL_IMPORTACIONES` |
| CA-15 | Parser multilenguaje (regresión GEN-07) |
| CA-18 | Contrato payload grupos (§5) |
| CA-19 | Suite tests |

### Escenarios Gherkin

(Heredados de HU-101-043.)

---

## 3) Reglas de negocio (implementación)

| RN | Implementación |
|----|----------------|
| RN-01 | Catálogo + `EXCEL_IMPORT_ENABLED`; `procedimiento_host=pw_importacionmasiva` |
| RN-02 | Campos = seeder individual (copiar lista `nombre_campo_interno`); sin columna vendedor |
| RN-03 | `permite_procesamiento_parcial=false`; sin grupos si hay errores |
| RN-04 | Perfil C: todo `cod_cliente` = sesión |
| RN-05 | V/S: `PedidosWebVisibilityGuard` por cada cliente del archivo |
| RN-06 | Coherencia **cruda** de cabecera **dentro del grupo** (no “un solo cliente en todo el archivo”) |
| RN-07 | Tras resolver cliente: `cod_vended` + nombre desde maestro; vacío → error |
| RN-08…RN-09 | Clave `(cod_cliente, cod_vended, nivel)`; nivel default 0; orden 1ª aparición |
| RN-10 | Reusar resolvers/validaciones de `PedidoIndividual*` donde sea seguro |
| RN-11 | Multi-cliente permitido |
| RN-12 | Payload grupo: cabecera resuelta + renglones + vendedor + campos para totales |
| RN-13 | APIs GEN-07 del proceso gated por `pw_importacionmasiva` |

### Diferencia vs `PEDIDO_INDIVIDUAL`

| Aspecto | Individual | Masivo |
|---------|------------|--------|
| Clientes por archivo | Exactamente 1 | N |
| Coherencia cabecera | Todas las filas del archivo | Solo dentro de cada grupo |
| Salida al host | `validRows` planas | **Grupos** (comprobantes armados) — ver §5 |
| `procedimiento_host` | `pw_cargapedidos` | `pw_importacionmasiva` |

### Campos cabecera — coherencia dentro del grupo

Excluir de comparación cruda: `cod_articulo`, `cantidad`, `precio_lista`, `bonif_renglon`.  
Incluir: resto de SPEC-101-16 §2 (mismo set que TR-16a).

---

## 3.1) Catálogo y handler

### Seeder

- Extender `PedidosWebExcelImportCatalogSeeder` (o seeder hermano idempotente) con proceso `PEDIDO_MASIVO`.
- Campos: clonar definición de `PEDIDO_INDIVIDUAL` (mismo orden/`nombre_campo_interno`/`tipo_dato`/obligatoriedad).
- `nombre_columna_excel` = fallback `es` (igual 16a).

| Campo proceso | Valor |
|---------------|-------|
| `codigo_proceso` | `PEDIDO_MASIVO` |
| `handler_backend` | `Importacion.Pedidos.MasivoHandler` |
| `permite_procesamiento_parcial` | `false` |
| `permite_solo_validar` | `false` |
| `genera_plantilla` | `true` |
| `procedimiento_host` | `pw_importacionmasiva` |

### Registry

```php
// config/excel_import.php
'Importacion.Pedidos.MasivoHandler' => \App\Services\ExcelImport\Handlers\PedidoMasivoExcelImportHandler::class,
```

### Clases (backend)

| Clase | Rol |
|-------|-----|
| `PedidoMasivoExcelImportHandler` | `validateBusinessRow`, `processRow`, lot-aware |
| `PedidoMasivoLotValidator` | Perfil C/V-S a nivel archivo; coherencia cruda **por grupo**; sin vendedor |
| `PedidoMasivoGroupAssembler` | Tras process: agrupar por clave; orden 1ª aparición; armar DTO grupos |
| Reuso | `PedidoIndividualRowResolver` / servicios de defaults (extraer shared si hace falta en D1) |

### Visibilidad permiso Excel

Asegurar que plantilla/carga/lote del proceso exijan `pw_importacionmasiva` (config visibility + middleware/guard Excel existente). Agregar clave en `config/paqsuite_visibility.php`:

```php
'importacionMasiva' => 'pw_importacionmasiva',
```

---

## 4) Impacto en datos

| Objeto | Cambio |
|--------|--------|
| `PQ_EXCEL_PROCESOS` / `_CAMPOS` | Nueva fila proceso + campos (seed) |
| `PQ_EXCEL_IMPORTACIONES*` | Uso estándar GEN-07 |
| Menú/permisos | Seed en TR-21b (host UI); APIs 21a ya gatean por procedimiento |

### Seed tests

- Catálogo `PEDIDO_MASIVO` + clientes con/sin vendedor + cartera V/S + perfil C.

---

## 5) Contratos de API

Reutilizar endpoints GEN-07 (`plantilla`, lote, filas válidas/errores) con `codigoProceso=PEDIDO_MASIVO`.

### 5.1 Entrega de grupos al host (contrato funcional)

**Decisión C1:** Opción A — el host FE **no** reagrupa. Tras lote OK, la API de filas válidas / respuesta de proceso entrega `resultado.grupos[]` (comprobantes armados).

```json
{
  "grupos": [
    {
      "idGrupo": "tmp-…",
      "clave": { "codCliente": "001", "codVended": "V1", "nivel": 0 },
      "cabecera": { },
      "renglones": [ { } ],
      "vendedor": { "codVended": "V1", "nombre": "…" }
    }
  ]
}
```

Envelope estándar PaqSuite (`error` / `respuesta` / `resultado`). Si GEN-07 solo expone `validRows` hoy, extender el contrato del proceso `PEDIDO_MASIVO` (handler/assembler) para materializar `grupos` en la respuesta que consume el host (sin romper `PEDIDO_INDIVIDUAL`).

### 5.2 Auth

| Operación | Permiso |
|-----------|---------|
| Plantilla / lote / filas `PEDIDO_MASIVO` | `pw_importacionmasiva` + Bearer + `X-Paq-Cliente` |

OpenAPI: anotar permiso en description; 401/403.

### 5.3 Matriz permisos

- [ ] Fila `pw_importacionmasiva` en matriz MVP (alta en TR-21b seed; referenciar aquí).

---

## 6) Frontend

Ninguno en este slice (salvo claves i18n de columnas ya existentes `PEDIDO_INDIVIDUAL.*`).

---

## 7) Plan de tareas

1. Seeder + registry handler.  
2. Lot validator (C, V/S, coherencia por grupo, sin vendedor).  
3. Row validate/process (reuso individual).  
4. Group assembler + contrato API grupos.  
5. Gate permiso `pw_importacionmasiva` en APIs Excel.  
6. Unit + Feature tests.  
7. Runbook: seed catálogo en §10.1.

---

## 8) Estrategia de pruebas

| Tipo | Cobertura |
|------|-----------|
| Unit | Agrupación 2 clientes; orden; coherencia grupo; sin vendedor; NivelExtremo |
| Feature | Lote feliz multi-grupo; error parcial=false; 403 sin permiso; perfil C |
| Regresión | `PEDIDO_INDIVIDUAL` intacto |

---

## 9) Definition of Done

- [ ] Seeder idempotente + handler registrado  
- [ ] CA-01…19 HU-043 verificables  
- [ ] Contrato grupos documentado y testeado  
- [ ] OpenAPI / matriz permiso actualizados  
- [ ] Sin regresión individual  

## 10) Decisiones C1 / notas D1

| ID | Decisión C1 |
|----|-------------|
| C1-21a-01 | Contrato host = **`resultado.grupos[]`** (Opción A) |

- Extraer shared `PedidoExcelImport*` solo si reduce duplicación sin riesgo de romper 16a.
- Punto de enganche GEN-07 concreto (qué endpoint/campo) se fija en D1 sin cambiar el contrato funcional.
