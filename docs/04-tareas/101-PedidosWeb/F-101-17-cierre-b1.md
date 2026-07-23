# F-101-17 — Cierre revisión B1 (mobile Capacitor PedidosWeb)

| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-101-17-mobile-capacitor-pedidosweb](../../05-open-spec/101-PedidosWeb/SPEC-101-17-mobile-capacitor-pedidosweb.md) |
| **Fecha** | 2026-06-30 |
| **Alcance** | Revisión B1 de HU-101-031 … HU-101-036 |
| **Veredicto** | **Apto** — **autorizada Parte C** (TR v1 prioritarias) |

## Resultado por HU

| HU | Release | Veredicto B1 | Lista para TR |
|----|---------|--------------|---------------|
| [HU-101-031-mobile-v1-scaffold](../../03-historias-usuario/101-PedidosWeb/HU-101-031-mobile-v1-scaffold.md) | `v1.2.0-mobile` | **Apto** | Sí — orden **1** |
| [HU-101-032-mobile-login-tenant](../../03-historias-usuario/101-PedidosWeb/HU-101-032-mobile-login-tenant.md) | `v1.2.0-mobile` | **Apto** | Sí — orden **2** |
| [HU-101-033-mobile-consulta-stock-kardex](../../03-historias-usuario/101-PedidosWeb/HU-101-033-mobile-consulta-stock-kardex.md) | `v1.2.0-mobile` | **Apto** | Sí — orden **3** |
| [HU-101-034-mobile-v2-consultas-kardex](../../03-historias-usuario/101-PedidosWeb/HU-101-034-mobile-v2-consultas-kardex.md) | `v1.2.1-mobile` | **Apto** | Sí — post v1 |
| [HU-101-035-mobile-v2-listados-kardex](../../03-historias-usuario/101-PedidosWeb/HU-101-035-mobile-v2-listados-kardex.md) | `v1.2.1-mobile` | **Apto** | Sí — post v1 |
| [HU-101-036-mobile-v3-carga-pedidos](../../03-historias-usuario/101-PedidosWeb/HU-101-036-mobile-v3-carga-pedidos.md) | `v1.2.2-mobile` | **Apto** | Sí — post v2 |

## Decisiones cerradas en B1 (stakeholder)

| ID | Tema | Decisión |
|----|------|----------|
| D1-17 | Idioma/tema shell v1 | **Sí** |
| D1-18 | Tags v2/v3 | **`v1.2.1-mobile`** / **`v1.2.2-mobile`** |
| D1-7 | Landing v1 | `/consultas/stock` tras login |
| D1-12 | Forgot/reset v1 | Fuera v1; `firstLogin` → change-password |

## Orden C recomendado

### v1 (`v1.2.0-mobile`)

```text
1. TR-SPEC-101-17-mobile-v1-scaffold      (HU-101-031) + TR-GEN-11-mobile-capacitor-scaffold
2. TR-SPEC-101-17-mobile-v1-login-tenant (HU-101-032) + TR-GEN-11-mobile-login-tenant
3. TR-SPEC-101-17-mobile-v1-stock-kardex (HU-101-033) + TR-GEN-11-mobile-shell
```

### v2 (`v1.2.1-mobile`)

```text
4. TR-SPEC-101-17-mobile-v2-consultas    (HU-101-034)
5. TR-SPEC-101-17-mobile-v2-listados     (HU-101-035)
```

### v3 (`v1.2.2-mobile`)

```text
6. TR-SPEC-101-17-mobile-v3-carga        (HU-101-036)
```

## Siguiente paso

Generar TR v1 (031–033) en coordinación con TR-GEN-11-*; revisión C1 antes de Parte D.
