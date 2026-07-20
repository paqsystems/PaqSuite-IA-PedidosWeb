# TR-SPEC-101-21b — Pantalla importación masiva (grilla, import, grabación FE)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-101-044-pantalla-importacion-masiva](../../03-historias-usuario/101-PedidosWeb/HU-101-044-pantalla-importacion-masiva.md) |
| **SPEC relacionada** | [SPEC-101-21-importacion-masiva-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Épica** | 101 — PedidosWeb |
| **Prioridad** | **Should** |
| **Dependencias** | [TR-SPEC-101-21-proceso-excel-pedido-masivo](TR-SPEC-101-21-proceso-excel-pedido-masivo.md); TR-GEN-07-ui-embebida-host; TR-SPEC-101-09 (menú); TR-SPEC-101-04/05 (grabar); TR-SPEC-101-13 (mails) |
| **Estado** | **C1 cerrado** — apto D1 |
| **Última actualización** | 2026-07-19 (Parte C1) |

**Origen:** HU-101-044  
**Producto:** [importacion-masiva-pedidos.md](../../02-producto/PedidosWeb/importacion-masiva-pedidos.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU refinada (resumen)

### Título
Pantalla `pw_importacionmasiva`: grilla de borradores, import Excel, toggles, grabación FE secuencial y guardas de salida.

### Narrativa
Como usuario autorizado, quiero importar, clasificar y grabar un lote con progreso «Cargando x de N», para procesar varios comprobantes sin que un error detenga al resto.

### In scope / Out of scope
- **In:** seed menú/permiso; ruta/página; DataGrid; toolbar Excel; modales; orquestador grabar; i18n; exclusion mobile; Vitest/E2E.
- **Out:** handler 21a; modo Consultar readonly (acción visible aquí, comportamiento TR-21c); endpoint lote backend.

---

## 2) Criterios de aceptación (AC)

HU-101-044 CA-01 … CA-19. Mapeo rápido:

| AC HU | Verificación |
|-------|----------------|
| CA-01, 14 | Menú + policy mobile |
| CA-02…CA-06, 08…CA-10, 12, 16…18 | UI + orquestador |
| CA-07 | Icono Consultar → navega; hidratación en 21c |
| CA-11 | Regresión mail vía APIs store existentes |
| CA-13 | `excelImportEnabled` |
| CA-15, 19 | i18n + tests |

---

## 3) Reglas de negocio (UI / FE)

| RN | Implementación |
|----|----------------|
| RN-01 | Solo web; filtrar en `pedidosWebMobilePolicy` + menú seed sin native |
| RN-02 | Estado inicial `filas=[]` |
| RN-03 | `onComplete` / consumo grupos 21a → filas con `esPedido=true`, `idInterno=uuid` |
| RN-04…05 | Modal reimport Replace / Append / Cancel; append sin merge |
| RN-06 | Bulk set `esPedido` + Switch por fila |
| RN-07 | Popup confirm delete |
| RN-08…11 | `grabarLoteSecuencial`: snapshot, loop, progress, no cancel, disable UI |
| RN-12 | `useBlocker` / guard shell: Cancel / Grabar todo / Retornar |
| RN-13 | Toolbar Excel disabled si `!excelImportEnabled` |
| RN-14 | Sin persist server; perder al logout |
| RN-15…16 | `POST /pedidos` o `POST /presupuestos` según toggle; auth = menú proceso |
| RN-17 | Totales con `renglonesCarga.ts` al hidratar import |

### Constante proceso

```typescript
export const EXCEL_PROCESO_PEDIDO_MASIVO = 'PEDIDO_MASIVO' as const;
```

### Pseudocódigo grabar

```typescript
async function grabarLoteSecuencial(filas: BorradorFila[]) {
  const snapshot = [...filas];
  const n = snapshot.length;
  let ok = 0;
  let err = 0;
  setProgreso({ x: 0, n, bloqueado: true });
  for (let i = 0; i < snapshot.length; i++) {
    setProgreso({ x: i + 1, n, bloqueado: true });
    const fila = snapshot[i];
    try {
      if (fila.esPedido) await api.postPedido(mapPayload(fila));
      else await api.postPresupuesto(mapPayload(fila));
      removeFila(fila.idInterno);
      ok++;
    } catch (e) {
      setErrorFila(fila.idInterno, resolveEnvelopeMessage(e));
      err++;
    }
  }
  setProgreso(null);
  toastResumen(ok, err);
  return { ok, err };
}
```

---

## 4) Impacto en datos

| Objeto | Cambio |
|--------|--------|
| Menú / roles | Seed `pw_importacionmasiva` (comando `paqsuite:seed-menus-mvp` o seeder dedicado idempotente) |
| `paqsuite_visibility.procedimientos` | `importacionMasiva => pw_importacionmasiva` |
| BD pedidos | Solo vía APIs store existentes al Grabar |

---

## 5) Contratos de API

No nuevos endpoints de lote. Reuso:

| Método | Path | Uso |
|--------|------|-----|
| GEN-07 | plantilla/lote/`PEDIDO_MASIVO` | Import |
| POST | `/api/v1/pedidos` | Grabar pedido |
| POST | `/api/v1/presupuestos` | Grabar presupuesto |

**Autorización grabar (C1):** mismas APIs y **mismo payload JSON** que carga individual (`POST /api/v1/pedidos`, `POST /api/v1/presupuestos`). Gate de **alta/store**:

```text
pw_cargapedidos  OR  pw_importacionmasiva
```

- Quien solo tiene `pw_importacionmasiva` puede grabar desde el lote masivo (AMB-C-03).
- No abrir por ese OR la pantalla de carga individual ni update/delete/edición si hoy están gated solo con `pw_cargapedidos` (ajustar únicamente `store` de pedido y presupuesto, salvo que ya compartan helper).
- Documentar en matriz permisos y OpenAPI description de ambos controllers.

### Payload

El mapper FE `mapPayload(fila)` produce el **mismo body** que usa `PedidosCargaPage` al Grabar pedido/presupuesto (cabecera + renglones + flags de alta). No inventar DTO paralelo.

### OpenAPI / matriz

- [ ] Matriz: `pw_importacionmasiva` (menú) + nota OR en store pedido/presupuesto  
- [ ] OpenAPI: description de permiso en `PedidoController@store` / `PresupuestoController@store`  
- [ ] Feature test: usuario solo con `pw_importacionmasiva` → 200 en store; sin ninguno → 403  

---

## 6) Frontend

### Ruta / página

| Elemento | Sugerencia |
|----------|------------|
| Ruta | `/pedidos/importacion-masiva` |
| Página | `ImportacionMasivaPage` |
| Proceso menú | `pw_importacionmasiva` |

### Componentes

| Componente | Rol |
|------------|-----|
| `ImportacionMasivaPage` | Orquestación estado borrador |
| `ImportacionMasivaGrid` | DataGrid + toggle + acciones |
| `ImportacionMasivaToolbar` | Plantilla, import, marcar todos, Grabar |
| `useImportacionMasivaGrabacion` | Loop secuencial + progreso |
| `useImportacionMasivaNavigationGuard` | Modal salida |
| Store/context | Estado en memoria de página + **sessionStorage** snapshot (C1 — ver 21c) |

### data-testid (estables)

| testid | Uso |
|--------|-----|
| `importacionMasivaPage` | Root |
| `importacionMasivaGrid` | Grilla |
| `importacionMasivaToggleTipo` | Toggle fila (suffix id) |
| `importacionMasivaConsultar` | Acción consultar |
| `importacionMasivaEliminar` | Acción eliminar |
| `importacionMasivaMarcarPedidos` | Toolbar |
| `importacionMasivaMarcarPresupuestos` | Toolbar |
| `importacionMasivaGrabar` | Toolbar Grabar |
| `importacionMasivaProgreso` | Overlay «Cargando x de N» |
| `importacionMasivaModalReimport` | Replace/Append/Cancel |
| `importacionMasivaModalSalida` | Cancel/Grabar todo/Retornar |
| `importacionMasivaModalEliminar` | Confirm delete |
| `excelHostToolbar` / `excelHostImport` | GEN-07 (proceso masivo) |

### i18n

Prefijo sugerido: `pedidos.importacionMasiva.*`  
Progreso: `pedidos.importacionMasiva.grabandoProgreso` con params `{x,n}`  
Menú: `menu.pw_importacionmasiva`

### Mobile

- Actualizar `pedidosWebMobilePolicy.ts` + tests: ruta excluida.  
- No ítem menú native.

### Navegación a Consultar (handoff 21c)

```typescript
navigate('/pedidos/carga', {
  state: {
    mode: 'readonly',
    from: 'importacionMasiva',
    borrador: { idInterno, cabecera, renglones, esPedido },
  },
});
```

---

## 7) Plan de tareas

1. Config visibility + seed menú/permiso.  
2. Ruta + página + grilla columnas.  
3. Integrar Excel host `PEDIDO_MASIVO` + apply grupos.  
4. Toggles / eliminar / reimport modals.  
5. Orquestador grabar + progreso + toast.  
6. Navigation guard salida.  
7. Policy mobile + i18n 5 locales.  
8. Vitest + E2E.  
9. Ajuste auth store: `ensurePermission` OR `pw_importacionmasiva` en `store` pedido/presupuesto.  
10. sessionStorage borrador (coordinado con 21c).  

---

## 8) Estrategia de pruebas

| Tipo | Cobertura |
|------|-----------|
| Vitest | Toggles, progreso x/N, append duplicate key, guard modal, mapPayload ≡ carga |
| Feature BE | Auth store OR: solo masiva OK; sin permiso 403 |
| E2E | Import feliz → 2 filas → Grabar 1 OK (mock/real) |
| Policy | Mobile exclude |

---

## 9) Definition of Done

- [ ] Menú usable web; ausente mobile  
- [ ] CA HU-044 verificables  
- [ ] Grabar secuencial + mismo payload/APIs individuales + mails  
- [ ] Auth OR documentada y testeada  
- [ ] i18n 5 locales + testids  

## 10) Decisiones C1 / notas D1

| ID | Decisión C1 |
|----|-------------|
| C1-21b-01 | Mismas APIs `POST /pedidos` y `POST /presupuestos` + mismo JSON que carga individual |
| C1-21b-02 | Gate store = `pw_cargapedidos` **OR** `pw_importacionmasiva` |

- Shell de pestañas: integrar blocker del producto existente si hay patrón.
- Hoy `PedidoController` usa `paqsuite_visibility.procedimientos.cargaComprobantes` (`pw_cargapedidos`) en varias acciones: **solo ampliar `store`** (pedido y presupuesto) con OR; no ampliar update/delete/edición en este slice salvo análisis D1 que demuestre compartición inevitable del helper.
