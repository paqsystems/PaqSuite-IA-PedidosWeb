import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { LocaleDemoGrid } from '../../i18n/components/LocaleDemoGrid';

export function DashboardPage() {
  const { t } = useTranslation();

  return (
    <section data-testid="process-dashboard">
      <h2>{t('dashboard.title')}</h2>
      <p>{t('dashboard.description')}</p>
      <Link to="/pedidos/ingresados" data-testid="nav-pedidos-ingresados">
        {t('dashboard.linkPedidos')}
      </Link>
      <LocaleDemoGrid />
    </section>
  );
}
