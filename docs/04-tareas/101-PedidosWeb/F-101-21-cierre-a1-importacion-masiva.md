# Cierre A1 — SPEC-101-21 — Importación masiva de pedidos / presupuestos

| Campo | Valor |
|-------|--------|
| **Fecha** | 2026-07-19 |
| **Épica** | Importación masiva de pedidos / presupuestos |
| **Producto** | [importacion-masiva-pedidos.md](../../02-producto/PedidosWeb/importacion-masiva-pedidos.md) |
| **SPEC** | [SPEC-101-21](../../05-open-spec/101-PedidosWeb/SPEC-101-21-importacion-masiva-pedidos.md) |
| **Método** | Skill `spec-ambiguity-review` |

---

## Resultado general

| Campo | Valor |
|-------|--------|
| **Veredicto** | **Apto con observaciones** |
| **Puede pasar a Parte B (HU)** | **Sí** |
| **Puede pasar a Parte D sin B/C** | **No** |
| **Bloqueantes documentales** | Ninguno (tras decisiones A1 abajo) |

---

# Revisión de ambigüedad — SPEC-101-21

## Checklist (10 ejes)

| # | Eje | Estado | Notas |
|---|-----|--------|-------|
| 1 | Alcance | OK | In/out explícitos; solo web; sin edición; sin borrador servidor |
| 2 | Actores | OK | `pw_importacionmasiva`; C vs V/S; permiso de lote alcanza para grabar P/P (A1-C3) |
| 3 | Flujo | OK | Import → grilla → toggle/consultar/eliminar → grabar FE secuencial → salida con guarda |
| 4 | Reglas | OK | Clave agrupación, vendedor maestro, coherencia cruda, AMB-01…05 cerradas |
| 5 | Datos | Obs. | Borrador en memoria FE; forma exacta del store — TR |
| 6 | UI | OK | Columnas, progreso x/N, modales, columna Error |
| 7 | APIs | Obs. | Import/agrupación `PEDIDO_MASIVO` vs reuso GEN-07 — TR; grabar = APIs individuales |
| 8 | Especiales | OK | Reimport, colisión agregar, perfil C, error parcial grabación |
| 9 | CA | OK | CA-01…14 medibles |
| 10 | Trazabilidad | OK | Producto + 101-16/10/04/13/06 |

## Ambigüedades críticas

Ninguna bloqueante para Parte B tras cierre A1.

| ID | Tema | Resolución A1 |
|----|------|----------------|
| AMB-C-101-21-01 | Hidratar Consultar (borrador no persistido) | **Cerrado:** pasar cabecera+renglones vía **estado de navegación** (location state / store de sesión de pantalla); Volver restaura grilla masiva |
| AMB-C-101-21-02 | Coherencia cabecera dentro del grupo | **Cerrado:** comparar **valores crudos del Excel** (antes de defaults); vacío vs vacío = ok (alineado 101-16) |
| AMB-C-101-21-03 | Permisos al Grabar lote | **Cerrado:** con **`pw_importacionmasiva`** alcanza para importar y grabar pedidos **y** presupuestos del lote |

## Ambigüedades menores (TR, no bloquean B)

| ID | Tema | Resolución / propuesta A1 |
|----|------|----------------------------|
| AMB-M-101-21-06 | ¿Cancelar a mitad del ciclo «Cargando x de N»? | **Propuesta A1:** no cancelable en MVP; espera fin de ciclo |
| AMB-M-101-21-07 | Grabar con grilla vacía | **Propuesta A1:** botón Grabar deshabilitado |
| AMB-M-101-21-08 | Orden al Agregar importación | **Propuesta A1:** conservar orden de filas existentes; anexar nuevos grupos al final (orden de 1ª aparición dentro del archivo nuevo) |
| AMB-M-101-21-09 | Cliente sin `cod_vended` | **Propuesta A1:** error de importación del lote (sin parcial a grilla) |
| AMB-M-101-21-10 | Flag `EXCEL_IMPORT_ENABLED` | **Propuesta A1:** aplica igual que GEN-07; si false, ocultar/deshabilitar import y plantilla |
| AMB-M-101-21-11 | Texto columna Error | **Propuesta A1:** mensaje de negocio del envelope API (o clave i18n resuelta); si varios, el primero / mensaje agregado según patrón grabación individual |
| AMB-M-101-21-12 | Dónde ocurre la agrupación | **Propuesta A1:** backend handler `PEDIDO_MASIVO` valida + resuelve + agrupa y entrega comprobantes armados al host FE (coherente con mermaid del SPEC) |
| AMB-M-101-21-13 | «x de N» ante fallos | **Propuesta A1:** N = cantidad al iniciar Grabar; x avanza en cada intento (OK o error) |

## Supuestos detectados

- El usuario con solo `pw_importacionmasiva` puede grabar **pedido y presupuesto** aunque no tenga acceso a `pw_cargapedidos` (decisión explícita A1-C3).
- Consultar no crea ni lee comprobante en BD; solo proyecta el borrador en UI readonly.
- Defaults de cabecera/renglón siguen el espíritu de 101-16 + `CabeceraInicialService` (vendedor siempre del cliente).
- Mails 101-13 se disparan por cada grabación individual exitosa del loop FE.

## Preguntas para decisión humana

Ninguna pendiente bloqueante. Las AMB-M-06…13 quedan como **propuestas A1** aceptables por defecto en TR salvo que producto las revierta en Parte B/C.

## Recomendaciones de ajuste del SPEC

- [x] Incorporar AMB-C-01…03 en decisiones A1 del SPEC.
- [x] Registrar propuestas AMB-M-06…13.
- [x] Marcar estado A1 cerrado / autoriza Parte B.
- [ ] En Parte B: sugerir HU separadas — (1) catálogo/handler `PEDIDO_MASIVO` + import/agrupación, (2) pantalla grilla + modales + grabación FE + guarda salida, (3) Consultar readonly hidratado.

## Veredicto

**Apto con observaciones** para cierre **A1**. **Autoriza Parte B** (generación de HU).
