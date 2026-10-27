import path from 'node:path';
import react from '@vitejs/plugin-react';
import { defineConfig, loadEnv } from 'vite';

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

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const repoLabFlag = (env.PAQ_REPO_LAB ?? process.env.PAQ_REPO_LAB ?? '').toLowerCase();
  const useRepoLabSdk = repoLabFlag === '1' || repoLabFlag === 'true';
  const frameworkReactCoreRoot = path.resolve(
    __dirname,
    '../../PaqSuite-IA-FRAMEWORK/packages/js/react-core',
  );
  const resolveAlias: Record<string, string> = {};

  if (useRepoLabSdk) {
    resolveAlias['@paqsuite/react-core/auth.css'] = path.join(
      frameworkReactCoreRoot,
      'src/ui/auth/authLayout.css',
    );
    resolveAlias['@paqsuite/react-core/shell.css'] = path.join(
      frameworkReactCoreRoot,
      'src/ui/shell/shellLayout.css',
    );
    resolveAlias['@paqsuite/react-core'] = path.join(frameworkReactCoreRoot, 'src/index.ts');
  }

  return {
    plugins: [react()],
    base: resolveViteBase(),
    resolve: {
      alias: resolveAlias,
      dedupe: ['react', 'react-dom', 'devextreme', 'devextreme-react'],
    },
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
  };
});
