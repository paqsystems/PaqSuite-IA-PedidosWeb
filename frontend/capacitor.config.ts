import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.paqsystems.pedidosweb',
  appName: 'PedidosWeb',
  webDir: 'dist',
  server: {
    androidScheme: 'https',
  },
  android: {
    // Smoke/dev: WebView https://localhost → API http://10.0.2.2 (mixed content)
    allowMixedContent: true,
  },
  plugins: {
    StatusBar: {
      overlaysWebView: false,
    },
  },
};

export default config;
