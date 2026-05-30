import React from 'react';
import ReactDOM from 'react-dom/client';
import 'devextreme/dist/css/dx.light.css';
import './features/i18n/i18n';
import { App } from './app/App';

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
