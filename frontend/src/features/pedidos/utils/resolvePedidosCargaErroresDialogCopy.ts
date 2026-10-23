import type { TFunction } from 'i18next';
import { resolveGrabacionErrorMessages } from './resolveGrabacionErrorMessages';

export type PedidosCargaErroresDialogContext =
  | 'grabacion'
  | 'copia'
  | 'cabecera'
  | 'comprobante'
  | 'clientes';

type PedidosCargaErroresDialogCopy = {
  titleKey: string;
  introKey: string;
  testId: string;
  navigateBackOnClose: boolean;
};

export function resolvePedidosCargaErroresDialogCopy(
  context: PedidosCargaErroresDialogContext,
): PedidosCargaErroresDialogCopy {
  switch (context) {
    case 'copia':
      return {
        titleKey: 'pedidos.carga.erroresCopiaTitulo',
        introKey: 'pedidos.carga.erroresCopiaIntro',
        testId: 'dialog-errores-copia',
        navigateBackOnClose: true,
      };
    case 'cabecera':
      return {
        titleKey: 'pedidos.carga.erroresCargaCabeceraTitulo',
        introKey: 'pedidos.carga.erroresCargaCabeceraIntro',
        testId: 'dialog-errores-cabecera',
        navigateBackOnClose: false,
      };
    case 'comprobante':
      return {
        titleKey: 'pedidos.carga.erroresCargaComprobanteTitulo',
        introKey: 'pedidos.carga.erroresCargaComprobanteIntro',
        testId: 'dialog-errores-comprobante',
        navigateBackOnClose: true,
      };
    case 'clientes':
      return {
        titleKey: 'pedidos.carga.erroresCargaClientesTitulo',
        introKey: 'pedidos.carga.erroresCargaClientesIntro',
        testId: 'dialog-errores-clientes',
        navigateBackOnClose: false,
      };
    default:
      return {
        titleKey: 'pedidos.carga.erroresGrabacionTitulo',
        introKey: 'pedidos.carga.erroresGrabacionIntro',
        testId: 'dialog-errores-grabacion',
        navigateBackOnClose: false,
      };
  }
}

export function resolveCargaErrorDialogMessages(
  error: unknown,
  t: TFunction,
  fallbackKey: string,
): string[] {
  const messages = resolveGrabacionErrorMessages(error, t);
  return messages.length > 0 ? messages : [t(fallbackKey)];
}
