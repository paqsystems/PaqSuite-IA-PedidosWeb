import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

// No mergear con vite.config (defineConfig callback): Vite no permite mergeConfig sobre funciones.
export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    setupFiles: ['./src/test/vitest.setup.ts'],
    include: ['src/**/*.test.ts', 'src/**/*.test.tsx', 'tests/unit/**/*.test.ts'],
  },
});
