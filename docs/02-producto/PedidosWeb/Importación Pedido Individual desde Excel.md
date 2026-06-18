# Carga de Pedidos
# Importación de datos desde Excel

| Campo | Valor |
|-------|--------|
| **SPEC** | [SPEC-101-16-importacion-pedido-individual-excel](../../05-open-spec/101-PedidosWeb/SPEC-101-16-importacion-pedido-individual-excel.md) (A1 cerrado — 2026-06-17) |

## Consideraciones

- Habilitar en este proceso el componenete de importar pedidos desde Excel (exportar planilla modelo, importar planilla con datos)
- Los **títulos de columna** de la plantilla y la validación estructural deben usar **i18n** (idioma activo del portal; parser acepta los 5 idiomas soportados) — ver SPEC §2.1
- los datos y comportamiento están en la imagen "Importación Pedido desde Excel.png"
-	No se aceptan ingreso parcial (si hay un error, no se procesa nada)
-	No se aplica en edición de Pedidos/Presupuestos (sólo carga)
-	Inhabilitar botón importación si ya se seleccionó cliente
-	Los datos de encabezado que se deseen personalizar, hay que repetir el mismo valor en todos los renglones
-	Los datos que no están permitidos editar según el tipo de usuario, deben venir vacíos 
(ej : si “ModificaListaPrecV” es false, y el usuarios es de tipo vendedor, la columna “Lista Precios” debe venir vacía).

## Cálculos tras importación

Tras la importación, calcular en forma adicional : 
Bonificación neta : en función de las bonficaciones 1, 2 y 3
En cada renglón de los artículos : 
	Precio Neto
	Importe Bruto
	Importe Neto
	IVA sobre Importe Neto
	Importe Total
