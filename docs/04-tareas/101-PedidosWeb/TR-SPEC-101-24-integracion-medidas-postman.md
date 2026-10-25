# TR-SPEC-101-24c — Collection Postman y pruebas de contrato de medidas

| Campo | Valor |
|---|---|
| **HU relacionada** | [HU-101-054](../../03-historias-usuario/101-PedidosWeb/HU-101-054-integracion-medidas-postman.md) |
| **SPEC relacionada** | [SPEC-101-24](../../05-open-spec/101-PedidosWeb/SPEC-101-24-importacion-medidas-erp.md) |
| **Prioridad** | Must |
| **Estado** | C1 apto con observaciones — listo para D1 |
| **Dependencias** | TR-SPEC-101-24a, TR-SPEC-101-24b |

## 1. Objetivo

Actualizar la collection local para que el flujo de importación de medidas sea
reproducible y verifique recepción, consulta, resultado final, idempotencia y
concurrencia.

Archivo:

```text
backend/storage/app/postman/integracion/PedidosWeb-Integracion.postman_collection.json
```

No publicar cambios en un workspace remoto de Postman.

## 2. Requests requeridas

### 2.1 Recibir medidas

Nombre:

```text
medidas - recibir lote 1/1
```

Contrato:

```text
POST {{backendUrl}}/api/v1/integracion/importaciones/medidas
```

Debe usar `tenant`, `integrationToken`, `importacionId` y `fechaProceso` del
environment existente.

Payload mínimo de prueba:

- una medida nueva;
- una medida existente para modificación;
- un ítem sin `codMedida` para rechazo;
- `modo`;
- `ultimoEnvio`;
- `numeroLote`;
- `items`.

Tests:

- HTTP 202.
- envelope `error: 0`.
- `resultado.recurso = medidas`.
- `resultado.importacionId` coincide con la variable.
- estado de recepción válido.

### 2.2 Consultar resultado

Nombre:

```text
importacion - consultar resultado medidas
```

Contrato:

```text
GET {{backendUrl}}/api/v1/integracion/importaciones/{{importacionId}}
```

Tests:

- estado intermedio: contadores y `errores` son `null`;
- estado final: existen los seis contadores;
- suma de los seis contadores = `procesados`;
- `resultado.recurso = medidas`.

## 3. Casos adicionales

Agregar requests o scripts para:

1. Reenviar el mismo lote y comprobar idempotencia.
2. Enviar un segundo ciclo mientras el primero está en procesamiento y
   comprobar HTTP 409.
3. Verificar que el ERP debe detener los lotes posteriores tras el 409.
4. Actualizar ejemplos de resultado existentes con:
   `agregadosErroneos`, `modificadosErroneos` y `eliminadosErroneos`.

## 4. Scripts Postman

Los tests deben usar propiedades del JSON, códigos HTTP y estados. No deben
acoplarse a textos visibles en español.

El polling del GET debe tener un número máximo de intentos y una espera
acotada; nunca debe quedar en un bucle indefinido. El límite concreto se fija
según el timeout del entorno de pruebas en D1.

## 5. Validación

- Validar sintaxis JSON de la collection.
- Ejecutar la carpeta de integración con environment local.
- Confirmar que la request de login conserva `integrationToken`.
- Confirmar que los ejemplos existentes siguen siendo JSON válidos.
- Registrar si la prueba requiere SQL Server, worker o datos previamente
  cargados.

## 6. Plan de trabajo

| ID | Trabajo | Evidencia |
|---|---|---|
| 24c-01 | Agregar request POST medidas | Collection diff |
| 24c-02 | Agregar request GET resultado | Collection diff |
| 24c-03 | Agregar tests 202/estado/contadores | Ejecución Postman |
| 24c-04 | Agregar idempotencia y 409 | Ejecución Postman |
| 24c-05 | Actualizar ejemplos existentes | JSON validado |

## 7. Criterios de terminado

- [ ] Collection JSON válido.
- [ ] POST y GET de medidas presentes.
- [ ] Estado intermedio y final cubiertos.
- [ ] Seis contadores y suma verificados.
- [ ] Rechazo por `codMedida` inválido verificado.
- [ ] Idempotencia y concurrencia verificadas.
- [ ] Sin publicación remota no autorizada.

### Checklist normas transversales

- [ ] Requests protegidas con Bearer y `X-Paq-Cliente`.
- [ ] Tests de 401 y 403 cubiertos por la collection o suite equivalente.
- [ ] Envelope validado con `error` entero y `resultado` objeto.
- [ ] Sin credenciales reales en archivos versionados.
- [ ] Sin publicación remota no autorizada.
