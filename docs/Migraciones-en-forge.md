# Migraciones en Forge y AWS

Guía operativa para aplicar esquema y datos iniciales en entornos desplegados con **Laravel Forge** (servidor EC2) y base **AWS RDS SQL Server**.

---

## Arquitectura (concepto clave)

`php artisan migrate` **no se ejecuta en RDS**. Se ejecuta en el **servidor Forge** (VM), dentro del proyecto Laravel (`backend/`). Laravel lee el `.env` del servidor y se conecta a la base en AWS.

```
Forge (EC2)                         AWS RDS (SQL Server)
     |                                      |
     |  php artisan migrate --force         |
     |------------------------------------->|  CREATE / ALTER tablas
```

| Componente | Rol |
|------------|-----|
| **Forge** | Código PHP, `artisan`, deploy, `.env` del backend |
| **RDS** | Solo almacena datos y esquema |
| **Vercel** | Solo frontend React (no ejecuta `migrate`) |

---

## Opción A — Automático en cada deploy (recomendado)

1. Entrar a [forge.laravel.com](https://forge.laravel.com).
2. **Server** → sitio del backend (ej. `backankas.on-forge.com`).
3. Pestaña **Deployments** → **Edit Deploy Script**.
4. Incluir `php artisan migrate --force` después de `composer install` y dentro de `backend/`:

```bash
cd /home/forge/tu-dominio.com

git pull origin $FORGE_SITE_BRANCH

cd backend

composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

php artisan migrate --force

# Catálogos idempotentes: menú MVP, Excel PEDIDO_INDIVIDUAL/PEDIDO_MASIVO,
# atributos visibility y proveedores chat (no toca passwords salvo --with-seguridad).
php artisan paqsuite:seed-deploy

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reinicio PHP-FPM / queue según configuración del sitio
```

5. Guardar y ejecutar **Deploy Now**.

Cada deploy aplica migraciones pendientes y **seeds de catálogo** contra la base definida en **Environment** del sitio.

> **Importante:** el comando `paqsuite:seed-deploy` debe estar en el Deploy Script de Forge.
> Sin esa línea, el código nuevo puede desplegarse pero menú / `PEDIDO_MASIVO` / flags de proceso no se materializan en BD.

---

## Opción B — Manual por SSH

### 1. Conectar al servidor

En Forge → **Server** → usar el comando SSH (requiere clave SSH cargada en Forge):

```bash
ssh forge@IP_DEL_SERVIDOR
```

### 2. Ir al backend

```bash
cd /home/forge/NOMBRE_DEL_SITIO/backend
```

El path exacto figura en Forge → sitio → **App Directory** (suele ser `/home/forge/dominio.com`).

### 3. Ejecutar migraciones

**Todas las pendientes:**

```bash
php artisan migrate --force
```

`--force` es obligatorio en producción (sin confirmación interactiva).

**Migraciones puntuales** (ejemplo chat asistente IA):

```bash
php artisan migrate --path=database/migrations/2026_06_21_100000_create_pq_pedidosweb_asistente_ia_proveedores_table.php --force
php artisan migrate --path=database/migrations/2026_06_21_110000_create_pq_pedidosweb_asistente_ia_credenciales_table.php --force
php artisan migrate --path=database/migrations/2026_06_22_100000_extend_chat_assistant_credentials_for_multiple.php --force
```

**Pivots:**

```bash
php artisan migrate --path=database/migrations/2026_06_11_100000_create_pq_pivots_catalog_tables.php --force
php artisan migrate --path=database/migrations/2026_06_11_110000_create_pq_pivots_config_tables.php --force
php artisan db:seed --class=Database\\Seeders\\Pivots\\PivotCatalogPilotSeeder --force
```

**Excel import:**

```bash
php artisan migrate --path=database/migrations/2026_06_16_100000_create_pq_excel_catalog_tables.php --force
php artisan migrate --path=database/migrations/2026_06_16_110000_create_pq_excel_import_tables.php --force
# Preferible (incluye PEDIDO_MASIVO + menú):
php artisan paqsuite:seed-deploy
# Equivalente puntual:
php artisan db:seed --class=Database\\Seeders\\ExcelImport\\PedidosWebExcelImportCatalogSeeder --force
```

### Comando post-deploy (`paqsuite:seed-deploy`)

Idempotente. Pensado para el Deploy Script (después de `migrate --force`):

| Qué hace | Notas |
|----------|--------|
| `paqsuite:seed-menus-mvp` | Inserta/actualiza ítems de `paqsuite_mvp.menuItems` (Importación masiva, Seguridad, etc.) |
| `PedidosWebExcelImportCatalogSeeder` | `PEDIDO_INDIVIDUAL` + `PEDIDO_MASIVO` |
| Chat providers | Catálogo `pq_asistente_ia_proveedores` si la tabla existe |
| Atributos visibility | Upsert `PQ_RolAtributo` desde `visibilityProcedimientosByRole` (no borra otros) |
| `--with-seguridad` | **Opcional.** Corre `seed-seguridad-mvp` (puede tocar usuarios MVP / passwords) |

**Tablas tocadas por defecto (sin `--with-seguridad`):** ver matriz en runbook § «paqsuite:seed-deploy».  
Regla: **no borra filas**; atributos de rol (`PQ_RolAtributo`) solo **inserta** procedimientos nuevos de config, sin pisar ABMR ya configurados por admin.

No ejecutar bootstrap destructivo desde este comando.
---

## Opción C — Comando rápido desde Forge (sin SSH)

Sitio → **Commands** → **Run Command**:

```bash
cd backend && php artisan migrate --force
```

---

## Qué verificar antes de `migrate`

En Forge → sitio → **Environment**:

| Variable | Debe apuntar a |
|----------|----------------|
| `DB_CONNECTION` | `sqlsrv` |
| `DB_HOST` | Endpoint RDS AWS |
| `DB_DATABASE` | Base tenant correcta (ej. `paqsystems_pedidosweb_quento`) |
| `DB_USERNAME` / `DB_PASSWORD` | Credenciales RDS |
| `DB_TRUST_SERVER_CERTIFICATE` | `true` (típico en RDS) |

Si `DB_DATABASE` es incorrecta, las tablas se crean en **otra** base.

Revisar salida en el log de deploy o en la terminal: `Migrating: ...` / errores SQL.

---

## Opción D — Scripts SQL manuales (sin `artisan`)

Útil cuando `migrate` falla a mitad de camino o en bases nuevas sin migraciones Laravel.

Ejecutar en el **cliente SQL** conectado a RDS (SSMS, Azure Data Studio, DBeaver), en la base tenant correcta.

### Orden recomendado (nueva empresa)

```text
1) backend/scripts/sql/create-pivot-tables.sql
2) backend/scripts/sql/seed-pivot-catalog.sql
3) backend/scripts/sql/create-excel-tables.sql
4) backend/scripts/sql/seed-excel-catalog-pedidosweb.sql
```

### Chat Asistente IA (si faltan tablas)

Ejecutar el script SQL de creación + seed de proveedores documentado en conversación operativa, o las migraciones:

- `2026_06_21_100000_create_pq_pedidosweb_asistente_ia_proveedores_table` → crea `pq_asistente_ia_proveedores`
- `2026_06_21_110000_create_pq_pedidosweb_asistente_ia_credenciales_table` → crea `pq_asistente_ia_credenciales`
- `2026_06_22_100000_extend_chat_assistant_credentials_for_multiple`
- `2026_07_03_100001_rename_pq_asistente_ia_tables_transversal` (solo tenants con nombres legacy)

SQL manual alternativo: `backend/scripts/sql/rename-pq-asistente-ia-tables-transversal.sql`

### Artículos ERP (`pq_pedidosweb_articulos`)

- `2026_07_03_100000_alter_pq_pedidosweb_articulos_descripcion_varchar60` — `descripcion` VARCHAR(60)
- SQL manual: `backend/scripts/sql/alter-pq-pedidosweb-articulos-descripcion-varchar60.sql`

### Tablas que crea cada bloque

**Pivots (`create-pivot-tables.sql`):**

| Tabla | Uso |
|-------|-----|
| `pq_pivots_consultas` | Definición de consultas pivot |
| `pq_pivots_plantillas` | Plantillas globales |
| `pq_pivots_campos` | Campos por consulta |
| `pq_pivots_plantillas_det` | Detalle plantillas |
| `pq_pivots_validaciones` | Restricciones |
| `pq_pivots_config` | Diseños guardados |
| `pq_pivots_config_last_used` | Último diseño por usuario |

Requisito: tabla `users` (FK en `pq_pivots_config`).

**Seed pivots (`seed-pivot-catalog.sql`):** consultas `CONSULTA_PILOTO_PIVOT`, `CONSULTA_DETALLE_PEDIDOS`, `CONSULTA_DEUDA`, `CONSULTA_CHEQUES`, `CONSULTA_STOCK`.

**Excel (`create-excel-tables.sql`):**

| Tabla | Uso |
|-------|-----|
| `pq_excel_procesos` | Catálogo procesos |
| `pq_excel_procesos_campos` | Columnas por proceso |
| `pq_excel_importaciones` | Lotes |
| `pq_excel_importaciones_filas` | Staging |
| `pq_excel_importaciones_filas_errores` | Errores por fila |
| `pq_excel_importaciones_notificaciones` | Notificaciones async |

**Seed Excel (`seed-excel-catalog-pedidosweb.sql`):** proceso `PEDIDO_INDIVIDUAL` con 23 campos.

Los scripts SQL en `backend/scripts/sql/` registran filas en `migrations` para que Laravel no intente recrearlas.

### Registrar migraciones manualmente (opcional)

```sql
INSERT INTO migrations (migration, batch)
SELECT v.migration, ISNULL((SELECT MAX(batch) FROM migrations), 0) + 1
FROM (VALUES
    (N'2026_06_11_100000_create_pq_pivots_catalog_tables'),
    (N'2026_06_11_110000_create_pq_pivots_config_tables')
) AS v(migration)
WHERE NOT EXISTS (SELECT 1 FROM migrations m WHERE m.migration = v.migration);
```

(Ajustar la lista según el bloque aplicado.)

---

## Flags `.env` post-migración

Activar funcionalidad en el backend (Forge → Environment):

```env
PIVOTS_ENABLED=true
PIVOT_LAYOUTS_ENABLED=true
EXCEL_IMPORT_ENABLED=true
```

Redeploy backend (y frontend si cambian variables `VITE_*`).

### Frontend (Vercel)

```env
VITE_API_BASE_URL=https://backankas.on-forge.com/api/v1
VITE_TENANT_DEFAULT_CLIENT=<codigo_tenant>
```

Las variables `VITE_*` se embeben en el build: tras cambiarlas, **redeploy** del frontend en Vercel.

---

## Verificación rápida en SQL Server

```sql
-- Migraciones aplicadas
SELECT migration, batch FROM migrations ORDER BY migration;

-- Pivots
SELECT consulta_id, pivot_habilitado FROM pq_pivots_consultas ORDER BY consulta_id;

-- Excel
SELECT codigo_proceso, procedimiento_host FROM pq_excel_procesos;
SELECT COUNT(*) AS campos FROM pq_excel_procesos_campos
WHERE id_proceso = (SELECT id FROM pq_excel_procesos WHERE codigo_proceso = 'PEDIDO_INDIVIDUAL');
-- Esperado: 23 campos

-- Chat IA
SELECT COUNT(*) AS proveedores FROM pq_asistente_ia_proveedores;
-- Esperado: 8 proveedores activos en catálogo
```

---

## Diagnóstico frecuente

### Error «No se pudo cargar la configuración del chat»

1. En DevTools → Network → filtrar **XHR/Fetch** por `chat-assistant`.
2. Las requests deben ir a **`backankas.on-forge.com/api/v1/...`**, no a `vercel.app`.
3. Si falta tabla `pq_asistente_ia_credenciales` → 503 → aplicar migraciones o SQL del chat.
4. Cargar catálogo de proveedores (seed o SQL).

### Requests que muestran `vercel.app` y tipo `html`

No son llamadas API: es el SPA devolviendo `index.html`. Revisar `VITE_API_BASE_URL` en Vercel.

### `migrate` falla en pivots (FK `pivot_id`)

Verificar esquema de `pq_pivots_config` (debe tener PK en `pivot_id`). Usar migración reforzada del repo o `create-pivot-tables.sql`.

### Tabla inexistente al ejecutar SQL de verificación

`Invalid object name 'pq_...'` → ejecutar primero el script `create-*-tables.sql` correspondiente.

---

## Resumen

| Objetivo | Dónde | Cómo |
|----------|-------|------|
| Migraciones Laravel | Servidor Forge | `php artisan migrate --force` (deploy script o SSH) |
| Seeds Laravel | Servidor Forge | `php artisan db:seed --class=... --force` |
| Scripts `.sql` | Cliente SQL → RDS | `backend/scripts/sql/*.sql` en orden |
| Comprobar éxito | RDS | `migrations` + `sys.tables` / queries de conteo |

---

## Referencias en el repo

| Recurso | Ruta |
|---------|------|
| DDL pivots | `backend/scripts/sql/create-pivot-tables.sql` |
| Seed pivots | `backend/scripts/sql/seed-pivot-catalog.sql` |
| DDL Excel | `backend/scripts/sql/create-excel-tables.sql` |
| Seed Excel | `backend/scripts/sql/seed-excel-catalog-pedidosweb.sql` |
| Migraciones Laravel | `backend/database/migrations/` |
| Runbook versión/deploy | `docs/_base/00-runbook-actualizacion-version.md` |

---

*Última actualización: 2026-06-23 — entornos Forge + RDS SQL Server + frontend Vercel.*
