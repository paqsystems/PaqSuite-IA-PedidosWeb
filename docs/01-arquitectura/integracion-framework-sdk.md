# Integración con PaqSuite IA Framework

Estado: adopción inicial del SDK; funcionalidad de importaciones aún no implementada.

PedidosWeb consume:

| Capa | Paquete | Pin |
|---|---|---|
| Backend | `paqsuite/laravel-core` | `1.3.16-beta.1` |
| Frontend | `@paqsuite/react-core` | `2.4.29-beta.1` |

## Resolución de paquetes

El modo de release utiliza Cloudsmith:

- Composer: `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/`;
- npm scope `@paqsuite`: `https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/`;
- secreto/variable: `CLOUDSMITH_READ_TOKEN`.

El modo de desarrollo conjunto utiliza el monorepo local
`C:\Programacion\PaqSuite-IA-FRAMEWORK`. Está documentado en
`frontend/MODO-REPO-LAB.md` y no debe llegar a ramas de release.

## Cableado inicial

El frontend importa las hojas oficiales:

```ts
import '@paqsuite/react-core/auth.css';
import '@paqsuite/react-core/shell.css';
```

Vite activa el alias local únicamente cuando `PAQ_REPO_LAB=1` o `true`. Sin esa bandera, la resolución normal utiliza `node_modules` y el pin de Cloudsmith.

## Regla de trabajo

No copiar implementaciones GEN desde Framework a PedidosWeb. El host debe consumir exports del SDK y aportar únicamente dominio, catálogo, permisos, adaptadores y configuración.

## Verificación mínima

Antes de avanzar con el motor genérico de importaciones:

1. `composer show paqsuite/laravel-core`;
2. `npm list @paqsuite/react-core`;
3. build/typecheck del frontend;
4. health y login del backend;
5. smoke del shell con la resolución Cloudsmith;
6. repetir el build frontend con `PAQ_REPO_LAB=1`;
7. verificar que el backend local resuelve el paquete mediante `sdk-link.ps1`.

La adopción del SDK y el desarrollo del motor de importaciones se mantienen separados para poder diagnosticar fallos de integración sin mezclarlos con la nueva funcionalidad.
