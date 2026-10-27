import react from '@vitejs/plugin-react';
import type { UserConfig } from 'vite';
import { mergeConfig } from 'vitest/config';
import viteConfig from './vite.config';

export default mergeConfig(
  viteConfig as unknown as UserConfig,
  {
    plugins: [react()],
    test: {
      environment: 'jsdom',
      setupFiles: ['./src/test/vitest.setup.ts'],
      include: ['src/**/*.test.ts', 'src/**/*.test.tsx', 'tests/unit/**/*.test.ts'],
    },
  },
);
