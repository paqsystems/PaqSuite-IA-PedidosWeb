import React from 'react';
import ReactDOM from 'react-dom/client';
import './shared/ui/tokens/paqAuthTokens.css';
import './features/i18n/i18n';
import './init-devextreme-license';
import { defaultThemeKey } from './features/theme/model/supportedThemes';
import { syncDevExtremeTheme } from './features/theme/syncDevExtremeTheme';
import { bootstrapMobileRuntime } from './shared/mobile/mobileRuntime';
import { App } from './app/App';

syncDevExtremeTheme(defaultThemeKey);

async function bootstrapApp() {
  await bootstrapMobileRuntime();

  ReactDOM.createRoot(document.getElementById('root')!).render(
    <React.StrictMode>
      <App />
    </React.StrictMode>,
  );
}

void bootstrapApp();
