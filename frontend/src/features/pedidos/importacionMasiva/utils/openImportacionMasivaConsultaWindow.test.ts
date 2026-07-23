import { afterEach, describe, expect, it, vi } from 'vitest';
import { openImportacionMasivaConsultaWindow } from './openImportacionMasivaConsultaWindow';

describe('openImportacionMasivaConsultaWindow', () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('abre solapa y anula opener sin usar feature noopener', () => {
    const fakeWindow = { opener: window } as unknown as Window;
    const openSpy = vi.spyOn(window, 'open').mockReturnValue(fakeWindow);

    const opened = openImportacionMasivaConsultaWindow('http://localhost:3010/pedidos/carga?imConsult=x');

    expect(opened).toBe(true);
    expect(openSpy).toHaveBeenCalledWith('http://localhost:3010/pedidos/carga?imConsult=x', '_blank');
    expect(openSpy.mock.calls[0]?.[2]).toBeUndefined();
    expect(fakeWindow.opener).toBeNull();
  });

  it('retorna false solo cuando el popup realmente fue bloqueado', () => {
    vi.spyOn(window, 'open').mockReturnValue(null);

    expect(openImportacionMasivaConsultaWindow('http://localhost:3010/pedidos/carga')).toBe(false);
  });
});
