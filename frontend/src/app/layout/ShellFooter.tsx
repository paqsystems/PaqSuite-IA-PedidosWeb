import { appVersion } from '../../shared/config/appVersion';

type ShellFooterProps = {
  userLabel: string;
};

export function ShellFooter({ userLabel }: ShellFooterProps) {
  return (
    <footer className="shellFooter" data-testid="shellFooter">
      <span>PaqSuite PedidosWeb v{appVersion}</span>
      <span className="shellFooterSession" data-testid="shell-footer-session">
        Sesion: {userLabel}
      </span>
      <span>PaqSuite IA</span>
    </footer>
  );
}
