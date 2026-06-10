import { useLocation } from 'react-router-dom';

export function ProcessPlaceholderPage() {
  const location = useLocation();

  return (
    <section data-testid="process-placeholder">
      <h2>Proceso activo</h2>
      <p data-testid="process-active-route">{location.pathname}</p>
    </section>
  );
}
