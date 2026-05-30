import { Link } from 'react-router-dom';

export function DashboardPage() {
  return (
    <section data-testid="process-dashboard">
      <h2>Dashboard</h2>
      <p>Punto de entrada post-login del portal PedidosWeb.</p>
      <Link to="/pedidos/ingresados" data-testid="nav-pedidos-ingresados">
        Ir a pedidos ingresados
      </Link>
    </section>
  );
}
