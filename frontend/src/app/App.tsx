import { BrowserRouter } from 'react-router-dom';
import { AuthProvider } from '../features/auth/AuthProvider';
import { SessionLifecycleManager } from '../features/auth/SessionLifecycleManager';
import { LocaleProvider } from '../features/i18n/LocaleProvider';
import { ThemeProvider } from '../features/theme/ThemeProvider';
import { AppRoutes } from './router/AppRoutes';

export function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <SessionLifecycleManager />
        <ThemeProvider>
          <LocaleProvider>
            <AppRoutes />
          </LocaleProvider>
        </ThemeProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}
