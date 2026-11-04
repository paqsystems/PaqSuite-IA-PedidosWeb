# Smoke manual — CC #19 piloto emisión (GEN-15)

| Campo | Valor |
|-------|--------|
| **Alcance** | Pedidos ingresados + Deuda (web) |
| **Prereq BD** | Tablas `pq_emission_*` + SP (GEN-15) — ver D1 R5 |
| **Prereq datos** | `paqsuite:seed-menus-mvp`, `paqsuite:seed-seguridad-mvp`, `paqsuite:seed-deploy` (sin `--skip-emission`) |

## Entorno

| Servicio | URL |
|----------|-----|
| Frontend | http://localhost:3010/?cliente=desarrollo |
| Backend | http://127.0.0.1:8088 |
| OpenAPI | http://127.0.0.1:8088/api/documentation |

Levantar stack (sin sync BD si aún no corres GEN-34):

```powershell
.\scripts\dev\start-dev.ps1 -All
```

Variables útiles: `PAQSUITE_EMISSION_ENABLED=true`, `VITE_EMISSION_ENABLED` unset o `true`, `PAQSUITE_EMISSION_DX_MODE=fake`.

## Usuario sugerido

- Código: `supervisor.mvp`
- Contraseña: valor de `SEED_MVP_PASSWORD` en `backend/.env` (tras `seed-seguridad-mvp`).

## Checklist — Pedidos ingresados

1. Login → menú **Pedidos ingresados** (`/pedidos/ingresados`).
2. Grilla con al menos una fila cargada.
3. Toolbar: botón **Emitir** visible (`data-testid="gridEmitReport"`), no disabled.
4. Clic **Emitir** → popup `data-testid="emission.dialog"`.
5. Canal PDF seleccionado; **Emitir** (`emission.submit`) → job termina en **done** (sync) o polling async.
6. **Descargar** (`emission.download`) → archivo `.pdf` que abre y comienza con `%PDF`.

## Checklist — Deuda

1. Menú **Deuda** (`/consultas/deuda`).
2. Misma secuencia 2–6 con `processCode` backend `pw_deudaclientes` (permiso menú `pw_deuda`).

## Checklist — Diseñador (opcional mismo día)

1. Menú **Diseñador de reportes** (`/gen/diseniador-reportes` o ruta MVP configurada).
2. Supervisor ve procesos; confirmar proceso → listado reportes piloto.

## API rápida (Postman / curl)

Con token Bearer post-login:

| Método | Ruta | Esperado |
|--------|------|----------|
| GET | `/api/v1/emissions/processes/pw_pedidosingresados` | `error: 0`, `reports` con principal |
| POST | `/api/v1/emissions/jobs` body `{ "processCode":"pw_pedidosingresados", "channel":"pdf", "mode":"consolidated" }` | `error: 0`, `item.status` = `done` o `queued` |
| GET | `/api/v1/emissions/jobs/{jobId}/download` | `Content-Type: application/pdf`, body `%PDF` |

Headers: `Authorization: Bearer …`, `X-Paq-Cliente: desarrollo`.

## Evidencia F1 (capturas)

- [ ] Toolbar con **Emitir** (ingresados)
- [ ] Diálogo emisión abierto
- [ ] Descarga PDF OK (ingresados)
- [ ] Idem deuda

## Resultado intento 04/11/2026 (sin artisan seed en sesión)

- Stack dev levantado (`start-dev.ps1 -All`).
- Login `supervisor.mvp` + password `.env.example` → **auth.invalidCredentials** (usuarios MVP no seedeados en BD actual).
- **Smoke UI autenticado pendiente** hasta ejecutar seeds + GEN-15 en BD (según acuerdo: lo harás en ejecución).
