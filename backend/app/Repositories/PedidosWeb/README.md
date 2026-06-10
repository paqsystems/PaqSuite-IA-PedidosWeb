# Repositories PedidosWeb (TR-SPEC-101-03)

Capa de persistencia sin reglas de negocio. Los **services** (TR-101-04) orquestan transacciones y validaciones; los repositories ejecutan lecturas/escrituras técnicas.

## Dependencias

```
Service (TR-101-04)
    ├── PedidoRepositoryInterface
    ├── PedidoDetalleRepositoryInterface
    ├── ClienteRepositoryInterface
    ├── ArticuloRepositoryInterface
    └── ConsultaRepositoryInterface
```

Registro IoC: `App\Providers\PedidosWebRepositoryServiceProvider`.

## Contratos

| Interface | Responsabilidad |
|-----------|-----------------|
| `PedidoRepositoryInterface` | Cabecera CRUD, `updateEstado`, `insertPresupuestoCierre` |
| `PedidoDetalleRepositoryInterface` | Renglones, `syncDetalle` (replace), delete por `cod_pedido` |
| `ClienteRepositoryInterface` | Cliente y direcciones de entrega (sin filtro visibilidad) |
| `ArticuloRepositoryInterface` | Artículo, precio lista, stock, descuento por cantidad |
| `ConsultaRepositoryInterface` | Lecturas deuda / cheques por cliente |

## Claves compuestas

`PedidoDetalleRepository::syncDetalle` elimina e inserta renglones (estrategia replace). Updates puntuales por PK compuesta quedan en query explícita si el service lo requiere.

## Transacciones

El **service** abre `DB::transaction()`; los repositories no anidan transacciones salvo necesidad documentada.

## Anti-patrones

- Filtrar clientes por vendedor/supervisor en repository → usar SPEC-101-06 / services de visibilidad.
- Validar transiciones de estado o `MinutosWeb` en repository.
- Lanzar excepciones de negocio (códigos 2000+).

## Tests

- Unit: `tests/Unit/PedidosWeb/Repositories/PedidosWebRepositoryBindingTest.php`
- Integración (requiere tenant `desarrollo`): `tests/Integration/PedidosWeb/Repositories/PedidoRepositoryIntegrationTest.php`
