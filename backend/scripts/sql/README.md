# Scripts SQL PedidosWeb

Scripts **idempotentes** para alta de empresa / tenants SQL Server. Preferir estos CREATE/ALTER frente a inventar DDL ad hoc.

## Orden sugerido (nueva empresa)

Ver también [docs/Migraciones-en-forge.md](../../../docs/Migraciones-en-forge.md) § Opción D.

| # | Script | Uso |
|---|--------|-----|
| 1 | `create-pq-pedidosweb-articulos.sql` | Maestro artículos — **DDL canónico** |
| 2 | `create-pivot-tables.sql` | Pivots |
| 3 | `seed-pivot-catalog.sql` | Catálogo pivots PedidosWeb |
| 4 | `create-excel-tables.sql` | Import Excel |
| 5 | `seed-excel-catalog-pedidosweb.sql` | Proceso `PEDIDO_INDIVIDUAL` |
| 6 | `alter-pq-pedidosweb-carga-unidades-venta.sql` | Solo si faltan `equivalencia_ventas` / `cantidad_venta` |
| 7 | `alter-pq-pedidosweb-stockeable.sql` | Solo si falta `stockeable` (+ param) |
| 8 | `alter-pq-pedidosweb-clientescontactos.sql` | Contactos API (CC #11) |
| 9 | `alter-pq-pedidosweb-clientesde-habitual-char1.sql` | `clientesde.habitual` → **char(1)** (`S`/`N`) |
| 10 | `alter-pq-pedidosweb-pedidosdetalle-bonificacion.sql` | `pedidosdetalle.bonificacion` → **decimal(6,2)** |

## Artículos — tipos canónicos (no sustituir)

Fuente: ERP / PQ. Documentación: `docs/02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md` §3.4.

- `codigo` **varchar(15)** PK  
- `descripcion` varchar(60)  
- `bonificacion` decimal(6,2)  
- `usa_esc` **char(1)** (`B` = BASE)  
- `base`, `valor1`, `valor2` **varchar(15)**  
- `porc_iva` numeric(6,2)  
- `equivalencia_ventas` decimal(18,4) NOT NULL DEFAULT 1  
- `stockeable` bit NOT NULL DEFAULT 1  

**Prohibido** en CREATE nuevos: `usa_esc bit`, `valor1`/`valor2` decimal, `codigo nvarchar(50)`.
