import { useCallback, useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import { fetchDashboardOperativo, type DashboardOperativo } from '../api/dashboardApi';
import './DashboardPage.css';

type KpiMetricCard = {
  testId: string;
  label: string;
  value: string;
  variant?: 'amount';
};

type KpiGroup = {
  key: string;
  title: string;
  accentClass: string;
  cards: KpiMetricCard[];
};

function formatAmount(value: number, currencySymbol: string): string {
  const formatted = value.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return `${currencySymbol}${formatted}`;
}

function formatUpdatedAt(isoDate: string | undefined, locale: string): string | null {
  if (!isoDate) {
    return null;
  }
  const parsed = new Date(isoDate);
  if (Number.isNaN(parsed.getTime())) {
    return null;
  }
  return parsed.toLocaleString(locale, {
    dateStyle: 'short',
    timeStyle: 'short',
  });
}

export function DashboardPage() {
  const navigate = useNavigate();
  const { t, i18n } = useTranslation();
  const [isLoading, setIsLoading] = useState(true);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [dashboardData, setDashboardData] = useState<DashboardOperativo | null>(null);

  const loadDashboard = useCallback(async () => {
    setIsLoading(true);
    setLoadError(null);
    try {
      const result = await fetchDashboardOperativo();
      setDashboardData(result);
    } catch {
      setDashboardData(null);
      setLoadError(t('dashboard.loadError'));
    } finally {
      setIsLoading(false);
    }
  }, [t]);

  useEffect(() => {
    void loadDashboard();
  }, [loadDashboard]);

  const currencySymbol = dashboardData?.moneda?.simbolo ?? '$';
  const updatedAtLabel = formatUpdatedAt(dashboardData?.fechaCalculo, i18n.language);

  const kpiGroups = useMemo<KpiGroup[]>(
    () => [
      {
        key: 'presupuestos',
        title: t('dashboard.section.presupuestos'),
        accentClass: 'dashboard-kpi-group--presupuestos',
        cards: [
          {
            testId: 'dashboardKpiPresupuestosCantidad',
            label: t('dashboard.kpi.presupuestosCantidad'),
            value: String(dashboardData?.presupuestosActivos?.cantidad ?? 0),
          },
          {
            testId: 'dashboardKpiPresupuestosImporte',
            label: t('dashboard.kpi.presupuestosImporte'),
            value: formatAmount(dashboardData?.presupuestosActivos?.importe ?? 0, currencySymbol),
            variant: 'amount',
          },
        ],
      },
      {
        key: 'ingresados',
        title: t('dashboard.section.pedidosIngresados'),
        accentClass: 'dashboard-kpi-group--ingresados',
        cards: [
          {
            testId: 'dashboardKpiPedidosIngresadosCantidad',
            label: t('dashboard.kpi.pedidosIngresadosCantidad'),
            value: String(dashboardData?.pedidosIngresados?.cantidad ?? 0),
          },
          {
            testId: 'dashboardKpiPedidosIngresadosImporte',
            label: t('dashboard.kpi.pedidosIngresadosImporte'),
            value: formatAmount(dashboardData?.pedidosIngresados?.importe ?? 0, currencySymbol),
            variant: 'amount',
          },
        ],
      },
      {
        key: 'pendientes',
        title: t('dashboard.section.pedidosPendientes'),
        accentClass: 'dashboard-kpi-group--pendientes',
        cards: [
          {
            testId: 'dashboardKpiPedidosPendientesCantidad',
            label: t('dashboard.kpi.pedidosPendientesCantidad'),
            value: String(dashboardData?.pedidosPendientes?.cantidad ?? 0),
          },
          {
            testId: 'dashboardKpiPedidosPendientesImporte',
            label: t('dashboard.kpi.pedidosPendientesImporte'),
            value: formatAmount(dashboardData?.pedidosPendientes?.importe ?? 0, currencySymbol),
            variant: 'amount',
          },
        ],
      },
    ],
    [currencySymbol, dashboardData, t],
  );

  const topPresupuestos = dashboardData?.topClientePresupuestos;
  const topPedidos = dashboardData?.topClientePedidosIngresados;

  if (isLoading && !dashboardData) {
    return (
      <section className="dashboard-pedidosweb" data-testid="page-dashboard">
        <h1 className="dashboard-pedidosweb__title" data-testid="dashboardOperativo.titulo">
          {t('dashboard.title')}
        </h1>
        <div className="dashboard-loading-block">{t('dashboard.loading')}</div>
      </section>
    );
  }

  return (
    <section className="dashboard-pedidosweb" data-testid="page-dashboard">
      <header className="dashboard-pedidosweb__header">
        <div className="dashboard-pedidosweb__headerTop">
          <div>
            <h1 className="dashboard-pedidosweb__title" data-testid="dashboardOperativo.titulo">
              {t('dashboard.title')}
            </h1>
            <p className="dashboard-pedidosweb__subtitle">{t('dashboard.subtitle')}</p>
            {updatedAtLabel ? (
              <p className="dashboard-pedidosweb__meta" data-testid="dashboardOperativo.actualizado">
                {t('dashboard.updatedAt', { value: updatedAtLabel })}
              </p>
            ) : null}
          </div>
          <div className="dashboard-pedidosweb__actions">
            <Button
              type="default"
              stylingMode="outlined"
              text={t('dashboard.refresh')}
              onClick={() => {
                void loadDashboard();
              }}
              elementAttr={{ 'data-testid': 'dashboardOperativo.refresh' }}
            />
          </div>
        </div>
        <div className="dashboard-pedidosweb__quickLinks" data-testid="dashboardOperativo.quickLinks">
          <Button
            stylingMode="outlined"
            text={t('dashboard.linkPresupuestos')}
            onClick={() => navigate('/presupuestos/ingresados')}
            elementAttr={{ 'data-testid': 'nav-presupuestos-ingresados' }}
          />
          <Button
            stylingMode="outlined"
            text={t('dashboard.linkPedidos')}
            onClick={() => navigate('/pedidos/ingresados')}
            elementAttr={{ 'data-testid': 'nav-pedidos-ingresados' }}
          />
          <Button
            stylingMode="outlined"
            text={t('dashboard.linkPedidosPendientes')}
            onClick={() => navigate('/pedidos/pendientes')}
            elementAttr={{ 'data-testid': 'nav-pedidos-pendientes' }}
          />
        </div>
      </header>

      {loadError ? (
        <div className="dashboard-error" role="alert" data-testid="dashboardLoadError">
          {loadError}
        </div>
      ) : null}

      {isLoading ? <div className="dashboard-loading-block">{t('dashboard.loading')}</div> : null}

      {!loadError && !isLoading ? (
        <>
          <div className="dashboard-pedidosweb__groups">
            {kpiGroups.map((group) => (
              <section
                key={group.key}
                className={`dashboard-kpi-group ${group.accentClass}`}
                data-testid={`dashboardOperativo.grupo.${group.key}`}
              >
                <h2 className="dashboard-kpi-group__title">{group.title}</h2>
                <div className="dashboard-kpi-cards">
                  {group.cards.map((card) => (
                    <article
                      key={card.testId}
                      data-testid={card.testId}
                      className={[
                        'dashboard-kpi-card',
                        card.variant === 'amount' ? 'dashboard-kpi-card--amount' : '',
                      ]
                        .filter(Boolean)
                        .join(' ')}
                    >
                      <span className="dashboard-kpi-label">{card.label}</span>
                      <span className="dashboard-kpi-value">{card.value}</span>
                    </article>
                  ))}
                </div>
              </section>
            ))}
          </div>

          <section
            className="dashboard-pedidosweb__topSection"
            data-testid="dashboardOperativo.topClientes"
          >
            <h3>{t('dashboard.section.topClientes')}</h3>
            <div className="dashboard-pedidosweb__topCards">
              <article className="dashboard-top-card" data-testid="dashboardTopClientePresupuestos">
                <span className="dashboard-top-card__label">{t('dashboard.kpi.topClientePresupuestos')}</span>
                {topPresupuestos?.razon_social ? (
                  <>
                    <span className="dashboard-top-card__name">{topPresupuestos.razon_social}</span>
                    <span className="dashboard-top-card__amount">
                      {formatAmount(topPresupuestos.importe ?? 0, currencySymbol)}
                    </span>
                  </>
                ) : (
                  <span className="dashboard-top-card__empty">{t('dashboard.emptyTopClient')}</span>
                )}
              </article>
              <article className="dashboard-top-card" data-testid="dashboardTopClientePedidos">
                <span className="dashboard-top-card__label">{t('dashboard.kpi.topClientePedidos')}</span>
                {topPedidos?.razon_social ? (
                  <>
                    <span className="dashboard-top-card__name">{topPedidos.razon_social}</span>
                    <span className="dashboard-top-card__amount">
                      {formatAmount(topPedidos.importe ?? 0, currencySymbol)}
                    </span>
                  </>
                ) : (
                  <span className="dashboard-top-card__empty">{t('dashboard.emptyTopClient')}</span>
                )}
              </article>
            </div>
          </section>
        </>
      ) : null}
    </section>
  );
}
