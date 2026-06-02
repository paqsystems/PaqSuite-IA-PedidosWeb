# TR-SPEC-101-03 — Repositories (acceso a datos)

| Campo | Valor |
|-------|--------|
| **HU relacionada** | Transversal — mismas HU que [TR-SPEC-101-02-modelos](TR-SPEC-101-02-modelos.md) (carga, consultas de datos maestros en pantalla) |
| **SPEC relacionada** | [SPEC-101-03-repositories](../../05-open-spec/101-PedidosWeb/SPEC-101-03-repositories.md) |
| **Épica** | 101-PedidosWeb |
| **Prioridad** | Must |
| **Dependencias** | [TR-SPEC-101-02-modelos](TR-SPEC-101-02-modelos.md) |
| **Estado** | Pendiente |
| **Última actualización** | 2026-06-01 |

**Origen:** [SPEC-101-03](../../05-open-spec/101-PedidosWeb/SPEC-101-03-repositories.md)  
**Referencia SPEC:** [SPEC-101-03-repositories](../../05-open-spec/101-PedidosWeb/SPEC-101-03-repositories.md)  
**Normas transversales:** [`../_NORMAS-TRANSVERSALES-TR.md`](../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) HU Refinada (resumen)

### Título
Capa de repositories para persistencia y consultas SQL/Eloquent sin reglas de negocio.

### Narrativa
Como **equipo backend**,  
quiero **encapsular lecturas, escrituras y queries compuestas en repositories con interfaces acotadas**,  
para **que los services (TR-101-04) orquesten reglas sin mezclar SQL ni duplicar filtros de visibilidad de negocio**.

### In scope / Out of scope

**In scope:**

- Interfaces + implementaciones:
  - `PedidoRepository` / `PedidoDetalleRepository` (cabecera, detalle, transacciones de persistencia).
  - `ClienteRepository`, `ArticuloRepository`.
  - `ConsultaRepository` (queries de apoyo: stock, precios, deuda — según necesidad del service).
- Métodos CRUD técnicos: `findByCodigo`, `insertCabecera`, `replaceDetalle`, `deleteByCodigo`, `updateEstado`, `lockForUpdate` donde aplique concurrencia.
- Filtros **técnicos** pasados desde service (ej. `cod_pedido`, `estadoIn: [0,99]`) — **no** calcular “qué clientes ve el vendedor” aquí (delegar a policy/service visibilidad SPEC-101-06).
- Manejo explícito de claves compuestas en detalle.
- Registro en contenedor Laravel (bindings interface → impl).

**Out of scope:**

- Cálculo de totales, bonificaciones, IVA, transiciones de estado, conversión, cierre 98.
- Validación `MinutosWeb`, `CodMotivoCierreExitoso`.
- Endpoints HTTP (TR-101-05).
- Policies de visibilidad comercial (TR-GEN-02-visibilidad / SPEC-101-06).

---

## 2) Criterios de Aceptación (AC)

- **AC-01**: `PedidoRepository` expone operaciones atómicas de cabecera sin validar reglas de negocio.
- **AC-02**: `PedidoDetalleRepository` persiste colección de renglones en transacción invocada por service (método `syncDetalle` o equivalente).
- **AC-03**: `ClienteRepository` obtiene cliente, direcciones y datos de inicialización de cabecera (joins simples).
- **AC-04**: `ArticuloRepository` resuelve artículo, precio en lista, stock disponible (queries según modelo datos).
- **AC-05**: **Ningún** repository lanza excepciones de negocio con códigos 2000+; solo excepciones técnicas o `null`/colección vacía.
- **AC-06**: Interfaces publicadas en `Contracts` del módulo; implementaciones testeables con SQLite/SQL Server según CI.
- **AC-07**: Tests de integración: insert pedido cabecera+2 renglones; update estado; delete físico cabecera+cascade detalle.
- **AC-08**: Filtros de visibilidad por perfil **no** están hardcodeados en repository (verificación estática / revisión PR).

### Escenarios Gherkin

```gherkin
Feature: Repositories sin reglas de negocio

  Scenario: Persistencia de detalle en transacción
    Given un PedidoDetalleRepository
    When el service invoca syncDetalle con 3 renglones
    Then la tabla detalle contiene exactamente 3 filas para cod_pedido

  Scenario: Repository no valida estado para delete
    Given PedidoRepository.deleteFisico(cod_pedido)
    When se invoca con pedido en estado 1
    Then elimina filas sin validar negocio
    And la validación estado 0 es responsabilidad del service

  Scenario: ClienteRepository solo datos
    Given cod_client válido
    When findConDirecciones
    Then retorna entidad cliente y colección direcciones sin aplicar regla supervisor
```

---

## 3) Reglas de Negocio

> Repositories **no** implementan RN de dominio. Solo **contratos de persistencia**.

1. **RN-01**: Toda regla de “puede grabar / puede eliminar / puede convertir” vive en TR-101-04.
2. **RN-02**: `PedidoRepository::updateEstado` es persistencia cruda; el service valida transición permitida.
3. **RN-03**: Eliminación física cabecera+detalle expuesta como método técnico; service restringe a pedido `estado=0`.
4. **RN-04**: Inserción en `presupuestos_cierres` vía método dedicado; sin inferir `id_motivo` en repository.
5. **RN-05**: Lecturas con `lockForUpdate` opcionales para edición concurrente — invocación decidida por service.

---

## 4) Impacto en Datos

### Tablas afectadas

Mismas que TR-101-02; acceso vía Eloquent/Query Builder:

- Escritura intensiva: `pedidoscabecera`, `pedidosdetalle`, `presupuestos_cierres`.
- Lectura intensiva: maestros + `stock` + `listaprecios_articulos` + `descuentocantidad`.

### Seed mínimo para tests

Reutilizar seed TR-101-02 + casos:

- Pedido `estado=0` con 2 renglones (delete test).
- Presupuesto `estado=99` (sin delete en service test posterior).
- Cliente inhabilitado (para tests de service, no repository).

---

## 5) Contratos de API y OpenAPI

> Sin endpoints en este slice.

### 5.1 Endpoints del slice

| Método | Path | Auth | Permiso / rol | Público |
|--------|------|------|---------------|---------|
| — | — | — | — | — |

### 5.2 Detalle por operación

No aplica.

### 5.3 Actualización matriz permisos

- [ ] N/A

---

## 6) Cambios Frontend

### Pantallas / componentes

- Ninguno.

### data-testid sugeridos

- N/A

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD |
|----|------|-------------|-----|
| T1 | Backend | Contratos `PedidoRepository`, `PedidoDetalleRepository` | Interfaces en `Contracts` |
| T2 | Backend | Implementación Eloquent + transacciones DB | AC-01, AC-02, AC-07 |
| T3 | Backend | `ClienteRepository` (cliente + DE) | AC-03 |
| T4 | Backend | `ArticuloRepository` (artículo, lista, stock) | AC-04 |
| T5 | Backend | `ConsultaRepository` (stubs lectura deuda/cheques si necesario para services posteriores) | Documentado |
| T6 | Backend | Service provider bindings | Inyección en services |
| T7 | Tests | Integration tests repositories | AC-07 |
| T8 | Docs | Diagrama dependencia service → repository | En README módulo |

---

## 8) Estrategia de Tests

- **Unit:** mocks de interface en tests de service (TR-101-04).
- **Integration:** CRUD cabecera/detalle; `syncDetalle` reemplaza renglones; delete físico; lectura cliente+artículo.
- **E2E:** no aplica.

---

## 9) Riesgos y Edge Cases

- **Duplicar visibilidad:** filtrar clientes por vendedor en repository — **anti-patrón**; riesgo de divergencia con SPEC-101-06.
- **Transacciones anidadas:** definir quién abre `DB::transaction` (recomendado: **service** orquesta, repositories participan).
- **Rendimiento N+1:** cargar detalle con eager loading en `findWithDetalle`.
- **Clave compuesta detalle:** delete/insert por lote vs update por renglón — documentar estrategia en `syncDetalle`.

---

## 10) Checklist final

### Checklist del slice

- [ ] AC cumplidos
- [ ] Interfaces + bindings registrados
- [ ] Tests integración verdes
- [ ] Sin reglas de negocio en repositories

### Checklist normas transversales

- [ ] Endpoints — **N/A**
- [ ] Matriz permisos — **N/A**
- [ ] OpenAPI — **N/A**
- [ ] Envelope — **N/A**
- [ ] Sin ampliación de alcance fuera de SPEC/HU/TR

---

## Archivos creados/modificados

(Post-implementación)

### Backend

- `backend/app/Contracts/PedidosWeb/PedidoRepositoryInterface.php`
- `backend/app/Repositories/PedidosWeb/PedidoRepository.php`
- `backend/app/Repositories/PedidosWeb/PedidoDetalleRepository.php`
- `backend/app/Repositories/PedidosWeb/ClienteRepository.php`
- `backend/app/Repositories/PedidosWeb/ArticuloRepository.php`
- `backend/app/Repositories/PedidosWeb/ConsultaRepository.php`
- `backend/app/Providers/PedidosWebRepositoryServiceProvider.php`
- `backend/tests/Integration/PedidosWeb/Repositories/*Test.php`

### Frontend

- —

### OpenAPI

- —

### Docs

- Referencia cruzada en TR-SPEC-101-04 (services consumen repositories)
