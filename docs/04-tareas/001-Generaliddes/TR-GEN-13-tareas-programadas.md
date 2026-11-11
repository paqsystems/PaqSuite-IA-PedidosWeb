# TR-GEN-13 — Tareas programadas y BACKUP_DATABASE

| Campo | Valor |
|-------|--------|
| **HU relacionada** | [HU-GEN-13-tareas-programadas](../../03-historias-usuario/001-Generaliddes/HU-GEN-13-tareas-programadas.md) |
| **SPEC** | [SPEC-001-09-tareas-programadas-update](../../05-open-spec/updates/001-Generaliddes/SPEC-001-09-tareas-programadas-update.md) |
| **Estado** | En Control Calidad |
| **Origen** | 00-ControlCalidad-PQ #19 — 03/11/2026 |

---

## 1) Backend

- Habilitar módulo scheduled tasks laravel-core (`TaskProcessRegistry`, handlers `BACKUP_DATABASE`).
- Migraciones + SP GEN-13 en pipeline installation update.
- Variables entorno backup (S3, RDS) documentadas en `docs/06-operacion/` (append runbook existente).
- **Prohibido** copiar handler backup al namespace `App\` del host.

## 2) Frontend

- Ruta `/general/tareas-programadas` → `TaskProcessesPage` SDK.
- Menú `gen_tareasprogramadas`, `grp_general`, orden 64.
- i18n títulos procesos (incl. backup).

## 3) AC técnicos

- **AC-T-01:** Listar procesos incluye `BACKUP_DATABASE`.
- **AC-T-02:** Definición guardada persiste en BD GEN-13.
- **AC-T-03:** Ejecución manual registra fila en historial ejecuciones.
- **AC-T-04:** Laravel scheduler / cron Forge documentado para disparar cola GEN-13.

## 4) Orden sugerido (D1)

1. Verificar GEN-13 en pin `1.3.18-beta.28` / react-core `2.4.30-beta.7`; bump si falta.
2. installation:update + status.
3. Menú + ruta.
4. Smoke definición backup en entorno dev (sin producción).

Ver [D1-PLAN-CC-PQ-19.md](../101-PedidosWeb/D1-PLAN-CC-PQ-19.md).
