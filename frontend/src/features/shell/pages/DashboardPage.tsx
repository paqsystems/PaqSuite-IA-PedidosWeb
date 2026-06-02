import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { fetchDashboardOperativo } from '../api/dashboardApi';

type DashboardKpiCard = {
  testId: string;
  title: string;
  value: string;
};

export function DashboardPage() {
  const navigate = useNavigate();
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [dashboardData, setDashboardData] = useState<Awaited<ReturnType<typeof fetchDashboardOperativo>> | null>(
    null,
  );

  useEffect(() => {
    let mounted = true;

    const load = async () => {
      setIsLoading(true);
      setLoadError(null);
      try {
        const result = await fetchDashboardOperativo();
        if (mounted) {
          setDashboardData(result);
        }
      } catch {
        if (mounted) {
          setDashboardData(null);
          setLoadError(t('dashboard.loadError'));
        }
      } finally {
        if (mounted) {
          setIsLoading(false);
        }
      }
    };

    void load();

    return () => {
      mounted = false;
    };
  }, [t]);

  const currencySymbol = dashboardData?.moneda?.simbolo ?? '$';

  const cards = useMemo<DashboardKpiCard[]>(
    () => [
      {
        testId: 'dashboardKpiPresupuestosCantidad',
        title: t('dashboard.kpi.presupuestosCantidad'),
        value: String(dashboardData?.presupuestosActivos?.cantidad ?? 0),
      },
      {
        testId: 'dashboardKpiPresupuestosImporte',
        title: t('dashboard.kpi.presupuestosImporte'),
        value: `${currencySymbol}${(dashboardData?.presupuestosActivos?.importe ?? 0).toFixed(2)}`,
      },
      {
        testId: 'dashboardKpiPedidosIngresadosCantidad',
        title: t('dashboard.kpi.pedidosIngresadosCantidad'),
        value: String(dashboardData?.pedidosIngresados?.cantidad ?? 0),
      },
      {
        testId: 'dashboardKpiPedidosIngresadosImporte',
        title: t('dashboard.kpi.pedidosIngresadosImporte'),
        value: `${currencySymbol}${(dashboardData?.pedidosIngresados?.importe ?? 0).toFixed(2)}`,
      },
      {
        testId: 'dashboardKpiPedidosPendientesCantidad',
        title: t('dashboard.kpi.pedidosPendientesCantidad'),
        value: String(dashboardData?.pedidosPendientes?.cantidad ?? 0),
      },
      {
        testId: 'dashboardKpiPedidosPendientesImporte',
        title: t('dashboard.kpi.pedidosPendientesImporte'),
        value: `${currencySymbol}${(dashboardData?.pedidosPendientes?.importe ?? 0).toFixed(2)}`,
      },
      {
        testId: 'dashboardTopClientePresupuestos',
        title: t('dashboard.kpi.topClientePresupuestos'),
        value: dashboardData?.topClientePresupuestos?.razon_social ?? t('dashboard.emptyTopClient'),
      },
      {
        testId: 'dashboardTopClientePedidos',
        title: t('dashboard.kpi.topClientePedidos'),
        value: dashboardData?.topClientePedidosIngresados?.razon_social ?? t('dashboard.emptyTopClient'),
      },
    ],
    [currencySymbol, dashboardData, t],
  );

  return (
    <section data-testid="page-dashboard">
      <h2>{t('dashboard.title')}</h2>
      <Button
        text={t('dashboard.linkPedidos')}
        stylingMode="text"
        onClick={() => navigate('/pedidos/ingresados')}
        elementAttr={{ 'data-testid': 'nav-pedidos-ingresados' }}
      />
      {isLoading ? <p>{t('dashboard.loading')}</p> : null}
      {loadError ? <p data-testid="dashboardLoadError">{loadError}</p> : null}
      <div className="dashboardPage__kpis">
        {cards.map((card) => (
          <article key={card.testId} data-testid={card.testId} className="dashboardPage__kpiCard">
            <h3>{card.title}</h3>
            <p>{card.value}</p>
          </article>
        ))}
      </div>
    </section>
  );
}
