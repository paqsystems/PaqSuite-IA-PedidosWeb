# SPEC-101-09 — Frontend base

| Campo | Valor |
|-------|--------|
| **SPEC madre** | [PedidosWeb_SPEC_MVP.md](PedidosWeb_SPEC_MVP.md) |
| **Estado** | Pendiente — **verificar herencia** GEN-01 / `v1.1.0-paq` |
| **Prioridad épica** | Must (extensión módulos negocio) |

## Objetivo

Estructura React + DevExtreme para módulos PedidosWeb: rutas, cliente API, integración menú.

## In scope

- Rutas lazy de procesos MVP (menú §8 producto)
- Cliente HTTP con `X-Paq-Cliente`
- Reutilizar shell, sidebar, i18n, temas, `DataGridDx` (GEN-03)

## Fuera de scope

- Pantalla de carga (101-10)
- Reimplementar login/shell ya entregados

## Dependencias

- `SPEC-001-01`, `SPEC-001-03` implementados

## Definición de listo

- [ ] Rutas registradas y protegidas
- [ ] Placeholders o páginas vacías enlazadas al menú seed
