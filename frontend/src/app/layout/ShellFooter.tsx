import { useTranslation } from 'react-i18next';
import { appVersion } from '../../shared/config/appVersion';

type ShellFooterProps = {
  userLabel: string;
};

export function ShellFooter({ userLabel }: ShellFooterProps) {
  const { t } = useTranslation();

  return (
    <footer className="shellFooter" data-testid="shellFooter">
      <span>{t('shell.footer.version', { version: appVersion })}</span>
      <span className="shellFooterSession" data-testid="shell-footer-session">
        {t('shell.footer.session', { user: userLabel })}
      </span>
      <span>{t('shell.footer.brand')}</span>
    </footer>
  );
}
