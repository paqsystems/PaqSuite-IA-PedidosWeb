import React from 'react';
import ReactDOM from 'react-dom/client';
import './features/i18n/i18n';
import { defaultThemeKey } from './features/theme/model/supportedThemes';
import { syncDevExtremeTheme } from './features/theme/syncDevExtremeTheme';
import { App } from './app/App';

syncDevExtremeTheme(defaultThemeKey);

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
