import { getApiBaseUrl } from '../shared/http/client';

export function App() {
  return (
    <main>
      <h1>PedidosWeb</h1>
      <p>Scaffold inicial MONO listo.</p>
      <p>API base URL: {getApiBaseUrl()}</p>
    </main>
  );
}
