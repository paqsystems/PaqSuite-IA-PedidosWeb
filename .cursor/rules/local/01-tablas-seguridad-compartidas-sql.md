---
alwaysApply: true
---

# 01 — Tablas de seguridad y menú compartidas (SQL Server)

## Contexto

En proyectos **MONO** (PedidosWeb), la seguridad vive en la **misma base del cliente**. Si esas tablas aún no existen en una instalación nueva, se crean **una sola vez** con el esquema legacy ERP (referencia `Diccionario_*`), vía migraciones Laravel con guard `Schema::hasTable`. **No alterar** tablas ya pobladas o compartidas con otros productos.

Tablas comunes PaqSuite en la BD del cliente:

| Tabla (SQL Server) | Modelo Eloquent |
|--------------------|-----------------|
| `pq_menus` | `PqMenu` |
| `users` | `User` |
| `Pq_Rol` | `PqRol` |
| `PQ_RolAtributo` | `PqRolAtributo` |
| `Pq_Permiso` | `PqPermiso` |

## Prohibido

- Crear, alterar o eliminar **estructura** de esas tablas: columnas, tipos, índices, FKs, constraints, triggers.
- Agregar migraciones Laravel que modifiquen esas tablas en entornos donde ya existen (BD compartida).
- Ejecutar `migrate:fresh`, `Schema::drop`, `ALTER TABLE` o scripts DDL orientados a esas tablas.
- Asumir un esquema “ideal” del repo si difiere del legacy real; **adaptar código**, no la BD.

## Permitido

- Leer y escribir **filas de datos** vía seeds idempotentes (usuarios `*.mvp`, roles MVP, permisos, atributos, ítems de menú faltantes).
- Mapear el esquema **legacy existente** en modelos, servicios, queries y tests.
- Configurar filtros de aplicación (p. ej. `id_empresa` en `Pq_Permiso` vía config, no DDL).

## Si hay inconsistencias

1. **Informar al usuario** (columna distinta, tipo inesperado, jerarquía de menú legacy, datos compartidos con otro proyecto).
2. **No proponer ni aplicar cambios DDL** salvo autorización explícita del usuario.
3. Resolver en **código**: modelos (`$fillable`, `$casts`, `$table`), DTOs, servicios, seeds, config (`config/paqsuite_mvp.php`, `config/paqsuite_seed.php`).

## Esquema legacy relevante (referencia)

- **`pq_menus`:** `procedimiento`, `text`, `orden`, `enabled`, `routeName`, `idparent`, `tipo`, **`tipo_proceso`** (`P` = proceso en convención ERP). `nodeType` API = derivado (D1-1 TR menu-sidebar); no columna en tabla.
- **`users`:** autenticación con `password_hash` (no `password`).
- **`Pq_Rol`:** `nombre_rol`, `descripcion_rol`, `acceso_total`.
- **`PQ_RolAtributo`:** autorización granular por `procedimiento` + flags (`permiso_repo`, etc.).
- **`Pq_Permiso`:** vínculo usuario ↔ rol; filtrar por `id_empresa` según config del proyecto.

## Seeds y menú

- El seed de menú **inserta procedimientos MVP faltantes** o habilita existentes; **no reestructura** el árbol legacy (`idparent` se respeta si la fila ya existe).
- Con `acceso_total = true`, el menú autorizado incluye **todos** los ítems `enabled` de `pq_menus`, no solo los del MVP.

## Migraciones del repo

Migraciones en `backend/database/migrations/` replican el esquema legacy ERP (inspeccionado en diccionario de referencia). Llevan guard `Schema::hasTable`: **no recrean** tablas existentes. Las tablas `pq_pedidosweb_*` legacy del script comercial **no** se crean desde Laravel; adaptar modelos/código al esquema del cliente.
