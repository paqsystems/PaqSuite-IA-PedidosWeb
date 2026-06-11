# HU-GEN-07-plantilla-excel — Plantilla modelo por proceso

| Campo | Valor |
|-------|--------|
| **ID** | HU-GEN-07-plantilla-excel |
| **SPEC origen** | [SPEC-001-07-importar-excel.md](../../05-open-spec/001-Generaliddes/SPEC-001-07-importar-excel.md) |
| **MONO** | [PQ_EXCEL_Documento_Conceptual_Funcional_v3.md](../../00-contexto/_mono/importar-excel/PQ_EXCEL_Documento_Conceptual_Funcional_v3.md) §12 |
| **Épica** | 001 — Generalidades / Importar Excel |
| **Prioridad** | Could |
| **Estado** | Pendiente |
| **B1** | Enriquecida (2026-06-11) |
| **Última actualización** | 2026-06-11 |
| **Dependencias** | HU-GEN-02-autorizacion-menu-api; tablas `PQ_EXCEL_PROCESOS`, `PQ_EXCEL_PROCESOS_CAMPOS` |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| Flujo paso 2: exportar plantilla modelo | Narrativa, CA-01 |
| Diseño fijo por proceso (sin mapeo manual) | RN-01, RN-02 |
| Encabezados exactos sin tildes/símbolos | RN-03, CA-03 |
| Inventario tablas `PQ_EXCEL_PROCESOS*` | Alcance |
| Fuera de alcance MVP portal | Metadatos, veredicto B1 |

## Narrativa

Como **usuario autorizado en un proceso de importación**,  
quiero **descargar la plantilla Excel oficial del proceso**,  
para **completar el archivo con la estructura exacta que el sistema espera**.

## Contexto funcional

Cada proceso de importación tiene diseño fijo en `PQ_EXCEL_PROCESOS_CAMPOS`. La plantilla se genera desde esa definición: fila 1 con `NombreColumnaExcel`, orden según `OrdenCampo`, formato por `TipoDato`, validaciones de celda según `LargoMaximo`/`CantidadDecimales` y comentarios desde `Observaciones`. Solo procesos con `GeneraPlantilla = 1` exponen la acción.

## Alcance incluido

- Acción **Descargar plantilla** en la pantalla del proceso de importación.
- Generación `.xlsx` con encabezado fila 1 (fondo azul, letras blancas).
- Columnas activas (`Activo = 1`) en orden `OrdenCampo`.
- Formato de columna según `TipoDato` (`texto`, `entero`, `decimal`, `fecha`, `booleano`, `codigo`).
- Validación de celda Excel según `LargoMaximo` y `CantidadDecimales` cuando aplique.
- Comentario de celda en encabezado si `Observaciones` no está vacío.
- Nombre de archivo sugerido: `{CodigoProceso}_plantilla_{fecha}.xlsx`.

## Fuera de alcance

- Edición web de definición de campos (`PQ_EXCEL_PROCESOS_CAMPOS`).
- Múltiples variantes de plantilla por proceso.
- Importación del archivo (otras HU-GEN-07).
- MVP portal PedidosWeb (`PedidosWeb_SPEC_MVP.md`).

## Reglas de negocio

1. La plantilla refleja **exactamente** los `NombreColumnaExcel` activos del proceso; sin alias ni corrección tipográfica.
2. Encabezados solo con letras sin tildes, números y espacios (contexto §4).
3. Si `GeneraPlantilla = 0`, la acción no se muestra.
4. Permisos: mismo criterio que ejecutar el proceso de importación (definir en TR por `CodigoProceso` / menú).
5. La plantilla exportada es la referencia normativa para validación estructural en carga.

## Decisiones cerradas (producto / B1)

| Tema | Decisión |
|------|----------|
| Fuente de columnas | `PQ_EXCEL_PROCESOS_CAMPOS` orden `OrdenCampo`, solo `Activo = 1` |
| Estilo encabezado | Fondo azul, texto blanco (contexto §12) |
| Formato booleano en plantilla | Definir por proceso en TR (`0`/`1`, `N`/`S` o `VERDADERO`/`FALSO`) según `HandlerBackend` |
| MVP portal | **Fuera** — epic posterior |

## Criterios de aceptación

- [ ] **CA-01:** Usuario con permiso ve acción Descargar plantilla en proceso con `GeneraPlantilla = 1`.
- [ ] **CA-02:** Archivo `.xlsx` con fila 1 = nombres visibles del proceso en orden correcto.
- [ ] **CA-03:** Encabezados coinciden carácter a carácter con `NombreColumnaExcel` (sin tildes ni símbolos prohibidos).
- [ ] **CA-04:** Columnas inactivas no aparecen en la plantilla.
- [ ] **CA-05:** Comentarios de encabezado visibles cuando `Observaciones` tiene valor.
- [ ] **CA-06:** Proceso con `GeneraPlantilla = 0` no muestra la acción.
- [ ] **CA-07:** i18n en etiqueta de acción y mensajes de error; `data-testid` estable (`excelTemplateDownload`).

## Escenarios Gherkin

```gherkin
Feature: Plantilla modelo Excel por proceso

  Scenario: Descargar plantilla oficial
    Given un usuario con permiso en el proceso ARTICULOS_ALTA
    And el proceso tiene GeneraPlantilla activo
    When descarga la plantilla modelo
    Then obtiene un archivo xlsx
    And la fila 1 contiene exactamente los encabezados definidos en PQ_EXCEL_PROCESOS_CAMPOS

  Scenario: Sin generación de plantilla
    Given un proceso con GeneraPlantilla deshabilitado
    When el usuario abre la pantalla del proceso
    Then no ve la acción Descargar plantilla

  Scenario: Comentario en encabezado
    Given un campo con Observaciones "Debe venir como texto"
    When descarga la plantilla
    Then la celda de encabezado de ese campo tiene el comentario indicado
```

## Supuestos explícitos

- Tablas `PQ_EXCEL_*` creadas según `PQ_EXCEL_SQL_Server_Tablas_y_Create.md`.
- Librería de generación Excel reutiliza patrones de exportación transversal (`HU-GEN-03-exportaciones`) donde aplique.

## Preguntas abiertas

| ID | Pregunta | Propuesta default |
|----|----------|-------------------|
| AMB-Q-07-01 | ¿Permiso dedicado `Permiso_Importar` vs permiso del proceso host? | Mismo permiso que la pantalla que invoca la importación (TR por proceso) |

## Veredicto B1

**Lista para TR** — epic importar Excel posterior al MVP portal. Resolver AMB-Q-07-01 en TR.
