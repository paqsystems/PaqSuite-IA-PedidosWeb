# Seed `PQ_PARAMETROS_GRAL` — módulo PedidosWeb

Fuente versionada de filas iniciales para `Programa = 'PedidosWeb'` en la Company DB.

| Archivo | Propósito |
|---------|-----------|
| [`PQ_PARAMETROS_GRAL.PedidosWeb.seed.json`](./PQ_PARAMETROS_GRAL.PedidosWeb.seed.json) | Inventario completo: `clave`, `caption`, `tooltip`, `tipoValor`, valores por defecto |
| [`Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql`](./Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql) | Script SQL Server: **solo** `CAPTION` + `TOOLTIP` (57 `UPDATE`) |
| [`_generate_caption_tooltip_sql.php`](./_generate_caption_tooltip_sql.php) | Regenera el `.sql` desde el JSON |

## Metadatos `caption` y `tooltip`

Según HU-007 y `docs/00-contexto/_mono/04-configuracion-global/parametros-generales.md`:

- **`caption` (`CAPTION`):** etiqueta breve visible en el listado de mantenimiento HU-007.
- **`tooltip` (`TOOLTIP`):** ayuda contextual (hover o bloque en modal de edición).

Los textos están en **español** como semilla en BD/seed. En runtime la UI resuelve i18n con claves **`parametros.pedidosWeb.{Clave}.caption|tooltip`** y fallback al valor de API/BD.

| Recurso frontend | Propósito |
|------------------|-----------|
| `frontend/src/locales/parametros/pedidosWeb.{en,it,fr,pt}.json` | 114 traducciones por idioma (57 claves × caption + tooltip) |
| `frontend/scripts/generate-parametros-pedidosWeb-i18n.mjs` | Genera borradores desde seed JSON |
| `frontend/scripts/merge-parametros-pedidosWeb-i18n.mjs` | Fusiona traducciones en archivos locale |
| `resolveParametroConsultaTexts.ts` | Resolución en pantalla consulta |

Ver [`idioma-multilingual.md`](../../../00-contexto/_mono/01-experiencia-base/idioma-multilingual.md) § Consulta de parámetros.

Ver [PaqSuite-IA-Tango — seed README](../../../PaqSuite-IA-Tango/docs/backend/seed/PQ_PARAMETROS_GRAL/README.md) (mismo contrato JSON).

## Carga en desarrollo

Cuando exista `PqParametrosGralSeeder` en este backend (o se reutilice el de Tango):

```bash
cd backend
php artisan db:seed --class=PqParametrosGralSeeder --database=sqlsrv
```

MVP PedidosWeb: los registros suelen existir ya en la BD ERP (`Ankas_del_sur`); usar este JSON para **completar o corregir** `CAPTION`/`TOOLTIP`/`tipo_valor` sin inventar claves nuevas.

## Aplicar CAPTION / TOOLTIP en SQL Server

1. Abrir SSMS (o `sqlcmd`) contra la Company DB, por ejemplo `Ankas_del_sur`.
2. Ejecutar el script [`Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql`](./Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql).
3. Revisar la salida: cada clave sin fila emite `AVISO: sin fila para …`.
4. Al final corre un `SELECT` de verificación ordenado por `Clave`.

El script **no toca** `tipo_valor` ni `Valor_*` (asume tipos ya verificados en BD). Va envuelto en `BEGIN TRANSACTION` / `COMMIT`.

Regenerar el SQL tras editar el JSON:

```bash
php docs/backend/seed/PQ_PARAMETROS_GRAL/_generate_caption_tooltip_sql.php
```

> Si el objeto en BD se llama `PQ_parametros_gral` (casing distinto), en SQL Server suele resolverse igual; si falla, reemplazar `dbo.PQ_PARAMETROS_GRAL` por el nombre exacto en tu base.

## Referencias

- Producto §10.6: `docs/02-producto/PedidosWeb/PedidosWeb_Definicion_Conceptual_Final_OpenSpec.md`
- SPEC: `docs/05-open-spec/001-Generaliddes/SPEC-001-04-configuracion-global.md`
- Tipos `tipo_valor`: `docs/_base/pq-parametros-gral-tipo-valor.md`
