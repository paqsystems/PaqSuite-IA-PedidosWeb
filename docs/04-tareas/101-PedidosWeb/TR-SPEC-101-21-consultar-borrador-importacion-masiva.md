# TR-SPEC-101-21c — Consultar borrador importación masiva (carga readonly)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-045-consultar-borrador-importacion-masiva](../../03-historias-usuario/101-PedidosWeb/HU-101-045-consultar-borrador-importacion-masiva.md) |
| **SPEC relacionada** | [SPEC-101-21-importacion-masiva-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | **Should** |
| **Dependencias** | [TR-SPEC-101-21-pantalla-importacion-masiva](TR-SPEC-101-21-pantalla-importacion-masiva.md); TR-SPEC-101-10-pantalla-carga |
| **Estado** | **C1 cerrado** — apto D1 |
| **Última actualización** | 2026-07-19 (Parte C1) |

**Origen:** HU-101-045  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Consultar un borrador de importación masiva en `/pedidos/carga` solo lectura y volver sin perder el lote.

### Narrativa
Como usuario en la grilla masiva, quiero revisar cabecera/renglones en la pantalla tradicional sin editar ni grabar, y volver al lote intacto.

### In scope / Out of scope
- **In:** modo `readonly` + origen `importacionMasiva`; hidratar desde `location.state`; ocultar Grabar/import; botón Volver; tests.
- **Out:** grabación; persistencia BD; cambios al flujo alta/edición normales salvo flags de modo.

---

## 2) Criterios de aceptación (AC)

HU-101-045 CA-01 … CA-07.

| AC | Verificación |
|----|----------------|
| CA-01…04 | Navegación Consultar / Volver |
| CA-05 | Sin INSERT/UPDATE pedido |
| CA-06 | Vitest/E2E |
| CA-07 | Excel import disabled en readonly masiva |

---

## 3) Reglas de negocio

| RN | Implementación |
|----|----------------|
| RN-01 | Ruta `/pedidos/carga` + state `{ mode:'readonly', from:'importacionMasiva', borrador }` |
| RN-02 | Hidratar cabecera/renglones desde state; **no** `GET /pedidos/{id}` |
| RN-03 | `readOnly=true`: inputs disabled; sin Grabar pedido/presupuesto; excel import disabled |
| RN-04 | Botón/acción Volver → `/pedidos/importacion-masiva` (borrador 21b intacto en store padre / session scoped) |
| RN-05 | No llamar APIs de store |
| RN-06 | No mostrar toolbar de grabación aunque `esPedido`/`presupuesto` |

### Preservación del borrador al Volver

**Decisión C1:** al navegar a Consultar, persistir **snapshot completo del borrador** de la grilla en `sessionStorage` (clave p. ej. `importacionMasiva.borrador`); al **Volver**, rehidratar la grilla desde ese snapshot (y limpiar o conservar según TR D1).

- Cumple SPEC (sin borrador en servidor; solo sesión de navegador/pestaña).
- Robusto si el shell desmonta `ImportacionMasivaPage`.
- El `location.state` de carga sigue llevando el comprobante consultado (`borrador` de la fila); el snapshot es del **lote completo**.

```typescript
const STORAGE_KEY = 'importacionMasiva.borrador';

function persistBorradorLote(filas: BorradorFila[]) {
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ filas, savedAt: Date.now() }));
}

function restoreBorradorLote(): BorradorFila[] | null {
  const raw = sessionStorage.getItem(STORAGE_KEY);
  if (!raw) return null;
  return JSON.parse(raw).filas ?? null;
}
```

---

## 4) Impacto en datos

Ninguno (solo lectura de memoria).

---

## 5) Contratos de API

Ningún endpoint nuevo. Prohibido invocar store al Consultar.

---

## 6) Frontend

### Cambios en `PedidosCargaPage`

| Cambio | Detalle |
|--------|---------|
| Leer `location.state` | Si `from==='importacionMasiva' && mode==='readonly'` → modo masiva RO |
| Hidratar | `setCabecera` / `setRenglones` desde `state.borrador` (mappers existentes) |
| UI | Toolbar Volver (`data-testid=cargaVolverImportacionMasiva`); ocultar Grabar*; `excelImportDisabled=true` |
| Guard | No activar guard de “cambios sin grabar” de edición normal por hidratación RO |

### data-testid

| testid | Uso |
|--------|-----|
| `cargaVolverImportacionMasiva` | Volver a masiva |
| `cargaModoReadonlyMasiva` | Marker root (opcional) |

### i18n

`pedidos.importacionMasiva.volver`, `pedidos.carga.modoSoloLectura` (si aplica).

---

## 7) Plan de tareas

1. Extender `PedidosCargaPage` para state readonly masiva.  
2. Hidratar sin fetch de comprobante.  
3. Botón Volver + restauración borrador 21b.  
4. Deshabilitar Grabar / Excel import.  
5. Vitest: hidratar + Volver conserva N filas; E2E smoke.  
6. Regresión: modo nuevo/edición de carga sin state no se rompe.

---

## 8) Estrategia de pruebas

| Tipo | Cobertura |
|------|-----------|
| Vitest | Entrada con state → RO; Volver; sin llamadas store |
| E2E | Desde masiva Consultar → Volver → 2 filas |

---

## 9) Definition of Done

- [ ] CA HU-045  
- [ ] Sin escrituras BD en Consultar  
- [ ] Regresión carga normal OK  

## 10) Decisiones C1 / notas D1

| ID | Decisión C1 |
|----|-------------|
| C1-21c-01 | Conservar lote con **sessionStorage** al Consultar/Volver |

- No inventar ruta dedicada — SPEC eligió reutilizar `/pedidos/carga`.
- Logout / nueva pestaña: coherente con SPEC CA-13 (borrador no sobrevive sesión).
