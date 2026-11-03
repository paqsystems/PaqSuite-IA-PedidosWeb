# Integración con PaqSuite IA Framework

Estado: adopción inicial del SDK; funcionalidad de importaciones aún no implementada.

PedidosWeb consume:

| Capa | Paquete | Pin |
|---|---|---|
| Backend | `paqsuite/laravel-core` | `1.3.18-beta.24` |
| Frontend | `@paqsuite/react-core` | `2.4.30-beta.6` |

## Resolución de paquetes

El modo de release utiliza Cloudsmith:

- Composer: `https://composer.cloudsmith.io/paqsystems/paqsuite-sdk/`;
- npm scope `@paqsuite`: `https://npm.cloudsmith.io/paqsystems/paqsuite-sdk/`;
- secreto/variable: `CLOUDSMITH_READ_TOKEN` (Forge Environment, GitHub Actions, Vercel; local en `backend/.env` y/o variable de usuario Windows).

### Desarrollo local (Windows)

1. Definir `CLOUDSMITH_READ_TOKEN` (entorno de usuario o `backend/.env`; ver `.env.example`).
2. **npm** (`frontend/`): `npm install` — usa `frontend/.npmrc` con `${CLOUDSMITH_READ_TOKEN}`.
3. **Composer** (`backend/`): el registry público `composer.cloudsmith.io` exige token; opciones:
   - Forge/producción: `backend/scripts/forge-composer-install.sh` (reescribe repo a `dl.cloudsmith.io/{token}/…` + `auth.json` temporal).
   - PC local: mismo patrón (script Forge) o `composer update paqsuite/laravel-core` tras inyectar URL con token; restaurar `composer.json` sin token en la URL antes de commitear.
4. Verificar: `composer show paqsuite/laravel-core` y `npm list @paqsuite/react-core` coinciden con los pins de la tabla.

**Forge (todos los backends PedidosWeb):** `CLOUDSMITH_READ_TOKEN` en Environment del sitio; Deploy Script con `scripts/forge-composer-install.sh` (ver `docs/Migraciones-en-forge.md`). Automatizar inyección del token: `backend/scripts/dev/forge-push-cloudsmith-environments.ps1` (requiere `FORGE_API_TOKEN` o `-McpJsonPath` al `mcp.json` local).

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
