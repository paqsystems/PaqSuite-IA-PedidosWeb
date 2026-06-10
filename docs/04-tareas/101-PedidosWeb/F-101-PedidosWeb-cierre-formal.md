# Cierre F Formal — Épica 101 PedidosWeb (Fase 1 MVP)

| Campo | Valor |
|-------|--------|
| **Fecha F1 / F** | 2026-06-03 |
| **Rama** | `v1.1.0-paq` |
| **Commit referencia** | `db041e9` |
| **QA manual** | Validado por el usuario (2026-06-03) |

## Alcance del cierre

Slices **101-02 … 101-15** (Must + Should implementados en Parte D), más **TR-GEN-04** (consulta parámetros).  
**Fuera de alcance:** `TR-SPEC-101-01-backend-base` (diferida — `EMPRESAS_CONEXION`).

| TR | Título | Estado TR |
|----|--------|-----------|
| 101-02 | Modelos | Finalizado |
| 101-03 | Repositories | Finalizado |
| 101-04 | Services pedidos | Finalizado |
| 101-05 | Controllers REST | Finalizado |
| 101-06 | Seguridad visibilidad | Finalizado |
| 101-07 | Consultas API | Finalizado |
| 101-08 | Logs integración | Finalizado |
| 101-09 | Frontend base | Finalizado |
| 101-10 | Pantalla carga | Finalizado |
| 101-11 | Consultas UI | Finalizado |
| 101-12 | Tratativas/cierre | Finalizado |
| 101-13 | Mails | Finalizado |
| 101-14 | Dashboard | Finalizado |
| 101-15 | Tests hardening | Finalizado |
| GEN-04 | Consulta parámetros | Finalizado |

Informe detallado Parte D: [`D-VERIFICACION-101.md`](D-VERIFICACION-101.md).

## Resultado global

- **Aprobado con observaciones**

No hay decisiones humanas bloqueantes pendientes para declarar cierre F de la Fase 1 MVP.

---

## Verificación F1 (agente) — 2026-06-03

### Resultado F1

- **Aprobado con observaciones**

### Fix aplicado en F1

- Paridad i18n `menu.detallePedidos`, `menu.grupoGeneral`, `menu.consultaParametros` en `fr.json` y `pt.json` (error TypeScript en `gridDevExtremeMessages.ts`).

### Evidencia de tests

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=PedidosWeb` | **75 passed**, 51 skipped (integración/403 sin SQL Server) |
| `php artisan test --filter=ParametrosConsulta` | **3 passed** |
| `npm run build` (frontend) | **OK** |
| `npx playwright test consultas-d1.spec.ts mvp-section9.spec.ts` | **7/7 OK** |

### QA manual (usuario)

- Carga/edición comprobante con cabecera y renglones completos.
- Consultas comerciales y detalle de pedidos.
- Consulta de parámetros (sin columna clave, orden por descripción).
- Dashboard operativo.

---

## Verificación F — documentación vs código — 2026-06-03

Contraste sistemático entre fuentes de verdad de producto y implementación vigente.

| Tema | Documento | Código | Resultado |
|------|-----------|--------|-----------|
| Rutas menú MVP | `paqsuite_mvp.php`, TR-101-09 | `mvpMenuRoutes.ts`, `pedidosWebRoutes.tsx` | **OK** — rutas alineadas |
| Consulta parámetros | `consulta-parametros.md`, TR-GEN-04 | `ParametrosConsultaService`, `ParametrosConsultaPage` | **OK** — sin columna clave; orden `CAPTION` |
| Detalle pedidos | `consulta-detalle-pedidos.md` | `DetallePedidosConsultaService`, `DetallePedidosPage` | **OK** |
| Cabecera consultas | `consulta-comprobantes-cabecera.md` | `ComprobanteConsultaColumns`, `ConsultaListadoService` | **OK** |
| Pantalla carga / edición | `pantalla-carga-comprobante-ui.md` §17 | `PedidosCargaPage`, `isDevExtremeUserChange` | **OK** — hidratación sin pisar renglones |
| Perfil cabecera | `pantalla-carga-comprobante-ui.md` §5 | `ComprobanteCabeceraForm`, `CabeceraInicialService` | **OK** |
| Dashboard KPIs | `patron-dashboard-operativo-ui.md` | `DashboardOperativoService`, `DashboardPage` | **OK** |
| Matriz permisos | `matriz-permisos-mvp.md` | `paqsuite_visibility.php`, controllers | **OK** (smoke auth 401) |
| Descuento por cantidad | producto §11.2, Updates §1 | frontend carga | **Observación** — reglas documentadas; wiring UI pendiente (no bloquea F) |
| TR-101-01 tenant | SPEC-101-01 | — | **N/A** — diferida |
| Tests integración SQL | TR-101-03, D-VERIFICACION | PHPUnit skipped | **Observación** — requiere tenant SQL en CI/tanda 2 |
| Tratativas presupuesto | TR-101-12 Should | placeholder UI | **Observación** — cierre presupuesto operativo; tratativas completas diferidas |

### Hallazgos críticos

- Ninguno abierto tras QA manual y verificación F.

### Advertencias (no bloquean F)

1. Tests de integración repositories y feature 403/200 con SQL Server siguen skipped en PHPUnit local.
2. Descuento automático por cantidad al cambiar cantidad en popup renglón: documentado, implementación frontend pendiente.
3. Popup tratativas presupuesto: alcance Should parcial coherente con TR-101-12.
4. Chunk JS DevExtreme > 500 kB (advertencia Vite preexistente).

### Recomendación final

- Épica **101 Fase 1 MVP** cerrada en **F formal con observaciones**.
- Manuales de usuario actualizados: [`Generalidades.md`](../../99-manual-usuario/Generalidades.md) §18, [`PedidosWeb.md`](../../99-manual-usuario/PedidosWeb.md).
- **101-01** permanece fuera de cierre hasta etapa `EMPRESAS_CONEXION`.
