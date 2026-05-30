import { Route } from 'react-router-dom';
import { ChangePasswordPage } from '../../features/auth/ChangePasswordPage';
import { ShellLayout } from '../layout/ShellLayout';
import { RequireAuth } from './RequireAuth';
import { RequirePasswordChange } from './RequirePasswordChange';
import { DashboardPage } from '../../features/shell/pages/DashboardPage';
import { ProcessPlaceholderPage } from '../../features/shell/pages/ProcessPlaceholderPage';
import { AppearanceStubPage } from '../../features/avatar/pages/AppearanceStubPage';
import { mvpMenuRoutePaths } from '../../features/menu/mvpMenuRoutes';

export const protectedRouteElements = (
  <Route element={<RequireAuth />}>
    <Route path="/change-password" element={<ChangePasswordPage />} />
    <Route element={<RequirePasswordChange />}>
      <Route element={<ShellLayout />}>
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/appearance" element={<AppearanceStubPage />} />
        {mvpMenuRoutePaths
          .filter((routePath) => routePath !== '/dashboard')
          .map((routePath) => (
            <Route key={routePath} path={routePath} element={<ProcessPlaceholderPage />} />
          ))}
        <Route path="*" element={<ProcessPlaceholderPage />} />
      </Route>
    </Route>
  </Route>
);

export const authenticatedHomePath = '/dashboard';
