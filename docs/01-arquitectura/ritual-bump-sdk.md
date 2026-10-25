# Ritual de bump del SDK Framework

Pins actuales de PedidosWeb:

- `paqsuite/laravel-core`: `1.3.16-beta.1`;
- `@paqsuite/react-core`: `2.4.29-beta.1`.

## Bump desde Cloudsmith

Antes de regenerar locks, comprobar el token sin imprimirlo:

```powershell
if ($env:CLOUDSMITH_READ_TOKEN) { 'CLOUDSMITH_READ_TOKEN=definido' } else { 'CLOUDSMITH_READ_TOKEN=FALTA' }
```

Backend:

```powershell
cd backend
composer update paqsuite/laravel-core --with-dependencies
composer show paqsuite/laravel-core
```

Frontend:

```powershell
cd frontend
npm install @paqsuite/react-core@2.4.29-beta.1 --save-exact
npm list @paqsuite/react-core
```

Los locks que se regeneren desde Cloudsmith sí forman parte del cambio de versión. No regenerar locks con una referencia local.

## Desarrollo contra repo-lab

Usar:

```powershell
cd C:\Programacion\PaqSuite-IA-FRAMEWORK
.\tools\sdk\sdk-link.ps1 -HostRoot ..\PaqSuite-IA-PedidosWeb
```

Y activar `PAQ_REPO_LAB=1` en el `frontend/.env` local. Ver
`frontend/MODO-REPO-LAB.md`.

Antes de PR/deploy, ejecutar `sdk-unlink.ps1`, eliminar cualquier resolución local y verificar que Composer/npm vuelvan a Cloudsmith.

## Verificación posterior

- comparar exports instalados contra la versión del Framework;
- ejecutar health/login y smoke del shell;
- ejecutar tests del host;
- actualizar esta documentación si cambian pins, exports o procedimiento;
- documentar cualquier gap del SDK, sin copiar el componente al host.
