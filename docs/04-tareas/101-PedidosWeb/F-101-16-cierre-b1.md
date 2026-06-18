# F-101-16 — Cierre revisión B1 (importación Excel pedido individual)

| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-101-16-importacion-pedido-individual-excel](../../05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) |
| **Fecha** | 2026-06-17 |
| **Alcance** | Revisión B1 de HU-101-029 y HU-101-030 |
| **Veredicto** | **Apto** — **autorizada Parte C** (TR) |

## Resultado por HU

| HU | Veredicto B1 | Lista para TR |
|----|--------------|---------------|
| [HU-101-029-proceso-excel-pedido-individual](../../03-historias-usuario/101-PedidosWeb/HU-101-029-proceso-excel-pedido-individual.md) | **Apto** | Sí — orden **1** |
| [HU-101-030-importacion-excel-pantalla-carga](../../03-historias-usuario/101-PedidosWeb/HU-101-030-importacion-excel-pantalla-carga.md) | **Apto** | Sí — orden **2** |

## Checklist B1 transversal

| Área | Estado | Notas |
|------|--------|-------|
| Cobertura SPEC CA-01 … CA-09 | OK | Repartido entre 029 (backend) y 030 (UI) |
| Actores V / S / C | OK | Perfil C: import con cliente fijo |
| i18n columnas Excel | OK | 5 idiomas; parser multilenguaje |
| Sin procesamiento parcial | OK | `permite_procesamiento_parcial = false` |
| CC PQ #6 | OK | Import (029) + grabar (030 CA-11) |
| Dependencias GEN-07 | OK | Motor + ui-embebida prerequisitos |
| Gherkin en ambas HU | OK | 4 + 5 escenarios |
| Preguntas abiertas AMB-101-16-* | OK | Cerradas en B1 |

## Decisiones cerradas en B1

| ID | Tema | Decisión |
|----|------|----------|
| AMB-M-101-16-03 | Descuento por cantidad | Host HU-030 al hidratar (`findDescuentoCantidad`) |
| AMB-M-101-16-04 | Artículo duplicado en Excel | Permitir; un renglón por fila |
| AMB-B1-029-01 | Traducciones plantilla | `locales/*.json` + `backend/lang/{locale}/excel_import.php` |
| AMB-B1-029-02 | Comentario obligatorio | i18n `excelImport.columnComment.required` |
| AMB-B1-030-01 | Toolbar en carga | Superior; no desplaza Grabar/Cancelar |
| AMB-B1-030-02 | Orden hidratación | Cliente → API cabecera → overlay Excel → renglones → totales |
| AMB-B1-030-03 | Copia comprobante | Import deshabilitado si ya hay renglones |

## Matriz trazabilidad SPEC CA → HU

| SPEC CA | HU principal |
|---------|--------------|
| CA-01 … CA-02 | HU-030 |
| CA-03, CA-03b | HU-029 |
| CA-04 … CA-06, CA-05b | HU-029 |
| CA-07 | HU-030 |
| CA-08 | HU-030 (CA-11) |
| CA-09 | HU-029 (CA-13) |

## Orden C recomendado

```text
1. TR-SPEC-101-16-proceso-excel-pedido-individual   (HU-101-029)
2. TR-SPEC-101-16-importacion-excel-pantalla-carga (HU-101-030)
```

Subtareas sugeridas en TR-029: extensión i18n GEN-07 → seeder → handler → tests feature.

## Fuera de alcance confirmado

- Importación masiva / edición de comprobante / grabación automática.
- Migrar `ARTICULOS_ALTA` a i18n (opcional post v1).
- ABM web de `PQ_EXCEL_PROCESOS*`.

## Próximo paso

**Parte C:** cerrada (2026-06-17) — ver [F-101-16-cierre-c1.md](F-101-16-cierre-c1.md).
