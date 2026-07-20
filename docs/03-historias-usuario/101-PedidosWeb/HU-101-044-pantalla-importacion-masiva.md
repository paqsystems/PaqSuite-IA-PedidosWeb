# HU-101-044 — Pantalla importación masiva (grilla, import, grabación FE)

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-044-pantalla-importacion-masiva |
| **SPEC origen** | [SPEC-101-21-importacion-masiva-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Épica** | 101 — PedidosWeb / Importación masiva |
| **Prioridad** | **Should** |
| **Estado** | **Especificado** |
| **B1** | **Cerrado** (2026-07-19) |
| **TR** | [TR-SPEC-101-21-pantalla-importacion-masiva](../../04-tareas/101-PedidosWeb/TR-SPEC-101-21-pantalla-importacion-masiva.md) |
| **Dependencias** | [HU-101-043](HU-101-043-proceso-excel-pedido-masivo.md); HU-GEN-07-ui-embebida-host; [HU-101-009](HU-101-009-grabar-pedido.md); [HU-101-010](HU-101-010-grabar-presupuesto.md); [HU-101-019](HU-101-019-mail-grabar.md) |
| **HUs relacionadas** | [HU-101-045](HU-101-045-consultar-borrador-importacion-masiva.md) |

## Trazabilidad SPEC

| Criterio / entregable SPEC | Cobertura en esta HU |
|----------------------------|----------------------|
| SPEC CA-01 menú/grilla vacía/permiso | RN-01, RN-02, CA-01 |
| SPEC CA-03 filas Pedido multi-grupo | RN-03, CA-03, CA-04 |
| SPEC CA-04 vendedor en grilla | CA-05 |
| SPEC CA-06 toggles | RN-06, CA-06 |
| SPEC CA-08 eliminar | RN-07, CA-08 |
| SPEC CA-09 reimport | RN-04, RN-05, CA-09 |
| SPEC CA-10 grabar FE + progreso | RN-08 … RN-11, CA-10, CA-16 |
| SPEC CA-11 mails | RN-15, CA-11 |
| SPEC CA-12 modal salida | RN-12, CA-12 |
| SPEC CA-13 borrador solo memoria | RN-14, CA-17 |
| SPEC CA-14 mobile | RN-01, CA-14 |
| §4 columnas / toolbar / DevExtreme / i18n | RN-02, CA-02, CA-15 |
| AMB-C-03 permiso grabar P/P | RN-16, CA-01 |
| AMB-M-06…08, 10–11, 13 | RN-04, RN-05, RN-08 … RN-13 |
| Acción Consultar (host) | CA-07 → detalle [045](HU-101-045-consultar-borrador-importacion-masiva.md) |

## Narrativa

Como **usuario con permiso de importación masiva**,  
quiero **ver en una grilla los comprobantes importados, marcarlos como pedido o presupuesto y grabarlos uno a uno con progreso visible**,  
para **procesar un lote completo sin que un error detenga al resto**.

## Contexto funcional

SPEC-101-21 §1, §4–§6 y §7 (eliminar/salida): pantalla host **`pw_importacionmasiva`**, borrador solo en sesión del navegador, import vía `PEDIDO_MASIVO` (043), ajuste de tipo, eliminación, reimportación y grabación **FE secuencial** a APIs individuales (sin endpoint de lote). Mails por cada OK (101-13).

## Actores

| Actor | Uso |
|-------|-----|
| Usuario con `pw_importacionmasiva` | Opera pantalla; puede grabar pedidos **y** presupuestos del lote (AMB-C-03) |
| Sin permiso | Sin menú / proceso no accesible |

## Alcance incluido

- Seed menú + permiso `pw_importacionmasiva` (web); i18n ES ref. «Importación masiva».
- Exclusión mobile / `pedidosWebMobilePolicy`.
- Ruta/pantalla grilla DevExtreme (§4): toggle tipo, cliente código+razón, vendedor código+desc, nivel (RO), totales sin/con IVA, Error, acciones Consultar/Eliminar.
- Toolbar: descargar plantilla, importar, marcar todos pedidos/presupuestos, Grabar.
- Modal reimport (reemplazar / agregar / cancelar) + orden al agregar (AMB-M-08).
- Modal eliminar con confirmación.
- Orquestación Grabar: «Cargando x de N», bloqueo concurrente, best-effort, toast resumen.
- Modal salida con borrador no vacío.
- Borrador no sobrevive logout/nueva sesión (SPEC CA-13).
- i18n 5 locales + `data-testid` (nombres en TR).

## Fuera de alcance

- Handler/agrupación backend → [HU-101-043](HU-101-043-proceso-excel-pedido-masivo.md).
- Comportamiento readonly de Consultar / hidratación → [HU-101-045](HU-101-045-consultar-borrador-importacion-masiva.md).
- Edición cabecera/renglones en grilla o desde Consultar.
- Persistencia borrador en servidor.
- Endpoint de grabación lote.
- Mobile.

## Datos involucrados

| Objeto | Rol |
|--------|-----|
| Store/sesión de pantalla | Borrador de filas (`idInterno`, tipo, cabecera, renglones, error) |
| APIs grabar pedido/presupuesto | Persistencia + mails por ítem |
| Componente Excel host GEN-07 | Plantilla + import `PEDIDO_MASIVO` |

## Reglas de negocio

1. **RN-01:** Solo web; no listar/habilitar en mobile.
2. **RN-02:** Al abrir con permiso, grilla vacía; columnas §4.
3. **RN-03:** Import OK aplica grupos de 043; cada fila nueva `esPedido=true`; Excel no define tipo.
4. **RN-04:** Reimport con filas → modal reemplazar / agregar / cancelar.
5. **RN-05:** Agregar: misma clave visual → nuevo `idInterno`; conservar orden existente; anexar nuevos al final.
6. **RN-06:** Marcar todos pedidos/presupuestos; toggle individual siempre permitido después.
7. **RN-07:** Eliminar → siempre confirmación; solo memoria.
8. **RN-08:** Grabar = snapshot al inicio; N llamadas FE en orden de grilla; no cancelable; bloquear acciones concurrentes.
9. **RN-09:** OK → quitar fila; error → dejar + columna Error (mensaje envelope/i18n); continuar.
10. **RN-10:** Progreso «Cargando x de N» (N fijo al inicio; x avanza OK o error); toast final OK/Error.
11. **RN-11:** Grabar deshabilitado si no hay filas.
12. **RN-12:** Salida con borrador: cancelar (descarta) / grabar todo (§6) / retornar; abandonar tras grabar todo **solo** si 100 % OK.
13. **RN-13:** `excelImportEnabled=false` → ocultar/deshabilitar plantilla e import.
14. **RN-14:** Borrador solo memoria/sesión navegador; no aparece tras logout/nueva sesión.
15. **RN-15:** Cada grabación exitosa dispara el mismo flujo de mails que individual (101-13).
16. **RN-16:** Autorización de grabación del lote = `pw_importacionmasiva` (pedido y presupuesto).
17. **RN-17:** Totales en grilla con las mismas funciones conceptuales que carga/import individual (SPEC §3).
18. **RN-18:** No fusionar al agregar (SPEC fuera de alcance).

## Criterios de aceptación

- [ ] **CA-01:** Con permiso, menú abre grilla vacía; sin permiso no accesible (SPEC CA-01).
- [ ] **CA-02:** Columnas: toggle, cliente código+razón, vendedor código+desc, nivel, totales, Error, Consultar, Eliminar.
- [ ] **CA-03:** Import exitoso con ≥2 grupos → ≥2 filas Pedido (SPEC CA-03 UI).
- [ ] **CA-04:** Filas nuevas = Pedido; Excel no define tipo.
- [ ] **CA-05:** Vendedor visible = código+nombre del maestro (SPEC CA-04).
- [ ] **CA-06:** Marcar todos pedidos/presupuestos + cambio individual (SPEC CA-06).
- [ ] **CA-07:** Existe control Consultar por fila (comportamiento → HU-045 / SPEC CA-07).
- [ ] **CA-08:** Eliminar pide confirmación y saca solo del borrador (SPEC CA-08).
- [ ] **CA-09:** Reimport no vacío: reemplazar / agregar / cancelar; misma clave al agregar → 2ª fila (SPEC CA-09).
- [ ] **CA-10:** Grabar = FE secuencial + «Cargando x de N»; fallo no detiene resto; OK salen; Error queda; toast (SPEC CA-10).
- [ ] **CA-11:** Cada OK dispara mail como grabación individual (SPEC CA-11).
- [ ] **CA-12:** Modal salida 3 opciones; grabar todo solo cierra si 100 % OK (SPEC CA-12).
- [ ] **CA-13:** `excelImportEnabled=false` → plantilla/import no usable.
- [ ] **CA-14:** Proceso no disponible en mobile (SPEC CA-14).
- [ ] **CA-15:** i18n 5 locales de strings de pantalla; DevExtreme en controles acordados.
- [ ] **CA-16:** Durante Grabar no se puede editar/reimportar/grabar de nuevo hasta fin de ciclo.
- [ ] **CA-17:** Tras logout o nueva sesión, el borrador no reaparece (SPEC CA-13).
- [ ] **CA-18:** Grabar sin filas no ejecuta (botón deshabilitado).
- [ ] **CA-19:** Vitest toggles/modales/progreso; E2E camino feliz web (import → grabar OK).

## Casos negativos

| Caso | Resultado esperado |
|------|-------------------|
| Import con errores 043 | Grilla sin cambio; errores GEN-07 |
| Cerrar pestaña del navegador con borrador | Se pierde (sin server draft) — sin modal de “antesunload” obligatorio en SPEC |
| Grabar todo desde modal con 1 error | Permanece en proceso con fila Error |

## Escenarios Gherkin

```gherkin
Feature: Pantalla importación masiva
  Scenario: Grabación parcial con progreso
    Given una grilla con 3 borradores como Pedido
    And el segundo fallará al grabar
    When el usuario pulsa Grabar
    Then ve el progreso "Cargando x de 3" en secuencia
    And al finalizar queda 1 fila con Error
    And las otras 2 ya no están en la grilla
    And se muestra toast con 2 OK y 1 error

  Scenario: Reimportar agregando
    Given la grilla tiene 1 fila
    When el usuario importa otro Excel y elige Agregar
    Then las filas nuevas se anexan al final
    And si la clave coincide se crea otra fila con idInterno distinto

  Scenario: Salida con borrador
    Given hay filas sin grabar
    When el usuario intenta cambiar de menú
    Then ve modal Cancelar proceso / Grabar todo / Retornar

  Scenario: Permiso insuficiente
    Given un usuario sin pw_importacionmasiva
    When intenta abrir el proceso
    Then no accede a la pantalla

  Scenario: Marcar todos presupuestos
    Given una grilla con 2 filas Pedido
    When marca todos como Presupuestos
    Then ambas filas quedan como Presupuesto
    And puede volver a cambiar una individualmente a Pedido
```

## Supuestos explícitos

- Las APIs individuales de pedido/presupuesto aceptan el payload armado desde el borrador (mismo contrato funcional que carga).
- Store de borrador: memoria de pantalla / sesión de navegación (detalle TR); sin tabla BD.
- «Cargando x de N» puede mostrarse como overlay/mensaje; texto i18n `pedidos.importacionMasiva.grabandoProgreso`.

## Preguntas abiertas

Ninguna bloqueante. Ruta exacta y `data-testid` en TR.

## Riesgos de ambigüedad

Medio-bajo: mapeo borrador → body de grabación (TR). Bloqueo concurrente y progreso están cerrados en SPEC.

## Veredicto B1

**Lista para TR: Sí** — cubre SPEC CA-01, 03–04, 06, 08–14 y orquestación §4–§6.
