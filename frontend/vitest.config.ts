import react from '@vitejs/plugin-react';
import { defineConfig, mergeConfig } from 'vitest/config';
import viteConfig from './vite.config';

export default mergeConfig(
  viteConfig,
  defineConfig({
    plugins: [react()],
    test: {
      environment: 'jsdom',
      setupFiles: ['./src/test/vitest.setup.ts'],
      include: ['src/**/*.test.ts', 'src/**/*.test.tsx', 'tests/unit/**/*.test.ts'],
    },
  }),
);
