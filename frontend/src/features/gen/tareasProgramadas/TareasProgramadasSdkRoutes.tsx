import { Navigate, NavLink, Outlet, Route, Routes, useNavigate, useParams } from 'react-router-dom';
import {
  ProcessPageLayout,
  TaskAutomationsMonitorPage,
  TaskDefinitionFormPage,
  TaskDefinitionsPage,
  TaskExecutionsPage,
  TaskProcessesPage,
} from '@paqsuite/react-core';
import { useTranslation } from 'react-i18next';
import { useGenTasksSdkProps } from './useGenTasksSdkProps';
import { resolveTasksBackupBackend } from './resolveTasksBackupBackend';
import { useTaskDefinitionEmpresaOptions } from './useTaskDefinitionEmpresaOptions';
import './tareasProgramadasShell.css';

const BASE = '/general/tareas-programadas';

function TareasProgramadasLayout() {
  const { t } = useTranslation();

  return (
    <div data-testid="page-tareas-programadas">
      <ProcessPageLayout
        title={t('menu.tareasProgramadas')}
        toolbar={
          <nav className="pwTareasProgramadas__nav" aria-label={t('menu.tareasProgramadas')}>
            <NavLink
              to={`${BASE}/procesos`}
              className="pwTareasProgramadas__tab"
              data-testid="tareasProgramadas.tab.procesos"
            >
              {t('tareasProgramadas.tabs.procesos', { defaultValue: 'Procesos' })}
            </NavLink>
            <NavLink
              to={`${BASE}/definiciones`}
              className="pwTareasProgramadas__tab"
              data-testid="tareasProgramadas.tab.definiciones"
            >
              {t('tareasProgramadas.tabs.definiciones', { defaultValue: 'Definiciones' })}
            </NavLink>
            <NavLink
              to={`${BASE}/ejecuciones`}
              className="pwTareasProgramadas__tab"
              data-testid="tareasProgramadas.tab.ejecuciones"
            >
              {t('tareasProgramadas.tabs.ejecuciones', { defaultValue: 'Ejecuciones' })}
            </NavLink>
            <NavLink
              to={`${BASE}/historial`}
              className="pwTareasProgramadas__tab"
              data-testid="tareasProgramadas.tab.historial"
            >
              {t('tareasProgramadas.tabs.historial', { defaultValue: 'Historial' })}
            </NavLink>
          </nav>
        }
      >
        <Outlet />
      </ProcessPageLayout>
    </div>
  );
}

function TareasProgramadasIndexRedirect() {
  return <Navigate to="definiciones" replace />;
}

function TareasProgramadasProcesosPage() {
  const sdk = useGenTasksSdkProps();

  return (
    <div data-testid="tareasProgramadas.procesos">
      <TaskProcessesPage t={sdk.t} platform={sdk.platform} accessToken={sdk.accessToken} />
    </div>
  );
}

function TareasProgramadasDefinicionesPage() {
  const navigate = useNavigate();
  const sdk = useGenTasksSdkProps();

  return (
    <div data-testid="tareasProgramadas.definiciones">
      <TaskDefinitionsPage
        t={sdk.t}
        platform={sdk.platform}
        accessToken={sdk.accessToken}
        supportsSimulationByProcessId={{}}
        onNew={() => navigate(`${BASE}/definiciones/nueva`)}
        onEdit={(id) => navigate(`${BASE}/definiciones/${id}/editar`)}
      />
    </div>
  );
}

function TareasProgramadasDefinicionFormPage() {
  const navigate = useNavigate();
  const { id } = useParams<{ id: string }>();
  const sdk = useGenTasksSdkProps();
  const isEdit = id !== undefined;
  const backupBackend = resolveTasksBackupBackend();
  const empresas = useTaskDefinitionEmpresaOptions();

  return (
    <div data-testid="tareasProgramadas.definicion.form">
      <TaskDefinitionFormPage
        mode={isEdit ? 'edit' : 'create'}
        definitionId={isEdit ? Number(id) : undefined}
        tenancyMode="single"
        backupBackend={backupBackend}
        empresas={empresas}
        t={sdk.t}
        platform={sdk.platform}
        accessToken={sdk.accessToken}
        onCancel={() => navigate(`${BASE}/definiciones`)}
        onSaved={() => navigate(`${BASE}/definiciones`)}
      />
    </div>
  );
}

function TareasProgramadasEjecucionesPage() {
  const sdk = useGenTasksSdkProps();

  return (
    <div data-testid="tareasProgramadas.ejecuciones.monitor">
      <TaskAutomationsMonitorPage t={sdk.t} platform={sdk.platform} accessToken={sdk.accessToken} />
    </div>
  );
}

function TareasProgramadasHistorialPage() {
  const sdk = useGenTasksSdkProps();

  return (
    <div data-testid="tareasProgramadas.historial">
      <TaskExecutionsPage t={sdk.t} platform={sdk.platform} accessToken={sdk.accessToken} />
    </div>
  );
}

export function TareasProgramadasRouter() {
  return (
    <Routes>
      <Route element={<TareasProgramadasLayout />}>
        <Route index element={<TareasProgramadasIndexRedirect />} />
        <Route path="procesos" element={<TareasProgramadasProcesosPage />} />
        <Route path="definiciones" element={<TareasProgramadasDefinicionesPage />} />
        <Route path="definiciones/nueva" element={<TareasProgramadasDefinicionFormPage />} />
        <Route path="definiciones/:id/editar" element={<TareasProgramadasDefinicionFormPage />} />
        <Route path="ejecuciones" element={<TareasProgramadasEjecucionesPage />} />
        <Route path="monitoreo" element={<Navigate to={`${BASE}/ejecuciones`} replace />} />
        <Route path="historial" element={<TareasProgramadasHistorialPage />} />
      </Route>
    </Routes>
  );
}
