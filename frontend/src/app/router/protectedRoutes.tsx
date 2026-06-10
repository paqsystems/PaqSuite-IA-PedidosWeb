import { Route } from 'react-router-dom';
import { ChangePasswordPage } from '../../features/auth/ChangePasswordPage';
import { ShellLayout } from '../layout/ShellLayout';
import { RequireAuth } from './RequireAuth';
import { RequirePasswordChange } from './RequirePasswordChange';
import { AbmDemoPage } from '../../features/demo/pages/AbmDemoPage';
import { ExportEmptyDemoPage } from '../../features/demo/pages/ExportEmptyDemoPage';
import { ProcessPlaceholderPage } from '../../features/shell/pages/ProcessPlaceholderPage';
import { pedidosWebRoutes } from '../../routes/pedidosWebRoutes';

export const protectedRouteElements = (
  <Route element={<RequireAuth />}>
    <Route path="/change-password" element={<ChangePasswordPage />} />
    <Route element={<RequirePasswordChange />}>
      <Route element={<ShellLayout />}>
        <Route path="/demo/abm" element={<AbmDemoPage />} />
        <Route path="/demo/export-empty" element={<ExportEmptyDemoPage />} />
        {pedidosWebRoutes.map((route) => (
          <Route key={route.path} path={route.path} element={route.element} />
        ))}
        <Route path="*" element={<ProcessPlaceholderPage />} />
      </Route>
    </Route>
  </Route>
);

export const authenticatedHomePath = '/dashboard';
