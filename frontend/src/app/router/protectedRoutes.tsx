import { Route } from 'react-router-dom';
import { ChangePasswordPage } from '../../features/auth/ChangePasswordPage';
import { ChatAssistantPage } from '../../features/chatAssistant/pages/ChatAssistantPage';
import { PreferencesPage } from '../../features/preferences/pages/PreferencesPage';
import { ShellLayout } from '../layout/ShellLayout';
import { RequireAuth } from './RequireAuth';
import { RequirePasswordChange } from './RequirePasswordChange';
import { AbmDemoPage } from '../../features/demo/pages/AbmDemoPage';
import { ExportEmptyDemoPage } from '../../features/demo/pages/ExportEmptyDemoPage';
import { ProcessPlaceholderPage } from '../../features/shell/pages/ProcessPlaceholderPage';
import { pedidosWebRoutes } from '../../routes/pedidosWebRoutes';
import { adminSecurityRoutes } from '../../routes/adminSecurityRoutes';

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
        {adminSecurityRoutes.map((route) => (
          <Route key={route.path} path={route.path} element={route.element} />
        ))}
        <Route path="/preferences" element={<PreferencesPage />} />
        <Route path="/chat-assistant" element={<ChatAssistantPage />} />
        <Route path="*" element={<ProcessPlaceholderPage />} />
      </Route>
    </Route>
  </Route>
);

export const authenticatedHomePath = '/dashboard';
