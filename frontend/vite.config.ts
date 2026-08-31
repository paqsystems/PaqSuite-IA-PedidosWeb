import react from '@vitejs/plugin-react';
import { defineConfig } from 'vite';

function resolveViteBase(): string {
  const modeFlagIndex = process.argv.indexOf('--mode');
  const modeFromArg = modeFlagIndex >= 0 ? process.argv[modeFlagIndex + 1] : undefined;
  const isMobileBuild =
    modeFromArg === 'mobile' ||
    process.env.VITE_MOBILE_BUILD === '1' ||
    process.env.npm_lifecycle_event === 'build:mobile';

  // Web/Vercel: '/' evita /pedidos/assets/* al deep-link.
  // Capacitor: './' para WebView (file://).
  return isMobileBuild ? './' : '/';
}

export default defineConfig({
  plugins: [react()],
  base: resolveViteBase(),
  server: {
    port: 3010,
    strictPort: true,
    proxy: {
      '/api': {
        target: 'http://localhost:8088',
        changeOrigin: true,
      },
    },
  },
});
