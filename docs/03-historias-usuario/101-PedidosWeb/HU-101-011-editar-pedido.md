# HU-101-011 — Edición de pedido ingresado

| Campo | Valor |
|-------|--------|
| **ID** | HU-101-011-editar-pedido |
| **SPEC origen** | [SPEC-101-04-services-pedidos](../../05-open-spec/101-PedidosWeb/SPEC-101-04-services-pedidos.md), [SPEC-101-10](../../05-open-spec/101-PedidosWeb/SPEC-101-10-pantalla-carga.md) |
| **Prioridad** | Must |
| **Estado** | Finalizado |
| **B1** | Enriquecida (2026-06-01) |

## Narrativa

Como **usuario con permiso**,  
quiero **editar un pedido ingresado (estado 0 o en modificación -1)**,  
para **corregir datos antes de la descarga al ERP**, respetando el bloqueo paramétrico ante interrupciones.

## Reglas de negocio

1. Editable en estado **0** y, con restricción, en estado **-1** (modificación en curso — producto §9, §21).
2. No editable en estados **1** o **2**.
3. Al iniciar edición desde **0**, el portal pasa a **-1**, registra **`fechahora_inicio_proceso`** (auditoría) y **`fechahora_ultima_actividad`** = ahora.
4. Durante la edición, cada actividad relevante actualiza **`fechahora_ultima_actividad`** (guardado parcial, cambios de renglón, heartbeat definido en TR).
5. **Ventana paramétrica (`MinutosWeb`):** evaluar solo **`fechahora_ultima_actividad`** (no `fechahora_inicio_proceso`):
   - Si `fechahora_ultima_actividad + MinutosWeb >= fechahora_actual` → **modificación activa** (otro usuario **no** puede abrir ese pedido en **-1**).
   - Si `fechahora_ultima_actividad + MinutosWeb < fechahora_actual` → **interrupción vencida** → **sí** permitir retomar edición del pedido en **-1**.
6. Descarga ERP concurrente: además validar `MinutosBloqueo` + `MinutosAviso` (producto §21; SPEC-001-04) antes de pasar a **-1**.
7. Al **Grabar pedido**, **Cancelar** o abandonar según TR: volver a **0** si corresponde y limpiar marcas de modificación.
8. La edición usa la **misma pantalla** que presupuesto (HU-101-009/010, SPEC-101-10).

## Criterios de aceptación

- [ ] **CA-01:** Edición exitosa en **0** pasa por **-1** durante la sesión y vuelve a **0** al grabar.
- [ ] **CA-02:** Intento sobre pedido **1** o **2** → rechazo claro.
- [ ] **CA-03:** Acceso desde consulta ingresados (ícono editar + tooltip i18n).
- [ ] **CA-04:** Pedido **-1** con `fechahora_ultima_actividad + MinutosWeb < ahora` → **puede** retomarse.
- [ ] **CA-05:** Pedido **-1** con `fechahora_ultima_actividad + MinutosWeb >= ahora` y otro usuario → rechazo.
- [ ] **CA-06:** Tras actividad del editor, `fechahora_ultima_actividad` se actualiza y extiende la ventana.
- [ ] **CA-07:** Parámetro `MinutosWeb` desde ERP (SPEC-001-04).

## Escenarios Gherkin

```gherkin
Feature: Edición pedido estado -1 y ventana MinutosWeb

  Scenario: Modificación activa impide segundo editor
    Given un pedido en estado -1
    And fechahora_ultima_actividad + MinutosWeb >= ahora
    When otro usuario intenta editar
    Then el sistema rechaza la edición

  Scenario: Interrupción vencida permite retomar
    Given un pedido en estado -1
    And fechahora_ultima_actividad + MinutosWeb < ahora
    When un usuario con permiso abre edición
    Then puede modificar y grabar pedido
```

## Veredicto B1

**Lista para TR** (SPEC-101-04/05/10/11).
