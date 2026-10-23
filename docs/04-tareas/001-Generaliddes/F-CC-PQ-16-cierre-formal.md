# Cierre F — CC PQ #16 (12/09/2026) — Suspender logout por inactividad

## Alcance

Verificación **F1 + F** sobre suspensión temporal del logout por inactividad hasta adopción SDK Framework.

| # | Tema | Updates |
|---|------|---------|
| 1 | Desactivar timer de inactividad en host | SPEC/HU/TR GEN-02-expiracion-inactividad-update · D1-31 |

**Fecha verificación F1/F:** 12/09/2026  
**Parte E:** [E-CC-PQ-16-tests.md](E-CC-PQ-16-tests.md)  
**TR:** [TR-GEN-02-expiracion-inactividad](TR-GEN-02-expiracion-inactividad.md)

---

## F1 — Verificación agente (8 ejes)

**Resultado F1:** **Aprobado**

### 1. Alcance

| Pedido CC #16 | Implementado | Fuera de alcance respetado |
|---------------|--------------|----------------------------|
| Cancelar momentáneamente logout por inactividad | `inactivityLogoutEnabled=false` | Sin borrar motor ni `MinutosWeb` |
| Restablecer al integrar SDK Framework | Comentario + constante documentada | Sin implementar SDK en este cambio |

### 2. Código

| Pieza | Evidencia |
|-------|-----------|
| Flag | `frontend/src/features/auth/sessionInactivity.ts` → `inactivityLogoutEnabled` |
| Shell | `SessionLifecycleManager.tsx` → `enabled: inactivityLogoutEnabled && …` |

### 3. Base de datos

Sin cambios.

### 4. Backend / API

Sin cambios. `inactivityTimeoutMinutes` sigue pudiendo exponerse; el FE no arma el timer.

### 5. Frontend

Timer de inactividad deshabilitado; logout manual y 401 intactos.

### 6. Tests

Unit del flag + E2E smoke de no-expiración (ver Parte E).

### 7. Documentación

SPEC/HU/TR-update + CC #16 (renumerado desde duplicado #15 del 12/09) + este F.

### 8. Trazabilidad

D1-31 enlazado en CC, SPEC-update, HU-update y TR-update.

---

## Veredicto F

**Aprobado** — suspensión operativa lista; reactivación pendiente de adopción SDK Framework.

## Parte I (12/09/2026)

Updates SPEC/HU/TR fusionados en bases y eliminados. CC #16 → **Finalizado**.
