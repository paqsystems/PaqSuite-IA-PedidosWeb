# Consulta de parámetros — fuente de verdad

| Campo | Valor |
|-------|--------|
| **Estado** | Vigente — **implementado** (D1 2026-06-03) |
| **HU** | [HU-GEN-04-consulta-parametros](../../03-historias-usuario/001-Generaliddes/HU-GEN-04-consulta-parametros.md) |
| **Menú** | **General** (`grp_general`) → último ítem → *Consulta de parámetros* |
| **Proceso** | `pw_consultaparametros` |
| **Ruta UI** | `/general/parametros` |
| **API** | `GET /api/v1/config/parametros?programa=PedidosWeb` |
| **Permiso** | `Permiso_Repo` sobre `pw_consultaparametros`; edición **inhibida** |

---

## 1) Objetivo

Mostrar el inventario de **`PQ_PARAMETROS_GRAL`** del tenant (módulo **PedidosWeb** y, si aplica transversalmente, parámetros globales visibles), con la **misma definición funcional y técnica** desarrollada en **PaqSuite-IA-Tango** / MONO HU-007.

En **este proyecto** los valores provienen del **ERP**; la pantalla es **solo consulta** (sin botón Editar, sin persistencia web).

---

## 2) Marco transversal (MONO)

Fuente del proceso reutilizable:

- `docs/00-contexto/_mono/04-configuracion-global/parametros-generales.md`
- `docs/_base/pq-parametros-gral-tipo-valor.md`

En Tango / MONO editable: listado con `Clave`, caption, valor homogéneo, tooltip, modal de edición por tipo.

En **PedidosWeb**: misma presentación de listado; **sin** modal de edición ni `PUT`/`PATCH`.

---

## 3) Datos y seed

| Recurso | Ubicación |
|---------|-----------|
| Inventario PedidosWeb (57 claves) | [`docs/backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json`](../../backend/seed/PQ_PARAMETROS_GRAL/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json) |
| CAPTION / TOOLTIP SQL | [`Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql`](../../backend/seed/PQ_PARAMETROS_GRAL/Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql) |
| Contrato Tango (referencia) | PaqSuite-IA-Tango — `docs/backend/seed/PQ_PARAMETROS_GRAL/README.md` |
| Producto §10.6 | [PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md](./PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md) |
| SPEC | [SPEC-001-04-configuracion-global.md](../../05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md) |

Filtro de filas: `Programa = 'PedidosWeb'` (comparación case-insensitive según MONO).

---

## 4) Columnas UI y orden

| Columna | Origen | Visible |
|---------|--------|---------|
| Descripción | `CAPTION` (fallback interno: `Clave` si `CAPTION` vacío) | Sí |
| Valor | Valor efectivo según `tipo_valor` → columna `Valor_*` | Sí |
| Tooltip | `TOOLTIP` (hover o panel informativo) | Sí |
| Tipo | `tipo_valor` | Oculta por defecto |
| Clave (`Clave`) | Identificador técnico ERP | **No** se muestra en grilla |

**Orden del listado:** `CAPTION` ascendente; si `CAPTION` está vacío, usar `Clave` como criterio de orden (misma fila, sin mostrar la clave).

Booleanos: etiquetas localizadas Sí/No (`pedidos.carga.cabecera.si` / `no`).

---

## 5) Internacionalización (i18n)

La API devuelve `caption` y `tooltip` desde BD (semilla en español). La UI **no** debe mostrarlos tal cual en locales distintos de `es` si existen traducciones.

| Elemento | Resolución |
|----------|------------|
| Columna Descripción | `parametros.pedidosWeb.{Clave}.caption` → fallback `row.caption` (BD) |
| Columna Tooltip | `parametros.pedidosWeb.{Clave}.tooltip` → fallback `row.tooltip` (BD) |
| Valor booleano | `pedidos.carga.cabecera.si` / `no` cuando `tipoValor === 'B'` |
| Fechas | `toLocaleDateString()` según locale activo cuando `tipoValor === 'D'` |

**Archivos de recursos:** `frontend/src/locales/parametros/pedidosWeb.{en,it,fr,pt}.json` (57 claves × caption + tooltip). Fusión en `i18n.ts` al bootstrap.

**Código:** `frontend/src/features/config/utils/resolveParametroConsultaTexts.ts`; página `ParametrosConsultaPage.tsx` remapea filas en `useEffect` dependiente de `i18n.language`.

**QA:** con locale `it`, la grilla `/general/parametros` debe mostrar descripciones en italiano (p. ej. «Minuti Web»), no el `CAPTION` español de BD.

Patrón transversal: [`idioma-multilingual.md`](../../00-contexto/_mono/01-experiencia-base/idioma-multilingual.md) § Consulta de parámetros.

---

## 6) Reglas PedidosWeb

1. **Solo lectura:** no `POST`/`PUT`/`DELETE` sobre parámetros desde el portal.
2. Administración de valores: ERP / herramientas internas (producto §10.6, SPEC-001-04).
3. El backend de carga (`ParametrosCargaService`, `PedidosWebParameterService`) sigue leyendo parámetros en runtime; esta pantalla es informativa.
4. Textos visibles vía i18n (`parametros.pedidosWeb.*`); `CAPTION`/`TOOLTIP` de BD como **fallback** si falta clave.

---

## 7) API

```http
GET /api/v1/config/parametros?programa=PedidosWeb
```

**Autorización:** Bearer + `Permiso_Repo` en `pw_consultaparametros`.

Respuesta envelope MONO:

```json
{
  "items": [
    {
      "clave": "MinutosWeb",
      "caption": "...",
      "tooltip": "...",
      "tipoValor": "I",
      "valorMostrado": "30"
    }
  ]
}
```

---

## 8) Menú

| Campo seed | Valor |
|------------|-------|
| Grupo | `grp_general` (`tipoProceso`: `G`, texto «General») |
| Ítem | `pw_consultaparametros` — último `orden` del grupo |
| Ruta | `/general/parametros` |
| Permiso | `Permiso_Repo` |

TR: [TR-GEN-04-consulta-parametros](../../04-tareas/001-Generaliddes/TR-GEN-04-consulta-parametros.md) (**C1** cerrada 2026-06-03).

---

## 9) Referencias implementación Tango

Reutilizar como referencia de lectura (no copiar edición):

- Servicio/repository de parámetros generales
- Mapeo `ParametrosGralTipoValor::fromRow()` (`backend/app/Support/ParametrosGralTipoValor.php`)
- Componente grilla listado (adaptar quitando acciones Editar)
