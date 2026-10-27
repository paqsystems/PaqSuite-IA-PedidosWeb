# Modo repo-lab — SDK Framework local

PedidosWeb utiliza el SDK de PaqSuite IA Framework con dos resoluciones:

| Entorno | Backend | Frontend |
|---|---|---|
| Release / CI / deploy | `paqsuite/laravel-core` desde Cloudsmith | `@paqsuite/react-core` desde Cloudsmith |
| Desarrollo conjunto local | Referencia `path` temporal al monorepo | Alias Vite al source de `react-core` |

## Release y Cloudsmith

El contrato versionado está en:

- `backend/composer.json`: `paqsuite/laravel-core` `1.3.16-beta.1`;
- `frontend/package.json`: `@paqsuite/react-core` `2.4.29-beta.1`;
- `frontend/.npmrc`: registry `@paqsuite` de Cloudsmith con `CLOUDSMITH_READ_TOKEN`.

El token debe existir en el entorno del desarrollador o del builder. Nunca se agrega el valor al `.npmrc`, al `.env` ni al repositorio.

## Desarrollo local contra Framework

Desde PowerShell, en el repositorio Framework:

```powershell
cd C:\Programacion\PaqSuite-IA-FRAMEWORK
.\tools\sdk\sdk-link.ps1 -HostRoot ..\PaqSuite-IA-PedidosWeb
```

Este comando agrega temporalmente el repositorio Composer `path` y actualiza el vendor local. No se debe commitear el cambio de `backend/composer.json` ni un lock que apunte al path.

Para el frontend, crear o editar localmente `frontend/.env`:

```dotenv
PAQ_REPO_LAB=1
```

`frontend/vite.config.ts` resolverá `@paqsuite/react-core` y sus hojas `auth.css`/`shell.css` directamente desde:

`C:\Programacion\PaqSuite-IA-FRAMEWORK\packages\js\react-core`

## Volver al modo Cloudsmith

Antes de commit, PR, CI o deploy:

1. Ejecutar `C:\Programacion\PaqSuite-IA-FRAMEWORK\tools\sdk\sdk-unlink.ps1 -HostRoot ..\PaqSuite-IA-PedidosWeb`.
2. Confirmar que `backend/composer.json` solo conserva el repositorio Composer de Cloudsmith.
3. Confirmar que `backend/composer.lock` no contiene `type: path`.
4. Restaurar `PAQ_REPO_LAB=0` o eliminar `frontend/.env`.
5. Regenerar locks únicamente con `CLOUDSMITH_READ_TOKEN` definido.

El modo repo-lab es exclusivamente local y no reemplaza el contrato de distribución de Cloudsmith.
