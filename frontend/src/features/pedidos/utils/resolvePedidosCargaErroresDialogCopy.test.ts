import { describe, expect, it } from 'vitest';
import { resolvePedidosCargaErroresDialogCopy } from './resolvePedidosCargaErroresDialogCopy';

describe('resolvePedidosCargaErroresDialogCopy', () => {
  it('copia y comprobante vuelven atras al cerrar el modal', () => {
    expect(resolvePedidosCargaErroresDialogCopy('copia').navigateBackOnClose).toBe(true);
    expect(resolvePedidosCargaErroresDialogCopy('comprobante').navigateBackOnClose).toBe(true);
  });

  it('cabecera y clientes dejan al usuario en la pantalla de carga', () => {
    expect(resolvePedidosCargaErroresDialogCopy('cabecera').navigateBackOnClose).toBe(false);
    expect(resolvePedidosCargaErroresDialogCopy('clientes').navigateBackOnClose).toBe(false);
  });

  it('asigna testId distinto por contexto', () => {
    expect(resolvePedidosCargaErroresDialogCopy('cabecera').testId).toBe('dialog-errores-cabecera');
    expect(resolvePedidosCargaErroresDialogCopy('comprobante').testId).toBe(
      'dialog-errores-comprobante',
    );
  });
});
