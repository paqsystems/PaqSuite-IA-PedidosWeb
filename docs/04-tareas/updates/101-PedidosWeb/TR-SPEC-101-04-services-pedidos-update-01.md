# TR-SPEC-101-04-update-01 — Copiar: `id_de` + leyendas 1–5 en borrador

| Campo | Valor |
|-------|--------|
| **TR base** | [TR-SPEC-101-04-services-pedidos](../../101-PedidosWeb/TR-SPEC-101-04-services-pedidos.md) |
| **SPEC relacionada** | [SPEC-101-04-update-01](../../../05-open-spec/updates/101-PedidosWeb/SPEC-101-04-services-pedidos-update-01.md) |
| **HU relacionada** | [HU-101-026-update-01](../../../03-historias-usuario/updates/101-PedidosWeb/HU-101-026-copiar-comprobante-update-01.md) |
| **Estado** | Implementado (D) — Pendiente de Revisión |
| **Origen** | `00-ControlCalidad-PQ` · Control de Calidad #15 · **09/09/2026** |
| **Última actualización** | 2026-09-09 |

**Normas transversales:** [`../../_NORMAS-TRANSVERSALES-TR.md`](../../_NORMAS-TRANSVERSALES-TR.md)

---

## 1) Alcance

Completar `ComprobanteCopiaService::mapCabecera` para incluir en el borrador:

| Campo API | Origen | Notas |
|-----------|--------|--------|
| `id_de` | `pq_pedidosweb_pedidoscabecera.id_de` | Dirección de entrega (D1-29) |
| `leyenda_1`…`leyenda_5` | columnas homónimas | Recortar con `LeyendaCabeceraLimits::recortarLeyendaCabecera` (D1-30) |

El frontend (`mapCabeceraFromApi`) ya mapea `id_de` → `idDe` y `leyenda_N` → `leyendaN`; **no** requiere cambio de UI si el backend envía los campos.

## 2) Criterios de aceptación

- **AC-CC15-T-C1:** PHPUnit: origen con `id_de` y leyendas → borrador con mismos valores.
- **AC-CC15-T-C2:** PHPUnit: leyenda de 61 caracteres en origen → borrador con longitud 60.
- **AC-CC15-T-C3:** PHPUnit: origen sin `id_de`/leyendas → borrador sin error; campos null/ausentes coherentes.
- **AC-CC15-T-C4:** Suite existente de `ComprobanteCopiaServiceTest` (precios / `ActualizarPrecioCopia`) sigue verde.

## 3) Implementación

| Área | Cambio |
|------|--------|
| `ComprobanteCopiaService.php` | Extender `mapCabecera`: agregar `id_de` y `leyenda_1`…`leyenda_5` (con helper de recorte). |
| Tests | Ampliar `ComprobanteCopiaServiceTest` con caso CC #15. |
| FE | Sin cambio obligatorio. |
| OpenAPI | Sin cambio de schema si `cabecera` del borrador ya documenta estos campos en grabar/detalle; si el ejemplo de copia los omite, opcional alinear en D. |

## 4) Tests

- Unit: `copiarBorradorCopiaIdDeYLeyendas`
- Unit: `copiarBorradorRecortaLeyendasA60`
- Regresión suite copia existente

## 5) Fuera de alcance

- Copiar bonificaciones / fecha_entrega / expreso (no pedidos en CC #15).
- Resolver texto descriptivo `direccion_entrega` vía join (suficiente `id_de` para SelectBox).
- Sync maestro cliente al copiar.
