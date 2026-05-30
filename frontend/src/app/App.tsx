import { BrowserRouter } from 'react-router-dom';
import { AuthProvider } from '../features/auth/AuthProvider';
import { LocaleProvider } from '../features/i18n/LocaleProvider';
import { AppRoutes } from './router/AppRoutes';

export function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <LocaleProvider>
          <AppRoutes />
        </LocaleProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}
