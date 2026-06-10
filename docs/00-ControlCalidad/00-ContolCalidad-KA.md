# Control de Calidad — KA

| Campo | Valor |
|-------|--------|
| **ID archivo** | `00-ControlCalidad-KA` |
| **Responsable** | Klauss Amapane (KA) |
| **Alcance** | Hallazgos de pruebas manuales y mejoras solicitadas por cliente en PaqSuite PedidosWeb |
| **Metodología** | Open-Spec / SDD — [`_OPEN-SPEC-METODOLOGIA.md`](../_base/_OPEN-SPEC-METODOLOGIA.md) |
| **Dispatcher** | Parte **G** (volcado), **H** (cierre opcional), ciclo **G → D → E → F → I** |

## Propósito

Registro operativo de **incidencias y mejoras** detectadas fuera del flujo automatizado de tests. Cada sesión de control se numera secuencialmente y conserva trazabilidad hasta su derivación a **SPEC-update**, **HU-update** y **TR-update** en `docs/.../updates/`.

Este archivo **no sustituye** SPEC, HU ni TR: es la **entrada** del circuito de correcciones (Parte G).

## Convenciones

| Tema | Regla |
|------|-------|
| **Fecha** | Formato `dd/MM/yyyy` en todo el documento |
| **Bloques** | `## Control de Calidad #N` — numeración incremental |
| **Ítems** | Preferir `### HU-XXX-slug` cuando la HU sea identificable |
| **Marcas de gestión** | `*Procesado*` tras volcado G; `*Sugerencia: HU-…*` si aún no hay HU asociable |
| **Comando de volcado** | `Corrige los errores del dd/MM/yyyy de KA` (o *Realiza las mejoras…* / *Procesa las solicitudes…*) |

## Estados del bloque (*Referencia del control*)

| Estado | Significado |
|--------|-------------|
| **Pendiente** | Control registrado; ítems sin volcar a `updates/` |
| **Con Sugerencias** | Volcado parcial: quedan ítems con sugerencia de HU sin archivo generado |
| **A Programar** | Todas las entradas marcadas `*Procesado*`; pendiente cierre formal G/H |
| **Especificado** | Parte G (o H) cerró el bloque: volcado documental completo; cola activa en `docs/.../updates/` — **no** implica código implementado ni **`Finalizado`** en metadatos de HU/TR |

> El **Estado** bajo *Referencia del control* es independiente del **Estado** en metadatos de HU/TR ([`07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)).

## Flujo tras registrar un control

1. Registrar hallazgos en el bloque `#N` con estado **Pendiente**.
2. Ejecutar **Parte G** (`Corrige… dd/MM/yyyy de KA`): §0 SPEC-update si cambia el alcance; HU-update / TR-update.
3. Implementar (**D**), tests (**E**), verificación (**F**).
4. Marcar updates **`Finalizado`** (manual) y **Parte I** (unificar).

## Índice de controles

| # | Fecha | Estado | Resumen |
|---|-------|--------|---------|
| 1 | 04/06/2026 | Pendiente | *(sin detalle aún)* |

---

## Control de Calidad #1

### Referencia del control

| Campo | Valor |
|-------|--------|
| **Fecha** | 04/06/2026 |
| **Responsable** | Klauss Amapane (KA) |
| **Estado** | Pendiente |
| **Entorno probado** | *(completar: local / staging / producción)* |
| **Build / rama** | *(completar si aplica)* |

### Hallazgos

Registrar cada ítem bajo la HU correspondiente (`### HU-XXX-slug`) o como bullet con contexto suficiente para derivar HU en Parte G.

#### Errores encontrados

*(pendiente de detalle)*

#### Mejoras solicitadas

*(si aplica)*

---

## Referencias

- Dispatcher Parte G/H/I: [`.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md`](../../.cursor/rules/base/00-arquitectura/01-prompts-programados-dispatcher.md)
- Estados HU/TR: [`.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md`](../../.cursor/rules/base/00-arquitectura/07-estado-hu-tr.md)
- Gobernanza SPEC-update: [`.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md`](../../.cursor/rules/base/00-arquitectura/08-open-spec-gobernanza.md)
